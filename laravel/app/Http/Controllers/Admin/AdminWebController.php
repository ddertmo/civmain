<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CitizenReport;
use App\Models\GovernmentOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminWebController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // Auth
    // ─────────────────────────────────────────────────────────────────────

    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Map password → password_hash field via Auth
        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        Auth::user()->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────────────────

    public function dashboard(Request $request): View
    {
        // Shared with the API summary: one grouped query, office-scoped.
        $stats = CitizenReport::statusCounts($request->user());

        $recentReports = CitizenReport::with('assignedOffice')
            ->visibleTo($request->user())
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $announcements = Announcement::published()
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReports', 'announcements'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Citizen Reports
    // ─────────────────────────────────────────────────────────────────────

    public function reportsIndex(Request $request): View
    {
        $query = CitizenReport::with(['citizen', 'assignedOffice'])
            ->visibleTo($request->user())
            ->orderBy('created_at', 'desc');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('barangay')) $query->where('barangay', $request->barangay);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('reference_number', 'like', "%{$s}%")
                                      ->orWhere('concern', 'like', "%{$s}%")
                                      ->orWhere('barangay', 'like', "%{$s}%"));
        }

        $reports = $query->paginate(25)->withQueryString();
        return view('admin.citizen-reports.index', compact('reports'));
    }

    public function reportsShow(Request $request, int $id): View
    {
        $report  = CitizenReport::with(['citizen', 'assignedOffice', 'activities'])
            ->visibleTo($request->user())
            ->findOrFail($id);
        $offices = GovernmentOffice::where('is_active', true)->orderBy('name')->get();
        return view('admin.citizen-reports.show', compact('report', 'offices'));
    }

    public function reportsValidate(int $id): RedirectResponse
    {
        $report = CitizenReport::findOrFail($id);
        if (! in_array($report->status, [CitizenReport::STATUS_SUBMITTED, CitizenReport::STATUS_PENDING_VALIDATION])) {
            return back()->with('error', 'Only Submitted or Pending Validation reports can be approved.');
        }
        try {
            $report->transitionTo(CitizenReport::STATUS_APPROVED); // also sets is_public
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Report approved and published to community map.');
    }

    public function reportsAssign(Request $request, int $id): RedirectResponse
    {
        $request->validate(['office_id' => 'required|exists:government_offices,id']);
        $report = CitizenReport::findOrFail($id);
        if ($report->status !== CitizenReport::STATUS_APPROVED) {
            return back()->with('error', 'Only approved reports can be assigned to an office.');
        }
        if ($report->assigned_office_id !== null) {
            return back()->with('error', 'This report is already assigned to an office.');
        }
        $office = GovernmentOffice::findOrFail($request->office_id);
        try {
            // Office + status in one locked write (see CitizenReport::transitionTo).
            $report->transitionTo(CitizenReport::STATUS_ASSIGNED, null, $office->name, [
                'assigned_office_id' => $office->id,
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', 'This report was changed by someone else. Please reload it.');
        }
        return back()->with('success', "Report assigned to {$office->name}.");
    }

    public function reportsStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status'      => 'required|in:' . implode(',', CitizenReport::STATUS_ORDER),
            'description' => $request->status === CitizenReport::STATUS_REJECTED
                ? 'required|string|max:500'
                : 'nullable|string|max:500',
        ]);
        $report = CitizenReport::findOrFail($id);

        if ($request->status === CitizenReport::STATUS_REJECTED
            && ! in_array($report->status, CitizenReport::REJECTABLE_STATUSES, true)) {
            return back()->with('error', 'A report can only be rejected before it is assigned to an office.');
        }

        // Same workflow rules as the API (CitizenReport::TRANSITIONS).
        if ($report->status !== $request->status && ! $report->canTransitionTo($request->status)) {
            return back()->with('error', "A report cannot move from '{$report->status}' to '{$request->status}'.");
        }

        try {
            $report->transitionTo($request->status, $request->description);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Status updated to: ' . $request->status);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Map
    // ─────────────────────────────────────────────────────────────────────

    public function map(Request $request): View
    {
        // Same filters as the API map: office-scoped, public only, bounded.
        $reports = CitizenReport::with('assignedOffice')
            ->visibleTo($request->user())
            ->where('is_public', true)
            ->latest()
            ->limit(2000)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereIn('status', [
                CitizenReport::STATUS_APPROVED,
                CitizenReport::STATUS_ASSIGNED,
                CitizenReport::STATUS_IN_PROGRESS,
                CitizenReport::STATUS_RESOLVED,
            ])
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'referenceNumber' => $r->reference_number,
                'category'        => $r->category,
                'concern'         => $r->concern,
                'status'          => $r->status,
                'barangay'        => $r->barangay,
                'lat'             => $r->lat,
                'lng'             => $r->lng,
                'assignedOffice'  => $r->assignedOffice?->name,
            ]);

        return view('admin.map', compact('reports'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Offices
    // ─────────────────────────────────────────────────────────────────────

    public function officesIndex(): View
    {
        $offices = GovernmentOffice::where('is_active', true)->orderBy('name')->get();
        return view('admin.offices.index', compact('offices'));
    }

    public function officesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            // Only offices with a staff login role can work reports.
            'abbreviation' => 'required|string|max:20|in:' . implode(',', CitizenReport::OFFICE_ROLES),
            'handles'      => 'required|in:Infrastructure,Environment,Both',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:191',
            'address'      => 'nullable|string|max:500',
        ]);
        GovernmentOffice::create($validated);
        return back()->with('success', 'Office created successfully.');
    }

    public function officesUpdate(Request $request, int $id): RedirectResponse
    {
        $office    = GovernmentOffice::findOrFail($id);
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            // Only offices with a staff login role can work reports.
            'abbreviation' => 'required|string|max:20|in:' . implode(',', CitizenReport::OFFICE_ROLES),
            'handles'      => 'required|in:Infrastructure,Environment,Both',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:191',
            'address'      => 'nullable|string|max:500',
        ]);
        $office->update($validated);
        return back()->with('success', 'Office updated.');
    }

    public function officesDestroy(int $id): RedirectResponse
    {
        GovernmentOffice::findOrFail($id)->update(['is_active' => false]);
        return back()->with('success', 'Office deactivated.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Announcements
    // ─────────────────────────────────────────────────────────────────────

    public function announcementsIndex(): View
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->get();
        return view('admin.announcements.index', compact('announcements'));
    }

    public function announcementsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'is_published' => 'boolean',
        ]);
        $validated['published_at'] = now();
        Announcement::create($validated);
        return back()->with('success', 'Announcement posted.');
    }

    public function announcementsUpdate(Request $request, int $id): RedirectResponse
    {
        $announcement = Announcement::findOrFail($id);
        $validated    = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'is_published' => 'boolean',
        ]);
        $announcement->update($validated);
        return back()->with('success', 'Announcement updated.');
    }

    public function announcementsDestroy(int $id): RedirectResponse
    {
        Announcement::findOrFail($id)->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}

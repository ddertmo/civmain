<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\GovernmentOffice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCitizenReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/citizen-reports
    // Full list with filters for admin panel.
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = CitizenReport::with(['citizen', 'assignedOffice', 'activities'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('barangay')) {
            $query->where('barangay', $request->barangay);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                  ->orWhere('concern', 'like', "%{$s}%")
                  ->orWhere('barangay', 'like', "%{$s}%");
            });
        }

        // The admin HTML pages pass ?limit=200/500 expecting all matching rows
        // in one shot; the Blade admin defaults to 25 per page. Honor both
        // without letting a caller page through unbounded results.
        $perPage = (int) ($request->input('per_page', $request->input('limit', 25)));
        $perPage = max(1, min($perPage, 500));

        $reports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => [
                'data'          => $reports->getCollection()->map(fn($r) => $r->toApiArray())->values(),
                'current_page'  => $reports->currentPage(),
                'last_page'     => $reports->lastPage(),
                'per_page'      => $reports->perPage(),
                'total'         => $reports->total(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/citizen-reports/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $report = CitizenReport::with(['citizen', 'assignedOffice', 'activities'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $report->toApiArray()]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/admin/citizen-reports/{id}/validate
    // Admin approves: Submitted / Pending Validation → Approved (is_public=true)
    // ─────────────────────────────────────────────────────────────────────
    public function validate(int $id): JsonResponse
    {
        $report = CitizenReport::findOrFail($id);

        if ($report->status !== CitizenReport::STATUS_SUBMITTED
            && $report->status !== CitizenReport::STATUS_PENDING_VALIDATION) {
            return response()->json([
                'success' => false,
                'message' => 'Report is not in a validatable state.',
            ], 422);
        }

        $report->update(['is_public' => true]);

        // Persist the APPROVED status — this is the transition every screen
        // downstream (map, assign office, Android, monitoring) keys off.
        $report->transitionTo(CitizenReport::STATUS_APPROVED);

        return response()->json([
            'success' => true,
            'message' => 'Report approved and published to community map.',
            'data'    => $report->fresh(['activities', 'assignedOffice'])->toApiArray(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/admin/citizen-reports/{id}/assign
    // Body: { office_id }
    // Only APPROVED reports that have not been assigned yet may be assigned.
    // ─────────────────────────────────────────────────────────────────────
    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'office_id' => 'required|exists:government_offices,id',
        ]);

        $report = CitizenReport::findOrFail($id);
        $office = GovernmentOffice::findOrFail($request->office_id);

        if ($report->status !== CitizenReport::STATUS_APPROVED) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved reports can be assigned to an office.',
            ], 422);
        }

        if ($report->assigned_office_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This report is already assigned to an office.',
            ], 422);
        }

        $report->update(['assigned_office_id' => $office->id]);
        $report->transitionTo(CitizenReport::STATUS_ASSIGNED, null, $office->name);

        return response()->json([
            'success' => true,
            'message' => "Report assigned to {$office->name}.",
            'data'    => $report->fresh(['activities', 'assignedOffice'])->toApiArray(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/admin/citizen-reports/{id}/status
    // Body: { status, description (optional) }
    // Admin can move status to any value in the flow, except that a rejection
    // is only legal while the report is still awaiting validation.
    // ─────────────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status'      => ['required', Rule::in(CitizenReport::STATUS_ORDER)],
            // A rejection without a reason gives the reporter nothing to act
            // on, so the note is mandatory exactly when it matters.
            'description' => [
                'required_if:status,' . CitizenReport::STATUS_REJECTED,
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $report = CitizenReport::findOrFail($id);

        if ($request->status === CitizenReport::STATUS_REJECTED
            && ! in_array($report->status, CitizenReport::REJECTABLE_STATUSES, true)) {
            return response()->json([
                'success' => false,
                'message' => 'A report can only be rejected before it is assigned to an office.',
            ], 422);
        }

        $report->transitionTo($request->status, $request->description);

        return response()->json([
            'success' => true,
            'message' => 'Status updated.',
            'data'    => $report->fresh(['activities', 'assignedOffice'])->toApiArray(),
        ]);
    }

    // ---------------------------------------------------------------------
    // POST /api/admin/citizen-reports/{id}/priority
    // Only authorized personnel of the office currently assigned to the
    // report may change its priority. TAPik/Super Admin and personnel of
    // other offices are denied on the server.
    // ---------------------------------------------------------------------
    public function updatePriority(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High'])],
        ]);

        $report = CitizenReport::with('assignedOffice')->findOrFail($id);
        $user   = $request->user();

        if (! in_array($user->role, ['ceo', 'cenro'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Priority updates are restricted to the assigned office.',
            ], 403);
        }

        $expectedOffice = $user->role === 'ceo' ? 'CEO' : 'CENRO';
        if (! $report->assignedOffice
            || $report->assignedOffice->abbreviation !== $expectedOffice) {
            return response()->json([
                'success' => false,
                'message' => 'You may only change priority for reports assigned to your office.',
            ], 403);
        }

        $report->update(['priority' => $request->priority]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated.',
            'data'    => $report->fresh(['activities', 'assignedOffice'])->toApiArray(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/citizen-reports/map
    // Public reports with coordinates for map view.
    // Map shows only reports that have left the validation queue: Approved,
    // Assigned to Office, In Progress, Resolved. Never Submitted / Pending
    // Validation / Rejected.
    // ─────────────────────────────────────────────────────────────────────
    public function map(Request $request): JsonResponse
    {
        $query = CitizenReport::with('assignedOffice')
            ->where('is_public', true)
            ->whereIn('status', [
                CitizenReport::STATUS_APPROVED,
                CitizenReport::STATUS_ASSIGNED,
                CitizenReport::STATUS_IN_PROGRESS,
                CitizenReport::STATUS_RESOLVED,
            ])
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $reports = $query->get()->map(fn($r) => [
            'id'              => $r->id,
            'referenceNumber' => $r->reference_number,
            'category'        => $r->category,
            'concern'         => $r->concern,
            'status'          => $r->status,
            'barangay'        => $r->barangay,
            'lat'             => $r->lat,
            'lng'             => $r->lng,
            'assignedOffice'  => $r->assignedOffice?->name,
            'isPublic'        => $r->is_public,
        ]);

        return response()->json(['success' => true, 'data' => $reports]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/citizen-reports/summary
    // Dashboard stat counts.
    // ─────────────────────────────────────────────────────────────────────
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total'              => CitizenReport::count(),
                'submitted'          => CitizenReport::where('status', CitizenReport::STATUS_SUBMITTED)->count(),
                'pending_validation' => CitizenReport::where('status', CitizenReport::STATUS_PENDING_VALIDATION)->count(),
                'approved'           => CitizenReport::where('status', CitizenReport::STATUS_APPROVED)->count(),
                'assigned'           => CitizenReport::where('status', CitizenReport::STATUS_ASSIGNED)->count(),
                'in_progress'        => CitizenReport::where('status', CitizenReport::STATUS_IN_PROGRESS)->count(),
                'resolved'           => CitizenReport::where('status', CitizenReport::STATUS_RESOLVED)->count(),
            ],
        ]);
    }
}

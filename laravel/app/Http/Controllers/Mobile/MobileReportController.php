<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\ReportActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MobileReportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // GET /api/mobile/reports
    // Returns the authenticated citizen's own reports.
    // ?status=Submitted|Pending Validation|Assigned to Office|In Progress|Resolved
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $citizen = $request->user('citizen');
        // 'citizen' eager-loaded: toApiArray() reads it, which was one extra
        // query per report (N+1). Bounded to the newest 200 reports.
        $query   = CitizenReport::with(['activities', 'assignedOffice', 'citizen'])
                                ->where('citizen_id', $citizen->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->limit(200)->get();

        return response()->json([
            'success' => true,
            'data'    => $reports->map(fn($r) => $r->toApiArray())->values(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/mobile/reports
    // Submit a new report.
    // Multipart form — photo is optional.
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $citizen = $request->user('citizen');

        $validated = $request->validate([
            'category'          => ['required', Rule::in(['Infrastructure', 'Environment', 'Others'])],
            'concern'           => 'required|string|max:100',
            'description'       => 'nullable|string|max:1000',
            'street'            => 'nullable|string|max:255',
            'purok'             => 'nullable|string|max:100',
            // Official list only — a free-text value (or the app's old
            // 'Unknown Barangay' fallback) routed reports wrongly and skewed
            // the per-barangay analytics.
            'barangay'          => ['required', 'string', Rule::in(CitizenReport::BARANGAYS)],
            'city'              => 'nullable|string|max:100',
            'province'          => 'nullable|string|max:100',
            'landmark'          => 'nullable|string|max:255',
            'lat'               => 'nullable|numeric|between:-90,90',
            'lng'               => 'nullable|numeric|between:-180,180',
            'severity'          => ['nullable', Rule::in(['Minor', 'Moderate', 'Severe'])],
            'photo'             => 'nullable|image|max:5120', // 5 MB
        ]);

        // Handle photo upload
        $photoUrl  = null;
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('report-photos', 'public');
            $photoUrl  = Storage::disk('public')->url($photoPath);
        }

        try {
            // One transaction: either the report, its first activity and its
            // Pending Validation step all exist, or none of them do.
            $report = DB::transaction(fn () => $this->createReport($citizen, $validated, $photoUrl));
        } catch (\Throwable $e) {
            // Don't leave an orphaned upload behind when the insert fails.
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $e;
        }

        // Reload with relationships
        $report->load(['activities', 'assignedOffice']);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully.',
            'data'    => $report->toApiArray(),
        ], 201);
    }

    /**
     * Insert the report (with a collision-free reference number), its
     * "Submitted" activity and the move to Pending Validation.
     */
    private function createReport($citizen, array $validated, ?string $photoUrl): CitizenReport
    {
        $report = CitizenReport::createWithReferenceNumber([
            'citizen_id'        => $citizen->id,
            'category'          => $validated['category'],
            'concern'           => $validated['concern'],
            'description'       => $validated['description'] ?? null,
            'street'            => $validated['street'] ?? null,
            'purok'             => $validated['purok'] ?? null,
            'barangay'          => $validated['barangay'],
            'city'              => $validated['city'] ?? 'Digos City',
            'province'          => $validated['province'] ?? 'Davao del Sur',
            'landmark'          => $validated['landmark'] ?? null,
            'lat'               => $validated['lat'] ?? null,
            'lng'               => $validated['lng'] ?? null,
            'severity'          => $validated['severity'] ?? 'Moderate',
            'photo_url'         => $photoUrl,
            'status'            => CitizenReport::STATUS_SUBMITTED,
            'is_public'         => false,
        ]);

        // Log initial "Submitted" activity
        ReportActivity::create([
            'citizen_report_id' => $report->id,
            'title'             => 'Report Submitted',
            'description'       => 'Your report has been successfully submitted.',
            'status'            => CitizenReport::STATUS_SUBMITTED,
            'created_at'        => now(),
        ]);

        // Auto-transition to Pending Validation
        $report->transitionTo(CitizenReport::STATUS_PENDING_VALIDATION);

        return $report;
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/mobile/reports/community
    // All validated/public reports for the community map.
    // Only reports past validation (Approved+) — never Submitted / Pending
    // Validation / Rejected, even if is_public has been set.
    // ─────────────────────────────────────────────────────────────────────
    public function community(Request $request): JsonResponse
    {
        $reports = CitizenReport::with(['assignedOffice'])
            ->where('is_public', true)
            ->whereIn('status', [
                CitizenReport::STATUS_APPROVED,
                CitizenReport::STATUS_ASSIGNED,
                CitizenReport::STATUS_IN_PROGRESS,
                CitizenReport::STATUS_RESOLVED,
            ])
            ->orderBy('created_at', 'desc')
            // Public, unauthenticated endpoint: never return an unbounded set
            // (easy to use for loading the server). Newest 500 is plenty for
            // the community feed/map.
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $reports->map(fn($r) => [
                'id'              => (string) $r->id,
                'referenceNumber' => $r->reference_number,
                'category'        => $r->category,
                'issue'           => $r->concern,
                'description'     => $r->description ?? '',
                'barangay'        => "Barangay {$r->barangay}, Digos City",
                'status'          => $r->status,
                'severity'        => $r->severity,
                'submittedAt'     => $r->created_at?->toIso8601String(),
                'resolvedAt'      => $r->resolved_at?->toIso8601String(),
                'imageUrl'        => $r->photo_url,
                'latitude'        => $r->lat,
                'longitude'       => $r->lng,
                'assignedOffice'  => $r->assignedOffice?->name,
                'activityLog'     => [],
            ])->values(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/mobile/reports/{id}
    // Single report with full activity log and assigned office.
    // ─────────────────────────────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $citizen = $request->user('citizen');

        $report = CitizenReport::with(['activities', 'assignedOffice'])
            ->where('citizen_id', $citizen->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $report->toApiArray(),
        ]);
    }
}

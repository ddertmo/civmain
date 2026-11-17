<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CitizenReport extends Model
{
    protected $table = 'citizen_reports';

    protected $fillable = [
        'reference_number',
        'citizen_id',
        'category',
        'concern',
        'description',
        'street',
        'purok',
        'barangay',
        'city',
        'province',
        'landmark',
        'lat',
        'lng',
        'severity',
        'priority',
        'photo_url',
        'status',
        'assigned_office_id',
        'resolved_at',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'lat'         => 'float',
            'lng'         => 'float',
            'is_public'   => 'boolean',
            'resolved_at' => 'datetime',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    // ── Status constants (match Flutter app exactly) ───────────────────────
    const STATUS_SUBMITTED          = 'Submitted';
    const STATUS_PENDING_VALIDATION = 'Pending Validation';
    const STATUS_APPROVED           = 'Approved';
    const STATUS_ASSIGNED           = 'Assigned to Office';
    const STATUS_IN_PROGRESS        = 'In Progress';
    const STATUS_RESOLVED           = 'Resolved';
    const STATUS_REJECTED           = 'Rejected';

    const STATUS_ORDER = [
        self::STATUS_SUBMITTED,
        self::STATUS_PENDING_VALIDATION,
        self::STATUS_APPROVED,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
        self::STATUS_REJECTED,
    ];

    /**
     * Statuses an admin may reject from. Rejection is only possible before a
     * report is handed to an office — afterwards the fix is a reassignment,
     * not a rejection.
     */
    const REJECTABLE_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_PENDING_VALIDATION,
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(Citizen::class, 'citizen_id');
    }

    public function assignedOffice(): BelongsTo
    {
        return $this->belongsTo(GovernmentOffice::class, 'assigned_office_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ReportActivity::class, 'citizen_report_id')
                    ->orderBy('created_at', 'asc');
    }

    public function citizenNotifications(): HasMany
    {
        return $this->hasMany(CitizenNotification::class, 'citizen_report_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Generate the next reference number in CW-YEAR-XXXXX format.
     * e.g. CW-2026-00125
     */
    public static function generateReferenceNumber(): string
    {
        // Placeholder only. The real number is assigned by
        // createWithReferenceNumber() from the row's auto-increment id. The
        // old "count this year's rows + 1" scheme collided under concurrent
        // submissions and after any delete (UNIQUE violation → HTTP 500).
        // 20 chars max (column is varchar(20)).
        return 'TMP-' . substr(bin2hex(random_bytes(8)), 0, 16);
    }

    /**
     * Insert a report and give it a unique, permanent reference number
     * derived from its auto-increment id: CW-{year}-{id, zero-padded to 5}.
     * Ids never repeat, so neither do reference numbers — no race, no
     * collision after deletes. Runs in a transaction so a report is never
     * left with its placeholder number.
     */
    public static function createWithReferenceNumber(array $attributes): static
    {
        return DB::transaction(function () use ($attributes) {
            $report = static::create(
                ['reference_number' => static::generateReferenceNumber()] + $attributes
            );

            $report->reference_number = sprintf('CW-%d-%05d', now()->year, $report->id);
            $report->save();

            return $report;
        });
    }

    /**
     * Log a status transition and notify the citizen.
     */
    public function transitionTo(string $newStatus, ?string $description = null, ?string $officeName = null): void
    {
        // ── Idempotency guard ───────────────────────────────────────────────
        // Skip creating a duplicate activity if the report is already in this
        // exact status. Only bypass the guard for STATUS_PENDING_VALIDATION
        // when called from validate() so an explicit re-validation is allowed,
        // but never create a second activity row for the same transition.
        if ($this->status === $newStatus) {
            // Status hasn't changed — nothing to log, nothing to notify.
            return;
        }

        $this->update(['status' => $newStatus]);

        // Any report the city has accepted past validation belongs on the
        // public community map, even if the admin never pressed
        // "Validate Report" (validate() is what usually sets is_public).
        // Approving a report is the first step of that: it persists the
        // APPROVED status and makes the report public in one transaction.
        if (in_array($newStatus, [self::STATUS_APPROVED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED], true)
            && ! $this->is_public) {
            $this->update(['is_public' => true]);
        }

        // validate() sets is_public = true, so a report that is rejected after
        // validation would otherwise stay visible on the community feed.
        if ($newStatus === self::STATUS_REJECTED && $this->is_public) {
            $this->update(['is_public' => false]);
        }

        if ($newStatus === self::STATUS_RESOLVED) {
            $this->update(['resolved_at' => now()]);
        }

        // Build activity entry title
        $title = $newStatus;
        $desc  = $description ?? $this->defaultActivityDescription($newStatus, $officeName);

        ReportActivity::create([
            'citizen_report_id' => $this->id,
            'title'             => $title,
            'description'       => $desc,
            'status'            => $newStatus,
        ]);

        // Notify the citizen
        CitizenNotification::send(
            citizenId:       $this->citizen_id,
            title:           $title,
            message:         $this->buildNotificationMessage($newStatus, $officeName, $description),
            type:            'status_update',
            reportId:        $this->id,
            referenceNumber: $this->reference_number,
            status:          $newStatus,
        );
    }

    private function defaultActivityDescription(string $status, ?string $officeName): string
    {
        return match ($status) {
            self::STATUS_SUBMITTED          => 'Your report has been successfully submitted.',
            self::STATUS_PENDING_VALIDATION => 'Your report is now waiting for validation by the administrator.',
            self::STATUS_APPROVED           => 'Your report has been approved and published to the community map.',
            self::STATUS_ASSIGNED           => 'Your report has been assigned to ' . ($officeName ?? 'a city office') . '.',
            self::STATUS_IN_PROGRESS        => 'Work is currently in progress.',
            self::STATUS_RESOLVED           => 'Your report has been resolved. Thank you for helping improve Digos City.',
            self::STATUS_REJECTED           => 'Your report was rejected by the administrator.',
            default                         => 'Status updated.',
        };
    }

    private function buildNotificationMessage(string $status, ?string $officeName, ?string $description = null): string
    {
        return match ($status) {
            self::STATUS_SUBMITTED          => "{$this->reference_number} has been submitted successfully.",
            self::STATUS_PENDING_VALIDATION => "{$this->reference_number} is now under review by the administrator.",
            self::STATUS_APPROVED           => "{$this->reference_number} has been approved and published to the community map.",
            self::STATUS_ASSIGNED           => "{$this->reference_number} has been assigned to " . ($officeName ?? 'a city office') . '.',
            self::STATUS_IN_PROGRESS        => "{$this->reference_number} — Work is now in progress.",
            self::STATUS_RESOLVED           => "{$this->reference_number} — {$this->concern} has been resolved.",
            // The admin's reason is the whole point of a rejection note.
            self::STATUS_REJECTED           => "{$this->reference_number} — Rejected. "
                . ($description !== null && $description !== ''
                    ? $description
                    : 'No reason was given by the administrator.'),
            default                         => "{$this->reference_number} status updated to {$status}.",
        };
    }

    /**
     * API response shape expected by the Flutter IncidentReport model.
     */
    public function toApiArray(): array
    {
        return [
            'id'              => (string) $this->id,
            'referenceNumber' => $this->reference_number,
            'category'        => $this->category,
            'issue'           => $this->concern,
            'description'     => $this->description ?? '',
            'barangay'        => $this->barangay,
            'status'          => $this->status,
            'severity'        => $this->severity,
            'priority'        => $this->priority ?? 'Medium',
            'submittedAt'     => $this->created_at?->toIso8601String(),
            'resolvedAt'      => $this->resolved_at?->toIso8601String(),
            'imageUrl'        => $this->photo_url,
            'latitude'        => $this->lat,
            'longitude'       => $this->lng,
            'assignedOffice'  => $this->assignedOffice?->name,
            'street'          => $this->street,
            'purok'           => $this->purok,
            'city'            => $this->city,
            'province'        => $this->province,
            'landmark'        => $this->landmark,
            'activityLog'     => $this->activities->map(fn($a) => $a->toApiArray())->values()->toArray(),
            // ── Citizen (reporter) info for admin panels ───────────────
            'citizen'         => $this->citizen ? [
                'fullName'    => $this->citizen->full_name,
                'phoneNumber' => $this->citizen->phone,
                'street'      => $this->citizen->street,
                'purok'       => $this->citizen->purok,
                'barangay'    => $this->citizen->barangay,
                'city'        => $this->citizen->city,
                'province'    => $this->citizen->province,
            ] : null,
        ];
    }
}

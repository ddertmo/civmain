<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Allowed status transitions (from → [to …]). Enforced by transitionTo(),
     * so no caller — API, Blade, office pages — can move a report backwards
     * or skip validation. 'Assigned to Office' additionally requires an
     * assigned office (set by the assign actions). Resolved/Rejected are final.
     */
    const TRANSITIONS = [
        self::STATUS_SUBMITTED          => [self::STATUS_PENDING_VALIDATION, self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_PENDING_VALIDATION => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED           => [self::STATUS_ASSIGNED],
        self::STATUS_ASSIGNED           => [self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED],
        self::STATUS_IN_PROGRESS        => [self::STATUS_RESOLVED],
        self::STATUS_RESOLVED           => [],
        self::STATUS_REJECTED           => [],
    ];

    /** Office role → government_offices.abbreviation. */
    const OFFICE_ROLES = ['ceo' => 'CEO', 'cenro' => 'CENRO'];

    /**
     * Official 26 barangays of Digos City (PSA 2020 / PhilAtlas). Single
     * source of truth for server-side validation; keep in sync with
     * AppStrings.barangays in the Flutter app.
     */
    const BARANGAYS = [
        'Aplaya', 'Balabag', 'Binaton', 'Cogon', 'Colorado', 'Dawis',
        'Dulangan', 'Goma', 'Igpit', 'Kapatagan', 'Kiagot', 'Lungag',
        'Mahayahay', 'Matti', 'Ruparan', 'San Agustin', 'San Jose',
        'San Miguel', 'San Roque', 'Sinawilan', 'Soong', 'Tiguman',
        'Tres de Mayo', 'Zone 1 (Pob.)', 'Zone 2 (Pob.)', 'Zone 3 (Pob.)',
    ];

    // ── Access scope ───────────────────────────────────────────────────────

    /**
     * Reports a staff user may see: office staff (ceo/cenro) only see reports
     * assigned to their own office; the super admin sees everything. Use it
     * for EVERY staff read (lists, detail, map, counts) — previously only the
     * list endpoint applied it, so office staff could open any report.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        $abbr = static::officeAbbreviationFor($user);

        if ($abbr !== null) {
            $query->whereHas('assignedOffice', fn ($q) => $q->where('abbreviation', $abbr));
        } elseif (! $user || $user->role !== 'super_admin') {
            $query->whereRaw('1 = 0'); // unknown role: nothing
        }

        return $query;
    }

    public static function officeAbbreviationFor(?User $user): ?string
    {
        return $user ? (self::OFFICE_ROLES[$user->role] ?? null) : null;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        if (! in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true)) {
            return false;
        }

        return $newStatus !== self::STATUS_ASSIGNED || $this->assigned_office_id !== null;
    }

    /**
     * Status counts in ONE grouped query (was 7-8 COUNT queries per call, and
     * every dashboard missed a different status). Keys are stable for the
     * admin/Blade dashboards.
     */
    public static function statusCounts(?User $user = null): array
    {
        $query = static::query();
        if ($user) {
            $query->visibleTo($user);
        }

        $byStatus = $query->selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status');
        $get = fn (string $s) => (int) ($byStatus[$s] ?? 0);

        return [
            'total'              => (int) $byStatus->sum(),
            'submitted'          => $get(self::STATUS_SUBMITTED),
            'pending_validation' => $get(self::STATUS_PENDING_VALIDATION),
            'approved'           => $get(self::STATUS_APPROVED),
            'assigned'           => $get(self::STATUS_ASSIGNED),
            'in_progress'        => $get(self::STATUS_IN_PROGRESS),
            'resolved'           => $get(self::STATUS_RESOLVED),
            'rejected'           => $get(self::STATUS_REJECTED),
        ];
    }

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
    /**
     * Move the report to $newStatus, log the activity and notify the citizen.
     *
     * Runs in one transaction with the row locked (SELECT … FOR UPDATE), so
     * two concurrent requests can't both apply a transition, and a failure
     * part-way never leaves a status change without its activity/notice.
     * Returns false (no-op) when the report is already in $newStatus; throws
     * InvalidArgumentException when TRANSITIONS doesn't allow the move.
     *
     * $extra lets a caller change other columns in the same locked write
     * (e.g. assign() sets assigned_office_id together with the status).
     */
    public function transitionTo(
        string $newStatus,
        ?string $description = null,
        ?string $officeName = null,
        array $extra = [],
    ): bool {
        return DB::transaction(function () use ($newStatus, $description, $officeName, $extra) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $this->setRawAttributes($locked->getAttributes(), true);

            // Idempotency: already there — nothing to log, nothing to notify.
            if ($this->status === $newStatus) {
                return false;
            }

            $this->forceFill($extra);
            if (! $this->canTransitionTo($newStatus)) {
                throw new \InvalidArgumentException(
                    "A report cannot move from '{$locked->status}' to '{$newStatus}'."
                );
            }

            $updates = ['status' => $newStatus];

            // Accepted past validation → visible on the community map.
            if (in_array($newStatus, [self::STATUS_APPROVED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED], true)) {
                $updates['is_public'] = true;
            }
            // A rejected report must leave the community feed.
            if ($newStatus === self::STATUS_REJECTED) {
                $updates['is_public'] = false;
            }
            $updates['resolved_at'] = $newStatus === self::STATUS_RESOLVED ? now() : null;

            $this->forceFill($updates)->save();

            $title = $newStatus;
            $desc  = $description ?? $this->defaultActivityDescription($newStatus, $officeName);

            ReportActivity::create([
                'citizen_report_id' => $this->id,
                'title'             => $title,
                'description'       => $desc,
                'status'            => $newStatus,
            ]);

            CitizenNotification::send(
                citizenId:       $this->citizen_id,
                title:           $title,
                message:         $this->buildNotificationMessage($newStatus, $officeName, $description),
                type:            'status_update',
                reportId:        $this->id,
                referenceNumber: $this->reference_number,
                status:          $newStatus,
            );

            return true;
        });
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

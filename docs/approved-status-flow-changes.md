# Recent changes — "Approved" status flow fix (report stays on Pending)

- **Date:** 2026-10-08
- **Commit:** `5c5c8b7` on `main`, pushed to `backup` (`dertmo01/civmain`)
- **Changelog:** `docs/changes/2026-10-08-032-approved-status-flow-fix.md`
- **Areas:** Laravel backend + DB, web admin (HTML + Blade), Flutter app

---

## 1. Why approving a report still showed "Pending Validation"

### The one-line answer

**There was no `Approved` status anywhere in the system.** The "Approve Report"
button called the `validate` endpoint, and that endpoint only flipped
`is_public = true` — it kept (or moved) the status at `Pending Validation`.
Every screen downstream just rendered whatever the database said, so after
clicking Approve the report still showed *Pending Validation* everywhere.

### The buggy flow (before)

```
Admin clicks "Approve Report"
        │
        ▼
POST /api/admin/citizen-reports/{id}/validate
        │
        ▼
AdminCitizenReportController::validate()
        │  • sets is_public = true
        │  • ONLY transitions to 'Pending Validation' if it was 'Submitted'
        │  • NEVER sets status to 'Approved'  ← the bug
        ▼
DB row: status = 'Pending Validation',  is_public = 1
        │
        ├── Assign Office  → query looks for "Pending Validation" + "Submitted"  (still shows it, wrong stage)
        ├── Map            → whereIn includes 'Pending Validation'               (pin is amber, wrong stage)
        ├── Monitoring     → page loads status:'Assigned' (typo) → matches nothing
        ├── Android        → renders 'Pending Validation' from the API response
        ▼
Nothing on screen was ever able to reflect the approval
```

### The fix

`validate()` now persists a real **`Approved`** status in the same transaction:

```php
$report->update(['is_public' => true]);
$report->transitionTo(CitizenReport::STATUS_APPROVED);
```

And every layer (DB enum, model, queries, admin pages, Flutter) was aligned to
the new workflow:

```
NEW REPORT → Pending Validation → Approved → (Map + Assign Office)
           → Rejected/History (if rejected)
Approved → Assigned to Office → In Progress → Resolved
```

> **If a report still shows Pending after this fix**: it is an *old* row that
> was `is_public = 1` + `Pending Validation` before the fix. The migration was
> intentionally schema-only (no data backfill), so stale rows stay Pending until
> you press **Approve Report** on them again — that is exactly how `CW-2026-00004`
> is exercised as the approve-flow test case.

---

## 2. The code, before → after

### 2.1 Migration — widen the `status` ENUM

The new migration adds `'Approved'` between `'Pending Validation'` and
`'Assigned to Office'`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * citizen_reports.status is a MySQL ENUM. Adding STATUS_APPROVED in PHP
     * alone is not enough — MySQL refuses values outside the column's list,
     * so every approve would fail. This widens the column to accept
     * 'Approved' between 'Pending Validation' and 'Assigned to Office'.
     */
    public function up(): void
    {
        if (! Schema::hasTable('citizen_reports')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE citizen_reports
            MODIFY status ENUM(
                'Submitted',
                'Pending Validation',
                'Approved',
                'Assigned to Office',
                'In Progress',
                'Resolved',
                'Rejected'
            ) NOT NULL DEFAULT 'Submitted'
        SQL);
    }

    public function down(): void
    {
        if (! Schema::hasTable('citizen_reports')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE citizen_reports
            MODIFY status ENUM(
                'Submitted',
                'Pending Validation',
                'Assigned to Office',
                'In Progress',
                'Resolved',
                'Rejected'
            ) NOT NULL DEFAULT 'Submitted'
        SQL);
    }
};
```

`laravel/database/migrations/2026_10_08_000001_add_approved_status_to_citizen_reports.php`

### 2.2 Model — `CitizenReport` gains the constant + flow order

```php
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
```

Approving now also auto-publishes to the community map (teal pin), like the
later statuses already did:

```php
// Any report the city has accepted past validation belongs on the
// public community map. Approving a report persists the APPROVED status
// and makes the report public in one transaction.
if (in_array($newStatus, [self::STATUS_APPROVED, self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_RESOLVED], true)
    && ! $this->is_public) {
    $this->update(['is_public' => true]);
}
```

Activity + notification messages for the new status:

```php
self::STATUS_APPROVED => 'Your report has been approved and published to the community map.',
// and in buildNotificationMessage():
self::STATUS_APPROVED => "{$this->reference_number} has been approved and published to the community map.",
```

`laravel/app/Models/CitizenReport.php`

### 2.3 Controller — the actual approve endpoint (`validate`)

**Before** (this is why the report stayed on Pending):

```php
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

    // Only transition if not already at Pending Validation
    if ($report->status === CitizenReport::STATUS_SUBMITTED) {
        $report->transitionTo(CitizenReport::STATUS_PENDING_VALIDATION);
    }

    return response()->json([
        'success' => true,
        'message' => 'Report validated and published to community map.',
        'data'    => $report->fresh(['activities', 'assignedOffice'])->toApiArray(),
    ]);
}
```

**After** (approve now persists `Approved`):

```php
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
```

### 2.4 Controller — `assign()` only accepts Approved, unassigned reports

```php
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
```

### 2.5 Map + mobile community — Approved and later, never Pending

Both the admin map and the Android community endpoint now use the same status
filter:

```php
->where('is_public', true)
->whereIn('status', [
    CitizenReport::STATUS_APPROVED,
    CitizenReport::STATUS_ASSIGNED,
    CitizenReport::STATUS_IN_PROGRESS,
    CitizenReport::STATUS_RESOLVED,
])
```

`laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` (`map()`)
`laravel/app/Http/Controllers/Mobile/MobileReportController.php` (`community()`)

### 2.6 Summary / analytics gain an `approved` bucket

```php
'approved' => CitizenReport::where('status', CitizenReport::STATUS_APPROVED)->count(),
```

`laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php`
`laravel/app/Http/Controllers/AnalyticsController.php`

### 2.7 Web admin (HTML prototype + Blade)

`public/js/utils.js` — Approved color + badge:

```js
STATUS_COLORS: {
    'Submitted': '#9CA3AF',
    'Pending Validation': '#F59E0B',
    'Approved': '#0E7C7B',        // ← new (teal)
    'Assigned to Office': '#1A56DB',
    ...
},
```

`public/css/variables.css` — Approved theme tokens:

```css
--status-approved: #0E7C7B;
--status-approved-bg: #E0F7F6;
--status-approved-border: #99F6E4;
```

`assign-office.html` now loads exactly the reports that may be assigned:

```js
Api.get('/api/admin/citizen-reports', { status: 'Approved', limit: 200 });
```

`monitoring.html` — fixed the bogus `status: 'Assigned'` filter (never matched
the canonical `'Assigned to Office'`) so it loads all reports and lets the
tabs/overview count real statuses; its update modal now posts the canonical
`'Assigned to Office'` value so it passes `Rule::in(STATUS_ORDER)`.

Blade `partials/status-badge.blade.php`:

```php
$map = [
    'Submitted'          => 'badge-submitted',
    'Pending Validation' => 'badge-pending',
    'Approved'           => 'badge-approved',   // ← new
    ...
];
```

`show.blade.php` — Approve card renamed from "Validate", and the Assign card is
shown only when `status === 'Approved'`.

### 2.8 Flutter app

`civ-main/lib/models/report.dart`:

```dart
static const List<String> statusOrder = [
    'Submitted',
    'Pending Validation',
    'Approved',                       // ← new step
    'Assigned to Office',
    'In Progress',
    'Resolved',
];

bool get isApproved => status.toLowerCase() == 'approved';
```

`civ-main/lib/core/constants/app_colors.dart` — teal Approved colors:

```dart
static const Color statusApproved = Color(0xFF0E7C7B);
static const Color statusApprovedBg = Color(0xFFE0F7F6);
static const Color statusApprovedBorder = Color(0xFF99F6E4);
```

Timeline steps in `progress_timeline.dart`, `status_update_screen.dart`,
`track_report_screen.dart`, and `track_by_reference_screen.dart` all gained an
`Approved` step, and `My Reports` keeps approved reports visible under its
"Pending" filter bucket.

---

## 3. Verified live (on this PC, stack running)

| Check | Result |
|---|---|
| Migration applied | `php artisan migrate --force` → done, 24.73ms |
| DB enum | `enum('Submitted','Pending Validation','Approved','Assigned to Office','In Progress','Resolved','Rejected')` |
| Approve `CW-2026-00004` | `Pending Validation → Approved`, `is_public=1`, appears in Assign Office + Map |
| Reject second pending report | `→ Rejected`, removed from Pending/Map, assign blocked (HTTP 422) |
| `/api/mobile/reports/community` | returns only `Approved` + `Assigned to Office` rows |
| PHP lint | no syntax errors on any changed file |
| `php artisan test` | 2 passed |
| `flutter analyze` | 111 issues, all pre-existing (deprecated `withOpacity`, unused imports) — none introduced |

## 4. How to see it yourself

1. Web admin → Pending reports → open `CW-2026-00004` → **Approve Report**.
2. Status card now reads **Approved**; the report appears on the **GIS map**
   (teal pin) and in **Assign Office**.
3. Assign it to an office → status becomes **Assigned to Office** and it leaves
   the assign queue.
4. Reject a different pending report (with a reason) → it leaves Pending/Map and
   shows in History on Android.
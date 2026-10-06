# Reject a report with a reason, end to end

- **Date:** 2026-10-06
- **Commit:** `04c3fba`
- **Type:** feature
- **Areas:** Laravel / Flutter / docs

## Problem

The super administrator had no working way to reject a report.

Pressing **Reject** on `report-details.html` appeared to succeed, but the
report stayed in the pending queue forever — it was never removed from the
work queue, never left the public community feed, and the citizen was never
told why. There was also no place to type a reason, so a rejection could not
carry any explanation.

## Cause

1. `Rejected` was missing from `CitizenReport::STATUS_ORDER`
   (`laravel/app/Models/CitizenReport.php`). The prototype's reject handler
   picked the status from that list, found no `Rejected` entry, and fell back
   to sending `status: 'Submitted'` — so rejecting a report *re-submitted* it.
2. The MySQL column `citizen_reports.status` was an ENUM without `'Rejected'`,
   so the value could not have been stored even if it had been sent.
3. `AdminCitizenReportController::updateStatus` had no rule tying a reason to
   the Rejected status, and no check that only a report awaiting validation
   may be rejected.
4. `transitionTo()` never cleared `is_public`, so a rejected report would
   have stayed in the public feed.
5. There was no `Rejected` case in the Blade badge partial, the Blade status
   filter, the Flutter colour/icon helpers, or `IncidentReport.statusOrder`,
   so a rejected report would have rendered as an unknown status with
   `statusIndex == -1` and an empty 5-step timeline.

## Changes

| File | Change |
|---|---|
| `laravel/database/migrations/2026_10_06_000001_add_rejected_status_to_citizen_reports.php` | New. `DB::statement` ALTER widens the `status` ENUM to append `'Rejected'`; `down()` reverts. |
| `laravel/app/Models/CitizenReport.php` | `STATUS_REJECTED`; added to `STATUS_ORDER` (6 entries); new `REJECTABLE_STATUSES = [Submitted, Pending Validation]`; `transitionTo()` sets `is_public = false` on Rejected; `defaultActivityDescription()` and `buildNotificationMessage($description)` gain Rejected cases so the admin's note reaches the citizen notification. |
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | `description` rule `required_if:status,Rejected`; **422** when rejecting a report outside `REJECTABLE_STATUSES`. |
| `laravel/app/Http/Controllers/Admin/AdminWebController.php` | Same two guards on the Blade `POST /admin/citizen-reports/{id}/status`. |
| `laravel/public/report-details.html` | Reject sends `status: 'Rejected'` + `description`; blank reason shows a toast and keeps the modal open. |
| `laravel/resources/views/admin/partials/status-badge.blade.php` | `Rejected => badge-rejected`. |
| `laravel/resources/views/admin/layout.blade.php` | `.badge-rejected` styles. |
| `laravel/resources/views/admin/citizen-reports/index.blade.php` | `Rejected` in the status filter dropdown. |
| `laravel/resources/views/admin/citizen-reports/show.blade.php` | `Rejected` in the status `<select>`; note label becomes "required when rejecting". |
| `civ-main/lib/core/constants/app_colors.dart` | `statusRejected`, `statusRejectedBg`, `statusRejectedBorder`. |
| `civ-main/lib/core/utils/helpers.dart` | `rejected` cases in `getStatusColor` / `getStatusBgColor` / `getStatusIcon`. |
| `civ-main/lib/models/report.dart` | `statusOrder` doc-commented as the happy path; `bool get isRejected`. |
| `civ-main/lib/core/state/app_state.dart` | `rejectedCount` getter; `_statusMessage`/`_statusDescription` `Rejected` case. |
| `civ-main/lib/widgets/timeline/rejection_notice.dart` | **New.** `RejectionNotice(report)` — red banner carrying the admin's reason, read from the last `status == 'Rejected'` activity entry. |
| `civ-main/lib/widgets/timeline/progress_timeline.dart` | Early-return `RejectionNotice` when `report.isRejected \|\| currentIdx < 0`; removed the old private `_RejectionNotice` class. |
| `civ-main/lib/screens/track_report/status_update_screen.dart` | `RejectionNotice` replaces `_HorizontalTimeline` when rejected; `rejected` case in `_statusMessage`. |
| `civ-main/lib/screens/track_report/track_report_screen.dart` | Early-return in `_VerticalTimeline`; `rejected` case in `_statusMessage` (its `default` previously said "submitted successfully"). |
| `civ-main/lib/screens/visitor/track_by_reference_screen.dart` | `_CompactTimeline` replaced by `RejectionNotice` when rejected. |

`civ-main/lib/screens/my_reports/my_reports_screen.dart` was left **unchanged**
— a rejected report shows under the existing "All" filter with a red badge,
not behind a new chip.

## Verification

Services restarted and everything run in one shell (they die between calls):

```
mariadb: COLUMN_TYPE = enum('Submitted','Pending Validation','Assigned to Office',
                            'In Progress','Resolved','Rejected')

POST /api/admin/citizen-reports/1/status {Rejected, reason} -> 200
  citizen_reports: status='Rejected', is_public 1 -> 0
  report_activities.description = "Duplicate report - same issue already tracked under CW-2026-00003."
  citizen_notifications.message = "CW-2026-00001 — Rejected. Duplicate report - ..."
GET  /api/mobile/reports/community -> only CW-2026-00002 remains
POST .../status on report 2 (Assigned to Office) -> 422
POST .../status on report 3 with no description  -> 422
     message: "The description field is required when status is Rejected."
GET  /api/admin/citizen-reports?status=Rejected -> 2
GET  /admin/citizen-reports/1   -> 200, renders `badge-rejected">Rejected`
GET  /admin/citizen-reports     -> 200, renders `Rejected</option>`
php -l on both controllers -> "No syntax errors detected"
```

**Not verified:** no Flutter SDK, no Dart, no Android SDK and no emulator in
this environment. All nine Dart edits were checked by static review only —
brace/paren balance per file plus reading each hunk of `git diff`. The one
file that reported an imbalance (`track_report_screen.dart`) was confirmed to
be a pre-existing false positive of the crude string stripper: HEAD was
281/286 and the edit moved it to 283/288, i.e. a balanced +2/+2.

## Impact / how to test by hand

1. Start MariaDB + `php -S 127.0.0.1:8000 -t public` from `prc/laravel`.
2. Sign in at `http://127.0.0.1:8000/` as `admin@civilwatch.ph` /
   `Admin@2026!`, open **Pending Reports**, click a report, **Reject**.
3. Leave the reason blank → a toast blocks you. Type one → the report leaves
   the queue and no longer appears in `pending-reports.html` after its 30s
   refresh.
4. As a citizen in the app, open My Reports → the badge is red **Rejected**;
   tapping it shows a rejection banner with the administrator's words instead
   of an empty timeline.
5. Confirm the report is gone from the public community feed.

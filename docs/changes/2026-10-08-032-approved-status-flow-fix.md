# Persist an Approved status so approve/assign/map/monitoring/Android agree

- **Date:** 2026-10-08
- **Commit:** `89d9eaf`
- **Type:** fix
- **Areas:** Laravel / Flutter / web admin (HTML + Blade)

## Problem

The report status flow was inconsistent across every surface. An admin pressing
**Approve Report** on the web panel got a success popup, but on the map, in
Assign Office, in Monitoring and on Android the report still showed
*Pending Validation*. Approving appeared to do nothing: the report never made
its way to the community map, never appeared in the assign-office queue, and
Android kept showing the old status.

## Cause

There was **no `Approved` status anywhere in the system**. `Approve Report`
called `POST /api/admin/citizen-reports/{id}/validate`, and
`AdminCitizenReportController::validate()` only set `is_public = true` while
leaving (or returning) the status at `Pending Validation`. Every downstream
screen faithfully rendered what the database said — *Pending Validation* — so
nothing downstream could ever see an approval:

- No `Approved` value in the `citizen_reports.status` MySQL ENUM or in
  `CitizenReport::STATUS_ORDER`, so no UI could show it and the `Rule::in`
  validator in `updateStatus()` would reject it.
- Assign Office fetched `Pending Validation` + `Submitted` (never a status that
  means "approved, ready to assign") — and its Monitoring sibling fetched
  `status: 'Assigned'`, which never matches the canonical
  `'Assigned to Office'`, and its update modal posted `status: "Assigned"`,
  which fails `Rule::in(STATUS_ORDER)`.
- Map and the mobile community endpoint filtered on `is_public` only, so the
  map could (and did) already show *Pending Validation* pins.
- `admin index()` paginated at hardcoded 25, ignoring the `limit: 200/500`
  that the HTML pages pass, truncating queues.
- Flutter had no `Approved` in its status order lists, colors, or timelines.

## Changes

| File | Change |
|---|---|
| `laravel/database/migrations/2026_10_08_000001_add_approved_status_to_citizen_reports.php` | New migration widening the `status` ENUM to include `'Approved'` between `'Pending Validation'` and `'Assigned to Office'`. Schema-only, no data backfill. |
| `laravel/app/Models/CitizenReport.php` | Add `STATUS_APPROVED`, add to `STATUS_ORDER`, add `Approved` to `transitionTo()` auto-publish list, to `defaultActivityDescription()`, and to `buildNotificationMessage()`. |
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | `validate()` now persists `Pending Validation → Approved` with `is_public=true` (approve is no longer a no-op); `assign()` guards on `status === Approved` + `assigned_office_id === null`; `map()` shows only `Approved+`; `summary()` adds `approved`; `index()` honors `limit`/`per_page` (capped at 500). |
| `laravel/app/Http/Controllers/Admin/AdminWebController.php` | Blade dashboard/validate/assign/map aligned: approve transitions to `Approved`, assign gated to `Approved`, map uses `Approved+` statuses, stats include `approved`. |
| `laravel/app/Http/Controllers/Mobile/MobileReportController.php` | `community()` (Android/mobile community map) filters to `Approved+` — never stale `Pending Validation`/`Rejected` rows even when `is_public=1`. |
| `laravel/app/Http/Controllers/AnalyticsController.php` | `summary()` exposes `approved`. |
| `laravel/resources/views/admin/*` | `status-badge.blade.php` + `layout.blade.php` add `badge-approved` (teal); dropdowns in `index.blade.php`/`show.blade.php`/`map.blade.php` include `Approved`; `show.blade.php` renames Validate → Approve and restricts the Assign card to `Approved`; `dashboard.blade.php` adds an Approved stat; `map.blade.php` status filter/legend/colors are `Approved+` only. |
| `laravel/public/*` (HTML admin) | `utils.js` add Approved color/badge; `badges.css`/`variables.css`/`cards.css` add teal Approved styles; `assign-office.html` loads only `Approved` and shows `approved` in the stat card; `monitoring.html` loads all statuses (drops bogus `'Assigned'` filter), adds an Approved tab/stat/filter, and fixes the update modal to post the canonical `'Assigned to Office'`; `gis-map.html` chips/legend/pin colors are `Approved+`; `report-details.html` timeline adds `Approved` and approve modal text says "Approved"; `dashboard.html`/`analytics.html` pin colors, icon maps and status charts include `Approved`. |
| `civ-main/lib/**` (Flutter) | `report.dart` adds `Approved` to `statusOrder` + `isApproved`; `app_colors.dart` adds teal `statusApproved*`; `helpers.dart` colors/backgrounds/icons handle `approved`; `progress_timeline.dart`, `status_update_screen.dart`, `track_report_screen.dart`, `track_by_reference_screen.dart` add the Approved step; `my_reports_screen.dart` keeps approved reports under the Pending filter; `app_state.dart` describes `Approved`; `visitor_reports_screen.dart` legend swaps `Pending` dot for `Approved`. |
| `laravel/civilwatch_FINAL.sql` | Canonical dump: status ENUM now contains `'Approved'`; footer integrity note updated. |

## Verification

- `php artisan migrate --force` — migration applied (24.73ms), verified column type:
  `enum('Submitted','Pending Validation','Approved','Assigned to Office','In Progress','Resolved','Rejected')`.
- `php -l` on every changed PHP file — no syntax errors.
- `php artisan test` — 2 passed.
- `graphify update .` could not be run: the graphify CLI is not installed on
  this PC (only the graphify-out output files are present in the repo).
- `flutter analyze` — 111 issues, all pre-existing warnings/infos (deprecated
  `withOpacity`, unused imports), no errors and none in the touched code.
- Live end-to-end against `http://127.0.0.1:8000` (MySQL + Laravel running):
  - Login as super admin → token issued.
  - **Approve** `CW-2026-00004`: `status` `Pending Validation → Approved`,
    `is_public=1`; appears in Assign Office list (`status=Approved` query) and
    on the map; activity trail shows `Submitted → Pending Validation → Approved`.
  - **Reject** a second pending report (`CW-2026-00005`): `status=Rejected`,
    `is_public=0`; gone from Pending queue; assign attempt on it returns
    **HTTP 422**; summary shows `approved=1, assigned=1, pending=0`.
  - `/api/mobile/reports/community` returns only `Approved` + `Assigned to Office` rows.

Not verified: rejected-report appear on Android history requires an Android
build (no SDK on this PC) — the API response is the direct source Android
renders from and it is correct.

## Impact / how to test by hand

1. Start the stack (`START_ALL.bat`), open the web admin, log in as super admin.
2. Open Pending reports → open `CW-2026-00004` → **Approve Report**.
3. It must now show status **Approved**, appear on the **GIS map** (teal pin)
   and in **Assign Office**, and refresh away from the pending queue on Android
   (My Reports / Track).
4. Assign it to an office → it leaves Assign Office, becomes
   *Assigned to Office*.
5. Reject a second pending report with a reason → it leaves the pending queue,
   map and Assign Office, and moves to History on Android.
# Drop the legacy /api/reports subsystem

- **Date:** 2026-10-06
- **Commit:** `3e21b70`
- **Type:** refactor
- **Areas:** Laravel

## Problem

About 500 lines of backend code described a report workflow that the
application does not use. It was reachable, unauthenticated-by-role, and
pointed at a table that no longer exists — so any request to it produced a
SQL error page rather than a useful answer.

Anyone reading the codebase had to work out, by inspection, which of the two
parallel report controllers was live.

## Cause

The project grew a second report subsystem. `citizen_reports` (with
`CitizenReport`, `ReportActivity`, `citizen_notifications`) is the one the
Flutter app and the admin prototype use. The older `reports` table was
dropped, but its controller and models were never deleted:

* `app/Http/Controllers/ReportController.php` (430 lines) still ran
  `Report::query()` against the missing table, plus `validateReport`,
  `rejectReport`, `assign`, `updateStatus`, `mapPins`.
* `routes/api.php` still registered 10 `/api/reports*` routes for it.
* `Report`, `ReportTimeline`, `ReportAssignment`, `ReportPhoto` and
  `Notification` existed only to feed that controller, and reference each
  other (`Notification->Report`, `ReportAssignment->Report`, …).
  `Notification` modelled the same missing `notifications` table that had
  already broken `NotificationController`.

Nothing called any of it: the prototype uses `/api/admin/citizen-reports`,
the app uses `/api/mobile/reports`, and a grep of `public/`, `resources/` and
`js/` found no reference to `/api/reports`.

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Controllers/ReportController.php` | **Deleted.** |
| `laravel/app/Models/Report.php` | **Deleted.** |
| `laravel/app/Models/ReportTimeline.php` | **Deleted.** |
| `laravel/app/Models/ReportAssignment.php` | **Deleted.** |
| `laravel/app/Models/ReportPhoto.php` | **Deleted.** |
| `laravel/app/Models/Notification.php` | **Deleted.** |
| `laravel/routes/api.php` | Removed `use App\Http\Controllers\ReportController;` and the 10 `/api/reports*` routes; a comment points at `/api/admin/citizen-reports`. |

`CitizenReport`, `ReportActivity` and `CitizenNotification` are the live path
and are untouched. `ClearDummyReports`, the Blade panel and both frontends
reference only `CitizenReport`.

**Ordering note:** this was only safe *after* `03aa975`, which moved
`AnalyticsController` off `Report` and `NotificationController` off
`Notification`. Reversing the two commits would not build.

## Verification

```
grep -rn "ReportController|Models\\Report;|Models\\ReportTimeline|
          Models\\ReportAssignment|Models\\ReportPhoto|Models\\Notification;"
      app/ routes/ resources/ config/ database/ tests/ bootstrap/
  -> only Mobile/AdminCitizenReportController substring matches (both live)

php -l routes/api.php          -> No syntax errors detected
php artisan route:list         -> Showing [77] routes   (was 87)
php artisan route:list | grep -c 'api/reports' -> 0

curl -H "Authorization: Bearer <token>" ... /api/reports      -> 404
                                                /api/reports/map  -> 404
                                                /api/reports/1    -> 404

still 200: /api/admin/citizen-reports{,/summary,/map,/1},
           /api/admin/offices, /api/admin/announcements, /api/admin/users,
           /api/analytics{,/summary}, /api/notifications{,/unread-count},
           /api/user, /api/mobile/reports/community, /admin/login, /
still 401: /api/mobile/reports  (auth:citizen, no token — unchanged)
```

**Not verified:** `php artisan test` was not run — the repository has no
test suite for these routes. No Flutter SDK exists here; the app does not
touch `/api/reports*`.

## Impact / how to test by hand

1. `curl -H "Authorization: Bearer <token>" 127.0.0.1:8000/api/reports`
   should return `{"message":"Not Found"}` with 404, not a SQL error.
2. Walk the normal flow — submit from the app, validate and assign from
   `pending-reports.html`, track from My Reports — everything should behave
   exactly as before, since none of it used the deleted code.
3. `php artisan route:list` should show 77 routes.

# Repair the dead analytics, notification and user-list endpoints

- **Date:** 2026-10-06
- **Commit:** `03aa975`
- **Type:** fix
- **Areas:** Laravel

## Problem

Three parts of the super-admin prototype were silently broken:

* The **Pending Reports badge** in the sidebar never showed a number.
* The **notification bell** had no badge, no dropdown contents, and its
  30-second poll did nothing.
* The **Users page** stats cards stayed on their skeleton placeholders.

None of them raised a visible error — every consumer wraps the call in
`.catch(() => {})` or checks `if (!res.ok) throw`, so the UI just looked
half-finished.

## Cause

All three endpoints were written against a schema that no longer exists.

1. `AnalyticsController::scopedQuery()` (`:261`) ran `Report::query()` — the
   `reports` table was dropped, so every call threw a "table not found" SQL
   error. On top of that the status literals were the old snake_case set
   (`pending`, `in_progress`, `for_resolution`) and `topIssues` grouped by a
   `title` column that `citizen_reports` does not have (it has `concern`),
   while `byCategory` looked for `infrastructure/environmental/public_safety/
   sanitation/other` instead of the real `Infrastructure/Environment/Others`.
   Only `/api/analytics/summary` is actually consumed (`public/js/app.js:372`).
2. `NotificationController` used the `Notification` model against the
   missing `notifications` table, filtered by a `user_id` column. Called by
   `app.js:552` (dropdown), `app.js:627` (30s poll) and `dashboard.html:783`.
3. `GET /api/admin/users` was **not routed at all** — a plain 404. Called by
   `public/users.html:756`.

A fourth, smaller bug: `users.html:769` counted active users with
`u.is_active === 1`, but Laravel emits JSON `true`, and `true === 1` is false
in JavaScript — so the count would have been 0 even after the route existed.

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Controllers/AnalyticsController.php` | `use App\Models\CitizenReport`; `scopedQuery()` now `CitizenReport::query()` scoped by joining `assignedOffice` on `abbreviation` (`CEO`/`CENRO`) instead of the nonexistent `assigned_to_office` column; status literals switched to the real enum values and `Rejected` added; `byCategory` uses the real three categories; `topIssues` groups by `concern`; added `rejected` to the summary payload; dropped the now-unused `formatLabel()`. Response shapes are unchanged, so `app.js:372` keeps working. |
| `laravel/app/Http/Controllers/NotificationController.php` | Rewritten against `CitizenNotification` (`citizen_notifications`). The per-`user_id` filter is gone by design: this is the organisation-wide feed with a global read state. Returns the `{notifications, unread_count}` / `{unread_count}` shapes `app.js` destructures; models serialise natively with `type`, `message`, `created_at`, `is_read` — exactly the fields `app.js:575` renders. |
| `laravel/routes/api.php` | Added `GET /api/admin/users` → `UserController::index`, inside the existing `admin` prefix group. |
| `laravel/public/users.html` | Unwrap the `{success, data}` envelope (`Array.isArray(body) ? body : body.data \|\| []`) and accept boolean `is_active`. |

## Verification

All in one shell with MariaDB + `php -S` restarted:

```
php -l routes/api.php / AnalyticsController.php / NotificationController.php -> OK

GET /api/analytics/summary              -> 200  {"total_reports":3,"pending_validation":0,
                                                "assigned":1,"in_progress":0,"resolved":0,"rejected":2}
GET /api/analytics/status-distribution  -> 200  labels include "Rejected", data [0,0,1,0,0,2]
GET /api/analytics/by-category          -> 200  {"labels":["Infrastructure"],"data":[3]}
GET /api/analytics/top-issues           -> 200  {"labels":["Fallen power line","Street flooding"],"data":[2,1]}
GET /api/analytics/top-barangays|weekly-trend|monthly-trend|analytics -> 200
GET /api/notifications                  -> 200  unread_count 6, 6 items,
                                                item {type:"status_update", message:"CW-2026-00001 — Rejected. ...",
                                                      created_at:"2026-10-06T10:31:39.000000Z", is_read:false}
GET /api/notifications/unread-count     -> 200  {"unread_count":6}
POST /api/notifications/6/read          -> 200  unread_count 6 -> 5 (row restored to is_read=0 afterwards)
GET /api/admin/users                    -> 200  success:true, 1 user, is_active:true, role:"super_admin"
GET /api/admin/doesnotexist             -> 404  (route list not over-matched)
```

**Not verified:** no browser was driven, so `app.js` / `users.html` were not
executed — the payloads were matched against the consuming code by reading
`app.js:372`, `app.js:575`, `app.js:627` and `users.html:756-771`. No Flutter
SDK exists here, and none of these endpoints are called by the app.

## Impact / how to test by hand

1. Sign in to `http://127.0.0.1:8000/`.
2. The sidebar **Pending Reports** badge should show a number from the
   summary endpoint instead of staying `0`.
3. The bell icon should show `6` (or however many unread rows exist), and the
   dropdown should list real rows with a relative timestamp.
4. Open `users.html` — the Total / Active / Administrator cards should show
   real numbers rather than the loading skeleton.
5. `curl -H "Authorization: Bearer <token>" 127.0.0.1:8000/api/analytics`
   should return 200, not a 500 HTML error page.

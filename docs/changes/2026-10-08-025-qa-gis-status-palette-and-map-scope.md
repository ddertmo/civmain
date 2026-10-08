# Change 025 — QA: GIS status/palette + map scope

**Date:** 2026-10-08  
**Commit:** `HEAD` (uncommitted changes)  
**Type:** fix  
**Areas:** admin-web, api, utils

## Problem
- Status chips/badges mismatched (e.g. "Assigned" chip vs "Assigned to Office"), legend missed Submitted/Rejected, Pending Validation/In Progress used same-orange in places.
- Admin GIS `/api/admin/citizen-reports/map` excluded `Pending Validation` (required `assigned_office_id != null`) so pending reports never appeared on map.
- Category badge mislabeled `Others`/`Environment` in places; inconsistent status colors.

## Cause
- `js/utils.js` had incomplete `statusBadge` and forced non-Infrastructure to "Environmental".
- `gis-map.html` used local `pinColors` with `#F97316` for In Progress (duplicate-ish vs amber) and chip/filter value `Assigned` didn't match API `'Assigned to Office'`.
- `AdminCitizenReportController::map()` had an early filter `assigned_office_id != null` and only `Assigned,In Progress,Resolved`.

## Changes
| Path | What | Why |
|---|---|---|
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | Include `Pending Validation` in map statuses; remove `assigned_office_id != null` requirement; update comment. | QA expects pending-validated reports with coords to show on admin GIS. |
| `laravel/public/js/utils.js` | Add `STATUS_COLORS` (shared), expand `statusBadge` to 6 statuses (Submitted,Pending Validation,Assigned to Office,In Progress,Resolved,Rejected); canonical `categoryBadge` (Environment→"Environmental", Others→"Others"). | Single source of truth for badges/pins/legend. |
| `laravel/public/css/badges.css` | Add `.badge-submitted`, `.badge-rejected`, `.badge-others` and minor spacing. | Style new states. |
| `laravel/public/gis-map.html` | Use `Utils.STATUS_COLORS`; fix "Assigned" chip to `Assigned to Office`; expand legend (Submitted, Rejected); add note "Pins = status • Emoji = category". | Fix dead chips + clarify legend. |

## Verification
- `php -l laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` → No syntax errors.
- Static JS edits only (no Node build here) — colors/palette strings reviewed.
- Map endpoint logic change is minimal; can be smoke-tested against scratch DB.

## Impact / how to test by hand
1. On a scratch DB with a Pending Validation report that is `is_public=1`, has lat/lng, open `/api/admin/citizen-reports/map` — row should appear with status `Pending Validation` (amber pin).
2. GIS page: legend shows Submitted/Pending/Assigned/In Progress/Resolved/Rejected; chip "Assigned" filters `Assigned to Office`.

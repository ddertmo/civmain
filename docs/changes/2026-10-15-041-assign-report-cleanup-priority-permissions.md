# Clean up Assign Report modal and restrict priority to the assigned office

- **Date:** 2026-10-15
- **Commit:** `HEAD` (working tree)
- **Type:** fix
- **Areas:** Laravel web admin / API / database

## Problem

The Assign Report modal showed too many controls for the current workflow:

- A redundant recommendation sentence above the office cards.
- A redundant "OR CHOOSE ANOTHER OFFICE" select even though office cards already support selection.
- A "Set Priority" control that allowed priority to be chosen during assignment.

The modal only needs to collect the target office and optional notes. Priority management
should belong to the office actually assigned to the report, not to Super Admin/TAPik users
during assignment.

## Cause

The backend ignored `priority` during `/api/admin/citizen-reports/{id}/assign`, while the
frontend still sent a selected priority. There was also no priority column or priority update
endpoint, so the feature could not be enforced server-side.

## Changes

| File | Change |
|---|---|
| `laravel/public/assign-office.html` | Removed the recommendation sentence above office cards; replaced by the existing "Recommended" badge and selected-card styling. |
| `laravel/public/assign-office.html` | Removed the "OR CHOOSE ANOTHER OFFICE" section, dropdown, and related selection helpers. |
| `laravel/public/assign-office.html` | Removed the "Set Priority" section, pills, and `selectAfPP`. Office cards are now the only office selector. |
| `laravel/public/assign-office.html` | Renumbered notes to 2, adjusted dividers, and stopped sending `priority` with assignment. |
| `laravel/database/migrations/2026_10_15_000001_add_priority_to_citizen_reports.php` | Added a `priority` enum with server-side default `Medium` without resetting existing rows. |
| `laravel/app/Models/CitizenReport.php` | Added `priority` to `$fillable` and report API output. |
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | Added `updatePriority()`, allowing only `ceo` or `cenro` whose office matches the assigned office. |
| `laravel/routes/api.php` | Added `POST api/admin/citizen-reports/{id}/priority`. |

Reason the redundant UI was removed: the recommended office was already shown through its badge
and selected card style, the office-card click already selected another office, and priority
should no longer be set during assignment.

Priority rules now enforced by the API:

- `super_admin`, `TAPik`, and other roles are denied.
- CEO/CENRO users are allowed only when the report is assigned to their own office.
- Invalid priority values are rejected with validation.

## Verification

- `php artisan migrate --force` applied the priority migration successfully.
- `php -l app/Models/CitizenReport.php` passed.
- `php -l app/Http/Controllers/Admin/AdminCitizenReportController.php` passed.
- `php artisan route:list` shows `POST api/admin/citizen-reports/{id}/priority`.
- `python` HTML parse succeeded; `af-pri-pills` no longer appears as markup, only as now-unused CSS.
- Verified `assign-office.html` no longer contains the recommendation paragraph or dropdown section.

## Impact / how to test by hand

1. Open the Assign Report modal for a report.
2. Confirm only office cards, notes, Cancel, and Assign Report are available.
3. Assign without choosing a priority.
4. Call `POST /api/admin/citizen-reports/{id}/priority` as the assigned office's authorized user with
   `{"priority":"High"}` and confirm it succeeds.
5. Repeat as Super Admin, TAPik, or a user from another office and confirm a 403 response.

## Limitation noted

The CEO/CENRO report pages are mostly static with dummy data and are not yet wired to the new priority endpoint. Backend authorization is implemented; office-side UI integration remains to be connected.
PY
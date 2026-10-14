# Office list restricted to CEO and CENRO

- **Date:** 2026-10-14
- **Commit:** `6e5704d`
- **Type:** fix
- **Areas:** Laravel / Flutter

## Problem

The office picker on the assign-office screen offered offices that should not
exist for Digos City. Only the City Engineering Office (CEO) and the City
Environment and Natural Resources Office (CENRO) should be selectable.

## Cause

- `laravel/app/Http/Controllers/Admin/AdminOfficeController.php` `index()`
  returned every active row (`GovernmentOffice::where('is_active', true)`), so
  any extra/seeded office was offered to the admin.
- The Flutter fallback list in `civ-main/lib/core/utils/dummy_data.dart`
  hard-coded four offices (CEO, CENRO, CPWD, CDRRMO).

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Controllers/Admin/AdminOfficeController.php` | `index()` now filters `whereIn('abbreviation', ['CEO','CENRO'])` |
| `civ-main/lib/core/utils/dummy_data.dart` | fallback `offices` list reduced to CEO and CENRO |

## Verification

- `GET /api/admin/offices` (super-admin token) returns exactly two rows:

  ```
  abbreviation name
  CEO          City Engineering Office
  CENRO        City Environment and Natural Resources Office
  ```

- The Flutter change is a static edit; no Flutter SDK run was performed for this
  change.

## Impact / how to test by hand

Log in to the HTML admin, open an approved report and click **Assign Office**.
The office cards list only CEO and CENRO.

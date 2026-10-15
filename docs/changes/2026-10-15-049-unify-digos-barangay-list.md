# Unify the Digos City barangay list to the official 26 everywhere

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** fix
- **Areas:** Flutter / Laravel

## Problem

The list of barangays shown to users was different depending on where you
looked, and several lists contained places that are **not in Digos City**:

- Citizen **registration** offered 22 names including `Badiang`, `Kiagdan`,
  `New Visayas` and `Rizal`.
- The incident **report form** offered the 26 official names (`Kiagot`,
  `Lungag`, `Mahayahay`, `Ruparan`, `San Agustin`, `San Roque`, `Sinawilan`,
  `Tiguman`, `Zone 1/2/3 (Pob.)`).
- The admin **barangay filters** on Monitoring / Pending / Resolved hard-coded a
  six-item sample (`Carmen`, `Zone II`, `Magsaysay`, `Badiang`) and the
  Assign-Office filter mixed in Davao City barangays (`Baracatan`, `Bato`,
  `Carmen`, `Kansas`, `Magsaysay`, `Santa Cruz`, `Zone I/II/III`).
- The Blade report filter used the same wrong 22-name list.

A resident could register with a barangay that does not exist in Digos City,
and an admin filtering by a real barangay saw an incomplete or wrong list.

## Cause

Five independent, hard-coded copies of the barangay list had drifted apart:

- `civ-main/lib/core/constants/app_strings.dart:193` — 22 names (used by
  registration).
- `civ-main/lib/screens/report/report_location.dart:19` — `_kDigosBarangays`,
  the correct 26 names (used by the report form).
- `laravel/app/Http/Controllers/Mobile/MobileAuthController.php:291` —
  `BARANGAYS`, the wrong 22 names (used as the registration `in:` rule).
- `laravel/public/{monitoring,pending-reports,resolved-reports}.html` — static
  `<option>` lists.
- `laravel/public/assign-office.html:1860` and
  `laravel/resources/views/admin/citizen-reports/index.blade.php:22`.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/core/constants/app_strings.dart` | `barangays` is now the official 26 Digos City barangays (PSA 2020) and is documented as the app's single source of truth. |
| `civ-main/lib/screens/report/report_location.dart` | Dropped the duplicate private list; `_kDigosBarangays` now aliases `AppStrings.barangays`, so the report form and registration can never drift. |
| `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` | `BARANGAYS` (the registration `in:` validation) updated to the official 26; comment corrected from "22" to "26" and cross-referenced to `AppStrings.barangays`. |
| `laravel/public/monitoring.html` | `#brgyFilter` options replaced with all 26 official barangays. |
| `laravel/public/pending-reports.html` | `#brgyFilter` options replaced with all 26. |
| `laravel/public/resolved-reports.html` | `#brgyFilter` options replaced with all 26. |
| `laravel/public/assign-office.html` | `BARANGAYS` array (feeds `#filterBarangay`) replaced with the official 26. |
| `laravel/resources/views/admin/citizen-reports/index.blade.php` | Filter `@foreach` list replaced with the official 26. |

The office prototype pages (`laravel/public/offices/**`) still contain mock
sample names; they are static prototypes and were left untouched.

## Verification

- `flutter analyze lib\core\constants\app_strings.dart lib\screens\report\report_location.dart lib\screens\auth\register_screen.dart lib\services\auth_service.dart`
  (Flutter 3.47.0 / Dart 3.13.0) → only pre-existing warnings (`_isLoading`
  unused; `withOpacity` deprecations). The `const _kDigosBarangays =
  AppStrings.barangays;` alias compiles. **No errors.**
- `php -l` on `MobileAuthController.php` → `No syntax errors detected`.
- `node --check` (via a `vm.Script` extractor) on every inline `<script>` block
  of `monitoring.html`, `pending-reports.html`, `resolved-reports.html` and
  `assign-office.html` → all blocks OK.
- Live `POST /api/mobile/auth/register`:
  - barangay `Zone 1 (Pob.)` → `201`, `citizen.barangay = "Zone 1 (Pob.)"`.
  - barangay `Badiang` → `422` `"The selected barangay is invalid."`
  - The test citizen + token were deleted; `citizens` is back to the two real
    rows.
- Existing reports already use official names (`San Jose`, `Zone 2 (Pob.)`), so
  the new filters match current data.

## Impact / how to test by hand

1. App → **Create Account** → open the **Home Barangay** dropdown. It now lists
   26 barangays including `Kiagot`, `Lungag`, `San Roque`, `Zone 1 (Pob.)`.
2. App → start a report → the report form's barangay dropdown matches exactly.
3. Admin → Monitoring / Pending / Resolved → the **Barangay** filter lists all
   26 and filters correctly.
4. Admin → Assign Office → the **All Barangay** filter (populated from the JS
   array) lists all 26.

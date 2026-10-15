# Citizen registration now captures a full home address (street, purok, city, province)

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** feature
- **Areas:** Flutter / Laravel

## Problem

Citizen sign-up only captured a **Home Barangay** dropdown. Admins and offices
who later verify a report ("who is this user, where do they live?") could only
see the barangay, with no street, purok, city or province to disambiguate
residents of the same barangay. The report form already captured a full
location, but the citizen's *residence* was stuck at barangay level.

## Cause

- The registration form (`civ-main/lib/screens/auth/register_screen.dart`) only
  had `_emailCtrl` / `_nameCtrl` / `_phoneCtrl` and a `_BarangayDropdown`;
  nothing else was collected or sent.
- `AuthService.register` (`civ-main/lib/services/auth_service.dart:100`) posted
  only `fullName`, `phone`, `barangay`, `pin`, `email`.
- `POST /api/mobile/auth/register` validated only `fullName`, `email`,
  `barangay`, `pin`, `phone` and created the citizen with a hard-coded
  `city = 'Digos City'`
  (`laravel/app/Http/Controllers/Mobile/MobileAuthController.php:169-206`).
- The `citizens` table had no `street`, `purok` or `province` columns
  (`laravel/database/migrations/2026_01_01_000001_create_citizens_table.php`).
- `CitizenReport::toApiArray()` only exposed `fullName`, `phoneNumber`,
  `barangay` for the reporter (`laravel/app/Models/CitizenReport.php:233`), so
  even if stored, a fuller address could not reach the admin panels.

## Changes

| File | Change |
|---|---|
| `laravel/database/migrations/2026_10_15_000002_add_address_to_citizens_table.php` | New migration: adds nullable `street` (150), `purok` (100) and `province` (100, default `Davao del Sur`) to `citizens`, each guarded by `Schema::hasColumn` so re-runs are safe. |
| `laravel/app/Models/Citizen.php` | Added `street`, `purok`, `province` to `$fillable`. |
| `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` | `register()` now validates optional `street`, `purok`, `city`, `province` and stores them (city/province fall back to `Digos City` / `Davao del Sur`); `citizenResponse()` returns `street`, `purok`, `city`, `province`. |
| `laravel/app/Models/CitizenReport.php` | `toApiArray()` reporter block now also returns `street`, `purok`, `city`, `province` so admin report views can show the reporter's full home address. |
| `civ-main/lib/services/auth_service.dart` | `register()` accepts optional `street`, `purok`, `city`, `province` and posts each only when non-empty. |
| `civ-main/lib/screens/auth/register_screen.dart` | Added `_streetCtrl` / `_purokCtrl` / `_cityCtrl` (prefilled `Digos City`) / `_provinceCtrl` (prefilled `Davao del Sur`), new **Street / Area**, **Purok (optional)**, **City / Municipality** and **Province** fields around the existing Home Barangay dropdown, disposal of the new controllers, and passed the values into `AuthService.register`. |

The Home Barangay dropdown still uses `AppStrings.barangays`, unchanged, so the
existing backend `in:` validation keeps working.

## Verification

- `flutter analyze lib\screens\auth\register_screen.dart lib\services\auth_service.dart`
  (Flutter 3.47.0 / Dart 3.13.0) → only 2 pre-existing warnings
  (`_isLoading` unused/final in `register_screen.dart`, untouched by this
  change). **No errors.**
- `php -l` on the four changed/added PHP files → `No syntax errors detected`.
- `php artisan migrate --force` → `2026_10_15_000002_add_address_to_citizens_table ... DONE`.
- `DESCRIBE citizens;` → `street`, `purok`, `province` present; existing rows
  (`id 1`, `2`) still readable with `NULL` street/purok (backward compatible).
- Live `POST http://127.0.0.1:8000/api/mobile/auth/register` with
  `{ street, purok, city: "Digos City", province: "Davao del Sur", ... }`
  returned `201` and a `citizen` object echoing all four address fields
  (`street: "123 Test Street"`, `purok: "Purok 9"`, `barangay: "Aplaya"`,
  `city: "Digos City"`, `province: "Davao del Sur"`). The test citizen and its
  token were then deleted; the table is back to the two real rows.

Not verified: the Flutter UI was not run on a device/emulator (no Android
emulator booted here) — the sign-up form was reviewed statically and via
`flutter analyze` only.

## Impact / how to test by hand

1. Open the app → **Create Account** and fill in Name, Phone, Email.
2. Confirm the new **Street / Area**, **Purok (optional)**, **Home Barangay**,
   **City / Municipality** (Digos City) and **Province** (Davao del Sur) fields
   appear. Pick a barangay.
3. Create a PIN, agree to the terms, tap **Create Account**.
4. Backend: `SELECT street, purok, barangay, city, province FROM citizens;`
   confirms the values were stored.
5. Super Admin → open a report by that citizen → the reporter data now carries
   the full home address (barangay + street/purok/city/province).

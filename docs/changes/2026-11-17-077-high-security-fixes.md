# High-severity fixes: OTP-verified registration, rate limits, admin bell, reference numbers, HTTPS app, Users page, tests

- **Date:** 2026-11-17
- **Commit:** `5fda2b4`
- **Type:** fix (security + data integrity)
- **Areas:** Laravel (mobile auth, staff auth, notifications, reports, migrations, tests), admin web (users.html), Flutter (auth flow, network config)

## Problem

These are the eight High findings of the 2026-11-17 audit. 076 covered the Critical ones.

| # | Problem |
|---|---|
| H1 | `register` accepted any phone number with no OTP proof. Anyone could claim a resident's number and immediately get a token. |
| H2 | There were no rate limits anywhere. PINs and OTPs (10^6 values) could be brute-forced. `verify-reset-otp` was an unlimited guessing oracle. `send-otp` could flood a phone and drain UniSMS credits. Staff login was unlimited. The citizen login said "No account found" vs "Incorrect PIN", which revealed registered numbers. |
| H3 | The admin bell's "mark all read" and "clear" wrote to the shared `citizen_notifications` table, which is also every citizen's own inbox. Any staff user could mark all citizens' notifications read or delete them. |
| H4 | `php artisan reports:clear-dummy` deleted `CW-2026-00101…00110`, numbers that real reports now get. |
| H5 | Reference numbers were `count(this year) + 1`. Concurrent submits, or any delete, reissued a number, the UNIQUE insert failed with HTTP 500, and the uploaded photo was orphaned. |
| H6 | The Android manifest allowed cleartext HTTP in release builds. iOS had `NSAllowsArbitraryLoads`. A release build without `--dart-define` pointed at `http://127.0.0.1`. |
| H7 | `users.html` was dead. `let usersData/filtered/activeUser` was declared twice (a SyntaxError), `AVATAR_COLORS` was undefined, and data was never loaded. |
| H8 | There were no real tests, and the MySQL-only `MODIFY ENUM` migrations made the SQLite test database fail to migrate. |

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` | Details below. |
| `laravel/routes/api.php` | Per-IP `throttle` on every mobile auth route (login, verify and reset 10/min; send-otp 5 per 10 min; register 5/min) and on staff `/api/login` (10/min). Removed `DELETE /notifications`, `DELETE /notifications/{id}` and `POST /notifications/{id}/read`. The panel never called them. |
| `laravel/routes/web.php` | `throttle:10,1` on the Blade `POST /admin/login`. |
| `laravel/app/Http/Controllers/AuthController.php` | Per-email limit of 5 failures per 15 min (429). The password is checked before the "deactivated" message, so account status can't be enumerated. |
| `laravel/app/Http/Controllers/NotificationController.php` | Rewritten as a read-only view of `citizen_notifications` with per-staff read state. `is_read` and `unread_count` are computed against `users.notifications_read_at`. `read-all` sets that timestamp for the current user only. |
| `laravel/database/migrations/2026_11_17_000001_add_notifications_read_at_to_users.php` | **New.** Nullable `users.notifications_read_at`, with a `hasColumn` guard for installs that imported the dump. |
| `laravel/app/Models/User.php` | `last_login_at` and `notifications_read_at` added to `$fillable`. `last_login_at` was being silently dropped (audit M6); `notifications_read_at` gets a datetime cast. |
| `laravel/app/Console/Commands/ClearDummyReports.php` | **Deleted.** |
| `laravel/database/seeders/CitizenReportSeeder.php` | Comment no longer points to the deleted command. |
| `laravel/app/Models/CitizenReport.php` | New `createWithReferenceNumber()`: inserts with a temporary placeholder number, then sets `CW-{year}-{id:05}` from the auto-increment id, all in one transaction. `generateReferenceNumber()` now only returns the placeholder. |
| `laravel/app/Http/Controllers/Mobile/MobileReportController.php` | `store()` creates the report, its "Submitted" activity and the Pending Validation step in one `DB::transaction`. It deletes the uploaded photo if the insert fails. |
| `laravel/database/migrations/2026_10_0{6,8}_000001_*.php` | The raw `ALTER TABLE … MODIFY ENUM` runs only on mysql/mariadb, so the SQLite test database can migrate. |
| `laravel/tests/Feature/SecurityRegressionTest.php` | **New.** 12 tests, listed under Verification. |
| `laravel/public/users.html` | Removed the duplicate declarations, defined `AVATAR_COLORS`, added `requireAuth()`, and loads real users from `GET /api/users` (`mapUser`, `loadUsers`, stats computed from rows). Shows an error state instead of fake data. The Reset, Deactivate and Add buttons are still not wired (out of scope). |
| `civ-main/android/app/src/main/AndroidManifest.xml`, `src/debug/…`, `src/profile/…` | `usesCleartextTraffic` moved out of the main manifest into the debug and profile overlays only. |
| `civ-main/ios/Runner/Info.plist` | Removed `NSAllowsArbitraryLoads`. The existing `NSAllowsLocalNetworking` keeps LAN dev servers working. |
| `civ-main/lib/core/constants/api_constants.dart`, `lib/main.dart` | `baseUrl` is now a getter. A release build with a missing or non-https `API_BASE_URL` throws a `StateError` at startup. Endpoint constants became getters. Debug and profile keep the `http://127.0.0.1:8000/api` default. |
| `civ-main/lib/services/auth_service.dart`, `lib/screens/auth/otp_screen.dart`, `lib/screens/auth/register_screen.dart` | `verifyOtp` returns `registrationTicket`. OtpScreen pops it back to RegisterScreen, which sends it with `register`. The debug Mock path still sends none. |

**`MobileAuthController.php` in detail:**
- **Registration ticket.** `verifyOtp` (new user) issues a 64-character `registrationTicket`. Only its SHA-256 hash is cached, for 15 minutes, bound to the phone number. `register` requires it and consumes it on success, and returns 422 "Please verify your mobile number first." otherwise. A missing ticket is accepted only when `APP_ENV=local` and `APP_DEBUG=true`, so the Flutter debug Mock registration from 072 keeps working.
- **Per-phone limits.** PIN: 5 failures per 15 min. OTP: 5 failures per 10 min, after which every outstanding code for the phone is burned. SMS sends: 3 per 10 min. Each returns 429 with `Retry-After`.
- **Single-use OTPs.** The consume is an atomic conditional `UPDATE`, inside the transaction for `resetPin`.
- **Other.** `verifyOtp` now rejects deactivated accounts. Login returns one generic "Incorrect mobile number or PIN." message.

## Verification

- **JS syntax.** Every inline `<script>` and `public/js/*.js` file (36 files) was compiled with `new Function()` in a browser served from a throwaway localhost server. `users.html` now compiles. The only remaining error is the dead, unreferenced `js/auth.js`, which was already broken.
- **Diff hygiene.** No mojibake in any added or modified file.
- **PHP.** Diffs reviewed by hand. The `report_activities` FK cascades, so the delete in the reference test is valid.
- **Not run on this machine (no PHP, no Flutter SDK):**
  - **PHP and tests:** `php -l`, `php artisan migrate`, and `php artisan test`. `SecurityRegressionTest` has not been executed yet.
  - **Flutter:** `flutter analyze`, the builds, and a device run. The Dart edits were reviewed by hand only; no const contexts use `ApiConstants`.
- **Commands to run on the PC:**
  ```
  cd laravel
  C:\xampp\php\php.exe artisan migrate
  C:\xampp\php\php.exe artisan test --filter=SecurityRegressionTest
  ```

## Notes

- **Mobile app update.** Live registration needs the updated app: older builds don't send `registrationTicket`. On a local debug backend the old behaviour still works.
- **Release builds** must pass `--dart-define=API_BASE_URL=https://…`.
- **Throttling and lockouts** use the cache store (`CACHE_STORE=database`, `cache` table). The `cache` table is in the dump.

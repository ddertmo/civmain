# First real test run: all 27 tests pass after two fixes

- **Date:** 2026-11-17
- **Commit:** `(see git log)`
- **Type:** fix + verification
- **Areas:** Laravel (routes, test harness)

## Problem

The regression tests from 077–079 had never been executed, because PHP was not installed on this machine. Running them for the first time gave 23 passed and 4 failed.

1. Three failures came from the test harness. Laravel's auth guards cache the first user they resolve for the whole test. Tests that switch tokens mid-test (CEO, then super admin) or deactivate a user between two requests were therefore still running as the stale cached user. Real HTTP requests always authenticate from scratch, so this was not an app bug.
2. One failure was a real routing issue. The catch-all HTML route used `where('any', '.*')`, so it matched every GET path. An unknown `POST`/`PUT`, such as `/api/users/abc`, answered **405** instead of **404**.

## Changes

| File | Change |
|---|---|
| `laravel/tests/TestCase.php` | `call()` runs `auth()->forgetGuards()` before every test request, so each one authenticates like a real request. |
| `laravel/routes/web.php` | The catch-all pattern is now `'.*\.html'`, so it only matches `.html` paths. |

## Verification (run on this machine)

**Setup:**
- PHP 8.3.36 NTS x64 from windows.php.net, with its SHA-256 verified.
- `C:\Users\Administrator\Downloads\tools\php83`, with php.ini enabling curl, fileinfo, gd, intl, mbstring, openssl, pdo_sqlite, sqlite3, zip and pdo_mysql.
- Composer 2.11.2, with its SHA-256 verified.
- `composer install` (lock file, 112 packages).
- A local `.env` from `.env.example` plus `php artisan key:generate`. `vendor/` and `.env` are gitignored.

**Results:**
- `php artisan test` → **27 passed (94 assertions)**:
  - `SecurityRegressionTest` 11/11
  - `MediumFixesTest` 9/9
  - `LowFixesTest` 5/5
  - 2 example tests
- Every migration, including the three new ones, ran on SQLite through `RefreshDatabase`.
- `php -l` passes on every PHP file in app/, routes/, config/, database/, bootstrap/ and tests/.
- `php artisan route:list` loads all 73 routes.
- `php artisan schedule:list` found the daily `sanctum:prune-expired` job. It then failed only because no MySQL server runs on this machine (the cache-lock store is the database).

**Not covered here:**
- Migrations on MySQL/MariaDB, including the ENUM `MODIFY` statements and `->change()` on `otp_codes.code`. These need the XAMPP database: run `php artisan migrate` on the PC.
- Any Flutter analysis or build (no Flutter SDK here).

# Auto-update run scripts for app and backend

- **Date:** 2026-10-07
- **Commit:** `297a78b`
- **Type:** feature
- **Areas:** Flutter / Laravel

## Problem

On the fresh PC clone there was no single way to start the project. The owner
had to remember and run separate manual steps first (`flutter pub upgrade`,
`composer update`, `.env`/key checks) every time — their stated goal was "run
the project and the packages update themselves, no more manual update steps".

## Cause

The repo only documented manual steps (`laravel/docs/SETUP_PC.md`) and the
Termux machine had its own launcher (`~/bin/termux-help-menu`); the PC Windows
workflow had no wrapper at all.

## Changes

| File | Change |
|---|---|
| `RUN_APP.bat` (new) | `cd civ-main` → `flutter pub upgrade` (calls the SDK by absolute path so it works before a terminal picks up the new `PATH`) → `flutter run`. |
| `RUN_BACKEND.bat` (new) | `cd laravel` → `composer update --no-interaction` → create `.env` from `.env.example` if missing → `php artisan key:generate --force` only when `findstr` finds an empty `APP_KEY` (guard stops the key being rotated on every run) → `php artisan serve`. |

## Verification

Commands actually run on this machine (Windows 11, Flutter 3.47.0, PHP 8.2.12,
Composer 2.10.3):

| Check | Result |
|---|---|
| `flutter pub upgrade` (step of RUN_APP) | `Changed 37 dependencies!` ✅ |
| `composer update --no-interaction` (step of RUN_BACKEND) | 81 packages, `No security vulnerability advisories found` ✅ |
| `php artisan serve` + `GET /api/ping` | `HTTP 200 {"success":true,"message":"CivilWatch API is running."}` ✅ |
| `GET /` and `GET /admin/login` | `HTTP 200` — only after the sessions-table fix in [018](2026-10-07-018-missing-sessions-migration.md) ✅ |

**Not verified:** the two `.bat` files themselves were not launched
end-to-end — `RUN_APP.bat` would open Flutter's interactive device picker and
`RUN_BACKEND.bat` blocks on `serve`, so each component command was executed
individually instead; batch syntax is static review only.
**`graphify update .` could not run:** the graphify CLI is not installed on
this Windows machine, so `graphify-out/` was not refreshed.

## Impact / how to test by hand

1. Open `C:\Users\Administrator\Documents\civmain` in Explorer.
2. Double-click `RUN_APP.bat` → packages update, then Flutter starts.
3. Double-click `RUN_BACKEND.bat` → Composer updates, server starts on
   <http://127.0.0.1:8000> (requires MySQL running + the dump imported, see
   `laravel/docs/SETUP_PC.md`).

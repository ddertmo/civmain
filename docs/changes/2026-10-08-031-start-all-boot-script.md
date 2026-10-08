# One-click boot script: START_ALL.bat starts the whole PC stack

- **Date:** 2026-10-08
- **Commit:** `05b8e67`
- **Type:** feature
- **Areas:** docs / PC tooling

## Problem

After a PC restart the running CIVILWATCH stack had to be started by hand in
five places: MySQL, Apache/phpMyAdmin, the `civilwatch` database import, the
Laravel backend, and the Flutter web app. The existing `RUN_APP.bat` and
`RUN_BACKEND.bat` covered the backend + Flutter but **not** MySQL, Apache, or
the one-time database import, and `RUN_BACKEND.bat` runs a slow
`composer update` on every launch.

## Cause

No single entry point existed. Each piece had its own launcher or manual
commands (`mysqld --standalone`, `httpd`, `mysql < civilwatch_FINAL.sql`,
`php artisan serve`, `flutter run -d chrome`), and the two existing `.bat`
files assumed MySQL/Apache were already running and the DB already imported.

## Changes

| File | Change |
|---|---|
| `START_ALL.bat` (new) | Single entry point that, in order and idempotently: (1) starts `mysqld --standalone` if not running, (2) starts `httpd` if not running, (3) imports `civilwatch_FINAL.sql` only when the `civilwatch` DB is missing (strips the MariaDB `M!999999` sandbox line the MySQL client chokes on), (4) creates `.env` + `APP_KEY` if absent and runs `php artisan migrate --force`, (5) starts `php artisan serve --host=127.0.0.1 --port=8000` if port 8000 is closed and waits (up to ~30 s) for `/api/ping`, (6) opens the web admin, phpMyAdmin and `localhost:5000` in Chrome, then (7) `cd`s into `civ-main` and runs `flutter run -d chrome --web-port 5000`. |

Everything is `if … already running` guarded, so re-running the script after a
half-started boot or a crashed service just fills in whatever is missing.

## Verification

Ran `START_ALL.bat` end-to-end twice on Windows (XAMPP MariaDB 10.4.32, PHP
8.2.12, Flutter 3.47.0) while MySQL, Apache and the backend were **already
up**:

```
[1/5] MySQL ............ already running / OK
[2/5] Apache ........... already running / OK
[3/5] Database civilwatch .... present
[4/5] Backend (Laravel :8000) ... Backend up at http://127.0.0.1:8000
[5/5] Opening in Chrome...
Starting Flutter web (first boot takes ~30s to compile)...
Launched: lib\main.dart on Chrome … 30.1s
GET  http://127.0.0.1:8000/api/mobile/reports/community
RESPONSE [200] http://127.0.0.1:8000/api/mobile/reports/community
```

- `http://localhost:5000/` returned HTTP 200.
- The Flutter app loaded and fetched the community feed from the Laravel API
  with `RESPONSE [200]`.
- The one-time DB import helper was tested in isolation by running the exact
  PowerShell line through `cmd`: it strips the `M!999999` line and writes the
  clean file (this is the code path used on a fresh machine where the DB is
  missing).

### First-run path not live-verified

The **missing DB → import** and **missing `.env` → generate** branches were
tested only as isolated commands, not inside the script on a DB-less machine,
because this PC already has `civilwatch` present and `.env` created. The logic
is the same code that succeeded during this session's original one-time setup
(import in the earlier session, `.env` + `key:generate`), but a fully fresh
machine should be smoke-tested once.

## Impact / how to test by hand

Double-click `START_ALL.bat` after a restart (or on a fresh PC after installing
the SDKs). Expect: MySQL + Apache start, the database imports on first run,
Laravel comes up on :8000, three Chrome tabs open, and the Flutter web app
compiles (~30 s) then loads live data. Log in with `admin@civilwatch.ph` /
`Admin@2026!`.
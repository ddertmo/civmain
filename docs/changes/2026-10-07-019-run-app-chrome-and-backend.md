# RUN_APP: run on Chrome and auto-start the backend

- **Date:** 2026-10-07
- **Commit:** `1c08c6e`
- **Type:** fix
- **Areas:** Flutter

## Problem

Double-clicking `RUN_APP.bat` did not produce a running app:

1. `flutter run` (no device flag) opened the device picker. Picking
   **Windows** fails — `flutter doctor` shows *Visual Studio not installed*.
   Picking **Android** fails — *Unable to locate Android SDK*. Only **Chrome**
   can build here.
2. Even after launching on Chrome, every API call in the browser console
   failed: `GET/POST http://127.0.0.1:8000/... net::ERR_CONNECTION_REFUSED`.
   The owner suspected the app was pointing at the old Termux host.

## Cause

1. `RUN_APP.bat` invoked bare `flutter run`, leaving device choice to the
   picker on a machine where 2 of the 3 listed devices cannot build.
2. Nothing was listening on port 8000 — the script never started Laravel, so
   the connection was refused. The host itself is correct:
   `civ-main/lib/core/constants/api_constants.dart:23` defaults to
   `http://127.0.0.1:8000/api` (this machine); a repo-wide grep for
   `termux`, `192.168.*`, `100.*` found **no Termux/LAN host** in any Dart
   file — only override examples in comments. `ERR_CONNECTION_REFUSED` means
   "nobody is listening", not "wrong host".

## Changes

| File | Change |
|---|---|
| `RUN_APP.bat` | `flutter run` → `flutter run -d chrome` (the only buildable device here). Added a PowerShell preflight: test port 8000; if closed, start `C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000` with working dir `laravel`, hidden window. |

## Verification

| Check | Result |
|---|---|
| `flutter run -d chrome` (manual run) | App launched: debug service `ws://127.0.0.1:52345/…`, Chrome window **"CIVILWATCH"** opened ✅ |
| Backend after preflight logic (started same way) | `GET /api/ping` → `HTTP 200 {"success":true,…}` ✅ |
| `GET /api/mobile/reports/community` | `HTTP 200` (456 bytes) — was ERR_CONNECTION_REFUSED ✅ |
| Why Windows/Android fail | `flutter doctor`: Visual Studio ✗, Android SDK ✗, Chrome √ |

**Not verified:** `RUN_APP.bat` itself was not double-clicked end-to-end (it
blocks on the interactive `flutter run`); each component command was executed
individually instead. `graphify update .` could not run — graphify CLI is not
installed on this Windows machine.

## Impact / how to test by hand

1. Make sure XAMPP MySQL is running (data), then double-click `RUN_APP.bat`.
2. Packages update → port 8000 checked/started → app opens in Chrome with a
   working API.
3. Retry the login in the app — errors should now be JSON responses (e.g.
   invalid credentials), never `ERR_CONNECTION_REFUSED`.
4. Note: running on a real phone/emulator still needs
   `--dart-define=API_BASE_URL=…` and `php artisan serve --host=0.0.0.0`
   (see `api_constants.dart` header).

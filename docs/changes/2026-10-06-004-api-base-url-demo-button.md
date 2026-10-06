# API base URL becomes build-time configurable; demo Advance Status button removed

- **Date:** 2026-10-06
- **Commit:** `a16f8c1`
- **Type:** fix
- **Areas:** Flutter

## Problem

1. `baseUrl` was a hardcoded constant. It only worked from the machine running
   Laravel, so an emulator or a phone on the same WiFi could never reach the API
   without editing source and rebuilding.
2. The Track Report screen had a **demo `_AdvanceStatusButton`** that pushed the
   report through Submitted → Pending Validation → Assigned → In Progress →
   Resolved *locally*, with no server call. Anyone tapping it saw a report
   "progress" that the backend never knew about.

## Cause

Prototype leftovers. The button was explicitly flagged in
`INTEGRATION_CONTEXT.md` §5.5 as "only for prototype testing — must be removed
before live integration", and the URL was documented as "if your IP changes,
update `baseUrl` here".

## Changes

| File | Change |
|---|---|
| `civ-main/lib/core/constants/api_constants.dart` | `baseUrl` → `String.fromEnvironment('API_BASE_URL', defaultValue: 'http://127.0.0.1:8000/api')`; doc comment rewritten with the emulator (`10.0.2.2`) and LAN-IP invocations and the `php artisan serve --host=0.0.0.0` requirement |
| `civ-main/lib/screens/track_report/status_update_screen.dart` | deleted the `_AdvanceStatusButton` widget and its call site (881 → 798 lines, −83) |

## Verification

```bash
# brace / paren balance on the edited screen
python3 -c "s=open('civ-main/lib/screens/track_report/status_update_screen.dart').read();print(s.count('{'),s.count('}'),s.count('('),s.count(')'))"
# 29 29 320 320
grep -rn "_AdvanceStatusButton" civ-main/lib   # no matches
```

**Not verified:** no Flutter SDK here — never ran `dart analyze`.

## Impact / how to test by hand

```bash
cd civ-main
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api   # emulator
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api  # phone on same WiFi
```
Track Report must show only server-driven statuses — no button that advances
status on its own.

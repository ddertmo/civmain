---
date: 2026-10-16
commit: b9c9bfe
type: feature
areas: [civ-main/lib/core/state/app_state.dart, civ-main/lib/app.dart, civ-main/lib/core/theme/app_theme.dart, civ-main/lib/screens/profile/profile_screen.dart]
---

# Dark mode on/off toggle in Profile (and fix the missing dark theme body)

## Problem

The app had dark mode hardcoded on (`themeMode: ThemeMode.dark`) with no way to
turn it off — the user wanted an on/off switch like the website's toggle. On top
of that, the committed build did not compile: `AppTheme.dark` referenced
`_darkTheme`, which was never actually inserted into `app_theme.dart` (an
earlier scripted edit checked for the string `"dark"`, matched the getter line
it had just added, and skipped inserting the definition).

## Cause

- `civ-main/lib/app.dart:21` set `themeMode: ThemeMode.dark` as a constant, so
  no UI could change it.
- `civ-main/lib/core/theme/app_theme.dart:10` declared
  `static ThemeData get dark => _darkTheme;` but no `_darkTheme` field existed
  anywhere in the file → analyzer error
  `undefined_identifier` at `app_theme.dart:10:32`.
- Theme choice had no persistence layer, so even a toggle would not survive a
  restart.

## Changes

| Path | Change |
|---|---|
| `civ-main/lib/core/state/app_state.dart` | Added `isDarkMode` + `setDarkMode(bool)` to the `AppState` singleton; choice persisted with `shared_preferences` key `dark_mode`; loaded in `init()` before the first frame (default: dark, preserving the shipped look). `notifyListeners()` rebuilds the `ListenableBuilder` in `app.dart`. |
| `civ-main/lib/app.dart` | `themeMode` now derives from `AppState().isDarkMode` (`ThemeMode.dark` / `ThemeMode.light`). |
| `civ-main/lib/core/theme/app_theme.dart` | Inserted the previously missing `_darkTheme` definition (dark scaffold `0xFF0F172A`, dark surfaces `0xFF1E293B`, dark app bar / nav bar / chips / inputs / text theme). |
| `civ-main/lib/screens/profile/profile_screen.dart` | New **Dark Mode** row in the Profile menu with a `Switch` (tapping the row toggles too); `_MenuTile` gained an optional `trailing` widget. |

## Verification

- `flutter analyze --no-pub` → **0 errors** (the pre-edit run reported
  `error - Undefined name '_darkTheme' ... app_theme.dart:10:32`; after the fix
  no `error -` lines remain; ~112 pre-existing `info` lints unchanged).
- `flutter run -d chrome --web-port=8080` rebuilt and served → HTTP 200.
- Laravel `http://127.0.0.1:8000/admin/login` → HTTP 200.
- **Not verified:** no automated UI test exists (Playwright was removed by
  request), so the visual toggle behavior was not exercised by a test suite.

## Impact / how to test by hand

1. Start the app (`flutter run -d chrome --web-port=8080`).
2. Log in → open **Profile** → menu → **Dark Mode** row.
3. Flip the switch (or tap the row): the whole app switches light/dark
   immediately.
4. Restart the app — the chosen mode persists (stored under the
   `dark_mode` key in `shared_preferences`).

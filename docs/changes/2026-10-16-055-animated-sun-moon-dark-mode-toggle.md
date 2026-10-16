# Animated Sun/Moon dark mode toggle — and a palette that actually switches app-wide

- **Date:** 2026-10-16
- **Commit:** `f8e24a7`
- **Type:** fix
- **Areas:** Flutter

## Problem

Two things the user reported / asked for:

1. **The dark mode switch did nothing.** Turning Dark Mode on in Profile (or
   setting `themeMode`) left every screen still light — the control looked
   broken. The user also wanted one working Light/Dark toggle in the Home
   header.
2. **The Home header had a second, duplicate notification bell.** Notifications
   are already reachable from the bottom navigation bar.

## Cause

The theme plumbing was already correct: `AppState.isDarkMode` (persisted via
`shared_preferences`) drove `themeMode` in `app.dart:21-22` inside a
`ListenableBuilder`.

The break was downstream: **all styling was compile-time `const`.** Screens
read `AppColors.*` as `const` fields (232 `AppColors.white`, ~62 hardcoded
backgrounds) and there were **zero `Theme.of(context)` usages** anywhere in
`lib/`. Const colors are baked in at compile time, so changing `themeMode`
never changed a single painted pixel — the toggle was purely cosmetic.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/core/constants/app_colors.dart` | Split into two groups: **accent** tokens (primary/navy/status hues) stay `const`; **surface** tokens (background, card, divider, text, input, status tints) became plain static fields swapped by the new `AppColors.setDark(bool)`. Added `onAccent` (const white for icons/text/rings on accent surfaces) and `card`; `white` is now a dynamic alias → `card`, so ~200 existing surface reads follow the theme without editing each one. |
| `civ-main/lib/core/state/app_state.dart` | `setDarkMode()` and `_loadThemePreference()` call `AppColors.setDark()` (before the first frame, so nothing flashes). New `_applySystemUi()` matches the status/navigation bar brightness to the active theme. |
| `civ-main/lib/core/theme/app_theme.dart` | Rewrote `light`/`dark` to use **fixed** per-mode palettes instead of the mutable tokens, so `theme` and `darkTheme` can't capture whichever token state existed when they were first built. |
| `civmain/…/widgets/common/theme_toggle.dart` (new) | The animated Sun/Moon pill: moving thumb (`AnimatedContainer`/`AnimatedAlign`), crossfade + slight rotation of the Sun/Moon icon (`AnimatedSwitcher`), track colour (warm yellow in light, indigo in dark), `Semantics` + `Tooltip`, and duration→0 when the OS reports reduced motion. Writes to the same `AppState.setDarkMode`. |
| `civ-main/lib/screens/home/home_screen.dart` | Removed the bell, its badge, and the now-unused `unreadCount` field/param from `_HomeAppBar`; inserted `ThemeToggle`. The bottom navigation's Notifications tab and `notification_screen` are untouched. |
| `civ-main/lib/main.dart` | Removed the hardcoded light system-UI overlay (it would have overridden the theme-aware one set by `AppState`). |
| ~47 files across `lib/` | Foreground-on-accent `AppColors.white` → `AppColors.onAccent` (icons/text/white rings on navy/green circles) so they stay white in dark mode; 152 `const` keywords removed at exactly the spots where tokens are now non-const. |
| `civ-main/test/theme_tokens_test.dart` (new) | Focused test: `AppColors.setDark` swaps the surface tokens while keeping accents constant, and `AppState.setDarkMode` flips the palette + persists `dark_mode`. |

## Verification

Commands actually run in `civ-main/`:

- `flutter analyze --no-pub` → **0 errors**, 111 issues (all pre-existing
  infos/warnings — same baseline as before the change).
- `flutter build web` → `√ Built build\web`, exit 0.
- `flutter test test/theme_tokens_test.dart` → `+2: All tests passed!`
- Served the fresh build: `http://localhost:8080/` → **200**;
  `http://localhost:8000/` → **200**.
- `dart format` was run on the **new** `theme_toggle.dart` only.

**Known follow-up (deferred by the user, logged in `docs/TASKS.md`):** the
status/navigation bars are theme-driven via `AppState._applySystemUi()`, but
four screens still hardcode `statusBarIconBrightness: Brightness.dark` on every
build (`home_screen.dart:40`, `splash_screen.dart:26`,
`landing_screen.dart:26`, `visitor_shell.dart:37`) and override it — in dark
mode those status icons stay dark. Visitor mode also has **no theme toggle at
all** (the toggle lives only in the logged-in Home header; guests have no
Profile). Both are tracked in `docs/TASKS.md` for a later change.

**Not verified / explicit caveats:**

- `flutter test` (the whole suite) **still fails** on the pre-existing
  `CivilWatch app smoke test`. I confirmed this failure is **not caused by this
  change** by running the same test at `HEAD` in a temporary `git worktree` — it
  fails identically there. Cause: `splash_screen.dart:45` schedules
  `Future.delayed(3s)`, which leaves a pending timer after the tree is disposed.
- **No automated browser/runtime visual check** of the toggles or of dark-mode
  legibility on each screen. Playwright was removed from this project, so there
  is no browser driver. The sweep was verified **statically**: every
  `Scaffold.backgroundColor`, every `helpers.dart` status/category background,
  and the `AppTextStyles` palette now go through the tokens; `Colors.white` is
  not used as a surface anywhere (its only remaining match is the toggle knob).

## Impact / how to test by hand

1. Open the app → Home header shows a Sun/Moon pill where the bell was.
2. Tap it → the whole app flips immediately (warm-yellow track/sun in Light,
   indigo track/moon in Dark).
3. Go to Profile → Dark Mode shows the same position/state; toggling it there
   changes the app too (both controls share one source of truth).
4. Restart the app → the last choice is restored (no wrong-theme flash).
5. Bottom nav → Notifications tab still opens the notification list.

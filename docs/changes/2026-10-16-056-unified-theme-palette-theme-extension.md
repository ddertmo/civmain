# Unified theme palette as a `ThemeExtension` — one theme state, Light default

**Date:** 2026-10-16
**Type:** fix / refactor (theming)

## Problem

Light and Dark mode rendered inconsistently across the app: some screens,
headings, cards, inputs and the bottom nav kept light colors in dark mode, and
the Home header toggle and the Profile switch were two different controls.

**Root cause:** surface colors came from **mutable static fields** on
`AppColors` (`background`, `card`, `textPrimary`, `status*Bg`, …). Those fields
are *not* part of the widget tree, so changing them did not mark any widget
dirty. Only widgets that happened to rebuild for another reason picked up the
new colors; `const` subtrees and idle screens kept the stale palette. There were
also two toggle designs and four hardcoded `SystemChrome` status-bar overrides
that fought the theme.

## Fix

- **`AppPalette` (new, in `core/constants/app_colors.dart`)** — a
  `ThemeExtension<AppPalette>` holding every surface token, with `AppPalette.light`
  and `AppPalette.dark` const instances (values are the previous `_*Light`/`_*Dark`
  constants, unchanged). Adding `context.c` (a `BuildContext` extension) lets a
  widget read the active palette from the tree, so the framework rebuilds it on a
  theme change — including `const` widgets, because the dependency is resolved
  through `Theme.of(context)`.
- **Deleted the mutable state.** `AppColors` now holds only the theme-invariant
  **accent** tokens (`primary`, `navy`, status/category hues, `onAccent`,
  `cardShadow`, `loginBgDark`). `setDark`, `isDark`, the `white` alias and all
  surface statics are gone — there is exactly **one** theme state,
  `AppState.isDarkMode`.
- **`app_theme.dart`** sources its colors from `AppPalette.light` /
  `AppPalette.dark` and registers `extensions: const [AppPalette.light]` /
  `[AppPalette.dark]` on the two `ThemeData`s.
- **Default is Light.** `_isDarkMode = false`; `_loadThemePreference()` reads
  `prefs.getBool('dark_mode') ?? false`. A saved Dark/Light choice is preserved.
- **Unified toggle.** Profile now renders the same animated `ThemeToggle`
  (Sun/Moon pill) as the Home header; the stock Material `Switch` is removed.
- **Removed the 4 hardcoded `SystemChrome.setSystemUIOverlayStyle` calls**
  (home, splash, landing, visitor_shell). `AppState._applySystemUi()` (init +
  every toggle) and the per-theme `appBarTheme.systemOverlayStyle` are now the
  single authority.

## Files

55 files changed (54 under `lib/`, 1 test). Mechanical part: ~50 screens/widgets
had `AppColors.<surface>` rewritten to `context.c.<surface>`, and the
status/category color lookups moved from `AppHelpers` to `AppPalette`
(`context.c.statusColor/statusBg/categoryColor/categoryBg`) across 58 call sites.
`const` was dropped where it wrapped a palette read. `core/theme/text_styles.dart`
(dead code, `AppTextStyles` has no call sites) was **kept** and only had the two
now-invalid surface `color:` lines removed so it still compiles — it was not
deleted.

## Non-goals (unchanged)

No layout, branding, navigation, routing, API, DB, auth, report-submission or
progress-tracker changes. The Community Map canvas stays light by request; only
its surrounding card/legend/labels/borders are now theme-aware. No new packages.

## Verification

- `flutter analyze --no-pub` → **0 errors** (111 issues total, identical to the
  HEAD baseline: the same pre-existing deprecation/info/warning set — no new
  issues introduced).
- `flutter test test/theme_tokens_test.dart` → **4/4 pass** (palette values,
  status/category lookups, Light default, single-state persistence).
- `flutter test` (full suite) → 4 pass, 1 fail: the **pre-existing**
  `widget_test.dart` smoke test ("A Timer is still pending" — the splash
  `Future.delayed(3s)`), unchanged from HEAD and unrelated to theming.
- `flutter build web` → succeeds.
- Manual: Home/Profile toggles are identical and flip the whole app immediately;
  preference persists across restart; default first launch is Light.

> Note: `AppPalette` is registered on both `ThemeData`s in `app_theme.dart`.
> A unit test cannot build `AppTheme` because `google_fonts` fetches Inter at
> construction time (fails/skips in the test sandbox), so the extension
> registration is covered by the analyzer + the running app rather than a test.

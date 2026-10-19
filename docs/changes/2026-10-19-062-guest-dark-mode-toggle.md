# Guests were stuck in one theme — Dark Mode toggle added to the visitor banner

- **Date:** 2026-10-19
- **Commit:** `958ce55`
- **Type:** fix
- **Areas:** Flutter

## Problem

Visitor mode had **no theme control at all**. Guests (`VisitorShell`) have no
Profile screen, and the animated Sun/Moon `ThemeToggle` (added to the logged-in
Home header in [055](2026-10-16-055-animated-sun-moon-dark-mode-toggle.md))
lived only there — so a guest was stuck with whatever theme was active and had
no way to switch. Task 2 in `docs/TASKS.md`; the "leave visitor mode as-is"
decision of 2026-10-16 was superseded by the user approving the task on
2026-10-19.

## Cause

Placement gap, not a state bug: the toggle widget already reads and writes the
single source of truth `AppState().setDarkMode` (persisted under the
`dark_mode` key), but it was simply never mounted anywhere in the guest flow.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/screens/visitor/visitor_shell.dart` | `_GuestBanner` row: added `const SizedBox(width: 12)` + `const ThemeToggle()` between the "Browsing as Guest" text column and the "Login / Register" pill, plus the `theme_toggle.dart` import (7 lines, comment included) |

**Placement decision (the task left this open):** the `_GuestBanner` is a
`Positioned` overlay at the top of the shell's `Stack`
(`visitor_shell.dart:43-53`), so it is visible on **every** guest tab — Map,
Reports and About — whereas `VisitorAboutScreen`'s header would have shown it
on one tab only. It was chosen for that reason.

**Tap-safety:** the banner's whole row is an `InkWell` whose tap opens the
login navigation. The toggle's own inner `GestureDetector` wins the gesture
arena for taps inside its bounds, so toggling the theme does **not** trigger the
login flow — noted in a comment at the call site; still worth confirming by
hand (see below).

Visual: the toggle's amber (light) and indigo (dark) tracks both read clearly
against the banner's fixed navy fill.

## Verification

- `flutter analyze --no-pub` → **109 issues, 0 errors** (baseline). This run
  and the tests below were executed with both this change **and** the companion
  status-tint change (061) in the working tree; they are independent and were
  committed separately.
- `flutter test test/theme_tokens_test.dart` → **+4 All tests passed**.
- `flutter test` → **+4 −1**, only the documented pre-existing
  `widget_test.dart` failure.
- `flutter build web` → **`√ Built build\web`**.
- Read the code paths: `ThemeToggle` builds a `ListenableBuilder` on
  `AppState()`, so the banner toggle repaints the whole `VisitorShell` (its
  pages read `context.c.*`), same as the Home header instance.
- **Not verified:** the actual tap and the persistence round-trip in a running
  app — no screen access from here (Playwright was removed); needs a human
  hand-check.

## Impact / how to test by hand

1. Run the app **logged out** and open the visitor flow (`RUN_APP.bat`, or
   `flutter run -d chrome` with the backend up — the Chrome window opened from
   an older build must be restarted to see this).
2. In the guest banner at the top of **every** tab, tap the Sun/Moon pill:
   the banner, the bottom nav, the map/reports/about content and the status
   icons should all flip instantly.
3. Tap the pill again — flips back; **tap "Login / Register"** — must still
   open the login flow (i.e. the toggle swallowed only its own taps).
4. Kill and relaunch the app — the guest theme must **persist** (same
   `dark_mode` key as Home/Profile).
5. Status-bar icons: light icons in Dark Mode, dark in Light Mode, on guest
   screens (single authority `AppState._applySystemUi()`, fixed in
   [056](2026-10-16-056-unified-theme-palette-theme-extension.md)).
6. When the hand-check passes, check off task 2 in `docs/TASKS.md`.

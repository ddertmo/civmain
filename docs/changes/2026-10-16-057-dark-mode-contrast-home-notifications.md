# Dark Mode still hid Home and Notifications text after the ThemeExtension refactor

- **Date:** 2026-10-16
- **Commit:** `3040d62`
- **Type:** fix
- **Areas:** Flutter

## Problem

Change [056](2026-10-16-056-unified-theme-palette-theme-extension.md) moved every
surface color onto `AppPalette` and gave the app one theme state, but a manual
pass in Dark Mode still found unreadable screens:

- **Notifications** showed several cards with a **white background** while their
  titles were near-white — the text was effectively invisible.
- **Home** had low-contrast pieces: the `CIVILWATCH` header text and logo disc,
  the `View All` / `View Map` links, section headings and secondary text, the
  report statistic cards, the Community Map legend and its surrounding card, and
  the announcement cards.

Light Mode was reported as intended and had to stay unchanged.

## Cause

Three distinct defects, all "fixed at the call site rather than at the token":

1. **A foreground token used as a fill.** `AppColors.onAccent` is documented in
   `app_colors.dart` as the *always-white foreground for icons/text on accent
   surfaces*. Two places used it as a **background**:
   - `notification_card.dart:29` — read notifications were filled with
     `AppColors.onAccent`, i.e. pure white in *both* themes, while the title read
     `context.c.textPrimary` (near-white `#F1F5F9` in dark) → **1.10:1**.
   - `home_screen.dart:501` — the mini-map legend overlay was
     `AppColors.onAccent.withOpacity(0.92)`, but its labels are
     `context.c.textSecondary` (`#94A3B8`) → **2.56:1** on white in dark.

2. **The brand green has no dark-surface variant.** `AppColors.primary`
   (`#1B5E20`) is a `const` accent, correct on light surfaces but ~1.2:1 on the
   dark palette, so every green *text/icon* drawn on a themed surface collapsed
   in Dark Mode: the section links (`section_title.dart:41,48`, **2.27:1** on
   `#0F172A`), `Mark all read` and the empty-state icon
   (`notification_screen.dart:100,127`), the announcement icon on
   `primarySurface` (`home_screen.dart:629`, **1.16:1**), and the unread dot
   (`notification_card.dart:66`, deep green on `#14532D`). No token existed that
   was both brand-green and readable on `#0F172A` / `#1E293B` / `#14532D`.

3. **Themed ink over a theme-invariant surface**, plus brand ink that never
   adapts: the `CIVILWATCH` wordmark and the Community Map my-location icon /
   reference number used `AppColors.navy` (`#0D2137`) on themed cards
   (**1.11:1** in dark); and `'Digos City'` used `context.c.textPrimary`, which
   flips near-white in dark while the map canvas is *deliberately* fixed light
   (`_HomeMiniMapPainter`, `home_screen.dart:723`) → **1.02:1**.

## Changes

| File | Change |
|---|---|
| `lib/core/constants/app_colors.dart` | New `AppPalette.primaryOnSurface`: light `AppColors.primary` (`0xFF1B5E20`, so Light is unchanged), dark `0xFF22C55E`. Field + constructor param + `light` + `dark` + `copyWith`. Fix is at the token level, read through `context.c` — no brightness checks, no second theme system. |
| `lib/widgets/cards/notification_card.dart` | Read-card fill `AppColors.onAccent` → `context.c.card`. Unread dot → `context.c.primaryOnSurface`. Unread fill/border and all title/message/time colors left as-is (already themed). |
| `lib/screens/notifications/notification_screen.dart` | `Mark all read` and the empty-state icon → `context.c.primaryOnSurface`. Unread pill unchanged (accent surface, correct in both themes). |
| `lib/widgets/common/section_title.dart` | `View All` / `View Map` label + chevron → `context.c.primaryOnSurface`. |
| `lib/screens/home/home_screen.dart` | Wordmark `navy` → `context.c.textPrimary`; logo disc gains a `context.c.divider` border (invisible on light, defines the disc on the dark header); mini-map legend fill → `context.c.card`; `'Digos City'` → `AppColors.navy.withValues(alpha: 0.7)` (fixed dark ink on the fixed-light canvas); announcement icon → `context.c.primaryOnSurface`. |
| `lib/screens/community_map/community_map_screen.dart` | My-location icon and reference number `navy` → `context.c.textPrimary`. Community Map is Home tab index 2. |
| `test/theme_tokens_test.dart` | Asserts `light.primaryOnSurface == AppColors.primary`, `dark.primaryOnSurface == 0xFF22C55E`, and that the two differ. |

Not changed, deliberately: theme state / preferences / `ThemeExtension` shape
beyond the one field; `ThemeToggle`; map canvas painters; layout, spacing and
branding; APIs, auth, report flows, the progress tracker; `AppColors.navy` on
accent surfaces (green CTA banner, logo disc) where it is correct; and the
`withOpacity` warnings already in the baseline.

`withValues()` was used instead of `withOpacity()` for the map label on purpose —
`withOpacity` is `deprecated_member_use` here and adding one would have changed
the analyzer baseline.

## Verification

Run on the Windows PC (Flutter 3.47.0 / Dart 3.13.0) after the change:

```
flutter analyze --no-pub   -> 109 issues found. (0 errors)
```
Baseline measured immediately before the edits was **111 issues / 0 errors**.
The delta is exactly `-2`, both of them the `deprecated_member_use` entries for
the two `withOpacity` calls this change removed (`home_screen` legend fill and
map label). A line-by-line check of every issue still reported in the seven
touched files shows only pre-existing ones (`unused_import` ×2, `withOpacity`
at untouched lines, `_ZoomBtn`, `unnecessary_underscores`) — **no issue at any
line that was edited**.

```
flutter test test/theme_tokens_test.dart -> 00:00 +4: All tests passed!
flutter test                             -> 00:02 +4 -1: Some tests failed.
```
The single failure is the documented pre-existing one: `test/widget_test.dart`
"CivilWatch app smoke test" — `A Timer is still pending` from the splash
`Future.delayed(3s)`. Identical to the baseline recorded before these edits.

```
flutter build web -> √ Built build\web
```
(The command still exits non-zero in PowerShell because of the pre-existing
`flutter_secure_storage_web` Wasm dry-run warning.)

**Not verified:** the rendered result. There is no screen/image access and
Playwright E2E was removed from the project, so the fixes are analyzer-,
test- and build-verified only. The user must confirm the appearance in a
browser in both themes.

## Impact / how to test by hand

1. `flutter build web` (already done) and open the app on `:8080`; the toggle
   sits in the Home header.
2. **Dark Mode → Notifications:** every card is slate (read) or green-tinted
   (unread); titles, messages and dates are readable; the unread dot, group
   labels and status icon circles are visible. Check Pending Validation,
   In Progress, Assigned to Office and Resolved where present.
3. **Light Mode → Notifications:** unchanged — white cards, dark text.
4. **Dark Mode → Home:** `CIVILWATCH` reads clearly with the logo disc defined
   against the header; `View All` / `View Map` are a readable green; stat cards,
   section headings and announcement text are readable; the mini-map legend is a
   dark card with readable labels and `Digos City` stays dark on the light map.
5. **Light Mode → Home:** visually unchanged.
6. **Community Map tab (dark):** the my-location icon and the `Ref. No.` value
   in the detail sheet are readable.
7. Toggle the header switch repeatedly — every element above must flip
   immediately, with no restart.

## Follow-up

Recorded in [`docs/TASKS.md`](../TASKS.md): dark-mode contrast of the status
tint backgrounds is marginal for three hues (Submitted `2.67:1`, Assigned
`2.84:1`, Approved `2.88:1`, against the 3:1 non-text target). Out of scope
here by decision — do not redesign status badges as part of this fix.

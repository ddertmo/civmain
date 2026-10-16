# Dark Mode still hid the Profile and My Reports icons — same fixed-ink defect as 057

- **Date:** 2026-10-16
- **Commit:** `f8d5e03`
- **Type:** fix
- **Areas:** Flutter

## Problem

Two user reports (CW-FIX), both in Dark Mode:

1. **My Reports** — the document icon inside the green empty-state circle
   ("No reports found") was almost invisible.
2. **Profile** — the icons for **Help Center**, **Dark Mode** and the other
   menu items had poor contrast against their backgrounds.

Light Mode was reported as intended and had to stay unchanged.

## Cause

The **same root cause as** [057](2026-10-16-057-dark-mode-contrast-home-notifications.md),
in call sites 057 never reached: a *fixed* brand colour used as **foreground
ink** on a **themed** surface.

Two tokens were responsible:

| Ink used | Surface (Dark Mode) | Contrast |
|---|---|---|
| `AppColors.primary` `#1B5E20` | `primarySurface` `#14532D` | **1.16:1** |
| `AppColors.primary` `#1B5E20` | `card` `#1E293B` | **1.86:1** |
| `AppColors.navy` `#0D2137` | 10 % navy tile over `card` | **1.10:1** |

The dark-capable token **already existed** — `AppPalette.primaryOnSurface`
(`#22C55E`), added in 057 — but these sites had never been migrated to it, and
no equivalent existed for the navy-ink case. So the fix is at the token level,
read through `context.c`; **no new token, no brightness checks, no second theme
system.**

The sweep also found the defect on the **persistent bottom navigation bar**:
the *active* tab icon and label measured 1.86:1 against the dark `card` while
the *inactive* ones measured 3.07:1 via `context.c.textHint` — so in Dark Mode
the **active** tab looked dimmer than the inactive ones.

## Changes

| File | Change |
|---|---|
| `lib/widgets/common/empty_state.dart:36` | The 80 px circle keeps `context.c.primarySurface`; its 36 px icon `AppColors.primary` → `context.c.primaryOnSurface` (1.16 → 4.0:1). Fixes the My Reports empty state (`my_reports_screen.dart:249` is the only caller). |
| `lib/screens/profile/profile_screen.dart` | **Reported:** Help Center icon `:262`; Dark Mode icon `:273` `AppColors.navy` → `context.c.textPrimary` (1.10 → 13.5:1) while keeping its tinted tile. **Same screen, same defect:** "Edit" pill icon `:80` + label `:86`, "Resident" chip label `:161` (1.16:1), and the avatar's 2.5 px green ring `:103` (1.86:1). |
| `lib/widgets/navigation/bottom_nav.dart:64,76` | Active tab icon + label → `context.c.primaryOnSurface` (1.86 → 6.4:1). The centre Map circle `:52` is a *fill* with white text and was left alone. |
| `lib/widgets/timeline/progress_timeline.dart:110` | Completed-step connector → `context.c.primaryOnSurface`. |
| `lib/screens/auth/forgot_password_flow.dart:341` | "Check your email" icon in the green circle. |
| `lib/screens/auth/otp_screen.dart:429,436` | Security-note icon + copy on the green note. |
| `lib/screens/landing/landing_screen.dart:293` | Feature icon in the green circle. |
| `lib/screens/map_preview/private_map_screen.dart:340,656` | Before/After info icons in the green circles. |

Scope was **user-approved before editing**: the two reported screens, the rest
of Profile, and the confirmed same-defect sites listed above. Status tint
colours were explicitly left alone (deferred to its own `docs/TASKS.md` item),
and every `AppColors.primary` / `AppColors.navy` use that is a **fill** carrying
`onAccent` text was left alone — button fills, badges, the bottom-nav centre
circle, the profile camera badge, the About dialog. No new palette token was
needed, so `app_colors.dart` is unchanged.

**Light Mode is provably unchanged:** `primaryOnSurface` in light *is*
`AppColors.primary`, and `textPrimary` light `#0F172A` vs navy `#0D2137` is
imperceptible (the same swap 057 already made for the Home wordmark). Colour
arguments only — no layout, size, spacing, navigation or branding change.
`const` was dropped on three `Icon`s (`private_map:656`, `otp:428`,
`profile:79`) whose colour is now theme-dependent.

## Verification

Run on the Windows PC (Flutter 3.47.0 / Dart 3.13.0):

```
flutter analyze --no-pub   -> 109 issues found. (0 errors)
```
Identical to the 057 baseline. The first pass caught one genuine error I had
introduced — `private_map_screen.dart:657:38 invalid_constant`, from changing a
colour inside a still-`const` `Icon(`; dropping the `const` cleared it. The
remaining issues reported in the eight touched files are all pre-existing
(`withOpacity` deprecations and `unnecessary_underscores`) at untouched lines —
**nothing at any edited line.**

```
flutter test test/theme_tokens_test.dart -> 00:00 +4: All tests passed!
flutter test                             -> 00:02 +4 -1: Some tests failed.
```
The single failure is the documented pre-existing one: `test/widget_test.dart`
"CivilWatch app smoke test" — `A Timer is still pending` from the splash
`Future.delayed(3s)`. Identical to the baseline.

```
flutter build web -> √ Built build\web
```
(Still exits non-zero in PowerShell because of the pre-existing
`flutter_secure_storage_web` Wasm dry-run warning.)

**Not verified:** the rendered result. There is no screen/image access and
Playwright E2E was removed from the project, so these fixes are
analyzer-, test- and build-verified only. The user must confirm the appearance
in a browser in both themes.

## Impact / how to test by hand

1. Open the app on `:8080`; the theme toggle is in the Home header (and in
   Profile → Dark Mode).
2. **My Reports (light, no reports):** search a term with no matches → the
   green circle now shows a readable document icon. Toggle to dark → still
   readable.
3. **Profile (dark):** the "Edit" pill, the "Resident" chip, the avatar's green
   ring, and the Help Center / Dark Mode menu icons are all clearly visible.
   The "My Reports" and "Notifications" tiles are unchanged.
4. **Bottom nav (dark):** the active tab's icon *and* label are now brighter
   than the inactive ones (previously dimmer).
5. **Track a report (dark):** the progress timeline's completed connectors are
   visible between the status circles.
6. **Pre-login (dark):** the landing screen's feature circles, the
   forgot-password "check your email" circle, and the OTP security note are
   readable.
7. **Light Mode:** spot-check the same seven places — visually unchanged.

## Follow-up

Recorded in [`docs/TASKS.md`](../TASKS.md): the **pre-login flow** (landing
CTA, `_LandingButton`, OTP timer/resend/back, forgot-password hint + secondary
button + OTP input text) still uses fixed `AppColors.primary` / `AppColors.navy`
ink and measures 1.02–2.3:1 in Dark Mode. Those sites were **outside** the
approved scope for this change. The status-tint item from 057 also stands.

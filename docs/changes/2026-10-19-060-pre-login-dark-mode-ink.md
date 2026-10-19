# Dark Mode hid the whole pre-login flow �?" same fixed-ink defect as 057/058, now on landing, auth, visitor and track screens

- **Date:** 2026-10-19
- **Commit:** `54d1ebe`
- **Type:** fix
- **Areas:** Flutter

## Problem

In Dark Mode the pre-login and visitor flow was covered in unreadable text and
icons (CW-FIX, continuation of the audit deferred in
[059](2026-10-16-059-record-pre-login-contrast-task.md)):

- **Typed digits were invisible** �?" the OTP cells (`otp_box`, forgot-password)
  and the login PIN field showed navy `#0D2137` digits on the `#1E293B` input
  fill: **1.11:1**.
- **Links and labels vanished**: "Forgot Password?", "Register", "Resend OTP",
  the countdown, both Back buttons, "Privacy Policy"/"Terms of Service", the
  reference numbers on track/report cards �?" all **1.05�?1.11:1**.
- **The CIVILWATCH wordmark disappeared** on Login, Splash and Visitor-About
  (navy on `#0F172A`/`#1E293B`: **1.10�?1.11:1**).
- **Focus rings were invisible**: tapping any input showed a navy/green ring at
  **1.05�?1.86:1** instead of the 3:1 minimum for UI components.
- **Green brand icons/labels sat at 1.16�?2.27:1** �?" Explore-as-Visitor button,
  security-hint box, active guest-nav item, "View on Map", office block,
  "No login required", stepper/timeline connectors.

Light Mode had to stay unchanged, and this was the audit spec'd (but not
started) in `docs/TASKS.md` task 4.

## Cause

The **same root cause as** [057](2026-10-16-057-dark-mode-contrast-home-notifications.md)
and [058](2026-10-16-058-dark-mode-ink-profile-reports-widgets.md): a *fixed*
brand colour used as **foreground ink** on a **themed** surface. The tokens are
`const` and identical in both themes, so in Dark Mode they stay dark while the
surface stays dark:

| Ink | Surface (Dark Mode) | Contrast |
|---|---|---|
| `AppColors.navy` `#0D2137` | `card`/`inputFill` `#1E293B`, `background` `#0F172A` | **1.05�?1.11:1** |
| `AppColors.primary` `#1B5E20` | `primarySurface` `#14532D` | **1.16:1** |
| `AppColors.primary` `#1B5E20` | `card` `#1E293B` | **1.86:1** |
| `AppColors.primary` `#1B5E20` | `loginBg` `#0F172A` | **2.27:1** |

The audit re-ran the `AppColors.(primary|navy)` sweep from scratch (221 matches
across 41 files; the line numbers recorded in `docs/TASKS.md` had drifted) and
confirmed the defect line-by-line against the real backgrounds before any code
was touched. The dark-capable replacements already exist and were approved with
057/058: `context.c.primaryOnSurface` (`#22C55E` in dark, `AppColors.primary`
in light) and `context.c.textPrimary` (`#F1F5F9` dark / `#0F172A` light).

Two recorded claims from the audit entry did **not** survive inspection and were
deliberately left alone:

- **Landing's CIVILWATCH wordmark** (`landing_screen.dart:130`) is *not*
  1.1:1 as recorded �?" Landing keeps a fixed light gradient
  (`#EAF4EE -> card` over the top 45 %) in Dark Mode, so the wordmark renders at
  **~5:1** where it sits. Unchanged.
- **The eco-icon inside the shield logos** (landing/login/splash/visitor-about)
  is green-on-navy **2.02:1 in *both* themes** �?" a brand logo identical in
  Light Mode, so it was left (changing it would alter Light Mode).

## Changes

| File | Change |
|---|---|
| `screens/landing/landing_screen.dart` | Explore-as-Visitor icon, label, chevron + its borders: `AppColors.primary -> context.c.primaryOnSurface` (7 lines) |
| `screens/auth/otp_screen.dart` | countdown, Resend link, Back icon/label navy -> `textPrimary`; spinner, hint-box border, star -> `primaryOnSurface` (7 lines) |
| `screens/auth/forgot_password_flow.dart` | hint lightbulb + text, Resend link -> `primaryOnSurface`; secondary-button fg, OTP digits, focus borders, status dot -> `textPrimary`/`primaryOnSurface` (8 lines) |
| `screens/auth/login_screen.dart` | wordmark, login-tile icon, both focus rings, Forgot/Register links, PIN digits -> `textPrimary`; badge icons -> `primaryOnSurface` (8 lines) |
| `screens/auth/register_screen.dart` | account/Privacy/Terms links, filled OTP digits + border -> `textPrimary`; 3 focus rings + OTP `activeColor`, checkbox borders -> `primaryOnSurface` (10 lines) |
| `screens/splash/splash_screen.dart` | wordmark -> `textPrimary`; spinner -> `primaryOnSurface` (2 lines) |
| `screens/visitor/visitor_shell.dart` | active guest-nav icon + label -> `primaryOnSurface`; "Login" nav label -> `textPrimary` (3 lines) |
| `screens/visitor/visitor_about_screen.dart` | wordmark + outlined-button label -> `textPrimary`; feature icons (param + render site) -> `primaryOnSurface` (4 lines) |
| `screens/visitor/visitor_reports_screen.dart` | reference number -> `textPrimary` (1 line) |
| `screens/track_report/status_update_screen.dart` | reference text -> `textPrimary`; step connector -> `primaryOnSurface` (2 lines) |
| `screens/visitor/track_by_reference_screen.dart` | helper/value text, input text, focus ring, active stepper -> `textPrimary`; labels, badge, search icon, completed stepper -> `primaryOnSurface` (15 lines) |
| `screens/track_report/track_report_screen.dart` | badge label -> `textPrimary`; View-on-Map, office block, connectors, compare icon, spinner -> `primaryOnSurface` (10 lines) |
| `widgets/inputs/otp_box.dart` | OTP digits + focus ring -> `textPrimary` (2 lines) |
| `widgets/common/app_dialog.dart` | loading-variant accent -> `primaryOnSurface` (spinner/icon/halo only; the action button never renders for `loading`) (1 line) |

**94 colour lines across 14 files**, all line-scoped: fills carrying
`AppColors.onAccent`, decorative circles, shadows, and both brand logos were
not touched. `const` was dropped on 14 lines where the constructor now holds a
`BuildContext` value (the same `invalid_constant` cleanup 058 had to make).

Left alone on purpose: `SecondaryButton` and `CustomTextField` have **zero call
sites** (no rendered impact), and `landing_screen.dart:196`'s navy button border
also feeds that button's `boxShadow`, so recolouring it would have produced a
glow artifact in Dark Mode �?" the white label already identifies the control.

## Verification

Run on the Windows PC (Flutter 3.47.0, Chrome device):

- `flutter analyze --no-pub` -> **109 issues, 0 errors** (identical to the
  057/058 baseline; was 123 with 14 `invalid_constant` mid-fix, 0 after the
  `const` cleanup).
- `flutter test test/theme_tokens_test.dart` -> **+4 All tests passed**.
- `flutter test` -> **+4 -1**: only the documented pre-existing
  `widget_test.dart` smoke failure (`!timersPending`), present since 057.
- `flutter build web` -> **`√ Built build\web`**.
- Diff audited: `git show --stat 54d1ebe` = 14 files, 94 insertions /
  94 deletions, nothing else in `git status`.
- Contrast ratios: computed with the WCAG 2.x relative-luminance formula on the
  exact token values from `AppPalette` (dark `card #1E293B`, `background
  #0F172A`, `primarySurface #14532D`; replacements `#22C55E` = 4.00:1 on
  `primarySurface`, 6.42:1 on `card`; `textPrimary` = 13.35:1 on `inputFill`).

**Not verified (stated plainly):**

- **No visual/browser check** �?" Playwright was removed from this project and
  this session has no screen access; both themes still need a human hand-check.
- **`graphify update .` was NOT run** �?" the graphify CLI needs Python
  (`graphifyy==0.9.76`) and this Windows PC has no Python installed, so
  `graphify-out/` still reflects change 058. Reported as not run rather than
  implied.

## Impact / how to test by hand

1. Run the app (`RUN_APP.bat`, or `flutter run -d chrome` with the Laravel
   backend up) and switch to **Dark Mode**.
2. **Logged out**, check: Landing "Explore as Visitor" button; Login (wordmark,
   PIN digits, focus rings, Forgot Password?/Register links); Register (links,
   OTP cells once focused *and* filled); OTP screen (countdown, Resend, Back);
   Forgot-password sheet (Resend, hint box, typed digits, secondary button);
   Splash wordmark.
3. **Visitor mode**: guest nav (active item, "Login" label), About tab (wordmark,
   feature icons), Track-by-reference (input text + focus ring, "No login
   required", result badge), report cards (reference number).
4. **Logged in**: track/status screens (reference text, View on Map, office
   block, steppers, badge) and the loading dialog.
5. Repeat step 2 in **Light Mode** first: nothing should look different from
   before (`primaryOnSurface` *is* `AppColors.primary` in light; `textPrimary`
   `#0F172A` vs navy `#0D2137` is the swap 057/058 already shipped).
6. When the hand-check passes, check off task 4 in `docs/TASKS.md`.

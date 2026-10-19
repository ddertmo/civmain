# CivilWatch — Task List

Running list of planned work. Nothing here is done unless its box is checked.

---

## [ ] Use UniSMS as the primary SMS provider

- **Status:** planned, not started. **Do not implement until this task is picked up.**
- **Decision:** UniSMS is the **prime** SMS provider. Keep it swappable via
  config so another provider could be added later, but UniSMS is the default.
- **Why:** UniSMS is a Philippines-focused SMS API with `+63` number support and
  a plain REST endpoint (Basic auth). CivilWatch is already wired for it — see
  `laravel/app/Http/Controllers/Mobile/MobileAuthController.php::sendOtp()`,
  which contains `TODO: Integrate your preferred SMS provider here`.
- **Docs:** https://unismsapi.com/docs/sms · PHP SDK:
  https://github.com/unisoftventures/unisms-php

### Scope
1. Send the OTP as a **real SMS** instead of returning the code in the JSON.
2. In production, remove the debug `otp` field from the response (gate it behind
   `config('app.debug')`).
3. (Optional, decide later) Send status-change / announcement SMS to citizens
   via `/sms` or `/blast`.

### Files to touch
- `laravel/config/services.php` — add a `unisms` block: `key`, `sender_id`, `base_url`.
- `laravel/.env.example` — add `UNISMS_SECRET_KEY=`, `UNISMS_SENDER_ID=`,
  `UNISMS_BASE_URL=https://unismsapi.com/api`.
- `laravel/.env` — real secret key (gitignored; never commit).
- new `laravel/app/Services/UniSmsService.php` — `send()`, `status()`, `credits()`
  using `Http::withBasicAuth($key, '')`. No new Composer dependency.
- `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` — call the
  service in `sendOtp()`; handle `401` / `422` / `429` honestly.
- (Optional) `otp_codes` migration with a nullable `reference_id`; webhook route
  `POST /api/webhooks/unisms` verifying the `webhook-secret-key` header.
- `laravel/docs/SETUP_PC.md` + a `docs/changes/` entry, then push to **`backup`**
  (never `origin`) per `AGENTS.md`.

### Preconditions (must be true before it can work live)
- [ ] UniSMS account + **API secret key** obtained.
- [ ] **SenderID registered** (`/sender_ids/new`, with business documents).
      ⚠️ The default test sender **cannot reach Smart/TNT/Sun** — mandatory for a
      real Digos City deployment.
- [ ] SMS **credits** funded (`GET /account`).

### Known limits
- UniSMS's SMS API covers **sending** + **delivery-status webhooks** only.
  **Inbound SMS (users texting you) is not supported here** — that is their
  separate **Virtual numbers** product.
- `/blast` and `/bulk` are rate-limited to **1 request / 2 seconds**.
- Live sending costs credits, so it cannot be verified on Termux/Android; verify
  on the PC.

### Open decisions (answer at implementation time)
1. Does "receive" mean delivery webhooks only, or true inbound SMS (Virtual numbers)?
2. Integrate with Laravel `Http` facade (recommended) or vendor their PHP SDK?
3. Which flows send SMS: OTP only, or also announcements / status blasts?
4. Keep the debug `otp` echo for local dev, or remove it entirely?

### Verification (when built)
- `php artisan test` with a faked `UniSmsService` (no live credits burned).
- One real send from the PC; check `GET /sms/:reference_id` or the webhook.
- Confirm the production response no longer contains `otp`.

---

## [ ] Dark mode toggle for visitor mode

- **Status:** implemented 2026-10-19 in
  [062](changes/2026-10-19-062-guest-dark-mode-toggle.md) (commit `958ce55`),
  user-approved — this supersedes the 2026-10-16 "leave visitor mode as-is"
  decision. **Box stays unchecked until the hand-check passes** (see 062's
  "Impact / how to test by hand"): as guest, the Sun/Moon pill in the
  `_GuestBanner` flips every tab, tapping it must *not* trigger the banner's
  login navigation, and the choice persists across restart. Placement chosen:
  the banner row (visible on all guest tabs), not the About header.
- **Context:** change [055](changes/2026-10-16-055-animated-sun-moon-dark-mode-toggle.md)
  added the animated Sun/Moon toggle (`lib/widgets/common/theme_toggle.dart`)
  to the logged-in Home header only.

### Scope
1. **Visitor mode has no theme control.** Guests (`/visitor`, `VisitorShell`)
   have no Profile screen and the toggle lives only in the logged-in Home
   header, so a guest is stuck with whatever theme is active. Add the
   `ThemeToggle` widget to the guest flow — decide placement:
   - in `_GuestBanner` row (visible on Reports/About tabs), or
   - in the `VisitorAboutScreen` header.
   It already reads/writes `AppState().setDarkMode`, so no state changes needed.
2. ~~Hardcoded status-bar brightness~~ — **fixed in change 056.** The four
   `SystemChrome.setSystemUIOverlayStyle` calls (home, splash, landing,
   visitor_shell) are deleted; `AppState._applySystemUi()` and the per-theme
   `appBarTheme.systemOverlayStyle` are now the single authority.

### Verification (when built)
- `flutter analyze --no-pub` stays at 0 errors.
- On Home/splash/landing/visitor in dark mode: status-bar icons are light
  (readable), in light mode dark.
- As guest: toggle flips the visitor shell, reports list, about tab and
  persists across restart (same `dark_mode` key).

---

## [ ] Dark-mode contrast of status tint backgrounds is marginal

- **Status:** fixed 2026-10-19 in
  [061](changes/2026-10-19-061-status-tint-dark-values.md) (commit `9525085`),
  user-approved. Dark `statusSubmittedBg` `#2E1065`→`#1C0A3D` (3.18:1),
  `statusAssignedBg` `#172554`→`#0E1632` (3.44:1), `statusApprovedBg`
  `#042F2E`→`#021C1C` (3.53:1) — Light Mode untouched. **Box stays unchecked
  until the hand-check passes.** Originally deferred 2026-10-16 while doing
  change [057](changes/2026-10-16-057-dark-mode-contrast-home-notifications.md)
  as a palette-wide value change kept out of every other task.
- **Finding:** accent-on-tint contrast in Dark Mode falls just under the 3:1
  non-text target for three hues:
  | Status | accent on dark tint |
  |---|---|
  | Submitted `#7C3AED` on `#2E1065` | 2.67:1 |
  | Assigned `#2563EB` on `#172554` | 2.84:1 |
  | Approved `#0E7C7B` on `#042F2E` | 2.88:1 |

  (Pending 6.97:1, In Progress 4.40:1, Resolved 4.52:1, Rejected 3.34:1 are
  fine, as are all Light Mode values.)
- **Where it shows:** anything drawing `context.c.statusColor` on
  `context.c.statusBg` — notification card icon circles, status chips,
  Home summary tiles, report status badges.
- **Proposed fix:** darken the three dark-mode `statusXBg` values in
  `AppPalette` (Light Mode untouched). No existing darker token is wrong for
  this; it needs a values change, not a new token.

---

## [ ] Future task — Dark Mode contrast audit of the pre-login flow

- **Status:** implemented 2026-10-19 in
  [060](changes/2026-10-19-060-pre-login-dark-mode-ink.md) (commit `54d1ebe`),
  requested and approved by the user. **This box stays unchecked until the
  visual hand-check of both themes passes** — 060's "Impact / how to test by
  hand" is the checklist. The per-screen defect list with verified line numbers
  and ratios lives in 060; **the line numbers and the Landing wordmark claim in
  this entry are stale** (Landing's wordmark renders on a fixed light gradient,
  ~5:1, and was not a defect). Recorded 2026-10-16 from the 058 audit, where the
  same defect was fixed on Profile / My Reports / shared widgets — see
  [058](changes/2026-10-16-058-dark-mode-ink-profile-reports-widgets.md).
- **Precondition:** the 057 + 058 fixes are visually verified first.
- **Objective:** fix the remaining Dark Mode contrast issues in the pre-login
  flow. Reuse the existing `AppPalette` / `ThemeExtension` and the `context.c`
  helpers. **No second theme system.**
- **Defect:** fixed `AppColors.primary` / `AppColors.navy` used as *foreground
  ink* on themed surfaces instead of `context.c.primaryOnSurface` /
  `context.c.textPrimary` — 1.02–2.3:1 in Dark Mode.

### 1. Landing screen — `lib/screens/landing/landing_screen.dart`

- "Explore as Visitor" button: icon, label and border (`:179-182`) —
  `AppColors.primary` on `context.c.primarySurface` = **1.16:1**.
- Landing feature buttons: icon circles, icons and chevrons (`_LandingButton`
  `:353`, `:378`, `:383`, `:419`) — **1.16:1**.
- `CIVILWATCH` wordmark (`:130`) — `AppColors.navy` on the themed background,
  **~1.1:1**.

### 2. OTP screen — `lib/screens/auth/otp_screen.dart`

- Countdown text (`:386`) and the "Resend OTP" link (`:400`) — navy,
  **~1.1:1**.
- Back button (`:452`, `:459`) — navy icon + label, **~1.1:1**.
- OTP digit text and related input styling
  (`forgot_password_flow.dart:1068,1113`) — navy on `inputFill` `#1E293B` =
  **~1.1:1**; the typed digits are barely visible in Dark Mode.

### 3. Forgot-password flow — `lib/screens/auth/forgot_password_flow.dart`

- Resend link (`:552`) — primary on themed card, **~1.9:1**.
- Secondary button (`:878`) — navy, **~1.1:1**.
- Password-hint icon and explanatory text (`:741`, `:747`) — primary on
  `primarySurface`, **1.16:1**.

### 4. Additional screens requiring an audit — suspected only

Inspect these for similar fixed-colour contrast defects; **inspect the actual
code before treating any element as defective**:
`login_screen.dart`, `register_screen.dart`, `splash_screen.dart`,
`visitor_shell.dart`, `visitor_about_screen.dart`,
`visitor_reports_screen.dart`, `track_by_reference_screen.dart`,
`track_report_screen.dart`, `status_update_screen.dart`. Re-run the
`AppColors.(primary|navy)` audit on resume — do not assume the line numbers
above are still accurate.

### Implementation requirements

1. Inspect the affected files and identify each **confirmed** contrast issue.
2. Report the affected elements, their current colours, the replacement theme
   tokens and the exact files, then **wait for approval before modifying code**.
3. Reuse existing `AppPalette` tokens — `primaryOnSurface`, `textPrimary`,
   `textSecondary` and other appropriate existing tokens.
4. Correct foreground/background combinations without unnecessarily changing the
   established CivilWatch branding.
5. Preserve the Light Mode appearance, layouts, spacing, sizes, navigation and
   authentication behaviour.
6. No API, backend, database-schema, report-workflow or unrelated-component
   changes.
7. No new packages, no second theme system.
8. Fix only confirmed issues inside the approved scope.

### Verification

- `flutter analyze --no-pub`, the theme tests and the full suite, then
  `flutter build web`; compare against the documented baseline.
- Visually inspect all affected screens in **both** Light and Dark Mode when
  browser access is available.
- Report remaining issues honestly — passing tests alone does not establish
  visual correctness.
- Finish with the `AGENTS.md` changelog workflow.

**Still leave every use that is a *fill* carrying `AppColors.onAccent` text**
(button backgrounds, badges, the bottom-nav centre circle, the OTP badge) —
those are correct in both themes, and Light Mode must stay unchanged either
way.

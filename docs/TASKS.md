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

- **Status:** planned, not started. **Do not implement until this task is picked up.** (User decided 2026-10-16: leave visitor mode as-is for now.)
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

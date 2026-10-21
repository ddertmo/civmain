# Reset PIN had no UI walkthrough — added a mockup that says what is real vs fake

- **Date:** 2026-10-21
- **Commit:** `(uncommitted)`
- **Type:** docs
- **Areas:** Laravel (public dev tools)

## Problem

Registration now has a visual demo (`register-demo.html`, change 068), but there
was no equivalent for **Forgot Password / reset PIN**. The app has a reset
sheet, yet nobody could see the intended 5-screen sequence (number → OTP sent →
code → new PIN → success) outside the Flutter app.

Worse, the app's reset flow *looks* real but is not: `forgot_password_flow.dart`
uses `Future.delayed` for send, verify and reset, and the backend has no
`/reset-pin` route. A visual demo risks implying the PIN is actually changed, so
the page has to be explicit about the gap.

## Cause

- `forgot_password_flow.dart` is UI-only; it never calls `send-otp` or
  `verify-otp`, and it never persists a new PIN.
- `routes/api.php` exposes `send-otp`, `verify-otp` and `register`, but **no**
  reset endpoint, so `citizens.pin_hash` cannot be updated by a reset.
- There was no browser page showing the flow at all.

## Changes

| File | Change |
|---|---|
| `laravel/public/reset-demo.html` | New dev-only mockup. Phone frame with the 5 screens from the app's reset sheet: mobile number → **OTP Sent!** status → 6-box OTP entry (with resend timer) → new PIN + confirm → **Password Reset!** success. Fully client-side: **no `fetch`, no SMS, no UniSMS credits, and no database write**. A "Reality check" card states that `send-otp`/`verify-otp` are real, `forgot_password_flow.dart` is fake, and there is no `POST /reset-pin`. Mock log labels every step `[MOCK — no network]`. |
| `laravel/public/register-demo.html` | Added a link to the reset mockup in the links row. |
| `docs/changes/README.md` | Index row 069. |

No Laravel routes, controllers, models or Flutter code were changed.

## Verification

- Inline script syntax: extracted `<script>` → `node --check` → exit **0**.
- Served by the live backend:
  - `GET http://127.0.0.1:8000/reset-demo.html` → **HTTP 200** (33,351 bytes)
  - `GET http://127.0.0.1:8000/register-demo.html` → **HTTP 200**
- No credits can be spent: the page contains no `fetch()` call and never talks
  to UniSMS or Laravel.
- Click-through was **not** automated (Playwright is removed); the page was
  opened in Chrome for manual use.

## Impact / how to test by hand

1. Open `http://127.0.0.1:8000/reset-demo.html`.
2. Enter any valid PH mobile number (e.g. `9171234567`) → **Send OTP**.
3. The **OTP Sent!** screen appears; press **Continue**.
4. Use the amber **mock code** chip → **use it →** → **Verify**.
5. Enter a new 6-digit PIN twice → **Reset Password** → success screen.

> This is a mockup, not a feature. The PIN is never saved. To make it real:
> add `POST /api/mobile/auth/reset-pin` (validate the OTP, hash the PIN into
> `citizens.pin_hash`, revoke tokens) and point `forgot_password_flow.dart` at
> the API. Dev-only file — delete before deploying to production.

> **Update:** made real in change 070. `verify-reset-otp` + `reset-pin` now
> exist, the Flutter flow calls them, and this page has a Live API toggle
> (Mock remains the default, so no credits are spent unless you switch).

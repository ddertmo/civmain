# Registration OTP flow had no visual demo — added an app-style browser walkthrough

- **Date:** 2026-10-21
- **Commit:** `(uncommitted)`
- **Type:** docs
- **Areas:** Laravel (public dev tools)

## Problem

After wiring OTP into registration (change 067), the only way to see/test the
flow outside the Flutter app was `test.html`, which is a developer harness of
stacked API cards. There was no visual representation of the actual app
screens (form → verify → success) to demo or sanity-check the step order.

## Cause

`test.html` was written for SMS/OTP delivery testing, not as a walkthrough of
the app UX. It has no phone frame, no step progression, and no success state.

## Changes

| File | Change |
|---|---|
| `laravel/public/register-demo.html` | New dev-only page. A phone-frame UI with the same three screens as the app: register form → 6-box OTP screen → account-created summary. Starts in **Mock mode** (default): OTP is generated in the browser, `send-otp`/`verify-otp`/`register` are simulated locally, and **no SMS or credits are used**. A Mock/Live switch runs the same UI against the real Laravel endpoints when needed. Includes a "Use sample details" shortcut and a request log like `test.html`. |
| `laravel/public/test.html` | Added a link to the new visual demo in the subtitle. |

No Laravel routes, controllers, models or Flutter code were changed.

## Verification

- Inline script syntax: `node --check` on the extracted `<script>` → exit **0**
  (re-checked after adding Mock mode).
- Pages served by the live backend:
  - `GET http://127.0.0.1:8000/register-demo.html` → **HTTP 200** (43,445 bytes)
  - `GET http://127.0.0.1:8000/test.html` → **HTTP 200** (17,815 bytes)
- Mock mode performs **no `fetch` calls** — `post()` dispatches to `mockPost()`
  and the log labels each entry `[MOCK — no network]`.
- The page reuses the endpoints already exercised earlier this session
  (`register` created citizen #2; PIN login and `/me` succeeded).
- The click-through itself was **not** automated (Playwright is removed); the
  page was opened in Chrome for manual use.

## Impact / how to test by hand

1. Open `http://127.0.0.1:8000/register-demo.html`.
2. Fill the form (use a fresh Globe/DITO number) → **Create Account**.
3. On the OTP screen, use the SMS code or the on-screen **debug code** → **Verify & Create Account**.
4. The success screen shows the new citizen's id, phone and barangay, and the
   right-hand log shows `send-otp`, `verify-otp` and `register` responses.

> Dev-only file — delete `register-demo.html` and `test.html` before deploying
> to production. Each OTP send costs 1 UniSMS credit.

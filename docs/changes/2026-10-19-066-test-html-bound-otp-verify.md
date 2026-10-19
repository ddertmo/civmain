# No way to test SMS/OTP outside the app — test.html register harness whose Verify step only accepts the OTP this page requested

- **Date:** 2026-10-19
- **Commit:** `0788307`
- **Type:** feature
- **Areas:** Laravel

## Problem

Task 1's delivery test needed a UI (the user asked for "a test.html for the
SMS verify"), and the first version of the page had an independent Verify box
that would happily verify *any* phone/code combination — it wasn't tied to the
OTP the test itself had requested. The requirement: verification must act on
**the OTP this test page requested**, for the number it requested it for.

## Cause

Missing tooling: no browser-side harness existed for the OTP → verify →
register flow (only the Flutter app, which needs a rebuild per change), and
the page's steps had no shared state — each read the input fields at click
time, so editing the phone after sending would have verified the wrong number.

## Changes

| File | Change |
|---|---|
| `laravel/public/test.html` | **new** — 3-step register-flow harness against the real APIs: (1) `send-otp` — binds `pending = {phone, debug-otp, time}` on every issued code and shows `sms_sent`/`sms_error`; (2) `verify-otp` — **always sends `pending.phone`**, never the current field value; when the debug code is known it first cross-checks the typed code locally (`typed ≠ requested` → refuses, offering an explicit "verify anyway (expect server 422)" button to test server rejection); on success stores `verifiedPhone` and clears pending; (3) `register` — **refuses to run unless `verifiedPhone` exists**, so registration only continues for the number this test verified. Plus a state line in step 2 showing which number/code/time is being verified, a timestamped request log, and the admin's theming (`/css/variables.css`, same `theme`/`data-theme` key as `js/app.js` with a dark toggle) |

Deliberately **no admin auth-gate** on the page (it only exposes endpoints
that are already public) — footer marks it "delete before deploying". 339
lines, all JS inline, no dependencies.

## Verification

- `node --check` on the extracted inline script → **JS syntax OK** (a real
  bug was caught and fixed this way: an unescaped apostrophe in a JS string).
- `GET http://127.0.0.1:8000/test.html` → **200** (17,698 bytes), markers for
  the bound-verify text present.
- `GET /account` with the real key → **`sms_credits: 10`** (user topped up on
  2026-10-19; was 0 when 065 was parked) — delivery test is now fundable.
- **Not run:** the actual SMS send — the agent deliberately did not send to
  the placeholder `09876543211` (unknown/possibly real recipient, would burn a
  credit on a test to nobody). The real send happens via the page with the
  user's own number (see below).
- `graphify update .` — **not run** (no Python/`graphify` on this PC).

## Impact / how to test by hand

1. Open **<http://127.0.0.1:8000/test.html>** (backend must be running).
2. Step 1: your real mobile number → **Send OTP** → costs 1 credit; expect
   `SMS SENT ✓` and a text `CivilWatch: your verification code is ....`.
3. Step 2: the state line must name your number; type the SMS code →
   **Verify this test's OTP** → `OTP VERIFIED ✓`. (Typing a wrong code is
   blocked locally and shows both codes; the warn-button proves the server
   rejects too.)
4. Step 3 appears only after verification → fill the form → **Register** →
   success banner. Wrong/missing verify → step 3 refuses.
5. **A received SMS = the task 1 delivery test** → check off the box (and the
   SenderID precondition state) in `docs/TASKS.md`.

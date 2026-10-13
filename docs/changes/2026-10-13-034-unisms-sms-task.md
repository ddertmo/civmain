# Record UniSMS as the planned primary SMS provider

- **Date:** 2026-10-13
- **Commit:** `e61e0bc`
- **Type:** docs
- **Areas:** docs / Laravel

## Problem

CivilWatch has no SMS provider wired in. `MobileAuthController::sendOtp()`
still contains `TODO: Integrate your preferred SMS provider here` and returns
the OTP in the JSON response, so there was nothing recorded about which provider
to use or what integrating it involves. Without a written task, the decision and
its gotchas (PH SenderID registration, credits, no inbound SMS) would be lost.

## Cause

No SMS provider had been chosen or documented. The OTP flow was built with a
placeholder so it could be filled in later, but nothing captured that intent.

## Changes

| File | Change |
|---|---|
| `docs/TASKS.md` | New running task list. First entry: "Use UniSMS as the primary SMS provider" — status, decision, scope, files to touch, preconditions (API key, registered SenderID, credits), known limits (send + delivery webhooks only, no inbound; blast rate limit 1/2s), open decisions, and verification steps. Explicitly marked *do not implement yet*. |

## Verification

Static review only — **no code was changed**. `docs/TASKS.md` was created and
read back; the UniSMS claims in it were taken from
https://unismsapi.com/docs/sms (Basic auth, `POST /api/sms`, `/sms/:reference_id`,
`/account`, webhooks, `UniSmsService` plan) and cross-checked against the real
hook at `laravel/app/Http/Controllers/Mobile/MobileAuthController.php:sendOtp`.
Not verified: no SMS was sent, no `UniSmsService` exists yet, and this is
Android/Termux so the live flow can only be tested on the PC later.

## Impact / how to test by hand

1. Open `docs/TASKS.md`; the UniSMS task is the first item with an unchecked box.
2. When the task is picked up, work through its "Files to touch" and
   "Preconditions", resolve the "Open decisions", then tick the box.

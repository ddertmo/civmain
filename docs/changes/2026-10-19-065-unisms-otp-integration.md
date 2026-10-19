# OTPs were never sent — UniSMS wired in as the primary SMS provider, with an honest no-key fallback

- **Date:** 2026-10-19
- **Commit:** `fd5d5a1`
- **Type:** feature
- **Areas:** Laravel

## Problem

`POST /api/mobile/auth/send-otp` had a literal
`TODO: Integrate your preferred SMS provider here` — no SMS was ever sent; the
6-digit code was simply returned in the JSON with a "remove before going live"
comment. `docs/TASKS.md` task 1 picked this up: UniSMS (Philippines, `+63`,
plain REST + Basic auth) is the chosen prime provider, kept swappable via
config, and the debug `otp` field must be gated behind `config('app.debug')`.

## Cause

Not a bug — a missing integration. No provider service, no config block, no
env keys existed anywhere (`config/services.php` had no `unisms` entry,
`.env.example` had no `UNISMS_*` lines), and the controller had no code to
call an SMS API. (Secondary find: `normalizePhone()` stores numbers as
`639XXXXXXXXX`, while UniSMS documents `+639…`/`09…` — the service now formats
the `+` form itself.)

## Changes

| File | Change |
|---|---|
| `laravel/app/Services/UniSmsService.php` | **new** — `configured()`, `send()` (POST `/sms`), `status()` (GET `/sms/:ref`), `credits()` (GET `/account`); `Http::withBasicAuth(key, '')` against `services.unisms.base_url`, 15 s timeout, recipient auto-formatted to `+63…`; non-2xx turned into honest `describe()` strings (401/422/429…) that never leak the key. No Composer dependency |
| `laravel/config/services.php` | `unisms` block: `key`, `sender_id`, `base_url` — code reads only through this block so another provider can be added later (swappable, per task) |
| `laravel/.env.example` | `UNISMS_SECRET_KEY=`, `UNISMS_SENDER_ID=`, `UNISMS_BASE_URL=https://unismsapi.com/api` + comments on getting a free key |
| `laravel/.env` | same three keys with **empty values** (gitignored — `git check-ignore` verified); real key to be filled after the user registers |
| `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` | `sendOtp()` calls the service when configured; adds `sms_sent` (+ `sms_error`) to `data`; **`otp` is returned only when `config('app.debug')`**; failures: `503` when unconfigured with debug off, `502` when a send failed with debug off; `Log::warning` on every failed send; docblock updated (TODO removed). Imports: `UniSmsService`, `Log` |
| `laravel/docs/SETUP_PC.md` | new `## SMS (UniSMS) — OTP delivery (task 1)` section: free registration, `.env` keys, curl tests, Smart/TNT/Sun caveat, pricing |

**Response shapes** (Flutter `otp_screen.dart` treats `otp` as nullable and
only autofills when present — verified, so the app keeps working):

- no key + `APP_DEBUG=true` → `200`, `otp` present, `sms_sent:false`,
  `sms_error:"UniSMS not configured (UNISMS_SECRET_KEY missing)."`
- key + delivered → `200`, `message:"OTP sent via SMS."`, no `otp` when debug off
- key + failed + debug off → `502` / no key + debug off → `503`

## Verification

- `php -l` on all three PHP files → **No syntax errors** (×3).
- `php artisan config:show services.unisms` → block loaded fresh (`base_url`
  default present; no stale config cache).
- **Live fallback test:** `POST /api/mobile/auth/send-otp` → **200**
  `{"success":true,"message":"OTP generated successfully.","data":{"phone":"639876543211","sms_sent":false,"sms_error":"UniSMS not configured (UNISMS_SECRET_KEY missing).","otp":"373463"}}`
  — old dev flow intact, honest signals added.
- **Live wiring test** (temp bootstrap script with `UNISMS_SECRET_KEY=sk_dummy_…`,
  script deleted afterwards): request reached `unismsapi.com` and returned
  **`401` → `error:"UniSMS 401 Unauthorized - check UNISMS_SECRET_KEY."`,
  `credits() → NULL`** — proves base URL, Basic-auth header format and payload
  shape end-to-end **without spending a credit**.
- **Not verified:** a real SMS delivery — requires the user's free API key and
  test credits (preconditions in `docs/TASKS.md`). Also not done: webhooks
  (need public HTTPS) and the optional blast/bulk scope.
- `graphify update .` — **not run** (no Python/`graphify` on this Windows PC).

## Impact / how to test by hand

1. Register free at <https://unismsapi.com/register> → copy the secret key
   (`/api_keys`) and the test Sender ID → put both in `laravel/.env` →
   restart `php artisan serve`.
2. `curl -s https://unismsapi.com/api/account -u "$UNISMS_SECRET_KEY:"` →
   should print your balance (checks key + credits before anything else).
3. In the app, request an OTP on a real `+63` number **that is not on
   Smart/TNT/Sun** (test Sender ID limitation) → the SMS should arrive within
   seconds; `GET /sms/:reference_id` (or `sms_sent:true` in the JSON while
   debug is on) confirms the state.
4. Flip `APP_DEBUG=false` to see the production behaviour: no `otp` echo,
   `503`/`502` on real failures.
5. When a real SMS was received, check off task 1's box and its three
   preconditions in `docs/TASKS.md`.

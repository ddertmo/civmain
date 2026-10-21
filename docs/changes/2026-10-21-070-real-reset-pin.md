# Reset PIN was a fake — a correct OTP now actually changes the PIN

- **Date:** 2026-10-21
- **Commit:** `d53f83f`
- **Type:** feature
- **Areas:** Laravel (mobile auth), Flutter (forgot-password flow), dev tools

## Problem

A citizen who forgot their PIN had no way back into the app. The Forgot
Password sheet walked through phone → OTP sent → code → new PIN → success, but
every step was a `Future.delayed` timer. No SMS was requested, any 6 digits were
accepted as the OTP (except `000000`), and the "reset" only changed a local
message — `citizens.pin_hash` was never written. The backend had no reset route.

## Cause

- `forgot_password_flow.dart` was UI-only: it never called `send-otp` or
  `verify-otp`, and it had no reset call at all.
- `routes/api.php` exposed registration OTP endpoints but nothing that could
  update a PIN. `verify-otp` also consumes the code and issues a login token for
  existing citizens, so it cannot be reused as a pre-reset check.

## Changes

| File | Change |
|---|---|
| `laravel/routes/api.php` | Added `POST /api/mobile/auth/verify-reset-otp` and `POST /api/mobile/auth/reset-pin`. |
| `laravel/app/Http/Controllers/Mobile/MobileAuthController.php` | `verifyResetOtp()` checks the code **without consuming it** so the OTP screen gets immediate feedback. `resetPin()` validates the same code again, then in one DB transaction marks the OTP used, updates `citizens.pin_hash`, and deletes every Sanctum token for that citizen. Both reject unknown or deactivated accounts. |
| `civ-main/lib/core/constants/api_constants.dart` | Added the two endpoint URLs. |
| `civ-main/lib/services/auth_service.dart` | Added `verifyResetOtp(phone, otp)` and `resetPin(phone, otp, pin)`, returning `(success, error)` like the other auth calls. |
| `civ-main/lib/screens/auth/forgot_password_flow.dart` | Replaced all `Future.delayed` fakes. Send/resend call `sendOtp`, Verify calls `verifyResetOtp`, and Reset calls `resetPin`. Backend errors are shown in the existing Invalid OTP / generic error modals (`_errorMessage`). |
| `laravel/public/reset-demo.html` | Now has the same **Mock / Live API** switch as the registration demo. Mock (default) still uses zero credits; Live calls `send-otp` → `verify-reset-otp` → `reset-pin`. Reality-check card and success note updated. |
| `docs/changes/README.md` | Index row 070. |

## Security behaviour

- The OTP is single-use: `resetPin()` sets `is_used = true` before/with the PIN
  update. Replaying the same code returns **422**.
- Codes expire after 5 minutes (`OtpCode::issue` already enforced this).
- A successful reset revokes **all** existing tokens, so a stolen session is
  signed out.
- Validation is identical to registration: PH mobile regex, 6-digit PIN, and an
  active account.

## Verification

All backend checks used an OTP inserted with `php artisan tinker` (no SMS, no
UniSMS credit). Test citizen: `09000000001`, PIN `654321` (restored at the end).

| Check | Result |
|---|---|
| `php -l MobileAuthController.php`, `php -l routes/api.php` | no syntax errors |
| `php artisan route:list --path=mobile/auth` | both new routes registered |
| Wrong OTP → `verify-reset-otp` | **422** `Invalid or expired OTP.` |
| Correct OTP → `verify-reset-otp` | **200** `OTP verified.` |
| Correct OTP + new PIN `777888` → `reset-pin` | **200** `PIN reset successfully…` |
| Login with `777888` | **200**, token issued |
| Replay the same OTP → `reset-pin` | **422** `Invalid or expired OTP.` |
| Old token → `GET /me` after reset | **401** (tokens revoked) |
| 5-digit PIN → `reset-pin` | **422** validation error |
| Restore PIN `654321` via new OTP | **200**, login **200** |
| `flutter analyze` | **0 errors**; 109 pre-existing info/warning lints, none in the touched files |
| `node --check` on `reset-demo.html` inline script | exit **0** |
| `GET /reset-demo.html` | **HTTP 200** (41,158 bytes) |

Playwright is removed, so the browser click-through was not automated; the page
was opened in Chrome and the underlying Live endpoints were verified directly.

## Impact / how to test by hand

1. Open `http://127.0.0.1:8000/reset-demo.html` (starts in free Mock mode).
2. Flip to **Live API** only if you want a real SMS (1 credit).
3. Enter `09000000001` → **Send OTP** → use the received code (or the debug code
   with `APP_DEBUG=true`) → **Verify**.
4. Choose a new 6-digit PIN → **Reset Password** → success.
5. Log in in the app with the new PIN; the old PIN now fails.

In the Flutter app, the same flow is reachable from the login screen's
**Forgot Password?** link. The app now calls the real API, so the reset only
succeeds when the OTP is right.

# Mock testing still needed SMS — registration and reset now skip OTP in debug

- **Date:** 2026-10-21
- **Commit:** `(uncommitted)`
- **Type:** feature
- **Areas:** Flutter (registration, forgot-password flow), dev tools

## Problem

The debug Mock switch added in 071 still walked through the OTP screen for the
reset flow, and registration had no Mock switch at all. Any free test of either
flow was blocked, so the app could not be demoed without spending the remaining
UniSMS credits — which may run out.

## Cause

- `register_screen.dart` always pushed `OtpScreen`, which calls `send-otp`.
  There was no debug branch to bypass it.
- `forgot_password_flow.dart` mocked the OTP itself but still required the user
  to tap through the OTP-sent/OTP-entry screens.
- The Mock/Live toggle was private to `forgot_password_flow.dart`, so the
  registration screen could not reuse it.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/widgets/common/debug_mode_toggle.dart` | New shared `DebugModeToggle` widget, extracted from the private widget in the reset flow. Used by both registration and reset. |
| `civ-main/lib/screens/auth/register_screen.dart` | Added the debug-only Mock / Live switch above the form. In Mock (`kDebugMode`, default on), the OTP screen is skipped and the existing `register` API is called directly — no SMS, no credit. Live still pushes `OtpScreen`. |
| `civ-main/lib/screens/auth/forgot_password_flow.dart` | Mock no longer shows the OTP screen. Phone → new PIN → Reset, then logs in the seeded demo citizen (`+639171234567` / PIN `123456`) and opens the normal `/home` dashboard. Mock changes no PIN and writes nothing. Live still uses `send-otp` → `verify-reset-otp` → `reset-pin`. |
| `docs/changes/README.md` | Index row 072. |
| `docs/SESSION_CONTEXT.md` | `## NOW` refreshed. |

## What is intentionally set aside

The real OTP code is **not deleted**. These remain intact and are used whenever
the switch is on **Live API**:

- `OtpScreen` and `AuthService.sendOtp` / `verifyOtp`
- `verify-reset-otp` and `reset-pin`
- the debug OTP hint on the OTP screen (Live + `APP_DEBUG=true`)

Mock mode only bypasses the OTP step in debug builds. Release builds have no
toggle and `_mockMode` initialises to `false`.

## Verification

| Check | Result |
|---|---|
| `flutter analyze` | **0 errors**; 109 pre-existing info/warning lints. No new lint in the touched flow. |
| Temp registration via `POST /api/mobile/auth/register` with no OTP | `success=True`, user `Mock Test User`; the test citizen and token were deleted afterwards with MySQL |
| Demo citizen login `POST /api/mobile/auth/login` (`09171234567` / `123456`) | `success=True`, user `Juan Dela Cruz` — the Mock reset destination works |
| `register` endpoint source review | no OTP/verification check; it creates the citizen and returns a token directly, so the Mock registration path works without `send-otp` |
| Flutter web rebuilt and restarted | `lib\main.dart.js is being served at http://127.0.0.1:5000`; **HTTP 200** |
| UniSMS credits used | **0** |

Playwright is removed, so the click-through was not automated. The screens were
verified by source review, the analyzer, and direct API checks.

## Impact / how to test by hand

### Registration (free)
1. Open `http://127.0.0.1:5000` → **Create Account**.
2. Leave the switch on **Mock** (green).
3. Fill in a **new** mobile number, name, address, barangay, and 6-digit PIN.
4. Tap **Create Account** — no OTP screen, no SMS. The account is created and
   the dashboard opens.

### Reset (free preview)
1. From the login screen tap **Forgot Password?**.
2. Leave the switch on **Mock**.
3. Enter any valid PH number → the OTP screen is skipped → enter a new PIN.
4. Tap **Reset Password** — the seeded demo citizen dashboard opens. The PIN is
   **not** changed and no database row is written.

Switch to **Live API** only when you explicitly want a real SMS (1 credit) and a
real PIN change.

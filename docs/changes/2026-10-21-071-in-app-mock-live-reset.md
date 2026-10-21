# Reset testing needed a real SMS in the app — added a debug-only Mock / Live switch

- **Date:** 2026-10-21
- **Commit:** `d53f83f`
- **Type:** feature
- **Areas:** Flutter (forgot-password flow, OTP input widget), dev tools

## Problem

After 070 the app's Forgot Password sheet was fully real: every test run sent an
SMS through UniSMS and spent a credit. The browser demos had a free Mock mode,
but the real app did not, so there was no way to click through the reset flow
inside the app without a phone and a finite credit balance.

## Cause

- `forgot_password_flow.dart` only had live branches after 070 — `_sendOtp()`,
  `_verifyOtp()` and `_resetPassword()` all called `AuthService` unconditionally.
- The Mock/Live switch lived only in `laravel/public/reset-demo.html` and
  `register-demo.html`, which are separate HTML pages, not the Flutter app.
- `OtpInputRow` kept its state class private (`_OtpInputRowState`), so the flow
  could not pre-fill the six boxes from a mock code.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/screens/auth/forgot_password_flow.dart` | Added a **Mock / Live API** segmented switch on the Enter Phone screen, rendered only when `kDebugMode`. Mock send/resend generate a local 6-digit code, Mock verify compares against it, and Mock reset jumps to success without calling the API. The OTP screen shows an amber `Debug OTP: <code> · tap to fill` hint; the new-PIN screen and success screen both say the PIN was **not** saved. Live mode is unchanged and still calls `send-otp` → `verify-reset-otp` → `reset-pin`. In live debug builds the server-returned `otp` is also shown in the same hint. |
| `civ-main/lib/widgets/inputs/otp_box.dart` | Renamed `_OtpInputRowState` to public `OtpInputRowState` and added `fill(String otp)` so the tap-to-fill hint can populate all six boxes and fire `onCompleted`. Existing callers are unchanged. |

## Safety

- The switch, the mock branches, and the debug OTP hint are all gated by
  `kDebugMode`. Release builds default `_mockMode` to `false`, never render the
  switch, and cannot reach the mock branches.
- Mock mode performs no network call and writes no PIN; the success screen says
  so explicitly.

## Verification

| Check | Result |
|---|---|
| `flutter analyze` (whole app) | **0 errors**; 109 pre-existing info/warning lints, none in the two touched files |
| Flutter web server restarted from the new source | `lib\main.dart.js is being served at http://127.0.0.1:5000` |
| `GET http://127.0.0.1:5000` | **HTTP 200** |
| `GET /main.dart.js`, `/flutter_bootstrap.js`, `/assets/AssetManifest.bin.json` | **HTTP 200** |
| `flutter run` process stays up | port **5000 listening** |

Playwright is removed, so the click-through was not automated. The UI was not
clicked by a machine; the flow was verified by source review plus the analyzer,
and the server was confirmed serving the rebuilt app.

## Impact / how to test by hand

1. Open `http://127.0.0.1:5000` and log in (or stay on the login screen).
2. Tap **Forgot Password?**.
3. The sheet opens in **Mock** (green). Enter any valid PH mobile number → **Send OTP**.
4. On the OTP screen, tap the amber **Debug OTP** hint — all six boxes fill.
5. **Verify** → new PIN → **Reset Password**. Success says the PIN was **not** saved.
6. Close the sheet, reopen it, flip the switch to **Live API**, and repeat only
   when you are ready to spend one UniSMS credit. Use `09000000001` / PIN
   `654321` as the test account.

Live mode is the only mode that can actually change `citizens.pin_hash`.

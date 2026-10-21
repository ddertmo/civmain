# Registration skipped the OTP screen — the existing `OtpScreen` was dead code

- **Date:** 2026-10-21
- **Commit:** `d53f83f`
- **Type:** feature
- **Areas:** Flutter

## Problem

Tapping **Register** in the app created the account immediately from the form.
No OTP was ever requested or entered, even though the project ships a real
`OtpScreen` and `/api/mobile/auth/send-otp` + `verify-otp` both work. The
number was never proven to belong to the person registering.

## Cause

`OtpScreen` exists and calls the real API, and `AppRoutes.otp` is registered in
`lib/core/routes/route_generator.dart`, but **no screen ever navigated to that
route**. `lib/screens/auth/register_screen.dart` called
`AuthService.instance.register()` directly, so the OTP screen was unreachable
dead code. The intended order is already documented in
`lib/services/auth_service.dart` (`Registration → sendOtp → verifyOtp → register`).

## Changes

| File | Change |
|---|---|
| `civ-main/lib/screens/auth/otp_screen.dart` | Added an optional `returnToCaller` flag. On successful verification it now pops with `true` instead of navigating, when the screen was opened by the registration form. Existing login/registration navigation is unchanged when the flag is false. |
| `civ-main/lib/core/routes/route_generator.dart` | The `AppRoutes.otp` branch forwards the optional `returnToCaller` argument to `OtpScreen`. |
| `civ-main/lib/screens/auth/register_screen.dart` | `_register()` now pushes the existing `OtpScreen` first (it sends the OTP on open and verifies it on submit) and only calls `AuthService.register()` after the OTP is verified. Cancelling the OTP screen aborts registration. |

No new screen, endpoint, service or model was added — the existing OTP screen,
`AuthService.sendOtp/verifyOtp`, and the existing Laravel endpoints are reused.

## Verification

- `flutter analyze lib\screens\auth\register_screen.dart lib\screens\auth\otp_screen.dart lib\core\routes\route_generator.dart`
  → **no errors**; only pre-existing infos (`unnecessary_underscores` in
  `route_generator.dart`, unused `_isLoading` in `register_screen.dart`).
- Backend endpoints used by this flow were exercised against the live Laravel
  server: `POST /api/mobile/auth/register` created citizen #2, PIN login and
  `GET /api/mobile/auth/me` both succeeded (see the registration test earlier
  in this session).
- The UI tap-through itself was **not** automated (Playwright is removed from
  this project). The app was rebuilt and served at `http://127.0.0.1:5000` for
  manual confirmation.

## Impact / how to test by hand

1. Open the app (`http://127.0.0.1:5000`) and tap **Register**.
2. Fill the form, then tap **Register**.
3. The existing OTP screen opens and sends a real SMS (1 UniSMS credit) to a
   Globe/DITO number. With `APP_DEBUG=true` the code is also shown on screen.
4. Enter the code and tap **Verify**:
   - success → returns to the form and the account is created;
   - back/cancel → no account is created.

> Note: the backend `register` endpoint still does not require a prior OTP when
> called directly. This change fixes the app flow; server-side enforcement is a
> separate hardening task.

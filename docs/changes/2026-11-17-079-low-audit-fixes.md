# Low audit fixes: hardening, CSP + SRI, hashed OTPs, small bugs, Flutter cleanup, scripts

- **Date:** 2026-11-17
- **Commit:** `2dba606`
- **Type:** fix (security hardening, bugs, cleanup)
- **Areas:** Laravel, admin and office web panel, Flutter app, startup scripts, docs

## Problem

These are the 22 Low findings of the 2026-11-17 audit; 076–078 covered the rest.

- **L1** (account enumeration) and the `last_login_at` part of L12 were already fixed in 077.
- **L19:** `t1.php` was already removed in 078.
- **L22:** the root `.gitignore` part was already done in 078.

## Changes

### Backend (laravel/)
| # | File | Change |
|---|---|---|
| L2 | `app/Http/Middleware/EnsureSuperAdmin.php`, `routes/api.php` | New `superadmin:all` mode, which gates reads too. `GET /api/users`, `/api/users/{id}` and `/api/admin/users` are now super-admin only; office staff could previously list every account's email and role. |
| L3 | `routes/web.php` | The catch-all `.html` route resolves `realpath()` and requires the result to stay inside `public/`. |
| L4 | `config/cors.php`, `.env.example` | `allowed_origins` comes from `CORS_ALLOWED_ORIGINS` (comma-separated); unset means `*` for local dev. |
| L5 | `app/Http/Controllers/Mobile/MobileAuthController.php` | `sms_error` (provider response text) is returned only in local debug; clients get the generic messages. Phone numbers in the log are masked. |
| L6 | `app/Models/OtpCode.php`, `database/migrations/2026_11_17_000003_widen_otp_codes_code_for_hashes.php` | OTPs are stored as HMAC-SHA256 (APP_KEY + phone + code). The `code` column is widened to 64 characters, and outstanding plain codes are expired. `issue()` runs in a transaction. |
| L6 | `app/Http/Controllers/Mobile/MobileReportController.php` | Uploaded JPEGs are re-encoded with GD, which drops EXIF data (GPS, device). Camera orientation is applied first. Best effort: the original is kept if GD is missing. |
| L6 | `.env.example` | Documents `SESSION_SECURE_COOKIE`, `SANCTUM_EXPIRATION` and `CORS_ALLOWED_ORIGINS`. |
| L7 | `app/Http/Middleware/SecurityHeaders.php` (**new**, appended to the `web` group), `public/.htaccess` | See the bullets after this table. |
| L11 | `routes/api.php`, `routes/web.php` | `->whereNumber('id')` on every route whose handler takes `int $id` (20 routes), so non-numeric ids return 404 instead of a TypeError 500. The routes that also accept `CW-` references (`show`, `status`) are unchanged. |
| L12 | `app/Http/Controllers/Admin/AdminOfficeController.php`, `AdminWebController.php` | Office create/update accept only abbreviations that have a staff login role (`CitizenReport::OFFICE_ROLES`: CEO, CENRO), matching what the list shows. Previously a new office was created but never listed or assignable. |
| L12 | `app/Http/Controllers/UserController.php` | The `is_active` filter uses `boolean()` ("true" used to match 0). `office` can be cleared to null. Dead `dashboardStats()` removed. |
| L12 | `app/Models/Citizen.php` | Unused `Notifiable` trait removed; its `notifications()` relation was silently overridden. |
| — | `tests/Feature/LowFixesTest.php` | **New.** 5 tests, listed under Verification. |

**L7 security headers** (CSP, `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy`):
- **Scripts:** only from self and the two pinned CDNs. `'unsafe-inline'` is still needed because the pages use inline scripts and `onclick` handlers.
- **Also blocked:** `object-src 'none'`, `base-uri 'self'`, `form-action 'self'` and `frame-ancestors 'none'`.
- **Where it applies:** Laravel adds the headers for HTML it serves, and Apache for the static `.html` files.

### Web panel (laravel/public/)
| # | File | Change |
|---|---|---|
| L7 | 13 pages | `integrity` + `crossorigin="anonymous"` on all 23 Chart.js 4.4.0 and Leaflet 1.9.4 tags. The hashes were computed from the raw bytes; Leaflet's match its official SHA-256 values. |
| L8 | `pending-reports.html`, `monitoring.html`, `resolved-reports.html` | See the bullets after this table. |
| L9 | `pending-reports.html`, `users.html` | Data loading and the refresh timer run only when `requireAuth()` returns true. |
| L9 | `js/auth.js`, `js/dashboard.js`, `js/users.js` | **Deleted.** They were unreferenced dead code, and `auth.js` had a syntax error. |
| L10 | `offices/{ceo,cenro}/report-details.html` | The progress timeline is built from the report's real `activityLog` (escaped) instead of fixed fake dates. The assigned date comes from `Utils.assignedDate`. Removed a `getElementById(...).remove?.()` TypeError. |
| — | `monitoring.html` | Its second script no longer redefines the global `fmtDate`/`normCategory` (renamed to `rdFmtDate`/`rdNormCategory`), so the list's date/time cells render correctly again. |

**L8 auto-refresh:**
- **What changed:** the 30 s refresh re-applies the active search, filters, page and tab instead of resetting them. The skeleton shows only on the first load.
- **Per-page selector:** now works.
- **Double load fixed:** pending-reports loaded twice on every visit (a `beforeunload` flag). It now loads once, and only reloads when restored from the back/forward cache.

### Flutter app (civ-main/)
| # | File | Change |
|---|---|---|
| L13 | — | Audited every `await` → `setState`/`Navigator`/`ScaffoldMessenger` in `lib/`; all are already guarded. Nothing to change. |
| L14 | `lib/screens/report/report_photo.dart` | HEIC/HEIF and non-image types are rejected with a clear message, and photos over 5 MB are rejected before upload. |
| L14 | `lib/widgets/cards/report_card.dart`, `community_map_screen.dart` | Use `AppNetworkImage` with a placeholder instead of a bare `Image.network`. |
| L15 | `lib/core/state/app_state.dart`, `notification_screen.dart` | Submitting no longer fabricates two local notifications; it reloads them from the server. Dead `addReport`/`updateStatus` helpers removed. "Mark all read" changes local state only after the API succeeds, and shows an error otherwise. |
| L15 | `lib/services/report_service.dart` | Stops sending a hard-coded `severity: Moderate` (the server defaults it). |
| L15 | `lib/core/network/api_client.dart` | `json['message']?.toString()` instead of a throwing cast. |
| L15 | `track_by_reference_screen.dart` | The "not found" text explains it only searches public and your own reports. |
| L16 | `lib/services/semaphore_service.dart` | **Deleted** (unused, placeholder key, returned the OTP to the client). |
| L17 | `pubspec.yaml` | Removed the unused `cupertino_icons`, `url_launcher` and `permission_handler`. |
| L18 | 23 files in `lib/` | All 64 `.withOpacity(x)` → `.withValues(alpha: x)` (the SDK constraint `^3.12.2` supports it). |
| L22 | `android/.gitignore` | `/build/` added; the tracked `android/build/reports/problems/problems-report.html` was removed from git. |

### Scripts and docs
| # | File | Change |
|---|---|---|
| L21 | `RUN_APP.bat` | `flutter pub get` instead of `pub upgrade` on every launch (dependencies no longer drift). Paths can be overridden with `CW_FLUTTER` / `CW_PHP`. |
| L21 | `START_ALL.bat` | Paths can be overridden with `CW_FLUTTER` / `CW_XAMPP` / `CW_CHROME`. The SQL import uses `%MYSQL%` instead of a hard-coded path. The defaults are unchanged. |
| L20 | `README.md` | No longer says the deleted Playwright `e2e/` suite is required. |
| L20 | `laravel/docs/SUPER_ADMIN.md` | The notifications section describes the read-only, per-staff feed from 077. |

### Not done (deliberately)
- **`welcome.blade.php` and the Vite/Tailwind `laravel/package.json` (L17/L19).** They're unrouted, but the setup docs reference them, and removing the Vite tooling can break `composer setup` / `npm run build`. Worth a separate cleanup if nobody uses them.
- **`google_fonts` runtime fetching (L17).** Turning it off requires bundling the font files as assets.
- **Pre-existing U+FFFD characters** in `settings.html` (~1279, ~1289, sample emoji) and mojibake in some Flutter comments were left as they were.

## Verification
- **JS syntax.** Every inline `<script>` and `public/js/*.js` file (30 files) was compiled in a browser from a throwaway localhost server: **0 errors**. No duplicate top-level declarations across a page's scripts. Every CDN tag has `integrity` + `crossorigin`.
- **SRI.** Loaded in a real browser: Chart.js, Leaflet JS and Leaflet CSS all load with their integrity attributes (`Chart`/`L` defined), and a control with a wrong hash is blocked.
- **Encoding.** Byte-exact comparison of every modified file against HEAD finds no new mojibake or replacement characters. Diffs are targeted, with no whole-file line-ending churn.
- **Script order.** `users.html` and `pending-reports.html` load `js/app.js` before their top-level `requireAuth()` call.
- **Not run (no PHP or Flutter SDK on this machine):** the new migration, the PHP code, `flutter analyze`, the Flutter builds, and the batch scripts.
- **New tests in `LowFixesTest`:**
  - staff list is super-admin only;
  - catch-all path traversal returns 404;
  - OTPs are stored hashed and still verify;
  - HTML responses carry the CSP headers;
  - non-numeric ids return 404.
- **To run on the PC:**
  ```
  cd laravel
  C:\xampp\php\php.exe artisan migrate
  C:\xampp\php\php.exe artisan test
  cd ..\civ-main
  C:\Users\Administrator\Downloads\flutter\bin\flutter.bat pub get
  C:\Users\Administrator\Downloads\flutter\bin\flutter.bat analyze
  ```
  `flutter pub get` also regenerates `pubspec.lock` and the desktop plugin registrants, which still list the removed packages.

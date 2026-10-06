# Graph Report - prc  (2026-10-05)

## Corpus Check
- 214 files · ~158,305 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 88 file(s) not represented in the graph (top: (none) 26, .xml 20, .css 15)

## Summary
- 1894 nodes · 2548 edges · 116 communities (90 shown, 26 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c1e90351`
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
- custom_textfield.dart
- login_screen.dart
- skeleton.dart
- route_generator.dart
- api_client.dart
- _
- app_routes.dart
- _ForgotPasswordSheet
- _
- report_details.dart
- track_report_screen.dart
- otp_screen.dart
- _
- report_concern.dart
- report_review.dart
- profile_screen.dart
- visitor_shell.dart
- _
- api_constants.dart
- package:flutter/services.dart
- StatelessWidget
- report_category.dart
- notification_service.dart
- splash_screen.dart
- package:flutter/material.dart
- auth_service.dart
- report_issue.dart
- user.dart
- _
- visitor_reports_screen.dart
- ../../core/constants/app_colors.dart
- app_bar.dart
- my_reports_screen.dart
- package:google_fonts/google_fonts.dart
- visitor_about_screen.dart
- icon_button.dart
- primary_button.dart
- secondary_button.dart
- Color
- validators.dart
- CIVILWATCH — Development Log
- report_service.dart
- app_images.dart
- semaphore_service.dart
- IconData
- 4. Complete Map Feature Flow — Step by Step
- ../../core/state/app_state.dart
- VoidCallback?
- Latest Session Changes
- text_styles.dart
- CIVILWATCH — Feature Documentation
- civ-main/README.md
- announcements/index.blade.php
- offices/index.blade.php
- build
- _next
- citizen-reports/index.blade.php
- show.blade.php
- dashboard.blade.php
- AppState
- app_network_image.dart
- _PinInputRow
- _next
- map.blade.php
- build
- _
- laravel/README.md
- 3. Report Concern Flow
- CIVILWATCH — Citizen App Integration Context
- 15. Extended Widget Library
- 2. Authentication & Registration
- 4. Report Submission
- 7. Design & UI
- 10. Critical Mismatches to Resolve
- 5. Report Tracking
- 9. Schema Changes Needed
- ../../core/routes/app_routes.dart
- AppRoutes.trackReport
- 10. Design System
- 1. Authentication
- 9. State Management & Data Layer
- 18. Legacy / Parallel Screens
- 5. Track Report
- 3. All Screens
- 6. Notifications
- 8. Integration Points & Field Mapping
- 13. Theme System
- 19. Additional Data Models
- 2. Home Dashboard
- _next
- AGENTS.md
- LaunchImage.imageset/README.md
- ReportLocationScreen

## God Nodes (most connected - your core abstractions)
1. `_` - 57 edges
2. `_` - 56 edges
3. `_` - 43 edges
4. `_` - 39 edges
5. `_` - 33 edges
6. `_` - 31 edges
7. `_` - 26 edges
8. `CIVILWATCH — Feature Documentation` - 25 edges
9. `_` - 18 edges
10. `Latest Session Changes` - 18 edges

## Surprising Connections (you probably didn't know these)
- `_` --references--> `Announcement`  [EXTRACTED]
  civ-main/lib/screens/home/home_screen.dart → civ-main/lib/core/utils/dummy_data.dart

## Import Cycles
- None detected.

## Communities (116 total, 26 thin omitted)

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
Nodes (41): addReport, _addSubmissionNotifications, _announcements, _annSvc, _auth, buildFromFormData, _communityReports, communityReportsPublic (+33 more)

### Community 4 - "report_location.dart"
Cohesion: 0.04
Nodes (41): _AddressCard, _addressCtrl, _AddressField, build, _canProceed, catColor, _cityCtrl, color (+33 more)

### Community 5 - "register_screen.dart"
Cohesion: 0.04
Nodes (43): agreed, _agreedToTerms, _animController, _BarangayDropdown, build, _buildPhoneField, _buildTextField, _confirmPin (+35 more)

### Community 6 - "helpers.dart"
Cohesion: 0.09
Nodes (20): AppHelpers, formatDate, formatDateShort, formatDateTime, formatTime, generateRefNumber, getCategoryBgColor, getCategoryColor (+12 more)

### Community 7 - "_"
Cohesion: 0.06
Nodes (30): _SkylinePainter, _TailPainter, _HomeMiniMapPainter, _MiniCityPainter, _LandingSkylinePainter, _TailPainter, _SuccessCityPainter, _PinTailPainter (+22 more)

### Community 8 - "track_by_reference_screen.dart"
Cohesion: 0.06
Nodes (30): _PhoneFmt, _PhoneNumberFormatter, _PhoneFormatter, build, _CompactTimeline, createState, _ctrl, _DetailRow (+22 more)

### Community 9 - "app_colors.dart"
Cohesion: 0.05
Nodes (38): AppColors, background, cardShadow, divider, environment, environmentBg, infrastructure, infrastructureBg (+30 more)

### Community 10 - "_"
Cohesion: 0.06
Nodes (27): _, _activeFilter, bg, build, _center, color, count, createState (+19 more)

### Community 11 - "dummy_data.dart"
Cohesion: 0.05
Nodes (32): Announcement, announcements, barangay, body, category, communityPins, communityReports, currentUser (+24 more)

### Community 12 - "status_update_screen.dart"
Cohesion: 0.04
Nodes (45): abbreviation, address, contactNumber, email, GovernmentOffice, handles, id, name (+37 more)

### Community 13 - "report.dart"
Cohesion: 0.06
Nodes (31): activityLog, afterImageUrl, assignedOffice, barangay, category, copyWith, description, id (+23 more)

### Community 14 - "report_photo.dart"
Cohesion: 0.06
Nodes (31): bg, build, _buildImage, bytes, canSkip, catColor, color, createState (+23 more)

### Community 15 - "custom_textfield.dart"
Cohesion: 0.12
Nodes (14): build, controller, CustomTextField, hint, inputFormatters, keyboardType, label, maxLength (+6 more)

### Community 16 - "login_screen.dart"
Cohesion: 0.08
Nodes (24): _animController, _badges, _CitySkyline, createState, _ctrl, dispose, _fadeAnim, formatEditUpdate (+16 more)

### Community 17 - "skeleton.dart"
Cohesion: 0.05
Nodes (51): LoginScreen, _LoginScreenState, _PinPasswordField, _PinPasswordFieldState, OtpScreen, _OtpScreenState, RegisterScreen, _RegisterScreenState (+43 more)

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

### Community 23 - "_"
Cohesion: 0.08
Nodes (24): _, _BeforeAfterDialog, build, color, _FullscreenPhotoViewer, _getReport, icon, imageUrl (+16 more)

### Community 24 - "report_details.dart"
Cohesion: 0.08
Nodes (21): _BarangayDropdown, build, catColor, createState, _descController, dispose, _formKey, icon (+13 more)

### Community 25 - "track_report_screen.dart"
Cohesion: 0.07
Nodes (25): afterPending, _BeforeAfterPhotos, color, fallbackColor, fallbackIcon, fullWidth, _getReport, icon (+17 more)

### Community 26 - "otp_screen.dart"
Cohesion: 0.08
Nodes (19): _animController, build, createState, _devOtp, dispose, _fadeAnim, initState, isNewUser (+11 more)

### Community 27 - "_"
Cohesion: 0.07
Nodes (25): _, announcement, _AnnouncementCard, bg, color, count, createState, firstName (+17 more)

### Community 28 - "report_concern.dart"
Cohesion: 0.10
Nodes (21): build, catBg, catColor, category, concern, _ConcernCard, _ConcernNavBar, _concerns (+13 more)

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

### Community 34 - "package:flutter/services.dart"
Cohesion: 0.25
Nodes (3): AppTheme, light, _lightTheme

### Community 35 - "StatelessWidget"
Cohesion: 0.11
Nodes (17): _FilterRow, _MapHeader, _PinDetailSheet, _StatsBar, _StatTile, _TeardropPin, _ZoomBtn, _GreetingSection (+9 more)

### Community 36 - "report_category.dart"
Cohesion: 0.11
Nodes (17): bg, build, _CategoryCard, color, createState, icon, isSelected, _next (+9 more)

### Community 37 - "notification_service.dart"
Cohesion: 0.12
Nodes (10): AnnouncementService, _api, instance, _parse, _api, instance, markAllRead, markRead (+2 more)

### Community 38 - "splash_screen.dart"
Cohesion: 0.15
Nodes (10): build, _CivilWatchLogo, _controller, createState, dispose, _fadeAnim, initState, _scaleAnim (+2 more)

### Community 40 - "auth_service.dart"
Cohesion: 0.13
Nodes (10): _api, AuthService, _currentUser, fetchMe, instance, isLoggedIn, logout, _normalizePhone (+2 more)

### Community 41 - "report_issue.dart"
Cohesion: 0.18
Nodes (9): build, catColor, category, createState, onNext, ReportIssueScreen, _ReportIssueScreenState, _ReportNavFooter (+1 more)

### Community 42 - "user.dart"
Cohesion: 0.14
Nodes (11): AppUser, avatarUrl, barangay, firstName, fullName, id, joinedDate, pendingReports (+3 more)

### Community 43 - "_"
Cohesion: 0.13
Nodes (12): _, build, _controller, createState, dispose, _fadeAnim, initState, paint (+4 more)

### Community 44 - "visitor_reports_screen.dart"
Cohesion: 0.14
Nodes (13): _activeFilter, color, createState, dispose, _filters, label, _LegendDot, _onLoginTap (+5 more)

### Community 45 - "../../core/constants/app_colors.dart"
Cohesion: 0.12
Nodes (14): build, dateLabel, message, showLock, status, StatusCard, AppLoading, build (+6 more)

### Community 46 - "app_bar.dart"
Cohesion: 0.18
Nodes (9): actions, backgroundColor, build, onBack, preferredSize, showBack, subtitle, title (+1 more)

### Community 47 - "my_reports_screen.dart"
Cohesion: 0.14
Nodes (10): _activeFilter, createState, dispose, embedded, _filters, _getFiltered, MyReportsScreen, _MyReportsScreenState (+2 more)

### Community 48 - "package:google_fonts/google_fonts.dart"
Cohesion: 0.07
Nodes (23): ActivityEntry, IncidentReport, build, currentStep, _labels, ReportStepper, ActivityCard, build (+15 more)

### Community 49 - "visitor_about_screen.dart"
Cohesion: 0.17
Nodes (10): body, child, color, _FeatureItem, icon, _onLoginTap, _SectionCard, subtitle (+2 more)

### Community 50 - "icon_button.dart"
Cohesion: 0.17
Nodes (10): AppIconButton, backgroundColor, badge, build, hasBorder, icon, iconColor, iconSize (+2 more)

### Community 51 - "primary_button.dart"
Cohesion: 0.17
Nodes (11): backgroundColor, borderRadius, build, height, icon, isLoading, label, onPressed (+3 more)

### Community 52 - "secondary_button.dart"
Cohesion: 0.18
Nodes (9): borderColor, build, height, icon, label, onPressed, SecondaryButton, textColor (+1 more)

### Community 53 - "Color"
Cohesion: 0.18
Nodes (9): activeBg, activeColor, build, icon, isFiltersChip, isSelected, label, MapFilterChip (+1 more)

### Community 54 - "validators.dart"
Cohesion: 0.20
Nodes (9): AppValidators, description, emailOptional, fullName, otp, password, phoneNumber, pin (+1 more)

### Community 55 - "CIVILWATCH — Development Log"
Cohesion: 0.07
Nodes (27): 1. File Migration, 2. Database, 3. Eloquent Models Created, 4. Controllers Created, 5. API Routes (`routes/api.php`), 6. Frontend Auth Updated, 7. Auth Approach — Laravel Sanctum, Auth in Prototype (now replaced) (+19 more)

### Community 56 - "report_service.dart"
Cohesion: 0.20
Nodes (5): _api, instance, _parseActivity, _parseReport, ReportService

### Community 57 - "app_images.dart"
Cohesion: 0.22
Nodes (7): AppImages, cityscape, emptyReports, logo, logoWhite, mapPlaceholder, successIllustration

### Community 58 - "semaphore_service.dart"
Cohesion: 0.22
Nodes (6): _apiKey, _baseUrl, SemaphoreService, _senderName, sendOtp, _toSemaphoreFormat

### Community 59 - "IconData"
Cohesion: 0.22
Nodes (7): actionLabel, build, EmptyState, icon, onAction, subtitle, title

### Community 60 - "4. Complete Map Feature Flow — Step by Step"
Cohesion: 0.09
Nodes (22): 1. Full Project File Structure, 2. Map-Related Files — What Each One Does, 3. Data Model — MapPin, 4. Complete Map Feature Flow — Step by Step, 5. Filter Flow — Category Filtering, 6. State Management Summary, 7. Navigation Map (All Routes), 8. Key Dependencies (Map-Specific) (+14 more)

### Community 61 - "../../core/state/app_state.dart"
Cohesion: 0.25
Nodes (5): build, embedded, _GroupLabel, label, NotificationScreen

### Community 62 - "VoidCallback?"
Cohesion: 0.14
Nodes (10): AppNotification, build, notification, NotificationCard, onTap, build, controller, hint (+2 more)

### Community 63 - "Latest Session Changes"
Cohesion: 0.09
Nodes (21): 22 Barangays (Digos City, Davao del Sur), Admin API Routes (`/api/admin/...`), Admin Web Panel Routes (`/admin/...`), Architecture, Auth (public — no token needed), Blade Views Created, CivilWatch Laravel Backend — Development Log, Config Changes (+13 more)

### Community 64 - "text_styles.dart"
Cohesion: 0.10
Nodes (19): appBarTitle, AppTextStyles, bodyLarge, bodyMedium, bodySmall, buttonLarge, buttonMedium, caption (+11 more)

### Community 65 - "CIVILWATCH — Feature Documentation"
Cohesion: 0.11
Nodes (18): 11. Project Structure, 12. Services Layer, 14. Extended Colour Tokens, 16. AppIcons Constants (`lib/core/constants/app_icons.dart`), 17. AppImages Constants (`lib/core/constants/app_images.dart`), 20. "Others" Category, 21. Six-Step Status Flow, 22. AppState Factory Method (+10 more)

### Community 66 - "civ-main/README.md"
Cohesion: 0.12
Nodes (15): 1. Download Flutter SDK, 2. Extract the SDK, 3. Add Flutter to PATH, Android licenses not accepted, Build the APK, Flutter command is not recognized, Flutter Setup Guide (Windows), If `flutter pub get` Finishes (+7 more)

### Community 76 - "app_network_image.dart"
Cohesion: 0.15
Nodes (9): AppNetworkImage, borderRadius, build, errorWidget, fit, height, _placeholder, url (+1 more)

### Community 86 - "build"
Cohesion: 0.22
Nodes (6): _viewOnMap, build, build, build, build, build

### Community 87 - "_"
Cohesion: 0.20
Nodes (10): _, build, category, color, isSelected, issue, MapMarker, onTap (+2 more)

### Community 88 - "laravel/README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 89 - "3. Report Concern Flow"
Cohesion: 0.25
Nodes (8): 3. Report Concern Flow, Progress Stepper, Step 1 — Category (`/report/category`), Step 2 — Concern (`/report/concern`), Step 3 — Photo (`/report/photo`), Step 4 — Location (`/report/location`), Step 5 — Review (`/report/review`), Submitted Screen (`/report/submitted`)

### Community 90 - "CIVILWATCH — Citizen App Integration Context"
Cohesion: 0.25
Nodes (7): 11. Not Yet Built — Planned Features, 1.1 App Name & Branding, 1.2 Tech Stack, 1.3 Current Backend Status, 1. Overview, CIVILWATCH — Citizen App Integration Context, Table of Contents

### Community 91 - "15. Extended Widget Library"
Cohesion: 0.29
Nodes (7): 15. Extended Widget Library, Buttons, Cards, Common, Inputs, Map Widgets (additions to Section 10), Timeline

### Community 92 - "2. Authentication & Registration"
Cohesion: 0.29
Nodes (7): 2.1 Registration — Fields Collected, 2.2 Login Method, 2.3 OTP Details, 2.4 Guest / Anonymous Mode, 2.5 Current Auth System, 2.6 Citizen User Data Model (`AppUser`), 2. Authentication & Registration

### Community 93 - "4. Report Submission"
Cohesion: 0.29
Nodes (7): 4.1 Current Concern Types (Final — as of latest update), 4.2 All Fields Collected During Submission, 4.3 Location Selection — Two Methods, 4.4 Photo Upload — Current Status, 4.5 After Submitting, 4.6 Exact Data Object Created on Submit, 4. Report Submission

### Community 94 - "7. Design & UI"
Cohesion: 0.29
Nodes (7): 7.1 Colour Palette, 7.2 Typography, 7.3 Platform, 7.4 Icons, 7.5 Map Library, 7.6 Key Design Conventions, 7. Design & UI

### Community 95 - "10. Critical Mismatches to Resolve"
Cohesion: 0.33
Nodes (6): 10.1 Category Spelling, 10.2 Severity vs Priority Column Name, 10.3 Status: "Assigned to Office" vs "Assigned", 10.4 Reference Number Generation, 10.5 `submitted_by` / `phone` Not Yet Wired, 10. Critical Mismatches to Resolve

### Community 96 - "5. Report Tracking"
Cohesion: 0.33
Nodes (6): 5.1 Can Citizens Track Reports?, 5.2 How They Find a Report, 5.3 Information Shown When Viewing a Report, 5.4 My Reports Page — Card Info Shown, 5.5 Report Status Flow (Citizen App Side), 5. Report Tracking

### Community 97 - "9. Schema Changes Needed"
Cohesion: 0.33
Nodes (6): 9.1 New `citizens` Table, 9.2 New Columns on `reports` Table, 9.3 New `citizen_notifications` Table, 9.4 Optional: `announcements` Table, 9.5 RLS Policy for `reports` — Citizen Access, 9. Schema Changes Needed

### Community 99 - "AppRoutes.trackReport"
Cohesion: 0.33
Nodes (4): _viewFullDetails, _viewDetail, build, build

### Community 100 - "10. Design System"
Cohesion: 0.40
Nodes (5): 10. Design System, Animations, Colour Palette, Component Conventions, Typography

### Community 101 - "1. Authentication"
Cohesion: 0.40
Nodes (5): 1. Authentication, Login Screen, OTP Screen, Register Screen, Splash Screen

### Community 102 - "9. State Management & Data Layer"
Cohesion: 0.40
Nodes (5): 9. State Management & Data Layer, Dummy Data (pre-loaded), Government Offices, Report Status Flow, Severity Normalisation

### Community 103 - "18. Legacy / Parallel Screens"
Cohesion: 0.50
Nodes (4): 18. Legacy / Parallel Screens, `ReportDetailsScreen` (`report/report_details.dart`) — Legacy Step 4, `ReportIssueScreen` (`report/report_issue.dart`) — Legacy Step 2, Route Aliases in `AppRoutes`

### Community 104 - "5. Track Report"
Cohesion: 0.50
Nodes (4): 5. Track Report, Private Map Screen, Status Update Screen, Track Report Screen

### Community 105 - "3. All Screens"
Cohesion: 0.50
Nodes (4): 3.1 Complete Screen Inventory, 3.2 Landing Page Before Login, 3.3 Bottom Navigation (5 tabs), 3. All Screens

### Community 106 - "6. Notifications"
Cohesion: 0.50
Nodes (4): 6.1 Notification System, 6.2 Events That Trigger Notifications, 6.3 Notification Data Model, 6. Notifications

### Community 107 - "8. Integration Points & Field Mapping"
Cohesion: 0.50
Nodes (4): 8.1 Citizen App → Admin DB Column Mapping, 8.2 Data the Citizen App Needs to READ from Admin System, 8.3 Auth Separation Strategy, 8. Integration Points & Field Mapping

### Community 108 - "13. Theme System"
Cohesion: 0.67
Nodes (3): 13. Theme System, AppTextStyles (`lib/core/theme/text_styles.dart`), AppTheme (`lib/core/theme/app_theme.dart`)

### Community 109 - "19. Additional Data Models"
Cohesion: 0.67
Nodes (3): 19. Additional Data Models, `Announcement` (defined in `dummy_data.dart`), `MapPin` (defined in `dummy_data.dart`)

### Community 110 - "2. Home Dashboard"
Cohesion: 0.67
Nodes (3): 2. Home Dashboard, Home Tab, Navigation Shell

## Knowledge Gaps
- **1370 isolated node(s):** `build`, `ApiConstants`, `baseUrl`, `ping`, `login` (+1365 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1473 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **26 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `_` connect `_` to `../../core/routes/app_routes.dart`, `StatelessWidget`, `package:flutter/services.dart`, `notification_service.dart`, `_`, `package:flutter/material.dart`, `dummy_data.dart`, `status_update_screen.dart`, `../../core/constants/app_colors.dart`, `my_reports_screen.dart`, `package:google_fonts/google_fonts.dart`, `skeleton.dart`, `route_generator.dart`, `Color`, `build`, `IconData`, `../../core/state/app_state.dart`, `VoidCallback?`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **What connects `build`, `ApiConstants`, `baseUrl` to the rest of the system?**
  _1370 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `app_strings.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.013333333333333334 - nodes in this community are weakly interconnected._
- **Why does `_` connect `_` to `_`, `auth_service.dart`, `_`, `../../core/constants/app_colors.dart`, `package:google_fonts/google_fonts.dart`, `Color`, `IconData`, `../../core/state/app_state.dart`, `VoidCallback?`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Should `forgot_password_flow.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.0273972602739726 - nodes in this community are weakly interconnected._
- **Why does `_` connect `_` to `../../core/routes/app_routes.dart`, `AppRoutes.trackReport`, `StatelessWidget`, `notification_service.dart`, `splash_screen.dart`, `_`, `../../core/constants/app_colors.dart`, `package:google_fonts/google_fonts.dart`, `skeleton.dart`, `Color`, `build`, `_`, `IconData`, `../../core/state/app_state.dart`, `VoidCallback?`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Should `app_icons.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.03225806451612903 - nodes in this community are weakly interconnected._
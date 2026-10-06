# Graph Report - prc  (2026-10-05)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1666 nodes · 2330 edges · 86 communities (67 shown, 19 thin omitted)
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
- State
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
- otp_box.dart
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
- ../../core/utils/helpers.dart
- app_bar.dart
- my_reports_screen.dart
- ../../core/constants/app_colors.dart
- visitor_about_screen.dart
- icon_button.dart
- primary_button.dart
- secondary_button.dart
- Color
- validators.dart
- List
- report_service.dart
- app_images.dart
- semaphore_service.dart
- IconData
- ../models/report.dart
- notification_screen.dart
- package:google_fonts/google_fonts.dart
- notification_card.dart
- report_card.dart
- VoidCallback
- String?
- announcements/index.blade.php
- offices/index.blade.php
- build
- _next
- citizen-reports/index.blade.php
- show.blade.php
- dashboard.blade.php
- AppState
- ApiException
- _PinInputRow
- _next
- map.blade.php

## God Nodes (most connected - your core abstractions)
1. `_` - 57 edges
2. `_` - 56 edges
3. `_` - 43 edges
4. `_` - 39 edges
5. `_` - 33 edges
6. `_` - 31 edges
7. `_` - 26 edges
8. `_` - 18 edges
9. `IncidentReport` - 7 edges
10. `build` - 5 edges

## Surprising Connections (you probably didn't know these)
- `_` --references--> `Announcement`  [EXTRACTED]
  civ-main/lib/screens/home/home_screen.dart → civ-main/lib/core/utils/dummy_data.dart

## Import Cycles
- None detected.

## Communities (86 total, 19 thin omitted)

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
Nodes (43): _AddressCard, _addressCtrl, _AddressField, build, _canProceed, catColor, _cityCtrl, color (+35 more)

### Community 5 - "register_screen.dart"
Cohesion: 0.04
Nodes (43): agreed, _agreedToTerms, _animController, _BarangayDropdown, build, _buildPhoneField, _buildTextField, _confirmPin (+35 more)

### Community 6 - "helpers.dart"
Cohesion: 0.04
Nodes (39): appBarTitle, AppTextStyles, bodyLarge, bodyMedium, bodySmall, buttonLarge, buttonMedium, caption (+31 more)

### Community 7 - "_"
Cohesion: 0.05
Nodes (40): _SkylinePainter, _TailPainter, _HomeMiniMapPainter, _MiniCityPainter, _LandingSkylinePainter, _TailPainter, _SuccessCityPainter, _ (+32 more)

### Community 8 - "track_by_reference_screen.dart"
Cohesion: 0.05
Nodes (35): _PhoneFmt, _PhoneNumberFormatter, _PhoneFormatter, _viewFullDetails, build, build, _CompactTimeline, createState (+27 more)

### Community 9 - "app_colors.dart"
Cohesion: 0.05
Nodes (38): AppColors, background, cardShadow, divider, environment, environmentBg, infrastructure, infrastructureBg (+30 more)

### Community 10 - "_"
Cohesion: 0.05
Nodes (36): _, _activeFilter, bg, build, _center, color, CommunityMapScreen, _CommunityMapScreenState (+28 more)

### Community 11 - "dummy_data.dart"
Cohesion: 0.05
Nodes (32): Announcement, announcements, barangay, body, category, communityPins, communityReports, currentUser (+24 more)

### Community 12 - "status_update_screen.dart"
Cohesion: 0.06
Nodes (32): _viewOnMap, build, build, activityLog, _ActivityRow, _AdvanceStatusButton, build, _buildPendingSteps (+24 more)

### Community 13 - "report.dart"
Cohesion: 0.06
Nodes (31): activityLog, afterImageUrl, assignedOffice, barangay, category, copyWith, description, id (+23 more)

### Community 14 - "report_photo.dart"
Cohesion: 0.06
Nodes (31): bg, build, _buildImage, bytes, canSkip, catColor, color, createState (+23 more)

### Community 15 - "custom_textfield.dart"
Cohesion: 0.07
Nodes (23): AppNetworkImage, borderRadius, build, errorWidget, fit, height, _placeholder, url (+15 more)

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
Nodes (22): ApiClient, delete, deleteToken, errors, _extractValidationError, getToken, _headers, _instance (+14 more)

### Community 20 - "_"
Cohesion: 0.07
Nodes (26): _, bg, border, build, _CivilWatchLogo, color, createState, _ctrl (+18 more)

### Community 21 - "app_routes.dart"
Cohesion: 0.08
Nodes (24): AppRoutes, communityMap, home, landing, login, myReports, notifications, otp (+16 more)

### Community 22 - "State"
Cohesion: 0.13
Nodes (22): _ForgotPasswordSheet, _ForgotPasswordSheetState, LoginScreen, _LoginScreenState, OtpScreen, _OtpScreenState, RegisterScreen, _RegisterScreenState (+14 more)

### Community 23 - "_"
Cohesion: 0.08
Nodes (23): _, _BeforeAfterDialog, build, color, _FullscreenPhotoViewer, _getReport, icon, imageUrl (+15 more)

### Community 24 - "report_details.dart"
Cohesion: 0.08
Nodes (21): _BarangayDropdown, build, catColor, createState, _descController, dispose, _formKey, icon (+13 more)

### Community 25 - "track_report_screen.dart"
Cohesion: 0.08
Nodes (22): afterPending, color, fallbackColor, fallbackIcon, fullWidth, _getReport, icon, imageUrl (+14 more)

### Community 26 - "otp_screen.dart"
Cohesion: 0.08
Nodes (19): _animController, build, createState, _devOtp, dispose, _fadeAnim, initState, isNewUser (+11 more)

### Community 27 - "_"
Cohesion: 0.09
Nodes (20): _, announcement, bg, color, count, createState, firstName, icon (+12 more)

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

### Community 34 - "otp_box.dart"
Cohesion: 0.11
Nodes (13): AppTheme, light, _lightTheme, build, _controllers, createState, dispose, _focusNodes (+5 more)

### Community 35 - "StatelessWidget"
Cohesion: 0.11
Nodes (18): _AnnouncementCard, _GreetingSection, _HomeAppBar, _HomeTab, _LegendItem, _MiniMapCard, _MiniPin, _ReportCtaBanner (+10 more)

### Community 36 - "report_category.dart"
Cohesion: 0.11
Nodes (17): bg, build, _CategoryCard, color, createState, icon, isSelected, _next (+9 more)

### Community 37 - "notification_service.dart"
Cohesion: 0.12
Nodes (10): AnnouncementService, _api, instance, _parse, _api, instance, markAllRead, markRead (+2 more)

### Community 38 - "splash_screen.dart"
Cohesion: 0.13
Nodes (12): build, _CivilWatchLogo, _controller, createState, dispose, _fadeAnim, initState, _scaleAnim (+4 more)

### Community 39 - "package:flutter/material.dart"
Cohesion: 0.14
Nodes (4): build, CivilWatchApp, main, main

### Community 40 - "auth_service.dart"
Cohesion: 0.13
Nodes (10): _api, AuthService, _currentUser, fetchMe, instance, isLoggedIn, logout, _normalizePhone (+2 more)

### Community 41 - "report_issue.dart"
Cohesion: 0.14
Nodes (11): _next, build, catColor, category, createState, _next, onNext, ReportIssueScreen (+3 more)

### Community 42 - "user.dart"
Cohesion: 0.14
Nodes (11): AppUser, avatarUrl, barangay, firstName, fullName, id, joinedDate, pendingReports (+3 more)

### Community 43 - "_"
Cohesion: 0.14
Nodes (12): _, build, _controller, createState, dispose, _fadeAnim, initState, paint (+4 more)

### Community 44 - "visitor_reports_screen.dart"
Cohesion: 0.14
Nodes (13): _activeFilter, color, createState, dispose, _filters, label, _LegendDot, _onLoginTap (+5 more)

### Community 45 - "../../core/utils/helpers.dart"
Cohesion: 0.15
Nodes (10): build, dateLabel, message, showLock, status, StatusCard, build, small (+2 more)

### Community 46 - "app_bar.dart"
Cohesion: 0.15
Nodes (10): actions, backgroundColor, build, CivilWatchAppBar, onBack, preferredSize, showBack, subtitle (+2 more)

### Community 47 - "my_reports_screen.dart"
Cohesion: 0.17
Nodes (8): _activeFilter, createState, dispose, embedded, _filters, _getFiltered, _searchController, _searchQuery

### Community 48 - "../../core/constants/app_colors.dart"
Cohesion: 0.18
Nodes (8): build, currentStep, _labels, ReportStepper, build, ProgressTimeline, report, _steps

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

### Community 55 - "List"
Cohesion: 0.20
Nodes (8): abbreviation, address, contactNumber, email, GovernmentOffice, handles, id, name

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

### Community 60 - "../models/report.dart"
Cohesion: 0.25
Nodes (6): ActivityEntry, ActivityCard, build, entry, isCurrent, isLast

### Community 61 - "notification_screen.dart"
Cohesion: 0.25
Nodes (5): build, embedded, _GroupLabel, label, NotificationScreen

### Community 62 - "package:google_fonts/google_fonts.dart"
Cohesion: 0.25
Nodes (5): build, controller, hint, onClear, SearchField

### Community 63 - "notification_card.dart"
Cohesion: 0.29
Nodes (5): AppNotification, build, notification, NotificationCard, onTap

### Community 64 - "report_card.dart"
Cohesion: 0.29
Nodes (5): IncidentReport, build, onTap, report, ReportCard

### Community 65 - "VoidCallback"
Cohesion: 0.29
Nodes (5): actionLabel, build, onAction, SectionTitle, title

### Community 66 - "String?"
Cohesion: 0.40
Nodes (3): AppLoading, build, message

## Knowledge Gaps
- **1192 isolated node(s):** `aboutCivilwatch`, `activityLog`, `addAPhoto`, `additionalDetails`, `additionalDetailsHint` (+1187 more)
  These have ≤1 connection - possible missing edges. (Counts symbols only; 1287 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **19 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `_` connect `_` to `VoidCallback`, `String?`, `StatelessWidget`, `_`, `auth_service.dart`, `package:flutter/material.dart`, `_`, `../../core/utils/helpers.dart`, `../../core/constants/app_colors.dart`, `Color`, `IconData`, `../models/report.dart`, `package:google_fonts/google_fonts.dart`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **What connects `aboutCivilwatch`, `activityLog`, `addAPhoto` to the rest of the system?**
  _1192 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `app_strings.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.013333333333333334 - nodes in this community are weakly interconnected._
- **Why does `_` connect `_` to `VoidCallback`, `otp_box.dart`, `StatelessWidget`, `notification_service.dart`, `_`, `package:flutter/material.dart`, `dummy_data.dart`, `status_update_screen.dart`, `../../core/utils/helpers.dart`, `../../core/constants/app_colors.dart`, `route_generator.dart`, `Color`, `State`, `List`, `IconData`, `notification_screen.dart`, `package:google_fonts/google_fonts.dart`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Should `forgot_password_flow.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.0273972602739726 - nodes in this community are weakly interconnected._
- **Why does `_` connect `_` to `VoidCallback`, `notification_service.dart`, `splash_screen.dart`, `_`, `track_by_reference_screen.dart`, `package:flutter/material.dart`, `status_update_screen.dart`, `../../core/utils/helpers.dart`, `../../core/constants/app_colors.dart`, `Color`, `_`, `IconData`, `../models/report.dart`, `package:google_fonts/google_fonts.dart`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Should `app_icons.dart` be split into smaller, more focused modules?**
  _Cohesion score 0.03225806451612903 - nodes in this community are weakly interconnected._
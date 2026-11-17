import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/app_colors.dart';
import '../../models/report.dart';
import '../../models/notification_model.dart';
import '../../models/user.dart';
import '../../services/auth_service.dart';
import '../../services/report_service.dart';
import '../../services/notification_service.dart';
import '../../services/announcement_service.dart';
import '../utils/dummy_data.dart'; // Announcement model lives here
import '../utils/helpers.dart';

/// Central app state — ChangeNotifier singleton.
///
/// Data sources:
///   - When logged in:  real API data from Laravel backend
///   - When guest:      community reports from API (public endpoint)
///   - On API failure:  previously loaded data (or empty lists) + *Error flag.
///                      Sample DummyData is never shown as real data.
class AppState extends ChangeNotifier {

  // ── Singleton ─────────────────────────────────────────────────────────────
  static final AppState _instance = AppState._internal();
  factory AppState() => _instance;
  AppState._internal();

  // ── Services ──────────────────────────────────────────────────────────────
  final _auth      = AuthService.instance;
  final _reportSvc = ReportService.instance;
  final _notifSvc  = NotificationService.instance;
  final _annSvc    = AnnouncementService.instance;

  // ── Init — called once from main.dart ─────────────────────────────────────
  bool _initialized = false;
  bool _isLoading = false;
  bool get isLoading => _isLoading;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;

    // Restore dark-mode preference first so the first frame renders correctly
    await _loadThemePreference();

    // Always load public data (community reports + announcements — no auth)
    // and, in parallel, try to restore the session from the saved token.
    // Each request is bounded by ApiClient's timeout, so this can't hang.
    final results = await Future.wait<Object?>([
      _loadCommunityReports(),
      _loadAnnouncements(),
      _auth.restoreSession(),
    ]);
    final user = results[2] as AppUser?;
    if (user != null) {
      _currentUser = user;
      _isGuest = false;
      await Future.wait([
        _loadMyReports(),
        _loadNotifications(),
      ]);
    } else {
      // Not logged in — no personal data (never show sample data as real)
      _reports = [];
      _notifications = [];
      _unreadCount = 0;
    }
    notifyListeners();
  }

  // ── Theme — dark mode toggle ──────────────────────────────────────────────
  static const _kDarkModeKey = 'dark_mode';
  // Default for first-time users (no saved preference): Light Mode.
  bool _isDarkMode = false;
  bool get isDarkMode => _isDarkMode;

  Future<void> _loadThemePreference() async {
    final prefs = await SharedPreferences.getInstance();
    // Restore the saved choice; first launch falls back to Light Mode.
    _isDarkMode = prefs.getBool(_kDarkModeKey) ?? false;
    _applySystemUi();
  }

  /// Flip dark mode on/off. Persists the choice across app restarts.
  ///
  /// This is the single source of truth: `MaterialApp.themeMode` listens and
  /// swaps the whole theme (including the [AppPalette] extension), so every
  /// screen repaints. There is no second palette state.
  Future<void> setDarkMode(bool value) async {
    if (_isDarkMode == value) return;
    _isDarkMode = value;
    _applySystemUi();
    notifyListeners(); // MaterialApp listens → switches themeMode
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kDarkModeKey, value);
  }

  /// Match the OS status/navigation bars to the active theme.
  /// (No-op on web; matters on Android/iOS.)
  void _applySystemUi() {
    SystemChrome.setSystemUIOverlayStyle(SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: _isDarkMode ? Brightness.light : Brightness.dark,
      statusBarBrightness: _isDarkMode ? Brightness.dark : Brightness.light,
      systemNavigationBarColor:
          _isDarkMode ? AppPalette.dark.surface : AppPalette.light.surface,
      systemNavigationBarIconBrightness:
          _isDarkMode ? Brightness.light : Brightness.dark,
    ));
  }

  // ── Auth / session ────────────────────────────────────────────────────────
  AppUser? _currentUser;
  AppUser? get currentUser => _currentUser;
  bool get isLoggedIn => _currentUser != null;

  bool _isGuest = false;
  bool get isGuest => _isGuest;

  void enterAsGuest() {
    _isGuest = true;
    notifyListeners();
  }

  void exitGuest() {
    _isGuest = false;
    notifyListeners();
  }

  /// Called after successful login/register from auth screens.
  Future<void> onLoginSuccess(AppUser user) async {
    _currentUser = user;
    _isGuest = false;
    await _loadAllData();
    notifyListeners();
  }

  /// Called on logout.
  Future<void> logout() async {
    await _auth.logout();
    _currentUser = null;
    _reports = [];
    _notifications = [];
    _unreadCount = 0;
    // Community reports and announcements stay loaded (public data)
    notifyListeners();
  }

  // ── Load all data from API ────────────────────────────────────────────────
  Future<void> _loadAllData() async {
    _isLoading = true;
    notifyListeners();

    await Future.wait([
      _loadMyReports(),
      _loadCommunityReports(),
      _loadNotifications(),
      _loadAnnouncements(),
    ]);

    _isLoading = false;
    notifyListeners();
  }

  /// Reload everything — call this to refresh (e.g. pull-to-refresh).
  Future<void> refresh() => _loadAllData();

  // ── Reports ───────────────────────────────────────────────────────────────
  List<IncidentReport> _reports = [];
  List<IncidentReport> _communityReports = [];

  List<IncidentReport> get reports => List.unmodifiable(_reports);
  List<IncidentReport> get communityReportsPublic =>
      List.unmodifiable(_communityReports);

  // Last load error per data set (null = last load succeeded). Screens can
  // show these alongside their empty states.
  String? _myReportsError;
  String? _communityReportsError;
  String? _announcementsError;
  String? _notificationsError;
  String? get myReportsError => _myReportsError;
  String? get communityReportsError => _communityReportsError;
  String? get announcementsError => _announcementsError;
  String? get notificationsError => _notificationsError;

  Future<void> _loadMyReports() async {
    final result = await _reportSvc.getMyReports();
    if (result.error == null) {
      _reports = result.reports;
      _myReportsError = null;
    } else {
      // Keep whatever was loaded before; never substitute sample data.
      _myReportsError = result.error;
    }
  }

  Future<void> _loadCommunityReports() async {
    final result = await _reportSvc.getCommunityReports();
    if (result.error == null) {
      _communityReports = result.reports;
      _communityReportsError = null;
    } else {
      // Keep whatever was loaded before; never substitute sample data.
      _communityReportsError = result.error;
    }
    notifyListeners();
  }

  /// Submit a report — calls API then reloads list on success.
  Future<({bool success, IncidentReport? report, String? error})>
      submitReport(Map<String, dynamic> data) async {
    final result = await _reportSvc.submitReport(data);
    if (result.error == null && result.report != null) {
      _reports.insert(0, result.report!);
      notifyListeners();
      // The server creates the submission notification — reload it rather
      // than fabricating local ones. Not awaited so the submit flow isn't
      // delayed by the extra request.
      _loadNotifications().then((_) => notifyListeners());
    }
    return (
      success: result.error == null && result.report != null,
      report: result.report,
      error: result.error,
    );
  }

  /// Fetch a fresh copy of a single report from API.
  Future<IncidentReport?> fetchReport(String id) async {
    final idInt = int.tryParse(id);
    if (idInt == null) return getById(id);
    final result = await _reportSvc.getReportById(idInt);
    if (result.report != null) {
      final idx = _reports.indexWhere((r) => r.id == id);
      if (idx != -1) {
        _reports[idx] = result.report!;
        notifyListeners();
      }
      return result.report;
    }
    return getById(id);
  }

  /// Re-fetch "My Reports" from the API and notify listeners.
  /// Screens must call this in initState so a report submitted elsewhere
  /// (or approved by an admin) shows up without restarting the app.
  Future<void> refreshMyReports() async {
    await _loadMyReports();
    notifyListeners();
  }

  /// Re-fetch the public community feed (validated reports for the map).
  Future<void> refreshCommunityReports() => _loadCommunityReports();

  /// Look up by ID across both lists (local cache).
  IncidentReport? getById(String id) {
    try { return _reports.firstWhere((r) => r.id == id); } catch (_) {}
    try { return _communityReports.firstWhere((r) => r.id == id); } catch (_) {
      return null;
    }
  }

  /// Look up by reference number (case-insensitive).
  IncidentReport? getByReference(String refNumber) {
    final q = refNumber.trim().toUpperCase();
    try {
      return _reports.firstWhere((r) => r.referenceNumber.toUpperCase() == q);
    } catch (_) {}
    try {
      return _communityReports.firstWhere(
          (r) => r.referenceNumber.toUpperCase() == q);
    } catch (_) { return null; }
  }

  // ── Announcements ─────────────────────────────────────────────────────────
  List<Announcement> _announcements = [];
  List<Announcement> get announcements => List.unmodifiable(_announcements);

  Future<void> _loadAnnouncements() async {
    final result = await _annSvc.getAnnouncements();
    if (result.error == null) {
      _announcements = result.announcements;
      _announcementsError = null;
    } else {
      _announcementsError = result.error;
    }
  }

  // ── Notifications ─────────────────────────────────────────────────────────
  List<AppNotification> _notifications = [];
  int _unreadCount = 0;

  List<AppNotification> get notifications => List.unmodifiable(_notifications);
  int get unreadCount => _unreadCount;

  Future<void> _loadNotifications() async {
    final result = await _notifSvc.getNotifications();
    if (result.error == null) {
      _notifications = result.notifications;
      _unreadCount = result.unreadCount;
      _notificationsError = null;
    } else {
      _notificationsError = result.error;
    }
  }

  /// Marks every notification read. Local state only changes once the API
  /// call succeeds; returns the error message on failure (null on success).
  Future<String?> markAllRead() async {
    final error = await _notifSvc.markAllRead();
    if (error != null) return error;
    _notifications = _notifications.map((n) => n.copyWith(isRead: true)).toList();
    _unreadCount = 0;
    notifyListeners();
    return null;
  }

  Future<void> markRead(String id) async {
    final idInt = int.tryParse(id);
    if (idInt != null) await _notifSvc.markRead(idInt);
    final idx = _notifications.indexWhere((n) => n.id == id);
    if (idx != -1) {
      _notifications[idx] = _notifications[idx].copyWith(isRead: true);
      _unreadCount = _notifications.where((n) => !n.isRead).length;
      notifyListeners();
    }
  }

  // ── Summary counts ────────────────────────────────────────────────────────
  int get pendingCount   => _reports.where((r) => r.isPending).length;
  int get inProgressCount => _reports.where((r) => r.status == 'In Progress').length;
  int get resolvedCount  => _reports.where((r) => r.status == 'Resolved').length;
  int get rejectedCount  => _reports.where((r) => r.isRejected).length;
  int get totalCount     => _reports.length;

  // ── Helpers ───────────────────────────────────────────────────────────────
  static IncidentReport buildFromFormData(Map<String, dynamic> data) {
    final now = DateTime.now();
    final issue = data['concern'] as String? ?? data['issue'] as String? ?? 'Others';
    final description = data['additionalDetails'] as String? ?? data['description'] as String? ?? '';
    final barangay = data['barangay'] as String? ?? 'Unknown Barangay';
    final severity = AppHelpers.normaliseSeverity(data['severity'] as String? ?? 'Medium');
    final lat = (data['latitude'] as num?)?.toDouble() ?? 6.7498;
    final lng = (data['longitude'] as num?)?.toDouble() ?? 125.3572;

    return IncidentReport(
      id: 'r-${now.millisecondsSinceEpoch}',
      referenceNumber: data['referenceNumber'] as String? ?? AppHelpers.generateRefNumber(),
      category: data['category'] as String? ?? 'Infrastructure',
      issue: issue,
      description: description,
      barangay: barangay,
      status: 'Pending Validation',
      severity: severity,
      submittedAt: now,
      imageUrl: null,
      latitude: lat,
      longitude: lng,
      assignedOffice: null,
      activityLog: [
        ActivityEntry(title: 'Concern Submitted', description: 'Your concern has been successfully submitted.', timestamp: now, status: 'Submitted'),
        ActivityEntry(title: 'Pending Validation', description: 'Your concern is now waiting for validation.', timestamp: now.add(const Duration(minutes: 1)), status: 'Pending Validation'),
      ],
    );
  }
}

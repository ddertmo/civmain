import 'package:flutter/foundation.dart' show kReleaseMode;

/// All API endpoint constants for the CivilWatch backend.
///
/// The base URL is overridable at build time so the same source works on
/// every target without editing code:
///
///   flutter run  --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
///   # Android emulator -> 10.0.2.2 (host loopback)
///   flutter run  --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
///   # Real phone on the same WiFi -> your machine's LAN IPv4
///   flutter run  --dart-define=API_BASE_URL=http://192.168.1.10:8000/api
///
/// Laravel must be started with --host=0.0.0.0 for phones to reach it:
///   php artisan serve --host=0.0.0.0 --port=8000
///
/// Default is 127.0.0.1, which only reaches the backend from the same
/// machine (web/desktop builds). The default applies to debug/profile only:
/// release builds require an https:// URL, e.g.
///   flutter build apk --release --dart-define=API_BASE_URL=https://api.example.com/api
class ApiConstants {
  ApiConstants._();

  // ── Base ────────────────────────────────────────────────────────────────
  /// Dev-only fallback used by debug/profile builds when API_BASE_URL is
  /// not passed. Never used by release builds.
  static const String _devBaseUrl = 'http://127.0.0.1:8000/api';

  /// Raw value of --dart-define=API_BASE_URL ('' when not provided).
  static const String _configuredBaseUrl = String.fromEnvironment('API_BASE_URL');

  /// Base URL for every API call.
  ///
  /// Release builds must talk to the backend over HTTPS: a release build
  /// whose API_BASE_URL is missing or not https:// throws a [StateError]
  /// (checked at startup by [ensureSecureBaseUrl]) instead of silently
  /// sending tokens/PINs in cleartext.
  static String get baseUrl {
    if (kReleaseMode) {
      if (!_configuredBaseUrl.startsWith('https://')) {
        final got = _configuredBaseUrl.isEmpty ? '<missing>' : _configuredBaseUrl;
        throw StateError(
          'Release builds need --dart-define=API_BASE_URL=https://... '
          '(got "$got").',
        );
      }
      return _configuredBaseUrl;
    }
    return _configuredBaseUrl.isEmpty ? _devBaseUrl : _configuredBaseUrl;
  }

  /// Call once at startup (main.dart) so a misconfigured release build
  /// fails immediately rather than on the first network request.
  static String ensureSecureBaseUrl() => baseUrl;

  // ── Health ──────────────────────────────────────────────────────────────
  static String get ping => '$baseUrl/ping';

  // ── Mobile Auth ─────────────────────────────────────────────────────────
  static String get login     => '$baseUrl/mobile/auth/login';
  static String get sendOtp   => '$baseUrl/mobile/auth/send-otp';
  static String get verifyOtp => '$baseUrl/mobile/auth/verify-otp';
  static String get verifyResetOtp => '$baseUrl/mobile/auth/verify-reset-otp';
  static String get resetPin => '$baseUrl/mobile/auth/reset-pin';
  static String get register  => '$baseUrl/mobile/auth/register';
  static String get logout    => '$baseUrl/mobile/auth/logout';
  static String get me        => '$baseUrl/mobile/auth/me';

  // ── Mobile Reports ───────────────────────────────────────────────────────
  static String get reports          => '$baseUrl/mobile/reports';
  static String get communityReports => '$baseUrl/mobile/reports/community';
  static String reportById(int id)   => '$baseUrl/mobile/reports/$id';

  // ── Mobile Notifications ─────────────────────────────────────────────────
  static String get notifications    => '$baseUrl/mobile/notifications';
  static String get markAllRead      => '$baseUrl/mobile/notifications/mark-all-read';
  static String markOneRead(int id)  => '$baseUrl/mobile/notifications/$id/read';

  // ── Mobile Announcements ─────────────────────────────────────────────────
  static String get announcements => '$baseUrl/mobile/announcements';

  // ── HTTP headers ──────────────────────────────────────────────────────────
  static Map<String, String> jsonHeaders([String? token]) => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  static Map<String, String> authHeaders(String token) => {
    'Accept': 'application/json',
    'Authorization': 'Bearer $token',
  };
}

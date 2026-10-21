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
/// machine (web/desktop builds).
class ApiConstants {
  ApiConstants._();

  // ── Base ────────────────────────────────────────────────────────────────
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api',
  );

  // ── Health ──────────────────────────────────────────────────────────────
  static const String ping = '$baseUrl/ping';

  // ── Mobile Auth ─────────────────────────────────────────────────────────
  static const String login     = '$baseUrl/mobile/auth/login';
  static const String sendOtp   = '$baseUrl/mobile/auth/send-otp';
  static const String verifyOtp = '$baseUrl/mobile/auth/verify-otp';
  static const String verifyResetOtp = '$baseUrl/mobile/auth/verify-reset-otp';
  static const String resetPin = '$baseUrl/mobile/auth/reset-pin';
  static const String register  = '$baseUrl/mobile/auth/register';
  static const String logout    = '$baseUrl/mobile/auth/logout';
  static const String me        = '$baseUrl/mobile/auth/me';

  // ── Mobile Reports ───────────────────────────────────────────────────────
  static const String reports          = '$baseUrl/mobile/reports';
  static const String communityReports = '$baseUrl/mobile/reports/community';
  static String reportById(int id)     => '$baseUrl/mobile/reports/$id';

  // ── Mobile Notifications ─────────────────────────────────────────────────
  static const String notifications        = '$baseUrl/mobile/notifications';
  static const String markAllRead          = '$baseUrl/mobile/notifications/mark-all-read';
  static String markOneRead(int id)        => '$baseUrl/mobile/notifications/$id/read';

  // ── Mobile Announcements ─────────────────────────────────────────────────
  static const String announcements = '$baseUrl/mobile/announcements';

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

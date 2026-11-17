import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

/// Shared HTTP client for all API calls.
///
/// - Automatically injects Bearer token from storage
/// - Parses JSON responses
/// - Throws [ApiException] on non-2xx responses
/// - Applies a [_timeout] to every request
/// - Token is kept in flutter_secure_storage (Keychain / Android Keystore) on
///   mobile; on web it stays in SharedPreferences (browser storage — secure
///   storage offers no real protection there anyway)
class ApiClient {
  ApiClient._();
  static final ApiClient _instance = ApiClient._();
  static ApiClient get instance => _instance;

  static const _tokenKey = 'cw_citizen_token';
  static const _timeout = Duration(seconds: 15);
  static const _uploadTimeout = Duration(seconds: 90);

  static const FlutterSecureStorage _secure = FlutterSecureStorage();

  // ── Token storage ─────────────────────────────────────────────────────

  Future<void> saveToken(String token) async {
    if (kIsWeb) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, token);
      return;
    }
    await _secure.write(key: _tokenKey, value: token);
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    if (kIsWeb) return prefs.getString(_tokenKey);

    String? secureToken;
    try {
      secureToken = await _secure.read(key: _tokenKey);
    } on Exception {
      // Keystore/Keychain read failure — treat as signed out.
      secureToken = null;
    }
    if (secureToken != null && secureToken.isNotEmpty) return secureToken;

    // One-time migration: older builds stored the token in SharedPreferences.
    final legacy = prefs.getString(_tokenKey);
    if (legacy != null && legacy.isNotEmpty) {
      try {
        await _secure.write(key: _tokenKey, value: legacy);
        await prefs.remove(_tokenKey);
      } on Exception {
        // Secure write failed — keep the legacy copy so the user stays in.
      }
      return legacy;
    }
    return null;
  }

  Future<void> deleteToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    if (kIsWeb) return;
    try {
      await _secure.delete(key: _tokenKey);
    } on Exception {
      // Nothing more we can do; the prefs copy is already gone.
    }
  }

  Future<bool> get hasToken async {
    final t = await getToken();
    return t != null && t.isNotEmpty;
  }

  // ── GET ───────────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> get(String url, {bool auth = true}) async {
    final headers = await _headers(auth);
    _log('GET', url);
    final response =
        await _send(() => http.get(Uri.parse(url), headers: headers));
    return _parse(response);
  }

  // ── POST (JSON) ───────────────────────────────────────────────────────

  Future<Map<String, dynamic>> post(
    String url,
    Map<String, dynamic> body, {
    bool auth = false,
  }) async {
    final headers = await _headers(auth);
    headers['Content-Type'] = 'application/json';
    _log('POST', url, body: body);
    final response = await _send(() => http.post(
          Uri.parse(url),
          headers: headers,
          body: jsonEncode(body),
        ));
    return _parse(response);
  }

  // ── POST multipart (for photo uploads) ───────────────────────────────

  Future<Map<String, dynamic>> postMultipart(
    String url,
    Map<String, String> fields, {
    List<http.MultipartFile> files = const [],
  }) async {
    final token = await getToken();
    final request = http.MultipartRequest('POST', Uri.parse(url));

    if (token != null) {
      request.headers['Authorization'] = 'Bearer $token';
    }
    request.headers['Accept'] = 'application/json';
    request.fields.addAll(fields);
    request.files.addAll(files);

    _log('POST multipart', url, body: fields);
    // Photo uploads (up to 5 MB) need longer than a normal call on slow
    // mobile data.
    final response = await _send(() async {
      final streamed = await request.send();
      return http.Response.fromStream(streamed);
    }, timeout: _uploadTimeout);
    return _parse(response);
  }

  // ── DELETE ────────────────────────────────────────────────────────────

  Future<Map<String, dynamic>> delete(String url) async {
    final headers = await _headers(true);
    _log('DELETE', url);
    final response =
        await _send(() => http.delete(Uri.parse(url), headers: headers));
    return _parse(response);
  }

  // ── Private helpers ───────────────────────────────────────────────────

  /// Runs [request] with [_timeout] and maps timeouts / connection failures
  /// to an [ApiException] with statusCode 0 (see [ApiException.isNetwork]).
  Future<http.Response> _send(
    Future<http.Response> Function() request, {
    Duration timeout = _timeout,
  }) async {
    try {
      return await request().timeout(timeout);
    } on TimeoutException {
      throw const ApiException(
        message: 'Cannot reach server. The request timed out — please check '
            'your connection and try again.',
        statusCode: 0,
      );
    } on http.ClientException {
      // On mobile, http wraps SocketException in ClientException.
      throw const ApiException(
        message: 'Cannot reach server. Please check your internet connection.',
        statusCode: 0,
      );
    }
  }

  Future<Map<String, String>> _headers(bool withAuth) async {
    final headers = <String, String>{'Accept': 'application/json'};
    if (withAuth) {
      final token = await getToken();
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Map<String, dynamic> _parse(http.Response response) {
    _log('RESPONSE', response.request?.url.toString() ?? '',
        status: response.statusCode);

    final body = response.body.isEmpty ? '{}' : response.body;
    late Map<String, dynamic> json;

    try {
      json = jsonDecode(body) as Map<String, dynamic>;
    } catch (_) {
      throw ApiException(
        message: 'Server returned invalid JSON.',
        statusCode: response.statusCode,
      );
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return json;
    }

    // Extract error message from Laravel's standard error shapes
    final rawMessage = json['message'] as String? ??
        _extractValidationError(json) ??
        'An error occurred (${response.statusCode})';

    // Sanitize — never show raw SQL, stack traces, or connection strings
    final message = _sanitizeError(rawMessage, response.statusCode);

    throw ApiException(
      message: message,
      statusCode: response.statusCode,
      errors: json['errors'] as Map<String, dynamic>?,
    );
  }

  /// Replace any technical server/SQL error with a plain-language message.
  String _sanitizeError(String raw, int statusCode) {
    final lower = raw.toLowerCase();

    // Database connection failures
    if (lower.contains('sqlstate') ||
        lower.contains('connection') ||
        lower.contains('refused') ||
        lower.contains('pdo') ||
        lower.contains('mysql') ||
        lower.contains('could not be made') ||
        lower.contains('target machine')) {
      return 'The server is temporarily unavailable. Please try again in a moment.';
    }

    // Duplicate key / unique constraint
    if (lower.contains('integrity constraint') ||
        lower.contains('duplicate entry') ||
        lower.contains('unique constraint')) {
      if (lower.contains('email')) {
        return 'That email address is already registered. Please use a different one.';
      }
      if (lower.contains('phone')) {
        return 'That mobile number is already registered. Please sign in instead.';
      }
      return 'This information is already taken. Please use different details.';
    }

    // Generic server errors
    if (statusCode >= 500) {
      return 'Something went wrong on our end. Please try again later.';
    }

    // If the message looks like a stack trace or SQL, replace it
    if (raw.length > 200 ||
        lower.contains('exception') ||
        lower.contains('stack trace') ||
        lower.contains('illuminate\\') ||
        lower.contains('select ') ||
        lower.contains('insert into')) {
      return 'An unexpected error occurred. Please try again.';
    }

    return raw;
  }

  String? _extractValidationError(Map<String, dynamic> json) {
    final errors = json['errors'];
    if (errors is Map && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) return first.first as String;
    }
    return null;
  }

  void _log(String method, String url,
      {Map<String, dynamic>? body, int? status}) {
    if (kDebugMode) {
      final statusStr = status != null ? ' [$status]' : '';
      debugPrint('🌐 $method$statusStr $url');
      if (body != null) debugPrint('   body: $body');
    }
  }
}

// ── Exception ─────────────────────────────────────────────────────────────

class ApiException implements Exception {
  final String message;
  final int statusCode;
  final Map<String, dynamic>? errors;

  const ApiException({
    required this.message,
    required this.statusCode,
    this.errors,
  });

  bool get isUnauthorized => statusCode == 401;
  bool get isValidation => statusCode == 422;
  bool get isNotFound => statusCode == 404;
  bool get isServer => statusCode >= 500;
  /// Timeout or connection failure — the server was never reached.
  bool get isNetwork => statusCode == 0;

  @override
  String toString() => 'ApiException($statusCode): $message';
}

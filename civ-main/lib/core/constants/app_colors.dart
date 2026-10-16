import 'package:flutter/material.dart';

/// CivilWatch color palette.
///
/// Two token groups:
///  - **Accent tokens** (primary green, navy, status hues): `const`, identical
///    in both themes — the brand never changes.
///  - **Surface tokens** (backgrounds, cards, text, borders, tints): plain
///    static fields swapped at runtime by [AppColors.setDark]. Every screen
///    reads these, so toggling repaints the whole app without per-screen
///    theming.
class AppColors {
  AppColors._();

  // ── Theme state ───────────────────────────────────────────────────────────
  static bool _isDark = false;
  static bool get isDark => _isDark;

  /// Swap every surface token between the light and dark palettes.
  /// Call before the first frame (from AppState.init) and on every toggle.
  static void setDark(bool dark) {
    if (_isDark == dark) return;
    _isDark = dark;

    background = dark ? _backgroundDark : _backgroundLight;
    surface = dark ? _surfaceDark : _surfaceLight;
    card = dark ? _cardDark : _cardLight;
    divider = dark ? _dividerDark : _dividerLight;

    textPrimary = dark ? _textPrimaryDark : _textPrimaryLight;
    textSecondary = dark ? _textSecondaryDark : _textSecondaryLight;
    textHint = dark ? _textHintDark : _textHintLight;
    textDisabled = dark ? _textDisabledDark : _textDisabledLight;

    inputBorder = dark ? _inputBorderDark : _inputBorderLight;
    inputFill = dark ? _inputFillDark : _inputFillLight;

    primarySurface = dark ? _primarySurfaceDark : _primarySurfaceLight;

    statusPendingBg = dark ? _pendingBgDark : _pendingBgLight;
    statusPendingBorder = dark ? _pendingBorderDark : _pendingBorderLight;
    statusApprovedBg = dark ? _approvedBgDark : _approvedBgLight;
    statusApprovedBorder = dark ? _approvedBorderDark : _approvedBorderLight;
    statusInProgressBg = dark ? _inProgressBgDark : _inProgressBgLight;
    statusInProgressBorder =
        dark ? _inProgressBorderDark : _inProgressBorderLight;
    statusResolvedBg = dark ? _resolvedBgDark : _resolvedBgLight;
    statusResolvedBorder = dark ? _resolvedBorderDark : _resolvedBorderLight;
    statusAssignedBg = dark ? _assignedBgDark : _assignedBgLight;
    statusAssignedBorder = dark ? _assignedBorderDark : _assignedBorderLight;
    statusSubmittedBg = dark ? _submittedBgDark : _submittedBgLight;
    statusRejectedBg = dark ? _rejectedBgDark : _rejectedBgLight;
    statusRejectedBorder = dark ? _rejectedBorderDark : _rejectedBorderLight;

    infrastructureBg = dark ? _pendingBgDark : _pendingBgLight;
    environmentBg = dark ? _resolvedBgDark : _resolvedBgLight;

    loginBg = dark ? _backgroundDark : _loginBgLight;
  }

  // ── Primary Palette (accent — same in both themes) ────────────────────────
  static const Color primary = Color(0xFF1B5E20);        // deep green
  static const Color primaryLight = Color(0xFF2E7D32);   // green
  static const Color primaryMid = Color(0xFF388E3C);

  // ── Navy / Dark (accent) ──────────────────────────────────────────────────
  static const Color navy = Color(0xFF0D2137);            // dark navy (CTAs)
  static const Color navyLight = Color(0xFF1A3A5C);

  // ── Status hues (accent) ──────────────────────────────────────────────────
  static const Color statusPending = Color(0xFFF59E0B);   // amber/yellow
  static const Color statusApproved = Color(0xFF0E7C7B);  // teal
  static const Color statusInProgress = Color(0xFFEA580C); // orange
  static const Color statusResolved = Color(0xFF16A34A);  // green
  static const Color statusAssigned = Color(0xFF2563EB);  // blue
  static const Color statusSubmitted = Color(0xFF7C3AED); // purple
  static const Color statusRejected = Color(0xFFDC2626);  // red

  // ── Category Colors (accent) ──────────────────────────────────────────────
  static const Color infrastructure = Color(0xFFF59E0B);
  static const Color environment = Color(0xFF16A34A);

  /// Always-white foreground for icons/text/rings on accent surfaces
  /// (green buttons, navy avatars, colored map pins). Never flips in dark
  /// mode — accent surfaces keep their color in both themes.
  static const Color onAccent = Color(0xFFFFFFFF);

  // ── Surface tokens (swapped by setDark) ───────────────────────────────────
  // Light palettes (also the app's original visual identity).
  static const Color _backgroundLight = Color(0xFFF8FAFC);
  static const Color _surfaceLight = Color(0xFFFFFFFF);
  static const Color _cardLight = Color(0xFFFFFFFF);
  static const Color _dividerLight = Color(0xFFE2E8F0);
  static const Color _textPrimaryLight = Color(0xFF0F172A);
  static const Color _textSecondaryLight = Color(0xFF64748B);
  static const Color _textHintLight = Color(0xFF94A3B8);
  static const Color _textDisabledLight = Color(0xFFCBD5E1);
  static const Color _inputBorderLight = Color(0xFFE2E8F0);
  static const Color _inputFillLight = Color(0xFFF8FAFC);
  static const Color _primarySurfaceLight = Color(0xFFE8F5E9);
  static const Color _pendingBgLight = Color(0xFFFFFBEB);
  static const Color _pendingBorderLight = Color(0xFFFDE68A);
  static const Color _approvedBgLight = Color(0xFFE0F7F6);
  static const Color _approvedBorderLight = Color(0xFF99F6E4);
  static const Color _inProgressBgLight = Color(0xFFFFF7ED);
  static const Color _inProgressBorderLight = Color(0xFFFED7AA);
  static const Color _resolvedBgLight = Color(0xFFF0FDF4);
  static const Color _resolvedBorderLight = Color(0xFFBBF7D0);
  static const Color _assignedBgLight = Color(0xFFEFF6FF);
  static const Color _assignedBorderLight = Color(0xFFBFDBFE);
  static const Color _submittedBgLight = Color(0xFFF5F3FF);
  static const Color _rejectedBgLight = Color(0xFFFEF2F2);
  static const Color _rejectedBorderLight = Color(0xFFFECACA);
  static const Color _loginBgLight = Color(0xFFE8F4FD);

  // Dark palettes — deep navy backgrounds, slightly lighter navy cards.
  static const Color _backgroundDark = Color(0xFF0F172A); // slate-900
  static const Color _surfaceDark = Color(0xFF1E293B);    // slate-800
  static const Color _cardDark = Color(0xFF1E293B);
  static const Color _dividerDark = Color(0xFF334155);    // slate-700
  static const Color _textPrimaryDark = Color(0xFFF1F5F9); // slate-100
  static const Color _textSecondaryDark = Color(0xFF94A3B8); // slate-400
  static const Color _textHintDark = Color(0xFF64748B);   // slate-500
  static const Color _textDisabledDark = Color(0xFF475569); // slate-600
  static const Color _inputBorderDark = Color(0xFF475569);
  static const Color _inputFillDark = Color(0xFF1E293B);
  static const Color _primarySurfaceDark = Color(0xFF14532D); // green-900
  static const Color _pendingBgDark = Color(0xFF451A04);
  static const Color _pendingBorderDark = Color(0xFF92400E);
  static const Color _approvedBgDark = Color(0xFF042F2E);
  static const Color _approvedBorderDark = Color(0xFF115E59);
  static const Color _inProgressBgDark = Color(0xFF431407);
  static const Color _inProgressBorderDark = Color(0xFF9A3412);
  static const Color _resolvedBgDark = Color(0xFF052E16);
  static const Color _resolvedBorderDark = Color(0xFF166534);
  static const Color _assignedBgDark = Color(0xFF172554);
  static const Color _assignedBorderDark = Color(0xFF1D4ED8);
  static const Color _submittedBgDark = Color(0xFF2E1065);
  static const Color _rejectedBgDark = Color(0xFF450A0A);
  static const Color _rejectedBorderDark = Color(0xFF991B1B);

  // Current values — start light; AppColors.setDark swaps them before runApp.
  static Color background = _backgroundLight;
  static Color surface = _surfaceLight;
  static Color card = _cardLight;
  static Color divider = _dividerLight;
  static Color textPrimary = _textPrimaryLight;
  static Color textSecondary = _textSecondaryLight;
  static Color textHint = _textHintLight;
  static Color textDisabled = _textDisabledLight;
  static Color inputBorder = _inputBorderLight;
  static Color inputFill = _inputFillLight;
  static Color primarySurface = _primarySurfaceLight;
  static Color statusPendingBg = _pendingBgLight;
  static Color statusPendingBorder = _pendingBorderLight;
  static Color statusApprovedBg = _approvedBgLight;
  static Color statusApprovedBorder = _approvedBorderLight;
  static Color statusInProgressBg = _inProgressBgLight;
  static Color statusInProgressBorder = _inProgressBorderLight;
  static Color statusResolvedBg = _resolvedBgLight;
  static Color statusResolvedBorder = _resolvedBorderLight;
  static Color statusAssignedBg = _assignedBgLight;
  static Color statusAssignedBorder = _assignedBorderLight;
  static Color statusSubmittedBg = _submittedBgLight;
  static Color statusRejectedBg = _rejectedBgLight;
  static Color statusRejectedBorder = _rejectedBorderLight;
  static Color infrastructureBg = _pendingBgLight;
  static Color environmentBg = _resolvedBgLight;
  static Color loginBg = _loginBgLight;

  // ── Kept for compatibility ────────────────────────────────────────────────
  /// Legacy alias — surfaces should prefer [card]; prefer [onAccent] for
  /// white-on-colored-circle icons. Dynamic so old surface reads still theme.
  static Color get white => card;

  static const Color loginBgDark = Color(0xFFBFDBFE);
  static const Color cardShadow = Color(0x0F000000);
}

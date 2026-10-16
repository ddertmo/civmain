import 'package:flutter/material.dart';

/// CivilWatch color tokens.
///
/// Two token groups:
///  - **Accent tokens** (primary green, navy, status hues, category hues):
///    `const`, identical in both themes — the brand never changes, so these
///    stay on [AppColors] and need no `BuildContext`.
///  - **Surface tokens** (backgrounds, cards, text, borders, status tints):
///    exposed through [AppPalette], a [ThemeExtension] registered on the light
///    and dark [ThemeData]. Screens read them with `context.c.<token>`, which
///    creates a dependency on the active theme, so every widget repaints when
///    the theme changes — including `const` widgets, because the dependency is
///    resolved through the widget tree rather than a mutable global.
///
/// There is exactly one theme state: `AppState.isDarkMode` (persisted in
/// `shared_preferences`). Never keep a second copy of the palette anywhere.
class AppColors {
  AppColors._();

  // ── Primary Palette (accent — same in both themes) ────────────────────────
  static const Color primary = Color(0xFF1B5E20); // deep green
  static const Color primaryLight = Color(0xFF2E7D32); // green
  static const Color primaryMid = Color(0xFF388E3C);

  // ── Navy / Dark (accent) ──────────────────────────────────────────────────
  static const Color navy = Color(0xFF0D2137); // dark navy (CTAs)
  static const Color navyLight = Color(0xFF1A3A5C);

  // ── Status hues (accent) ──────────────────────────────────────────────────
  static const Color statusPending = Color(0xFFF59E0B); // amber/yellow
  static const Color statusApproved = Color(0xFF0E7C7B); // teal
  static const Color statusInProgress = Color(0xFFEA580C); // orange
  static const Color statusResolved = Color(0xFF16A34A); // green
  static const Color statusAssigned = Color(0xFF2563EB); // blue
  static const Color statusSubmitted = Color(0xFF7C3AED); // purple
  static const Color statusRejected = Color(0xFFDC2626); // red

  // ── Category Colors (accent) ──────────────────────────────────────────────
  static const Color infrastructure = Color(0xFFF59E0B);
  static const Color environment = Color(0xFF16A34A);

  /// Always-white foreground for icons/text/rings on accent surfaces
  /// (green buttons, navy avatars, colored map pins). Never flips in dark
  /// mode — accent surfaces keep their color in both themes.
  static const Color onAccent = Color(0xFFFFFFFF);

  static const Color loginBgDark = Color(0xFFBFDBFE);
  static const Color cardShadow = Color(0x0F000000);
}

/// Theme-dependent surface palette — a [ThemeExtension] so values are read
/// from the widget tree (`context.c.background`) and update automatically.
///
/// Register the light variant on the light theme and the dark variant on the
/// dark theme (see `app_theme.dart`). Do **not** mutate these at runtime.
class AppPalette extends ThemeExtension<AppPalette> {
  const AppPalette({
    required this.background,
    required this.surface,
    required this.card,
    required this.divider,
    required this.textPrimary,
    required this.textSecondary,
    required this.textHint,
    required this.textDisabled,
    required this.inputBorder,
    required this.inputFill,
    required this.primaryOnSurface,
    required this.primarySurface,
    required this.statusPendingBg,
    required this.statusPendingBorder,
    required this.statusApprovedBg,
    required this.statusApprovedBorder,
    required this.statusInProgressBg,
    required this.statusInProgressBorder,
    required this.statusResolvedBg,
    required this.statusResolvedBorder,
    required this.statusAssignedBg,
    required this.statusAssignedBorder,
    required this.statusSubmittedBg,
    required this.statusRejectedBg,
    required this.statusRejectedBorder,
    required this.infrastructureBg,
    required this.environmentBg,
    required this.loginBg,
  });

  final Color background;
  final Color surface;
  final Color card;
  final Color divider;
  final Color textPrimary;
  final Color textSecondary;
  final Color textHint;
  final Color textDisabled;
  final Color inputBorder;
  final Color inputFill;

  /// Brand green for text/icons drawn **on themed surfaces**.
  ///
  /// The deep brand green ([AppColors.primary]) reads well on light surfaces
  /// but measures ~1.2:1 on the dark palette (#0F172A / #1E293B / #14532D),
  /// so Dark Mode substitutes a lighter green (7.8:1 on `background`,
  /// 6.4:1 on `card`, 4.0:1 on `primarySurface`). The Light value is
  /// identical to [AppColors.primary], so Light Mode is unchanged.
  final Color primaryOnSurface;
  final Color primarySurface;
  final Color statusPendingBg;
  final Color statusPendingBorder;
  final Color statusApprovedBg;
  final Color statusApprovedBorder;
  final Color statusInProgressBg;
  final Color statusInProgressBorder;
  final Color statusResolvedBg;
  final Color statusResolvedBorder;
  final Color statusAssignedBg;
  final Color statusAssignedBorder;
  final Color statusSubmittedBg;
  final Color statusRejectedBg;
  final Color statusRejectedBorder;
  final Color infrastructureBg;
  final Color environmentBg;
  final Color loginBg;

  /// Light palette — the app's original visual identity (off-white / cool-gray
  /// background, white cards, dark slate text).
  static const AppPalette light = AppPalette(
    background: Color(0xFFF8FAFC),
    surface: Color(0xFFFFFFFF),
    card: Color(0xFFFFFFFF),
    divider: Color(0xFFE2E8F0),
    textPrimary: Color(0xFF0F172A),
    textSecondary: Color(0xFF64748B),
    textHint: Color(0xFF94A3B8),
    textDisabled: Color(0xFFCBD5E1),
    inputBorder: Color(0xFFE2E8F0),
    inputFill: Color(0xFFF8FAFC),
    primaryOnSurface: AppColors.primary,
    primarySurface: Color(0xFFE8F5E9),
    statusPendingBg: Color(0xFFFFFBEB),
    statusPendingBorder: Color(0xFFFDE68A),
    statusApprovedBg: Color(0xFFE0F7F6),
    statusApprovedBorder: Color(0xFF99F6E4),
    statusInProgressBg: Color(0xFFFFF7ED),
    statusInProgressBorder: Color(0xFFFED7AA),
    statusResolvedBg: Color(0xFFF0FDF4),
    statusResolvedBorder: Color(0xFFBBF7D0),
    statusAssignedBg: Color(0xFFEFF6FF),
    statusAssignedBorder: Color(0xFFBFDBFE),
    statusSubmittedBg: Color(0xFFF5F3FF),
    statusRejectedBg: Color(0xFFFEF2F2),
    statusRejectedBorder: Color(0xFFFECACA),
    infrastructureBg: Color(0xFFFFFBEB),
    environmentBg: Color(0xFFF0FDF4),
    loginBg: Color(0xFFE8F4FD),
  );

  /// Dark palette — deep navy background, slightly lighter navy cards,
  /// near-white text.
  static const AppPalette dark = AppPalette(
    background: Color(0xFF0F172A), // slate-900
    surface: Color(0xFF1E293B), // slate-800
    card: Color(0xFF1E293B),
    divider: Color(0xFF334155), // slate-700
    textPrimary: Color(0xFFF1F5F9), // slate-100
    textSecondary: Color(0xFF94A3B8), // slate-400
    textHint: Color(0xFF64748B), // slate-500
    textDisabled: Color(0xFF475569), // slate-600
    inputBorder: Color(0xFF475569),
    inputFill: Color(0xFF1E293B),
    primaryOnSurface: Color(0xFF22C55E), // green-500, readable on dark surfaces
    primarySurface: Color(0xFF14532D), // green-900
    statusPendingBg: Color(0xFF451A04),
    statusPendingBorder: Color(0xFF92400E),
    statusApprovedBg: Color(0xFF042F2E),
    statusApprovedBorder: Color(0xFF115E59),
    statusInProgressBg: Color(0xFF431407),
    statusInProgressBorder: Color(0xFF9A3412),
    statusResolvedBg: Color(0xFF052E16),
    statusResolvedBorder: Color(0xFF166534),
    statusAssignedBg: Color(0xFF172554),
    statusAssignedBorder: Color(0xFF1D4ED8),
    statusSubmittedBg: Color(0xFF2E1065),
    statusRejectedBg: Color(0xFF450A0A),
    statusRejectedBorder: Color(0xFF991B1B),
    infrastructureBg: Color(0xFF451A04),
    environmentBg: Color(0xFF052E16),
    loginBg: Color(0xFF0F172A),
  );

  // ── Status / category lookups (theme-dependent tints) ─────────────────────
  // Accent hues live in [AppColors]; only the tinted backgrounds depend on
  // the theme, so these lookups hang off the palette.
  Color statusBg(String status) {
    switch (status.toLowerCase()) {
      case 'submitted':
        return statusSubmittedBg;
      case 'pending validation':
      case 'pending':
        return statusPendingBg;
      case 'approved':
        return statusApprovedBg;
      case 'assigned to office':
      case 'assigned':
        return statusAssignedBg;
      case 'in progress':
        return statusInProgressBg;
      case 'resolved':
        return statusResolvedBg;
      case 'rejected':
        return statusRejectedBg;
      default:
        return background;
    }
  }

  Color categoryBg(String category) {
    switch (category.toLowerCase()) {
      case 'infrastructure':
        return infrastructureBg;
      case 'environment':
        return environmentBg;
      case 'others':
        return statusSubmittedBg;
      default:
        return background;
    }
  }

  Color statusColor(String status) {
    switch (status.toLowerCase()) {
      case 'submitted':
        return AppColors.statusSubmitted;
      case 'pending validation':
      case 'pending':
        return AppColors.statusPending;
      case 'approved':
        return AppColors.statusApproved;
      case 'assigned to office':
      case 'assigned':
        return AppColors.statusAssigned;
      case 'in progress':
        return AppColors.statusInProgress;
      case 'resolved':
        return AppColors.statusResolved;
      case 'rejected':
        return AppColors.statusRejected;
      default:
        return textSecondary;
    }
  }

  Color categoryColor(String category) {
    switch (category.toLowerCase()) {
      case 'infrastructure':
        return AppColors.infrastructure;
      case 'environment':
        return AppColors.environment;
      case 'others':
        return AppColors.statusSubmitted;
      default:
        return textSecondary;
    }
  }

  @override
  AppPalette copyWith({
    Color? background,
    Color? surface,
    Color? card,
    Color? divider,
    Color? textPrimary,
    Color? textSecondary,
    Color? textHint,
    Color? textDisabled,
    Color? inputBorder,
    Color? inputFill,
    Color? primaryOnSurface,
    Color? primarySurface,
    Color? statusPendingBg,
    Color? statusPendingBorder,
    Color? statusApprovedBg,
    Color? statusApprovedBorder,
    Color? statusInProgressBg,
    Color? statusInProgressBorder,
    Color? statusResolvedBg,
    Color? statusResolvedBorder,
    Color? statusAssignedBg,
    Color? statusAssignedBorder,
    Color? statusSubmittedBg,
    Color? statusRejectedBg,
    Color? statusRejectedBorder,
    Color? infrastructureBg,
    Color? environmentBg,
    Color? loginBg,
  }) {
    return AppPalette(
      background: background ?? this.background,
      surface: surface ?? this.surface,
      card: card ?? this.card,
      divider: divider ?? this.divider,
      textPrimary: textPrimary ?? this.textPrimary,
      textSecondary: textSecondary ?? this.textSecondary,
      textHint: textHint ?? this.textHint,
      textDisabled: textDisabled ?? this.textDisabled,
      inputBorder: inputBorder ?? this.inputBorder,
      inputFill: inputFill ?? this.inputFill,
      primaryOnSurface: primaryOnSurface ?? this.primaryOnSurface,
      primarySurface: primarySurface ?? this.primarySurface,
      statusPendingBg: statusPendingBg ?? this.statusPendingBg,
      statusPendingBorder: statusPendingBorder ?? this.statusPendingBorder,
      statusApprovedBg: statusApprovedBg ?? this.statusApprovedBg,
      statusApprovedBorder: statusApprovedBorder ?? this.statusApprovedBorder,
      statusInProgressBg: statusInProgressBg ?? this.statusInProgressBg,
      statusInProgressBorder:
          statusInProgressBorder ?? this.statusInProgressBorder,
      statusResolvedBg: statusResolvedBg ?? this.statusResolvedBg,
      statusResolvedBorder: statusResolvedBorder ?? this.statusResolvedBorder,
      statusAssignedBg: statusAssignedBg ?? this.statusAssignedBg,
      statusAssignedBorder: statusAssignedBorder ?? this.statusAssignedBorder,
      statusSubmittedBg: statusSubmittedBg ?? this.statusSubmittedBg,
      statusRejectedBg: statusRejectedBg ?? this.statusRejectedBg,
      statusRejectedBorder: statusRejectedBorder ?? this.statusRejectedBorder,
      infrastructureBg: infrastructureBg ?? this.infrastructureBg,
      environmentBg: environmentBg ?? this.environmentBg,
      loginBg: loginBg ?? this.loginBg,
    );
  }

  @override
  AppPalette lerp(ThemeExtension<AppPalette>? other, double t) {
    // The app switches themes instantly (no cross-fade), so an instant swap is
    // all that is needed here.
    if (other is! AppPalette) return this;
    return t < 0.5 ? this : other;
  }
}

/// `context.c` — the active [AppPalette]. Creates a dependency on the theme, so
/// any widget that reads it rebuilds when the theme changes.
extension AppPaletteContext on BuildContext {
  AppPalette get c =>
      Theme.of(this).extension<AppPalette>() ?? AppPalette.light;
}

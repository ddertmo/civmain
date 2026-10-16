import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';

/// Theme definitions for CivilWatch.
///
/// These themes intentionally use **fixed** color constants (not the mutable
/// [AppColors] surface tokens) so that `theme` and `darkTheme` never capture
/// the wrong palette depending on toggle order. Screens read `AppColors.*`
/// directly for surfaces; the `ThemeData` here drives Material defaults
/// (dialogs, text selection, forms, etc.).
class AppTheme {
  AppTheme._();

  static ThemeData get light => _lightTheme;
  static ThemeData get dark => _darkTheme;

  // Light surface palette (fixed).
  static const _lBackground = Color(0xFFF8FAFC);
  static const _lSurface = Color(0xFFFFFFFF);
  static const _lDivider = Color(0xFFE2E8F0);
  static const _lTextPrimary = Color(0xFF0F172A);
  static const _lTextSecondary = Color(0xFF64748B);
  static const _lTextHint = Color(0xFF94A3B8);
  static const _lInputBorder = Color(0xFFE2E8F0);
  static const _lInputFill = Color(0xFFF8FAFC);

  // Dark surface palette (fixed).
  static const _dBackground = Color(0xFF0F172A);
  static const _dSurface = Color(0xFF1E293B);
  static const _dDivider = Color(0xFF334155);
  static const _dTextPrimary = Color(0xFFF1F5F9);
  static const _dTextSecondary = Color(0xFF94A3B8);

  static final ThemeData _lightTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.light,
    scaffoldBackgroundColor: _lBackground,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      primary: AppColors.primary,
      secondary: AppColors.navy,
      surface: _lSurface,
      error: const Color(0xFFDC2626),
      brightness: Brightness.light,
    ),

    // ── Typography ────────────────────────────────────────────────────────────
    textTheme: GoogleFonts.interTextTheme().copyWith(
      displayLarge: GoogleFonts.inter(
        fontSize: 32,
        fontWeight: FontWeight.w800,
        color: _lTextPrimary,
      ),
      headlineLarge: GoogleFonts.inter(
        fontSize: 24,
        fontWeight: FontWeight.w700,
        color: _lTextPrimary,
      ),
      headlineMedium: GoogleFonts.inter(
        fontSize: 20,
        fontWeight: FontWeight.w700,
        color: _lTextPrimary,
      ),
      titleLarge: GoogleFonts.inter(
        fontSize: 18,
        fontWeight: FontWeight.w600,
        color: _lTextPrimary,
      ),
      titleMedium: GoogleFonts.inter(
        fontSize: 16,
        fontWeight: FontWeight.w600,
        color: _lTextPrimary,
      ),
      bodyLarge: GoogleFonts.inter(
        fontSize: 16,
        fontWeight: FontWeight.w400,
        color: _lTextPrimary,
      ),
      bodyMedium: GoogleFonts.inter(
        fontSize: 14,
        fontWeight: FontWeight.w400,
        color: _lTextSecondary,
      ),
      labelLarge: GoogleFonts.inter(
        fontSize: 14,
        fontWeight: FontWeight.w600,
        color: _lTextPrimary,
      ),
    ),

    // ── AppBar ────────────────────────────────────────────────────────────────
    appBarTheme: AppBarTheme(
      backgroundColor: _lSurface,
      foregroundColor: _lTextPrimary,
      elevation: 0,
      centerTitle: false,
      systemOverlayStyle: const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.dark,
      ),
      titleTextStyle: GoogleFonts.inter(
        fontSize: 18,
        fontWeight: FontWeight.w700,
        color: _lTextPrimary,
      ),
      iconTheme: const IconThemeData(color: _lTextPrimary),
    ),

    // ── Elevated Button ───────────────────────────────────────────────────────
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.navy,
        foregroundColor: AppColors.onAccent,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        elevation: 0,
        textStyle: GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w700,
        ),
      ),
    ),

    // ── Outlined Button ───────────────────────────────────────────────────────
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.navy,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        side: const BorderSide(color: AppColors.navy, width: 1.5),
        textStyle: GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),

    // ── Text Button ───────────────────────────────────────────────────────────
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.primary,
        textStyle: GoogleFonts.inter(
          fontSize: 14,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),

    // ── Input Decoration ──────────────────────────────────────────────────────
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: _lInputFill,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: _lInputBorder),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: _lInputBorder),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFDC2626)),
      ),
      hintStyle: GoogleFonts.inter(
        fontSize: 14,
        color: _lTextHint,
      ),
    ),

    // ── Card ──────────────────────────────────────────────────────────────────
    cardTheme: CardThemeData(
      color: _lSurface,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: _lDivider),
      ),
      margin: EdgeInsets.zero,
    ),

    // ── Chip ──────────────────────────────────────────────────────────────────
    chipTheme: ChipThemeData(
      backgroundColor: _lBackground,
      selectedColor: AppColors.navy,
      labelStyle: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w500),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: const BorderSide(color: _lDivider),
      ),
    ),

    // ── Divider ───────────────────────────────────────────────────────────────
    dividerTheme: const DividerThemeData(
      color: _lDivider,
      thickness: 1,
      space: 0,
    ),

    // ── Bottom Navigation ─────────────────────────────────────────────────────
    bottomNavigationBarTheme: BottomNavigationBarThemeData(
      backgroundColor: _lSurface,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: _lTextHint,
      selectedLabelStyle: GoogleFonts.inter(
        fontSize: 11,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: GoogleFonts.inter(
        fontSize: 11,
        fontWeight: FontWeight.w400,
      ),
      type: BottomNavigationBarType.fixed,
      elevation: 0,
    ),
  );

  // ── Dark theme ────────────────────────────────────────────────────────────
  static final ThemeData _darkTheme = ThemeData(
    useMaterial3: true,
    brightness: Brightness.dark,
    scaffoldBackgroundColor: _dBackground,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      primary: AppColors.primaryLight,
      secondary: AppColors.primaryLight,
      surface: _dSurface,
      error: const Color(0xFFDC2626),
      brightness: Brightness.dark,
    ),

    textTheme: GoogleFonts.interTextTheme()
        .apply(bodyColor: _dTextPrimary, displayColor: _dTextPrimary)
        .copyWith(
          displayLarge: GoogleFonts.inter(
              fontSize: 32, fontWeight: FontWeight.w800, color: _dTextPrimary),
          headlineLarge: GoogleFonts.inter(
              fontSize: 24, fontWeight: FontWeight.w700, color: _dTextPrimary),
          headlineMedium: GoogleFonts.inter(
              fontSize: 20, fontWeight: FontWeight.w700, color: _dTextPrimary),
          titleLarge: GoogleFonts.inter(
              fontSize: 18, fontWeight: FontWeight.w600, color: _dTextPrimary),
          titleMedium: GoogleFonts.inter(
              fontSize: 16, fontWeight: FontWeight.w600, color: _dTextPrimary),
          bodyLarge: GoogleFonts.inter(
              fontSize: 16, fontWeight: FontWeight.w400, color: _dTextPrimary),
          bodyMedium: GoogleFonts.inter(
              fontSize: 14,
              fontWeight: FontWeight.w400,
              color: _dTextSecondary),
          labelLarge: GoogleFonts.inter(
              fontSize: 14, fontWeight: FontWeight.w600, color: _dTextPrimary),
        ),

    appBarTheme: AppBarTheme(
      backgroundColor: _dSurface,
      foregroundColor: _dTextPrimary,
      elevation: 0,
      centerTitle: false,
      systemOverlayStyle: const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
      ),
      titleTextStyle: GoogleFonts.inter(
        fontSize: 18,
        fontWeight: FontWeight.w700,
        color: _dTextPrimary,
      ),
      iconTheme: const IconThemeData(color: _dTextPrimary),
    ),

    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.primary,
        foregroundColor: AppColors.onAccent,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        elevation: 0,
        textStyle: GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w700,
        ),
      ),
    ),

    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: AppColors.primaryLight,
        minimumSize: const Size(double.infinity, 54),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        side: const BorderSide(color: AppColors.primaryLight, width: 1.5),
        textStyle: GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),

    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: AppColors.primaryLight,
        textStyle: GoogleFonts.inter(
          fontSize: 14,
          fontWeight: FontWeight.w600,
        ),
      ),
    ),

    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: _dSurface,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: _dDivider),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: _dDivider),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.primaryLight, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFDC2626)),
      ),
      hintStyle: GoogleFonts.inter(
        fontSize: 14,
        color: _dTextSecondary,
      ),
    ),

    cardTheme: CardThemeData(
      color: _dSurface,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: const BorderSide(color: _dDivider),
      ),
      margin: EdgeInsets.zero,
    ),

    chipTheme: ChipThemeData(
      backgroundColor: _dDivider,
      selectedColor: AppColors.primary,
      labelStyle: GoogleFonts.inter(
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: _dTextPrimary,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20),
        side: const BorderSide(color: Color(0xFF475569)),
      ),
    ),

    dividerTheme: const DividerThemeData(
      color: _dDivider,
      thickness: 1,
      space: 0,
    ),

    bottomNavigationBarTheme: BottomNavigationBarThemeData(
      backgroundColor: _dSurface,
      selectedItemColor: AppColors.primaryLight,
      unselectedItemColor: _dTextSecondary,
      selectedLabelStyle: GoogleFonts.inter(
        fontSize: 11,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: GoogleFonts.inter(
        fontSize: 11,
        fontWeight: FontWeight.w400,
      ),
      type: BottomNavigationBarType.fixed,
      elevation: 0,
    ),
  );
}

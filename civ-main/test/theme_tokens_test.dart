import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:civilwatch/core/constants/app_colors.dart';
import 'package:civilwatch/core/state/app_state.dart';

/// Verifies the root fix: the runtime-swappable surface palette that makes
/// dark mode actually repaint the app (previously every screen read `const`
/// colors, so switching `themeMode` had no visible effect).
void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('AppColors.setDark swaps surface tokens, keeps accents', () {
    AppColors.setDark(false);
    expect(AppColors.isDark, isFalse);
    expect(AppColors.background, const Color(0xFFF8FAFC));
    expect(AppColors.card, const Color(0xFFFFFFFF));
    expect(AppColors.textPrimary, const Color(0xFF0F172A));

    AppColors.setDark(true);
    expect(AppColors.isDark, isTrue);
    expect(AppColors.background, const Color(0xFF0F172A));
    expect(AppColors.card, const Color(0xFF1E293B));
    expect(AppColors.textPrimary, const Color(0xFFF1F5F9));

    // Accent tokens must be identical in both themes.
    expect(AppColors.primary, const Color(0xFF1B5E20));
    expect(AppColors.onAccent, const Color(0xFFFFFFFF));

    AppColors.setDark(false); // reset
  });

  test('AppState.setDarkMode flips the palette and persists', () async {
    SharedPreferences.setMockInitialValues({});
    final state = AppState();

    // Start known-dark: default _isDarkMode is already true, so flip to false.
    await state.setDarkMode(false);
    expect(state.isDarkMode, isFalse);
    expect(AppColors.isDark, isFalse);

    await state.setDarkMode(true);
    expect(state.isDarkMode, isTrue);
    expect(AppColors.isDark, isTrue); // palette actually swapped

    final prefs = await SharedPreferences.getInstance();
    expect(prefs.getBool('dark_mode'), isTrue);
  });
}

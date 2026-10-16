import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:civilwatch/core/constants/app_colors.dart';
import 'package:civilwatch/core/state/app_state.dart';

/// Verifies the theme architecture: surface colors live on [AppPalette], a
/// [ThemeExtension] registered on both themes. Widgets read it via
/// `context.c`, so switching `themeMode` repaints the whole app — there is a
/// single theme state (`AppState.isDarkMode`), persisted in `shared_preferences`.
void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('first-time default is Light Mode (no saved preference)', () {
    // A fresh session with no persisted choice must start light.
    expect(AppState().isDarkMode, isFalse);
  });

  test('AppPalette light/dark tokens differ; accent tokens never change', () {
    expect(AppPalette.light.background, const Color(0xFFF8FAFC));
    expect(AppPalette.light.card, const Color(0xFFFFFFFF));
    expect(AppPalette.light.textPrimary, const Color(0xFF0F172A));

    expect(AppPalette.dark.background, const Color(0xFF0F172A));
    expect(AppPalette.dark.card, const Color(0xFF1E293B));
    expect(AppPalette.dark.textPrimary, const Color(0xFFF1F5F9));

    // Accent tokens are identical in both themes.
    expect(AppColors.primary, const Color(0xFF1B5E20));
    expect(AppColors.onAccent, const Color(0xFFFFFFFF));

    // Brand green drawn ON themed surfaces: Light stays byte-identical to the
    // brand primary; Dark is lighter so links/icons remain readable (~6.4:1 on
    // card, ~7.8:1 on background) instead of the ~1.2:1 of AppColors.primary.
    expect(AppPalette.light.primaryOnSurface, AppColors.primary);
    expect(AppPalette.dark.primaryOnSurface, const Color(0xFF22C55E));
    expect(
      AppPalette.light.primaryOnSurface,
      isNot(AppPalette.dark.primaryOnSurface),
    );
  });

  test('status/category tint lookups resolve against the palette', () {
    expect(
      AppPalette.light.statusBg('pending'),
      AppPalette.light.statusPendingBg,
    );
    expect(
      AppPalette.dark.statusBg('pending'),
      AppPalette.dark.statusPendingBg,
    );
    expect(
      AppPalette.light.categoryBg('environment'),
      AppPalette.light.environmentBg,
    );
    // Accent hues come from AppColors in both themes.
    expect(AppPalette.light.statusColor('resolved'), AppColors.statusResolved);
    expect(AppPalette.dark.categoryColor('others'), AppColors.statusSubmitted);
  });

  test(
    'AppState.setDarkMode flips the single theme state and persists',
    () async {
      SharedPreferences.setMockInitialValues({});
      final state = AppState();

      await state.setDarkMode(false);
      expect(state.isDarkMode, isFalse);

      await state.setDarkMode(true);
      expect(state.isDarkMode, isTrue);

      final prefs = await SharedPreferences.getInstance();
      expect(prefs.getBool('dark_mode'), isTrue);
    },
  );
}

import 'package:flutter/material.dart';
import '../../core/state/app_state.dart';

/// Compact animated Light/Dark switch — a sun/moon pill.
///
/// Sits in the Home header where the notification bell used to be. It reads
/// and writes the single source of truth ([AppState.isDarkMode]), so it always
/// agrees with the Profile → "Dark Mode" switch. Respects reduced-motion.
class ThemeToggle extends StatelessWidget {
  const ThemeToggle({super.key});

  // Warm-yellow track for light mode, indigo track for dark mode.
  static const _lightTrack = Color(0xFFFDE68A);
  static const _lightTrackBorder = Color(0xFFFCD34D);
  static const _darkTrack = Color(0xFF4338CA);
  static const _darkTrackBorder = Color(0xFF6366F1);
  static const _sunColor = Color(0xFFF59E0B);
  static const _moonColor = Color(0xFF4F46E5);

  @override
  Widget build(BuildContext context) {
    final reduceMotion = MediaQuery.disableAnimationsOf(context);
    final duration = reduceMotion
        ? Duration.zero
        : const Duration(milliseconds: 320);

    return ListenableBuilder(
      listenable: AppState(),
      builder: (context, _) {
        final state = AppState();
        final isDark = state.isDarkMode;

        return Tooltip(
          message: isDark ? 'Switch to light mode' : 'Switch to dark mode',
          child: Semantics(
            button: true,
            toggled: isDark,
            label: 'Dark mode',
            child: GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: () => state.setDarkMode(!isDark),
              child: AnimatedContainer(
                duration: duration,
                curve: Curves.easeInOut,
                width: 64,
                height: 32,
                padding: const EdgeInsets.all(3),
                decoration: BoxDecoration(
                  color: isDark ? _darkTrack : _lightTrack,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isDark ? _darkTrackBorder : _lightTrackBorder,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(
                        alpha: isDark ? 0.35 : 0.08,
                      ),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: AnimatedAlign(
                  duration: duration,
                  curve: Curves.easeInOut,
                  alignment: isDark
                      ? Alignment.centerRight
                      : Alignment.centerLeft,
                  child: Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.18),
                          blurRadius: 4,
                          offset: const Offset(0, 1),
                        ),
                      ],
                    ),
                    child: AnimatedSwitcher(
                      duration: duration,
                      transitionBuilder: (child, animation) =>
                          RotationTransition(
                            turns: Tween<double>(
                              begin: 0.6,
                              end: 1.0,
                            ).animate(animation),
                            child: FadeTransition(
                              opacity: animation,
                              child: child,
                            ),
                          ),
                      child: Icon(
                        isDark
                            ? Icons.nightlight_round
                            : Icons.wb_sunny_rounded,
                        key: ValueKey<bool>(isDark),
                        size: 18,
                        color: isDark ? _moonColor : _sunColor,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

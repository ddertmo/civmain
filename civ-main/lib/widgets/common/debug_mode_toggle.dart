import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';

/// Debug-only Mock / Live selector.
///
/// Only ever rendered when `kDebugMode` is true — see the call sites. In
/// release builds the app has no way to select Mock, so no SMS step can be
/// bypassed.
class DebugModeToggle extends StatelessWidget {
  final bool mock;
  final ValueChanged<bool>? onChanged;
  final String mockLabel;
  final String liveLabel;

  const DebugModeToggle({
    super.key,
    required this.mock,
    this.onChanged,
    this.mockLabel = 'Mock',
    this.liveLabel = 'Live API',
  });

  @override
  Widget build(BuildContext context) {
    Widget item(String label, bool value, IconData icon, Color active) {
      final selected = mock == value;
      return Expanded(
        child: GestureDetector(
          onTap: onChanged == null ? null : () => onChanged!(value),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 150),
            padding: const EdgeInsets.symmetric(vertical: 8),
            decoration: BoxDecoration(
              color: selected ? active : Colors.transparent,
              borderRadius: BorderRadius.circular(9),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon,
                    size: 14,
                    color: selected ? Colors.white : context.c.textSecondary),
                const SizedBox(width: 5),
                Text(
                  label,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: selected ? Colors.white : context.c.textSecondary,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(
        color: context.c.inputFill,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: context.c.inputBorder),
      ),
      child: Row(
        children: [
          item(mockLabel, true, Icons.science_outlined, AppColors.primary),
          item(liveLabel, false, Icons.cloud_outlined,
              const Color(0xFFB45309)),
        ],
      ),
    );
  }
}

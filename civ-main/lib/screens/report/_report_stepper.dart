import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';

class ReportStepper extends StatelessWidget {
  final int currentStep; // 0=Category, 1=Concern, 2=Photo, 3=Location, 4=Review

  const ReportStepper({super.key, required this.currentStep});

  static const _labels = [
    'Category',
    'Concern',
    'Photo',
    'Location',
    'Review',
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppColors.cardShadow,
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Column(
        children: [
          Row(
            children: List.generate(_labels.length, (i) {
              final isDone = i < currentStep;
              final isCurrent = i == currentStep;
              final isLast = i == _labels.length - 1;

              return Expanded(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        children: [
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 300),
                            curve: Curves.easeOutCubic,
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              color: isDone
                                  ? AppColors.primary
                                  : Colors.transparent,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: isCurrent
                                    ? AppColors.primary
                                    : isDone
                                        ? Colors.transparent
                                        : AppColors.divider,
                                width: isCurrent ? 2 : 1.5,
                              ),
                              boxShadow: isCurrent
                                  ? [
                                      BoxShadow(
                                        color: AppColors.primary.withOpacity(0.25),
                                        blurRadius: 6,
                                        offset: const Offset(0, 2),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: isDone
                                ? const Icon(Icons.check_rounded,
                                    color: AppColors.onAccent, size: 16)
                                : isCurrent
                                    ? Center(
                                        child: Container(
                                          width: 10,
                                          height: 10,
                                          decoration: const BoxDecoration(
                                            color: AppColors.primary,
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                      )
                                    : Center(
                                        child: Container(
                                          width: 8,
                                          height: 8,
                                          decoration: BoxDecoration(
                                            color: AppColors.textDisabled,
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                      ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            _labels[i],
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(
                              fontSize: 9,
                              fontWeight: isCurrent || isDone
                                  ? FontWeight.w700
                                  : FontWeight.w400,
                              color: isDone || isCurrent
                                  ? AppColors.primary
                                  : AppColors.textHint,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (!isLast)
                      Expanded(
                        child: SizedBox(height: 28),
                      ),
                  ],
                ),
              );
            }),
          ),
          const SizedBox(height: 10),
          LayoutBuilder(
            builder: (context, constraints) {
              final total = _labels.length;
              final completedWidth = (currentStep.clamp(0, total)) / total * constraints.maxWidth;
              return Container(
                height: 4,
                width: double.infinity,
                decoration: BoxDecoration(
                  color: AppColors.divider,
                  borderRadius: BorderRadius.circular(2),
                ),
                child: Align(
                  alignment: Alignment.centerLeft,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 300),
                    curve: Curves.easeOutCubic,
                    width: completedWidth,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFF1B5E20),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

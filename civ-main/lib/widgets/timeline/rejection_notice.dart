import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/helpers.dart';
import '../../models/report.dart';

/// Shown in place of a progress timeline once a report has been rejected.
///
/// Rejected is an exit from the status flow rather than a step in it, so a
/// five-step timeline would render as five uncompleted circles and read as
/// "nothing happened". This explains where it stopped instead.
///
/// The reason is pulled from the rejection activity entry, which is where the
/// backend stores the note written in the admin's reject dialog.
class RejectionNotice extends StatelessWidget {
  final IncidentReport report;

  const RejectionNotice({super.key, required this.report});

  String get reason {
    for (final entry in report.activityLog.reversed) {
      if (entry.status == 'Rejected' && entry.description.isNotEmpty) {
        return entry.description;
      }
    }
    return 'No reason was provided.';
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.statusRejectedBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.statusRejectedBorder),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            AppHelpers.getStatusIcon('Rejected'),
            size: 22,
            color: AppColors.statusRejected,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Rejected',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: AppColors.statusRejected,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  reason,
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    height: 1.4,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

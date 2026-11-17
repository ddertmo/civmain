import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';

import '../../core/constants/app_colors.dart';
import '../../core/routes/app_routes.dart';
import '../../widgets/navigation/app_bar.dart';
import '_report_stepper.dart';

class ReportPhotoScreen extends StatefulWidget {
  final Map<String, dynamic> reportData;
  const ReportPhotoScreen({super.key, required this.reportData});

  @override
  State<ReportPhotoScreen> createState() => _ReportPhotoScreenState();
}

class _ReportPhotoScreenState extends State<ReportPhotoScreen> {
  XFile? _pickedFile;
  Uint8List? _imageBytes;   // works on both Web and Android
  final _picker = ImagePicker();

  // Server limits: max 5 MB, image types jpg/png/gif/webp/bmp.
  static const int _maxPhotoBytes = 5 * 1024 * 1024;
  static const Set<String> _allowedExtensions = {
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp',
  };
  static const Set<String> _allowedMimeTypes = {
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
    'image/bmp', 'image/x-ms-bmp',
  };

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: GoogleFonts.inter(fontSize: 13),
        ),
        backgroundColor: const Color(0xFFDC2626),
        behavior: SnackBarBehavior.floating,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  /// Returns an error message if the picked file can't be uploaded, else null.
  String? _validatePhoto(XFile file, int byteLength) {
    final name = file.name.toLowerCase();
    final dot = name.lastIndexOf('.');
    final ext = dot != -1 ? name.substring(dot + 1) : '';
    final mime = file.mimeType?.toLowerCase();

    if (ext == 'heic' || ext == 'heif' ||
        mime == 'image/heic' || mime == 'image/heif') {
      return 'HEIC/HEIF photos are not supported.\n'
          'Please choose a JPG or PNG photo, or set your camera to '
          '"Most Compatible".';
    }
    // If the platform reports neither an extension nor a MIME type, let the
    // server decide rather than rejecting a possibly valid photo.
    final typeOk = _allowedExtensions.contains(ext) ||
        (mime != null && _allowedMimeTypes.contains(mime)) ||
        (ext.isEmpty && mime == null);
    if (!typeOk) {
      return 'Unsupported photo format.\n'
          'Please use a JPG, PNG, GIF, WEBP or BMP image.';
    }
    if (byteLength > _maxPhotoBytes) {
      final mb = (byteLength / (1024 * 1024)).toStringAsFixed(1);
      return 'Photo is too large ($mb MB).\nThe maximum size is 5 MB.';
    }
    return null;
  }

  // ── Pick from camera or gallery ─────────────────────────────────────────
  Future<void> _pickImage(ImageSource source) async {
    try {
      // maxWidth/maxHeight/imageQuality make image_picker re-encode the
      // image (as JPEG on most platforms), which also keeps it small.
      final file = await _picker.pickImage(
        source: source,
        imageQuality: 85,
        maxWidth: 1920,
        maxHeight: 1920,
      );
      if (file != null) {
        final bytes = await file.readAsBytes();
        if (!mounted) return;
        final error = _validatePhoto(file, bytes.lengthInBytes);
        if (error != null) {
          _showError(error);
          return;
        }
        setState(() {
          _pickedFile  = file;
          _imageBytes  = bytes;
        });
      }
    } catch (e) {
      if (mounted) {
        _showError(
            'Could not access camera or gallery.\nPlease allow permission in Settings.');
      }
    }
  }

  void _removePhoto() => setState(() {
    _pickedFile = null;
    _imageBytes = null;
  });

  // ── Fullscreen image viewer ──────────────────────────────────────────────
  void _openFullscreen() {
    if (_imageBytes == null) return;
    showDialog(
      context: context,
      barrierColor: Colors.black.withValues(alpha: 0.92),
      builder: (_) => _FullscreenImageModal(bytes: _imageBytes!),
    );
  }

  void _next() {
    Navigator.pushNamed(context, AppRoutes.reportLocation, arguments: {
      ...widget.reportData,
      'photoFile':  _pickedFile,
      'photoBytes': _imageBytes,   // cross-platform image data
      'hasPhoto':   _pickedFile != null,
    });
  }

  @override
  Widget build(BuildContext context) {
    final category =
        widget.reportData['category'] as String? ?? 'Infrastructure';
    final catColor = context.c.categoryColor(category);
    final catBg = context.c.categoryBg(category);

    return Scaffold(
      backgroundColor: context.c.background,
      appBar: CivilWatchAppBar(
        title: 'Report Concern',
        subtitle: 'Step 3 of 5',
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
            child: ReportStepper(currentStep: 2),
          ),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Add a photo',
                    style: GoogleFonts.inter(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: context.c.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'A clear photo helps us verify\nthe concern faster.',
                    style: GoogleFonts.inter(
                      fontSize: 13,
                      color: context.c.textSecondary,
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Real photo preview OR picker options ────────────────
                  if (_pickedFile != null) ...[
                    _RealPhotoPreview(
                      file:  _pickedFile!,
                      bytes: _imageBytes,
                      onRemove: _removePhoto,
                      onView:   _openFullscreen,
                    ),
                    const SizedBox(height: 14),
                    // Allow replacing the photo
                    OutlinedButton.icon(
                      onPressed: () => _showSourceSheet(catColor),
                      icon: const Icon(Icons.swap_horiz_rounded, size: 18),
                      label: const Text('Change Photo'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: catColor,
                        side: BorderSide(color: catColor.withValues(alpha: 0.5)),
                        minimumSize: const Size(double.infinity, 46),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        textStyle: GoogleFonts.inter(
                            fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ] else ...[
                    // ── Take Photo ──────────────────────────────────────
                    _PhotoOption(
                      icon: Icons.camera_alt_rounded,
                      title: 'Take Photo',
                      subtitle: 'Open camera to capture now',
                      color: catColor,
                      bg: catBg,
                      onTap: () => _pickImage(ImageSource.camera),
                      isPrimary: true,
                    ),
                    const SizedBox(height: 12),

                    Row(children: [
                      const Expanded(child: Divider()),
                      Padding(
                        padding:
                            const EdgeInsets.symmetric(horizontal: 16),
                        child: Text('or',
                            style: GoogleFonts.inter(
                                fontSize: 13,
                                color: context.c.textHint)),
                      ),
                      const Expanded(child: Divider()),
                    ]),
                    const SizedBox(height: 12),

                    // ── Gallery ─────────────────────────────────────────
                    _PhotoOption(
                      icon: Icons.photo_library_rounded,
                      title: 'Choose from Gallery',
                      subtitle: 'Pick an existing photo',
                      color: context.c.textSecondary,
                      bg: context.c.background,
                      onTap: () => _pickImage(ImageSource.gallery),
                      isPrimary: false,
                    ),
                  ],

                  const SizedBox(height: 20),

                  // ── Tip ─────────────────────────────────────────────────
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: context.c.primarySurface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppColors.primary.withValues(alpha: 0.2)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.lightbulb_outline_rounded,
                            color: AppColors.primary, size: 18),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Tip: Make sure the concern is clearly visible in the photo.',
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: AppColors.primary,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          _ReportPhotoFooter(
            onNext: _next,
            canSkip: _pickedFile == null,
            catColor: catColor,
          ),
        ],
      ),
    );
  }

  // ── Bottom sheet for camera/gallery choice (when changing photo) ─────────
  void _showSourceSheet(Color catColor) {
    showModalBottomSheet(
      context: context,
      backgroundColor: context.c.card,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Handle
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 18),
                decoration: BoxDecoration(
                  color: context.c.divider,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Text('Choose Source',
                  style: GoogleFonts.inter(
                      fontSize: 16, fontWeight: FontWeight.w700)),
              const SizedBox(height: 16),
              ListTile(
                leading: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: catColor.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(Icons.camera_alt_rounded,
                      color: catColor, size: 22),
                ),
                title: Text('Camera',
                    style: GoogleFonts.inter(
                        fontSize: 14, fontWeight: FontWeight.w600)),
                onTap: () {
                  Navigator.pop(context);
                  _pickImage(ImageSource.camera);
                },
              ),
              ListTile(
                leading: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: context.c.background,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(Icons.photo_library_rounded,
                      color: context.c.textSecondary, size: 22),
                ),
                title: Text('Gallery',
                    style: GoogleFonts.inter(
                        fontSize: 14, fontWeight: FontWeight.w600)),
                onTap: () {
                  Navigator.pop(context);
                  _pickImage(ImageSource.gallery);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Real photo preview — shows the actual picked image
// ─────────────────────────────────────────────────────────────────────────────
class _RealPhotoPreview extends StatelessWidget {
  final XFile file;
  final Uint8List? bytes;
  final VoidCallback onRemove;
  final VoidCallback onView;

  const _RealPhotoPreview({
    required this.file,
    required this.bytes,
    required this.onRemove,
    required this.onView,
  });

  Widget _buildImage() {
    if (bytes != null) {
      // Works on both Web and Android
      return Image.memory(
        bytes!,
        width: double.infinity,
        height: 220,
        fit: BoxFit.cover,
      );
    }
    if (!kIsWeb) {
      return Image.file(
        File(file.path),
        width: double.infinity,
        height: 220,
        fit: BoxFit.cover,
      );
    }
    // Fallback placeholder (bytes should always be set, this is a safety net)
    return Container(
      width: double.infinity,
      height: 220,
      color: const Color(0xFF1A2A3A),
      child: const Icon(Icons.image_rounded, color: Colors.white54, size: 48),
    );
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onView,
      child: Stack(
        children: [
          // ── Actual image ──────────────────────────────────────────────
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: _buildImage(),
          ),

          // ── Dark gradient overlay at bottom ───────────────────────────
          Positioned(
            left: 0, right: 0, bottom: 0,
            child: Container(
              height: 56,
              decoration: BoxDecoration(
                borderRadius: const BorderRadius.vertical(
                    bottom: Radius.circular(16)),
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.55),
                  ],
                ),
              ),
            ),
          ),

          // ── "Tap to view" label ───────────────────────────────────────
          Positioned(
            bottom: 10, left: 12,
            child: Row(
              children: [
                const Icon(Icons.fullscreen_rounded,
                    color: Colors.white, size: 16),
                const SizedBox(width: 4),
                Text(
                  'Tap to view full image',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: Colors.white,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),

          // ── Remove (×) button ─────────────────────────────────────────
          Positioned(
            top: 10, right: 10,
            child: GestureDetector(
              onTap: onRemove,
              child: Container(
                width: 32, height: 32,
                decoration: const BoxDecoration(
                  color: Color(0xFFDC2626),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.close_rounded,
                    color: Colors.white, size: 17),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Fullscreen image modal — shown when user taps the preview
// ─────────────────────────────────────────────────────────────────────────────
// ─────────────────────────────────────────────────────────────────────────────
// Fullscreen image modal — shown when user taps the preview
// ─────────────────────────────────────────────────────────────────────────────
class _FullscreenImageModal extends StatelessWidget {
  final Uint8List bytes;
  const _FullscreenImageModal({required this.bytes});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.of(context).pop(),
      child: Scaffold(
        backgroundColor: Colors.transparent,
        body: Stack(
          children: [
            // ── Pinch-to-zoom image ─────────────────────────────────────
            Center(
              child: InteractiveViewer(
                minScale: 0.8,
                maxScale: 4.0,
                child: Image.memory(
                  bytes,
                  fit: BoxFit.contain,
                ),
              ),
            ),

            // ── Close button ────────────────────────────────────────────
            Positioned(
              top: MediaQuery.of(context).padding.top + 12,
              right: 16,
              child: GestureDetector(
                onTap: () => Navigator.of(context).pop(),
                child: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.55),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.close_rounded,
                      color: Colors.white, size: 20),
                ),
              ),
            ),

            // ── "Tap to close" hint at bottom ───────────────────────────
            Positioned(
              bottom: MediaQuery.of(context).padding.bottom + 20,
              left: 0,
              right: 0,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.45),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    'Pinch to zoom  •  Tap anywhere to close',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      color: Colors.white.withValues(alpha: 0.85),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Photo option button (camera / gallery)
// ─────────────────────────────────────────────────────────────────────────────
class _PhotoOption extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final Color bg;
  final VoidCallback onTap;
  final bool isPrimary;

  const _PhotoOption({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.bg,
    required this.onTap,
    required this.isPrimary,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 20),
        decoration: BoxDecoration(
          color: isPrimary ? bg : AppColors.onAccent,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isPrimary ? color.withValues(alpha: 0.35) : context.c.divider,
            width: isPrimary ? 1.5 : 1,
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 30),
            const SizedBox(width: 14),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                    color: color,
                  ),
                ),
                if (subtitle.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: GoogleFonts.inter(
                        fontSize: 12, color: context.c.textHint),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Footer nav bar
// ─────────────────────────────────────────────────────────────────────────────
class _ReportPhotoFooter extends StatelessWidget {
  final VoidCallback onNext;
  final bool canSkip;
  final Color catColor;

  const _ReportPhotoFooter({
    required this.onNext,
    required this.canSkip,
    required this.catColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
      decoration: BoxDecoration(
        color: context.c.card,
        border: Border(top: BorderSide(color: context.c.divider)),
      ),
      child: Row(
        children: [
          // Back
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.arrow_back_rounded, size: 18),
              label: const Text('Back'),
              style: OutlinedButton.styleFrom(
                foregroundColor: context.c.textPrimary,
                side: BorderSide(color: context.c.divider),
                minimumSize: const Size(0, 52),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                textStyle: GoogleFonts.inter(
                    fontSize: 14, fontWeight: FontWeight.w600),
              ),
            ),
          ),
          const SizedBox(width: 12),

          // Next / Skip
          Expanded(
            flex: 2,
            child: ElevatedButton(
              onPressed: onNext,
              style: ElevatedButton.styleFrom(
                backgroundColor: catColor,
                foregroundColor: Colors.white,
                minimumSize: const Size(0, 52),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                elevation: 0,
                textStyle: GoogleFonts.inter(
                    fontSize: 15, fontWeight: FontWeight.w700),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(canSkip ? 'Skip' : 'Next'),
                  const SizedBox(width: 6),
                  Icon(
                    canSkip
                        ? Icons.skip_next_rounded
                        : Icons.arrow_forward_rounded,
                    size: 18,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

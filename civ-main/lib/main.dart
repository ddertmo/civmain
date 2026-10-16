import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'app.dart';
import 'core/state/app_state.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Restore session from saved token + load API data if logged in
  await AppState().init();

  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Status/navigation bar styling is applied by AppState.init() and on every
  // theme toggle (see AppState._applySystemUi) so it matches the active theme.
  runApp(const CivilWatchApp());
}

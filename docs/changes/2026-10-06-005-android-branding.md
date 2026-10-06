# Android: camera permission, cleartext HTTP, real package name

- **Date:** 2026-10-06
- **Commit:** `71df2c2`
- **Type:** fix
- **Areas:** Android

> Note: this commit is titled `graphify: refresh graph after android/ios/api fixes`
> but it also carries the Android source changes (the graph refresh and the
> Android fixes were committed together).

## Problem

1. `image_picker` could not open the camera — the `CAMERA` permission was never
   declared.
2. The app could not reach the Laravel API over plain `http://` on Android 9+,
   where cleartext is blocked by default.
3. The installed app was still called `my_app` with package `com.example.my_app`,
   so it collided with the Flutter template and looked wrong on the launcher.

## Cause

Default Flutter project scaffold, never rebranded.

## Changes

| File | Change |
|---|---|
| `civ-main/android/app/src/main/AndroidManifest.xml` | added `<uses-permission android:name="android.permission.CAMERA" />` |
| `civ-main/android/app/src/main/AndroidManifest.xml` | `android:usesCleartextTraffic="true"` on `<application>` so `http://10.0.2.2:8000` / LAN HTTP works |
| `civ-main/android/app/src/main/AndroidManifest.xml` | `android:label` `my_app` → `CIVILWATCH` |
| `civ-main/android/app/build.gradle.kts` | `namespace` and `applicationId` `com.example.my_app` → `com.civilwatch.app` |
| `civ-main/android/app/src/main/kotlin/com/civilwatch/app/MainActivity.kt` | moved from `kotlin/com/example/my_app/`, package rewritten to `com.civilwatch.app` |

## Verification

```bash
grep -c "com.civilwatch.app" civ-main/android/app/build.gradle.kts   # 2
grep -o "usesCleartextTraffic=\"true\"" civ-main/android/app/src/main/AndroidManifest.xml
grep -o "CAMERA" civ-main/android/app/src/main/AndroidManifest.xml
test -f civ-main/android/app/src/main/kotlin/com/civilwatch/app/MainActivity.kt && echo ok
```

**Not verified:** no Flutter SDK / Android SDK in this environment — static
review only, never compiled or installed.

## Impact / how to test by hand

```bash
cd civ-main
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api   # emulator
```
1. Launcher icon reads **CIVILWATCH**
2. Report → add photo → camera opens
3. Login/submit succeed against the dev API (no `CLEARTEXT communication not permitted` error)

**Downside to be aware of:** `usesCleartextTraffic="true"` is a dev convenience.
It should be scoped with a `network_security_config.xml` before a real release.

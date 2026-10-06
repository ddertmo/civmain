# iOS: usage descriptions, ATS exception, real bundle id

- **Date:** 2026-10-06
- **Commit:** `c0669de`
- **Type:** fix
- **Areas:** iOS

## Problem

The iOS build would **terminate the app the moment** the user opened the camera
or asked for location, and could not reach the Laravel API over plain HTTP.

## Cause

1. `Info.plist` had none of the `NS*UsageDescription` keys. iOS kills the process
   on first access to a protected resource when the key is absent (no dialog, no
   error — just a crash).
2. The API is `http://127.0.0.1:8000` in development; App Transport Security
   blocks plain HTTP by default.
3. The bundle was still the Flutter template: `com.example.myApp`, display name
   "My App".

## Changes

| File | Change |
|---|---|
| `civ-main/ios/Runner/Info.plist` | added `NSCameraUsageDescription`, `NSPhotoLibraryUsageDescription`, `NSLocationWhenInUseUsageDescription`, `NSLocationAlwaysAndWhenInUseUsageDescription` |
| `civ-main/ios/Runner/Info.plist` | added `NSAppTransportSecurity` → `NSAllowsLocalNetworking` + `NSAllowsArbitraryLoads` so the dev API over `http://` works |
| `civ-main/ios/Runner/Info.plist` | `CFBundleDisplayName` `My App` → `CIVILWATCH`; `CFBundleName` `my_app` → `civilwatch` |
| `civ-main/ios/Runner.xcodeproj/project.pbxproj` | `PRODUCT_BUNDLE_IDENTIFIER` `com.example.myApp` → `com.civilwatch.app` (6 occurrences: app + tests) |

## Verification

```bash
python3 -c "import plistlib;d=plistlib.load(open('civ-main/ios/Runner/Info.plist','rb'));print(sorted(d))"
# NSAppTransportSecurity, NSCameraUsageDescription, NSLocationAlwaysAndWhenInUseUsageDescription,
# NSLocationWhenInUseUsageDescription, NSPhotoLibraryUsageDescription ... all present
grep -c "com.civilwatch.app" civ-main/ios/Runner.xcodeproj/project.pbxproj   # 6
```

**Not verified:** no Xcode/macOS in this environment — never compiled for iOS.

## Impact / how to test by hand

On a Mac: `cd civ-main && flutter build ios`, install on a device, then
1. Report flow → add photo → camera must open (previously: instant crash)
2. "Use Current Location" → permission prompt with the CivilWatch wording
3. Login against `http://<your-ip>:8000` → must connect (previously ATS block)

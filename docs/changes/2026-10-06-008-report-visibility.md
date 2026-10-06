# Submitted and approved reports were invisible until the app restarted

- **Date:** 2026-10-06
- **Commit:** `98e02f5`
- **Type:** fix
- **Areas:** Flutter / Laravel

## Problem

Two user-facing symptoms, both about reports vanishing:

1. "I submit a report and it succeeds, but I can't see my pending report in
   My Reports."
2. "I approve a report as admin and it stays Pending — the accepted report
   never shows on the map."

## Cause

**Symptom 1 — the submission bypassed AppState.**
`lib/screens/report/report_review.dart:40` called
`ReportService.instance.submitReport(...)` directly instead of
`AppState().submitReport(...)`. Only the latter inserts the new report into
`AppState._reports` (`app_state.dart:152`). `MyReportsScreen` reads only
`AppState().reports` and has **no fetch of its own**, so the just-filed report
was missing until a full app restart.

**Symptom 2 — the community feed was loaded exactly once.**
`AppState.init()` (`app_state.dart:41`) calls `_loadCommunityReports()` a single
time at startup. There was no `RefreshIndicator`, no `initState` reload and no
retry anywhere, so anything validated by an admin *after* launch never reached
the map. `MyReports` had the same one-shot problem.

**Symptom 2b — assigning a report did not publish it.**
`/api/mobile/reports/community` filters `is_public = true`. Only
`validate()` set that flag. An admin who pressed **Assign** (or used the status
dropdown) advanced the status but left `is_public = false`, so the report was
accepted by the city and still filtered out of the map.

**Bonus:** `VisitorReportsScreen` read `DummyData.communityReports` (7 fake
pins) instead of the API, so guest mode could never show a real report.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/core/state/app_state.dart` | added public `refreshMyReports()` (fetch + `notifyListeners`) and `refreshCommunityReports()` |
| `civ-main/lib/screens/report/report_review.dart` | `ReportService.instance.submitReport` → `AppState().submitReport`; added the `app_state.dart` import, dropped the now-unused `report_service.dart` import |
| `civ-main/lib/screens/my_reports/my_reports_screen.dart` | `initState` → `AppState().refreshMyReports()`; "In Progress" filter now also matches `Assigned to Office` (previously an assigned report showed only under "All") |
| `civ-main/lib/screens/community_map/community_map_screen.dart` | `initState` → `AppState().refreshCommunityReports()` |
| `civ-main/lib/screens/visitor/visitor_reports_screen.dart` | source switched from `DummyData.communityReports` to `AppState().communityReportsPublic`; added `initState` refresh + `setState` on completion (this screen is not a `ListenableBuilder`); removed the `dummy_data.dart` import |
| `laravel/app/Models/CitizenReport.php` | `transitionTo()` now sets `is_public = true` when the new status is `Assigned to Office`, `In Progress` or `Resolved`, so an accepted report lands on the public map even if **Validate Report** was never pressed |

## Verification

Static (no Flutter SDK in this environment):
```bash
python3 -c "s=open('civ-main/lib/core/state/app_state.dart').read();print(s.count('{'),s.count('}'),s.count('('),s.count(')'))"
# 58 58 183 183
php -l laravel/app/Models/CitizenReport.php   # No syntax errors detected
```

Live, end to end against the running backend:
```
POST /admin/citizen-reports/2/assign   → 302   (report 2 was NOT validated)
  → citizen_reports: id=2 status='Assigned to Office' is_public=1  assigned_office_id=1
POST /admin/citizen-reports/3/validate → 302
GET  /api/mobile/reports/community     → count: 3
     CW-2026-00003 Pending Validation
     CW-2026-00002 Assigned to Office
     CW-2026-00001 Pending Validation
```
Before the fix, report 2 was `is_public = 0` and absent from that endpoint.

**Not verified:** the Dart side was never compiled or run here — static review
only.

## Impact / how to test by hand

Requires a rebuild (Dart changed): `cd civ-main && flutter run …`

1. Submit a report → open **My Reports** → it is there immediately, under
   *Pending*.
2. As admin (`admin@civilwatch.ph` / `Admin@2026!`) open
   `http://127.0.0.1:8000/admin/citizen-reports` → **Validate** or **Assign**
   a report.
3. Back in the app, open **Community Map** (or the visitor reports list) → the
   accepted report's pin is there without restarting the app.

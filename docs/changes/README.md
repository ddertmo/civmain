# CIVILWATCH — Change Log

One markdown file per change. Newest last. Every code change made by the
`changelog` skill lands here with its commit hash.

| # | Date | Commit | Change |
|---|---|---|---|
| 001 | 2026-10-04 | `c1e9035` | [Initial backup — civ-main + laravel](2026-10-04-001-initial-backup.md) |
| 002 | 2026-10-06 | `ddbf5be` | [Graphify knowledge graph + opencode integration](2026-10-06-002-graphify-setup.md) |
| 003 | 2026-10-06 | `c0669de` | [iOS permissions, ATS exception, bundle id](2026-10-06-003-ios-permissions.md) |
| 004 | 2026-10-06 | `a16f8c1` | [API base URL + remove demo Advance Status button](2026-10-06-004-api-base-url-demo-button.md) |
| 005 | 2026-10-06 | `71df2c2` | [Android branding, camera permission, cleartext](2026-10-06-005-android-branding.md) |
| 006 | 2026-10-06 | `fc497bd` | [401 JSON on API + DB session timezone](2026-10-06-006-401-json-db-timezone.md) |
| 007 | 2026-10-06 | `5db5ba6` | [Docs: stop claiming the app has no backend](2026-10-06-007-docs-backend-truth.md) |
| 008 | 2026-10-06 | `98e02f5` | [Submitted/approved reports invisible until restart](2026-10-06-008-report-visibility.md) |
| 009 | 2026-10-06 | `04c3fba` | [Reject a report with a reason, end to end](2026-10-06-009-reject-with-reason.md) |
| 010 | 2026-10-06 | `03aa975` | [Repair the dead analytics, notification and user-list endpoints](2026-10-06-010-repair-dead-admin-endpoints.md) |
| 011 | 2026-10-06 | `3e21b70` | [Drop the legacy /api/reports subsystem](2026-10-06-011-drop-legacy-reports-code.md) |
| 012 | 2026-10-06 | `9bfeab2` | [Gate every admin write behind super_admin](2026-10-06-012-super-admin-write-gate.md) |
| 013 | 2026-10-06 | `2e58906` | [Super admin reference, DEVLOG cross-links, graph refresh](2026-10-06-013-super-admin-reference-graph.md) |
| 014 | 2026-10-07 | `cdac1cb` | [One-file XAMPP database import and PC setup guide](2026-10-07-014-xampp-database-import-setup.md) |
| 015 | 2026-10-07 | `9fdad84` | [Export session context for cross-machine handoff](2026-10-07-015-session-context-export.md) |
| 016 | 2026-10-07 | `994d6ed` | [Refresh Flutter and Composer dependencies for PC setup](2026-10-07-016-pc-dependency-refresh.md) |
| 017 | 2026-10-07 | `297a78b` | [Auto-update run scripts for app and backend](2026-10-07-017-auto-update-run-scripts.md) |
| 018 | 2026-10-07 | `cccc83c` | [Add the missing sessions migration so web pages stop 500ing](2026-10-07-018-missing-sessions-migration.md) |
| 019 | 2026-10-07 | `1c08c6e` | [RUN_APP: run on Chrome and auto-start the backend](2026-10-07-019-run-app-chrome-and-backend.md) |
| 020 | 2026-10-07 | `40b619b` | [Report photos: missing storage link and CORS header](2026-10-07-020-report-photo-storage-link-and-cors.md) |
| 021 | 2026-10-07 | `dcc171c` | [PC health-check scripts (Windows + Termux) and companion repo](2026-10-07-021-pc-health-check-scripts.md) |
| 022 | 2026-10-07 | `dc8c30c` | [What `git pull` gives you on the PC vs what you must install](2026-10-07-022-pull-vs-install-on-pc.md) |
| 023 | 2026-10-07 | `e95e9ea` | [PC setup: the SQL import path never ran the `sessions` migration](2026-10-07-023-sessions-migration-in-pc-setup.md) |
| 024 | 2026-10-08 | `857a723` | [Pull the PC companion repo: civilwatch_check_pc into civmain](2026-10-08-024-pull-civilwatch-check-pc.md) |

## Template

New files follow `docs/changes/TEMPLATE.md`. The `changelog` skill writes them
automatically after each change.

| 025 | 2026-10-08 | `HEAD` | [QA: GIS status/palette + map scope](2026-10-08-025-qa-gis-status-palette-and-map-scope.md) |
| 026 | 2026-10-08 | `HEAD` | [QA: category naming + filter integrity](2026-10-08-026-category-naming-and-filter-integrity.md) |
| 027 | 2026-10-08 | `HEAD` | [QA: date filter search + dynamic range labels](2026-10-08-027-date-filter-search-and-dynamic-range-labels.md) |
| 028 | 2026-10-08 | `HEAD` | [QA: login creds + users table from API](2026-10-08-028-login-creds-and-users-table-from-api.md) |
| 029 | 2026-10-08 | `HEAD` | [QA: polish + cleanup + docs](2026-10-08-029-polish-cleanup-docs.md) |
| 030 | 2026-10-08 | `1f9c078` | [Unauthenticated admin pages returned 500 instead of redirecting to login](2026-10-08-030-guest-redirect-admin-login.md) |
| 031 | 2026-10-08 | `05b8e67` | [One-click boot script: START_ALL.bat starts the whole PC stack](2026-10-08-031-start-all-boot-script.md) |
| 032 | 2026-10-08 | `89d9eaf` | [Persist an Approved status so approve/assign/map/monitoring/Android agree](2026-10-08-032-approved-status-flow-fix.md) |
| 033 | 2026-10-09 | `fa9a49f` | [graphify now runs on the PC — graph refreshed with SQL + PHP folded in](2026-10-09-033-graphify-on-pc.md) |
| 033 | 2026-10-13 | `91086ee` | [Require Playwright E2E: scaffold, run script and docs](2026-10-13-033-playwright-required.md) |
| 034 | 2026-10-13 | `e61e0bc` | [Record UniSMS as the planned primary SMS provider](2026-10-13-034-unisms-sms-task.md) |
| 035 | 2026-10-14 | `6e5704d` | [Office list restricted to CEO and CENRO](2026-10-14-035-office-list-ceo-cenro.md) |
| 036 | 2026-10-14 | `6e5704d` | [Assign-office: real Date Submitted, readable recommendation, responsive layout](2026-10-14-036-assign-office-date-and-form.md) |
| 037 | 2026-10-14 | `6e5704d` | [Approved reports leave Pending Reports](2026-10-14-037-approved-leaves-pending.md) |
| 038 | 2026-10-14 | `6e5704d` | [Remove Playwright E2E](2026-10-14-038-remove-playwright.md) |
| 039 | 2026-10-15 | `HEAD` | [Graph-first retrieval rules + in-repo Obsidian/ICM memory structure](2026-10-15-039-graph-first-retrieval-and-icm-memory.md) |
| 040 | 2026-10-15 | `HEAD` | [GIS map emoji marker replaced with a proper category icon](2026-10-15-040-gis-map-emoji-marker-to-icon.md) |
| 041 | 2026-10-15 | `HEAD` | [Clean up Assign Report modal and restrict priority to the assigned office](2026-10-15-041-assign-report-cleanup-priority-permissions.md) |
| 042 | 2026-10-15 | `HEAD` | [Monitoring report view reused pending-reports detail layout with corrected navigation, dates, and photo placeholder](2026-10-15-042-monitoring-report-details-reuse.md) |
| 043 | 2026-10-15 | `HEAD` | [Fix report view link JavaScript quoting](2026-10-15-043-report-view-link-javascript-quoting.md) |
| 044 | 2026-10-15 | `HEAD` | [Report details redesigned to two-column workflow layout](2026-10-15-044-report-details-redesign.md) |
| 045 | 2026-10-15 | `HEAD` | [Embedded report detail panel in Monitoring page](2026-10-15-045-monitoring-embedded-report-detail.md) |
| 046 | 2026-10-15 | `HEAD` | [Dedicated Monitoring report details page](2026-10-15-046-monitoring-dedicated-report-detail-page.md) |
| 047 | 2026-10-15 | `HEAD` | [Reporter details panel in admin report views + Home Barangay fix](2026-10-15-047-reporter-details-and-home-barangay.md) |
| 048 | 2026-10-15 | `HEAD` | [Citizen registration now captures a full home address](2026-10-15-048-registration-full-address.md) |
| 049 | 2026-10-15 | `HEAD` | [Unify the Digos City barangay list to the official 26 everywhere](2026-10-15-049-unify-digos-barangay-list.md) |
| 050 | 2026-10-15 | `HEAD` | [Session start: fresh sessions continue from a short handoff, existing sessions keep their history](2026-10-15-050-session-start-handoff.md) |
| 051 | 2026-10-15 | `HEAD` | [Token-lean session start: read `## NOW` + the last 3 changelog rows, with research fallbacks](2026-10-15-051-token-lean-handoff-now-block.md) |

# Monitoring report view reused pending-reports detail layout

- **Date:** 2026-10-15
- **Commit:** `HEAD` (working tree)
- **Type:** fix
- **Areas:** Laravel web admin

## Problem

When a report was opened from Monitoring, the browser showed the shared report details page
with "Back to Pending Reports" and no origin-aware behavior. The same page was also used
by Pending Reports, so its content needed to depend on the originating list.

The detail page also displayed raw timestamps such as `2026-10-15T08:19:43+00:00` and
only hid the image when no photo existed.

## Cause

The shared `report-details.html` was hardcoded to Pending Reports and the Monitoring list
linked to it without a source parameter:

- Back link target was always `pending-reports.html`.
- Back label was always "Back to Pending Reports".
- Reporter/date fields rendered raw API values.
- The image element was simply hidden on missing photos.
- Pending Approve/Reject controls were not origin-aware.

## Changes

| File | Change |
|---|---|
| `laravel/public/monitoring.html` | Linked reports to the existing shared detail page using `report-details.html?id=...&from=monitoring`, preserved the list URL in sessionStorage, and made the current monitoring update modal restorable from report details. |
| `laravel/public/pending-reports.html` | Linked reports to the existing shared detail page using `report-details.html?id=...&from=pending` and preserved the list URL. |
| `laravel/public/report-details.html` | Made the back link target and label origin-aware, formatted record dates with the existing `fmtDate()`, and added a clean "No photo available" placeholder. |
| `laravel/public/report-details.html` | Hid Pending Reports' Approve/Reject controls and showed the Monitoring status update entry-point only when opened from Monitoring, while reusing the existing Monitoring status modal. |

## Notes

- No separate Monitoring report-details page was created.
- Priority, status transition, and role rules remain unchanged.

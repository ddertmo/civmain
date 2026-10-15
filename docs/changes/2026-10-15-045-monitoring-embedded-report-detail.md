# Embedded report detail panel in Monitoring page

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** fix
- **Areas:** Laravel web admin

## Changes
- Changed Monitoring **View** from navigating to `report-details.html` to opening an embedded detail panel inside `monitoring.html`.
- Added a full-screen overlay containing a report details iframe.
- Reused the existing redesigned report details, status update, map, timeline, assigned office, reporter, and quick information UI.
- Added Escape/close handling and hidden details back link when embedded.

## Verification
- HTML parse succeeded.
- Monitoring JavaScript passed syntax check.
- check_pc.ps1 passed.

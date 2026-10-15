# Dedicated Monitoring report details page

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** fix
- **Areas:** Laravel web admin

## Changes
- Removed the temporary embedded modal approach from `monitoring.html`.
- Added `monitoring-report-details.html` based on the redesigned report details page.
- Monitoring **View** now opens this dedicated page.

## Verification
- HTML parse succeeded.
- Monitoring JavaScript passed syntax check.
- check_pc.ps1 passed.

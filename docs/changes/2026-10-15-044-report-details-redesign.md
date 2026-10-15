# Report details redesigned to two-column workflow layout

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** fix
- **Areas:** Laravel web admin / UI

## Changes
- Rebuilt the shared `report-details.html` into the requested two-column report details layout.
- Added Report Summary, photo, Location map, Description, and Status Timeline cards in the left column.
- Added Update Report Status, Assigned Office, Reporter, and Quick Information cards in the right column.
- Implemented selectable status cards, notes character counter, cancel/save behavior, and category/priority-safe status persistence.
- Preserved existing report data, map initialization, approve/reject functionality, navigation, and activity timeline handling.

## Verification
- HTML parse succeeded.
- Embedded JavaScript syntax check succeeded.
- check_pc.ps1 passed all checks except pending Git commit.

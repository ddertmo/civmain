# Assign-office form: real Date Submitted, readable recommendation, responsive layout

- **Date:** 2026-10-14
- **Commit:** `6e5704d`
- **Type:** fix
- **Areas:** Laravel (HTML/JS) / Flutter

## Problem

On the assign-office page:

- **Date Submitted** showed the placeholder text `â€”` instead of a date.
- The recommendation line rendered a raw JavaScript expression
  (`Recommended because category = ${...}`).
- The form/sidebar grid could overflow on narrow viewports.

## Cause

- **Date:** `assign-office.html` read `r.created_at`, but the API serialises the
  creation time as **`submittedAt`** and returns no `created_at`. The value was
  empty, so it fell back to the placeholder. `report-details.html` and the
  Flutter `track_by_reference_screen.dart` had the same wrong field.
- **Mojibake:** the placeholder was itself double-encoded. `assign-office.html`
  and `gis-map.html` contained UTF-8 em dashes (`E2 80 94`) that had been
  decoded as Windows-1252 and re-saved as UTF-8, so `—` rendered as the literal
  text `â€”` (25 occurrences in assign-office, 12 in gis-map).
- **Recommendation:** the string was written as a template literal in plain
  HTML, so `${...}` was never evaluated.
- **Layout:** `grid-template-columns: 1fr 300px` had no stacking breakpoint.

## Changes

| File | Change |
|---|---|
| `laravel/public/assign-office.html` | `afDate` uses `fmtDate(r.submittedAt \|\| r.created_at)`; recommendation text is a real element filled from `activeReport.category`; `minmax(0,1fr) 300px` + ≤900px single column; all corrupted `â€”` replaced with `-` |
| `laravel/public/report-details.html` | date uses `r.submittedAt \|\| r.created_at` |
| `laravel/public/gis-map.html` | corrupted `â€”` replaced with `-` |
| `civ-main/lib/screens/visitor/track_by_reference_screen.dart` | Date Submitted uses `report.submittedAt` |

## Verification

- `GET /api/admin/citizen-reports` returns `submittedAt`
  (e.g. `2026-10-14T12:29:57+00:00`) and no `created_at`.
- The served page contains the fixed code:
  ```
  if (!str) return '-';
  document.getElementById('afDate').textContent = fmtDate(r.submittedAt || r.created_at);
  ```
- Corrupted-em-dash scan of both HTML files returns 0.
- The Flutter edit was not compiled (no Flutter run for this change); static
  review only.

## Impact / how to test by hand

Open an approved report → **Assign Office**. Date Submitted shows a real date
(e.g. `Oct 14, 2026 at 12:29 PM`), the recommendation reads
`Recommended based on category: ...`, and the layout stacks on a narrow window.

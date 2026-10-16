# CEO/CENRO list pages load from the API; offices can post status updates

- **Date:** 2026-10-16
- **Commit:** `HEAD`
- **Type:** feature
- **Areas:** Laravel

## Problem

The three CEO/CENRO office list pages rendered only hardcoded sample rows, so a
signed-in office saw the same fake reports no matter what was really assigned to
it:

- `offices/{ceo,cenro}/reports.html` — "My Assigned Reports" table.
- `offices/{ceo,cenro}/inprogress.html` — "Active Reports" cards + stat counts.
- `offices/{ceo,cenro}/resolved.html` — "Resolved Reports" table + summary
  stats (the numbers `22 / 128 / 3.2` were literal text in the HTML).

The office **Save Update** and **Mark as Resolved** buttons on
`offices/{ceo,cenro}/report-details.html` only rewrote the status badge in the
DOM; nothing was sent to the backend, so reloading lost the change.

Both office workflows also exposed a status, **"For Resolution"**, that the
backend does not know about — it is not one of the canonical statuses in
`CitizenReport::STATUS_ORDER`.

## Cause

- Each list page opened with `const activeData/resolvedData/reportsData = [ … ]`
  and rendered that array directly; there was no `Api.get` call anywhere in the
  list pages.
- `saveUpdate()` / `confirmResolve()` in
  `laravel/public/offices/{ceo,cenro}/report-details.html` set
  `statusBadgeEl.innerHTML` and showed a toast, but performed no HTTP request.
- "For Resolution" is absent from `CitizenReport::STATUS_ORDER`
  (`laravel/app/Models/CitizenReport.php:57`), so it could never be persisted —
  it only ever existed in the prototypes.
- The write route `POST admin/citizen-reports/{id}/status` lived inside the
  `['auth:sanctum', 'superadmin']` group in `laravel/routes/api.php`, so CEO and
  CENRO requests were rejected by `EnsureSuperAdmin` before reaching the
  controller.
- `AdminCitizenReportController::updateStatus(int $id)` only accepted a numeric
  id, but the office pages link and post by **reference number** (`CW-…`).

## Changes

| File | Change |
|---|---|
| `laravel/routes/api.php` | Moved `POST admin/citizen-reports/{id}/status` out of the `superadmin`-gated group into the auth-only group (same URL). Super admin can still call it (e.g. from Monitoring); CEO/CENRO can now reach it, and `updateStatus()` enforces office ownership. |
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | `updateStatus(int $id)` → `updateStatus(string $id)` accepting a numeric id **or** a reference number. Added role/office checks: non-super-admin callers must be `ceo`/`cenro`, the report must be assigned to their office, and they may only target `Assigned to Office` / `In Progress` / `Resolved`. Also added office auto-scoping to `index()` so a CEO sees only CEO reports and CENRO only CENRO reports. |
| `laravel/public/js/utils.js` | New office-report helpers: `officeStatusLabel` / `apiStatusValue` (translate the API's `Assigned to Office` ↔ the short UI label `Assigned`), `shortDate`, `shortTime`, `assignedDate`, `officeReport` (flatten one API row into the shape every list page renders) and `fetchOfficeReports(sampleRows)` (fetch `GET /api/admin/citizen-reports`, falling back to the caller's sample rows when there is no backend/token). |
| `laravel/public/offices/{ceo,cenro}/reports.html` | Dropped the `For Resolution` filter/modal options. Renamed the local array to `sampleReports`, render from `reportsData` loaded via `Utils.fetchOfficeReports`, added a loading state, and hardened null-safe filters / encoded `?ref=` links. |
| `laravel/public/offices/{ceo,cenro}/inprogress.html` | "For Resolution" stat card → **Total Active** (`#countTotal`, `filterBy('')`); removed the For Resolution legend row and pill mapping; counts now derive from the full active set. Loads the office's live reports and keeps only `Assigned` + `In Progress`, with a loading state. |
| `laravel/public/offices/{ceo,cenro}/resolved.html` | Gave the summary numbers ids (`#sumWeek` / `#sumTotal` / `#sumAvg`) and compute them from the loaded resolved reports (resolved-this-week, total, average days to resolve). Render from `Utils.fetchOfficeReports` with a loading state. |
| `laravel/public/offices/{ceo,cenro}/report-details.html` | Status dropdown is now `Assigned` / `In Progress` / `Resolved` (no `For Resolution`). `saveUpdate()` and `confirmResolve()` now `POST /api/admin/citizen-reports/{ref}/status` via `Api.action(...)` with `{ status, description }`, converting the short label back with `Utils.apiStatusValue`. A shared `applyStatusLocally()` updates the badge/timeline on success; a no-token fallback keeps the prototype working. `normalizeStatus()` now delegates to `Utils.officeStatusLabel`. |

## Verification

- Every inline `<script>` block of all eight touched HTML pages was extracted and
  parsed with Node:

  ```
  node check-inline-js.js offices/{ceo,cenro}/{reports,inprogress,resolved,report-details}.html
  OK reports.html · OK inprogress.html · OK resolved.html · OK report-details.html (both offices)
  ```

- `node --check laravel/public/js/utils.js` → no syntax errors.
- `git diff --check` → clean (no whitespace errors).

**Not verified live — and this matters:** this clone has **no PHP binary, no
`laravel/vendor/` and no `laravel/.env`** (neither `php` nor
`C:\xampp\php\php.exe` exists on this machine), so `php -l`, `php artisan route:list`
and any live API call could **not** be run. The controller and route changes were
verified by static review only; the office pages were verified to parse, not by a
browser click-through.

## Impact / how to test by hand

1. **CEO/CENRO:** sign in, open **My Assigned Reports**, **Active Reports** and
   **Resolved Reports**. Each should briefly show a loading line, then the real
   reports assigned to your office (no more sample rows). The summary numbers on
   the Resolved page should reflect the loaded rows.
2. Open a report and use **Save Update** / **Mark as Resolved**. The request is
   `POST /api/admin/citizen-reports/CW-…/status`; on success the badge and
   timeline update and a confirmation shows. Reloading the page should keep the
   new status.
3. As a **CEO/CENRO** user, posting a status for a report assigned to the *other*
   office (or a validation/approval/rejection transition) should be refused with
   a 403/422 message.
4. **Offline sanity:** with no token (or the backend down) every list page still
   renders its built-in sample rows, and the detail pages still update locally.

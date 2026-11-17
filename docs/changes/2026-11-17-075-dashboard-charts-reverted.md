# Dashboard charts reverted — dashboard back to stat cards only

- **Date:** 2026-11-17
- **Commit:** `(uncommitted)`
- **Type:** refactor
- **Areas:** Laravel (admin web dashboard)

## Problem

At the user's request the admin dashboard should not carry charts. Changes 073
(charts added) and 074 (chart code moved into `js/dashboard-charts.js`) put a bar,
area and doughnut chart row onto `dashboard.html`; the user asked for that to be
undone.

## Cause

Not a defect — the feature was delivered as asked, then withdrawn by the user
before it was used in production.

## Changes

| File | Change |
|---|---|
| `laravel/public/dashboard.html` | Restored to its pre-073 content: `git revert` of `e460ae3` and `beb8d9e`. Diff against `ae379ae` is empty. |
| `laravel/public/js/dashboard-charts.js` | Deleted (it only existed for the dashboard charts). |
| `docs/changes/2026-11-17-073-dashboard-charts.md` | Deleted with the revert. |
| `docs/changes/2026-11-17-074-dashboard-charts-module.md` | Deleted with the revert. |
| `docs/changes/README.md` | Rows 073 and 074 removed by the revert; row 075 (this file) added. |

`analytics.html` and its five existing charts are untouched — they were already
in the project before 073. The standalone `dashboard-demo` repo is unaffected
(it is a separate local repository and was never pushed).

## Verification

- `git revert --no-commit beb8d9e e460ae3` applied with no conflicts.
- `git diff ae379ae -- laravel/public/dashboard.html docs/changes/README.md`
  → empty, i.e. the page is byte-identical to the state before 073.
- `grep -c "chart-card\|dashboard-charts\|Chart.js\|charts.css"` on
  `dashboard.html` → 0 matches.
- `laravel/public/js/` contains only `app.js`, `auth.js`, `dashboard.js`,
  `users.js`, `utils.js`.

**Not verified:** nothing was re-run in a browser, because the reverted page is
byte-identical to the version that was serving before 073 and does not depend on
any of the removed code.

## Impact / how to test by hand

1. Start the backend and log in as super admin, open `dashboard.html`.
2. The page should show stat cards, Recent Reports, the map, Recent Activity and
   Quick Actions — no chart cards, and no Chart.js download in the network tab.
3. Analytics (`analytics.html`) should still show its own five charts.

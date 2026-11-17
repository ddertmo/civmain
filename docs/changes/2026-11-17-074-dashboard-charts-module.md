# Dashboard charts moved out of dashboard.html into js/dashboard-charts.js

- **Date:** 2026-11-17
- **Commit:** `(uncommitted)`
- **Type:** refactor
- **Areas:** Laravel (admin web dashboard)

## Problem

The three dashboard charts added in 073 lived as ~340 lines of inline
`<script>` inside `laravel/public/dashboard.html`, mixed in with the page's map,
stat-card, activity-feed and auto-refresh code. The page file had grown to 1230
lines and the chart code could not be reused by the CEO/CENRO dashboards or read
in isolation.

## Cause

073 copied the pattern from `analytics.html`, which keeps its chart code inline.
Nothing in the codebase forced it — the project already loads shared behaviour
from `/js/*.js` (`app.js`, `utils.js`, `dashboard.js`, `users.js`).

## Changes

| File | Change |
|---|---|
| `laravel/public/js/dashboard-charts.js` | **New.** `DashboardCharts` module (same IIFE/global-object style as `Api`, `Utils`, `Skeleton`) exposing `init()`, `load()`, `setMode()`. Pure data helpers `bucketByPeriod()`, `dailySeries()`, `categoryCounts()` are separated from the three renderers; shared axis/tooltip options are built once instead of being repeated per chart; colours/ids are named constants. |
| `laravel/public/dashboard.html` | Inline chart block removed (343 lines); loads `/js/dashboard-charts.js` after `app.js`; boot calls `DashboardCharts.init()`; the two period buttons call `DashboardCharts.setMode(...)`. Chart markup, ids and the doughnut sizing CSS are unchanged. |

Behaviour is intentionally identical to 073 — same ids, same labels, same
colours, same empty states.

## Verification

- `node --check laravel/public/js/dashboard-charts.js` → exit 0.
- Inline script of `dashboard.html` extracted and `node --check`ed → exit 0.
- HTML tag-balance parse of `dashboard.html` → no unclosed or mismatched tags;
  903 lines (was 1230).
- Browser check against the mock API (no PHP/MySQL in this environment):
  `typeof DashboardCharts === 'object'` with exports `init, load, setMode`;
  `window.setVolumeMode` is now undefined (old global gone); all three canvases
  populated; weekly bar data `2026 W40…W47` = `[7,8,16,5,5,11,7,5]`; clicking
  **Monthly** retitled the card and regrouped to `Oct 2026`/`Nov 2026`;
  console had no errors.

**Not verified:** not run against the real Laravel backend, and the CEO/CENRO
dashboards do not use the module yet (they have no charts). Playwright is
intentionally not used in this project.

## Impact / how to test by hand

1. Start the backend and log in as super admin, open `dashboard.html`.
2. The three chart cards should look and behave exactly as before 074.
3. Toggle Weekly/Monthly — only the bar chart should rebuild.
4. With an empty database all three should still show their empty states.

# Analytics charts fit their cards on all three roles (Super Admin, CEO, CENRO)

- **Date:** 2026-11-17
- **Commit:** see the index row (backfilled in the following commit)
- **Type:** fix (UI layout)
- **Areas:** web panel — `analytics.html`, `offices/ceo/analytics.html`, `offices/cenro/analytics.html`, shared `css/charts.css`

## Problem

On the Analytics pages, the "Reports by Category" / "Status Distribution" doughnuts (Super Admin) and the "Status" / "Priority Distribution" doughnuts (CEO, CENRO) were drawn far larger than their cards. They overlapped titles, legends and the card below. The Status legend also ran past the right edge of its card.

The layout ignored screen size. The bottom row stayed at three columns and the CEO/CENRO summary strip at four columns at every width.

The role Dashboards (`dashboard.html`, `offices/*/dashboard.html`) have no Chart.js charts (stat cards and a Leaflet map), so they were not affected.

## Cause

1. **Doughnut feedback loop.**
   - The doughnuts use `responsive: true, maintainAspectRatio: false`, so Chart.js sizes the canvas to its parent `.doughnut-canvas`.
   - That box had no height. It was a `flex: 1` item centred in a row (`align-items: center`), so its height was the canvas' own height.
   - Chart.js and the box kept enlarging each other. The `style="height:200px"` was on the outer `.doughnut-wrap` and never reached the canvas.
2. **Grid tracks couldn't shrink.** `grid-template-columns: 1fr …` tracks and grid items (`min-width: auto`) cannot shrink below their content, so a wide legend or canvas pushed the card past its column.
3. **Breakpoints never applied.**
   - `responsive.css` targets `.analytics-top-row` / `.analytics-bottom-row`, but all three pages use `.analytics-grid-top` / `.analytics-grid-bottom`.
   - Each page also redefined those grids, and the CEO/CENRO `.summary-strip`, in its own `<style>` block. That block loads after the shared CSS, so even a correct shared media query would have lost.

## Changes

| File | Change |
|---|---|
| `laravel/public/css/charts.css` | See the bullets after this table. |
| `laravel/public/analytics.html` | Removed the page-local `.analytics-grid-top/-bottom` rules and the inline `style="height:200px"` on both `.doughnut-wrap` elements. Nothing else changed. |
| `laravel/public/offices/ceo/analytics.html` | Same, plus the page-local `.summary-strip` grid rule (now shared). |
| `laravel/public/offices/cenro/analytics.html` | Same as CEO. |

**`charts.css` changes:**
- **`.chart-card`:** `min-width: 0` and `overflow: hidden`, flex column, and `container-type: inline-size` so the doughnut layout can react to the card's own width.
- **`.doughnut-canvas`:** an explicit **200×200** box (170×170 when stacked), so Chart.js always gets a fixed size.
- **`.doughnut-wrap`:** `min-height: 200px`, so a long legend (7 statuses) grows the card instead of being clipped.
- **Legend:** shrinks, with ellipsis on long labels; values never wrap.
- **Grids:** `.analytics-grid-top/-bottom` (and the old `-row` names) defined once with `repeat(n, minmax(0, 1fr))`.
- **`.summary-strip`:** moved here from the office pages.
- **Breakpoints:**
  - ≤1280px: bottom row and summary strip go to 2 columns.
  - ≤1024px: 1 column.
  - ≤480px: summary strip goes to 1 column.
  - `@container` ≤400px: the doughnut stacks above its legend.

Chart.js options were already `responsive: true, maintainAspectRatio: false` on every chart, so no JS changed. The data, calculations, colours, legends, totals and features are unchanged.

## Verification (real browser, local app on MariaDB, signed in as each role)

At each width, cards and canvases were measured with `getBoundingClientRect`, and every element inside a card was checked for crossing the card edge.

| Page | 1920 | 1366 | 1100 | 768 | ~375 |
|---|---|---|---|---|---|
| Super Admin `analytics.html` | 2/3 cols; doughnuts 200×200, legend right; 0 overflow | 2/3; doughnuts 170×170 stacked; 0 | 2/2; 0 | 1/1; 0 | 1/1; 0 |
| CEO `offices/ceo/analytics.html` | strip 4, 2/3; 200×200; 0 | strip 4, 2/3; 170 stacked; 0 | — | — | strip 1, 1/1; 0 |
| CENRO `offices/cenro/analytics.html` | strip 4, 2/3; 200×200; 0 | strip 4, 2/3; 170 stacked; 0 | — | — | strip 1, 1/1; 0 |

- No page scrolls sideways at any tested width.
- Bottom-row cards are equal height within each row.
- Line and bar charts fill their card width at their fixed 220/200px height. (One 768px reading was taken mid-resize; re-measured after the resize settled.)
- No new console errors. The only errors were 401s from earlier failed logins.

**Local test accounts** (local MariaDB only, not in git):
- The CEO password was rotated, and a CENRO test user (`cenro@civilwatch.ph`) was created.
- Passwords are in `laravel/.env` as `SEED_CEO_PASSWORD` / `SEED_CENRO_PASSWORD`.

# Dashboard charts — reports volume, daily trend, category mix

- **Date:** 2026-11-17
- **Commit:** `(uncommitted)`
- **Type:** feature
- **Areas:** Laravel (admin web dashboard)

## Problem

The admin dashboard showed only stat cards, a recent-reports table, a map and the
activity feed. Anything chart-shaped (trend, volume over time, category split)
required opening the separate Analytics page, so the dashboard could not answer
"how are reports trending?" at a glance.

## Cause

The dashboard never loaded `charts.css` or Chart.js, and had no chart markup or
chart-builder code. The chart patterns existed only in
`laravel/public/analytics.html` (line chart, horizontal bar, doughnut) which is a
different page reached from the sidebar.

## Changes

| File | Change |
|---|---|
| `laravel/public/dashboard.html` | Links `/css/charts.css`; adds a `.dashboard-charts` row between the stat cards and the Recent Reports / Map row with three cards: a vertical **bar** chart of reports per week/month (Weekly/Monthly toggle), an **area** chart of submitted vs resolved over the last 30 days, and a **doughnut** chart of the category mix with a percentage legend. Loads Chart.js 4.4.0 from the same CDN version `analytics.html` uses, and adds `loadCharts()` (fetches `/api/admin/citizen-reports?limit=1000`, buckets client-side), `buildVolumeChart()`, `buildTrendAreaChart()`, `buildCategoryMixChart()` and `setVolumeMode()` to the page's inline script. |

Notes:

- No backend change was needed — the existing list endpoint already returns
  `submittedAt`, `resolvedAt`, `status` and `category`.
- Charts are loaded once on boot and are **not** in the existing 30 s
  auto-refresh loop, because that loop would re-fetch up to 1000 reports every
  30 seconds.
- Charts degrade to an empty "No data" state (and the doughnut to a grey ring)
  when the API fails or returns nothing, matching the analytics page behaviour.

## Verification

- `node --check` on the page's inline script (extracted with Python) → exit 0,
  no syntax errors.
- HTML tag-balance parse of `dashboard.html` (Python `html.parser`) → no
  unclosed or mismatched tags.
- Static serve of `laravel/public` (`python3 -m http.server 8899`) → `dashboard.html`,
  `css/charts.css`, `css/cards.css`, `js/app.js`, `js/utils.js` all returned 200.

- Browser check on the cloud preview, against a temporary mock of the four
  `/api` endpoints (64 synthetic reports; PHP/MySQL are not installed in that
  environment):
  - bar chart rendered weekly buckets `2026 W40…W47` = `[7,8,16,5,5,11,7,5]`;
  - area chart rendered 30 daily points (33 submissions in range);
  - doughnut legend read `Infrastructure 50.0% | Environmental 50.0% | 64 Total`;
  - clicking **Monthly** regrouped to `Oct 2026`/`Nov 2026` = `[45,19]` and
    retitled the card;
  - console had no errors.
- The browser check caught one real bug: the doughnut canvas grew to 707×300 and
  overflowed its 200px wrapper, because Chart.js sized it from the very wide
  flex child. Fixed with `.dashboard-charts .doughnut-canvas { height:100%;
  min-width:0 }` and a 240px `max-width`/`max-height` on its canvas — measured
  200×200 afterwards.

**Not verified:** nothing was run against the real Laravel + MySQL backend (no
PHP/MySQL in this environment) and tooltips were not hovered. The mock used the
same response shape the code reads (`submittedAt`, `resolvedAt`, `status`,
`category`), so one pass against the live backend is still worth doing.
Playwright is intentionally not used in this project.

## Impact / how to test by hand

1. Start the backend (`RUN_BACKEND.bat` / `php artisan serve`) with MySQL up.
2. Log in as a super admin and open `http://127.0.0.1:8000/dashboard.html`.
3. Below the five stat cards there should be three chart cards: a bar chart, a
   submitted-vs-resolved area chart for the last 30 days, and a category-mix
   doughnut whose legend shows percentages and the total.
4. Click **Monthly** on the first card — bars should regroup by month and the
   title should change to "Reports Submitted (Monthly)".
5. With no reports in the database, all three should render their empty state
   instead of erroring (check the browser console for `Charts data failed`).

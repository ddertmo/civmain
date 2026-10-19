# The admin Monitoring Report Details page ignored Dark Mode — hardcoded light palette in its inline CSS

- **Date:** 2026-10-19
- **Commit:** `f45c762`
- **Type:** fix
- **Areas:** Laravel

## Problem

Opening `monitoring-report-details.html?id=3&from=monitoring` with the admin's
Dark Mode active (`data-theme="dark"`, set by `js/app.js`) still rendered the
whole detail view in light colours: `#F5F8FC` page canvas, `#FFFFFF` cards,
`#0F2D62` title, `#111827`/`#64748B` ink — reported by the user 2026-10-19.
Everywhere else on the page (sidebar, navbar, shared cards/badges) did go dark,
so the page looked half-themed.

## Cause

Not a toggle bug — the page **does** load `/js/app.js` (line 375), so the
navbar's dark-mode button and `loadTheme()` run fine and the shared stylesheets
(`variables.css` + `[data-theme="dark"]` overrides) respond. The defect is in
the page's own `<style>` block (lines 28–131): it hardcodes the light palette
in ~20 `.rd-*` rules instead of using the CSS custom properties, so none of the
page-specific content (canvas, cards, title, badges, summary tiles, status
options, quick-info labels) could follow the theme. One `<label>` additionally
carried an inline `color:#111827`.

The CENRO/CEO `offices/*/report-details.html` pages already solved this exact
problem with `[data-theme="dark"] .selector { … }` blocks appended to their
inline styles — that pattern was copied.

## Changes

| File | Change |
|---|---|
| `laravel/public/monitoring-report-details.html` | Appended a `DARK MODE (063)` override block (29 lines) before `</style>`, mapping every hardcoded `.rd-*` colour to its token (`--white`, `--gray-50/200/500/900`, `--primary`, `--status-assigned-bg/border`, `--shadow-sm`); inline `color:#111827` on the Notes label → `var(--gray-900)` |

**29 additions + 1 value swap.** The overrides only match under
`[data-theme="dark"]`, so **Light Mode is byte-identical** (the one swap is
equal-value: `--gray-900` is `#111827` in the light theme). No script, PHP or
shared-CSS changes; status-option fill circles (`#FFFFFF` dot inside
amber/blue/green chips) were mapped, while the coloured chips themselves and
the white icon-on-fill stay as-is by design (same both themes). The Leaflet
map keeps standard light tiles (admin-wide behaviour, unchanged elsewhere).

## Verification

- **Static:** `git diff` reviewed — only the appended dark block and the one
  equal-value token swap; no light-mode rule altered. No leftover named/light
  colours remain in the page (`grep` for `color:white|black|rgba(2xx,2xx`
  → empty).
- **Served:** `GET http://127.0.0.1:8000/monitoring-report-details.html` →
  200, and the delivered HTML **contains the override block and the label
  fix** (Laravel serves `public/` from disk — no rebuild/refresh needed beyond
  reloading the page).
- **Contrast:** every mapping reuses tokens already shipped across the admin
  (e.g. `--gray-900` #EDF2F7 text on `--white` #1E2330 card in dark ≈ 13:1;
  `--primary` #4D8EFF on `--status-assigned-bg` ≈ 6:1) — no new colour pair
  was introduced.
- **Not verified:** the rendered look in a browser — this session has no
  screen access (Playwright removed from the project); needs the user's
  hand-check.

**Same-defect audit (grep over `laravel/public`, hexes > 0 and zero
`data-theme` overrides):** `analytics.html` (35), `assign-office.html` (60),
`monitoring.html` (29), `report-details.html` (26), `gis-map.html` (17),
`dashboard.html` (13), `offices/cenro/reports.html` (13),
`offices/ceo/reports.html` (13), `offices/cenro/resolved.html` (7),
`offices/ceo/resolved.html` (7) — plus `index.html`/`resolved-reports.html`
with only 2 hexes each (likely decorative, unverified). `pending-reports.html`
is clean (0 hexes). **Reported to the user; not yet fixed** — awaiting
approval before touching them.

## Impact / how to test by hand

1. Admin (`RUN_BACKEND.bat` / `php artisan serve`, already running on
   `127.0.0.1:8000`), flip the **navbar moon button** into Dark Mode.
2. Open `monitoring-report-details.html?id=3&from=monitoring` from the
   Monitoring list — reload if it was already open.
3. Dark: canvas `#161B27`, cards `#1E2330`, title/labels light, blue badges
   on their tinted background. Light: looks exactly as before.
4. Same page in Light Mode must be unchanged (byte-level CSS guarantee above).

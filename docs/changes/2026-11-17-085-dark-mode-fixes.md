# Dark mode: no light flash, readable secondary text, themed charts, missing dark colours

- **Date:** 2026-11-17
- **Commit:** `cde3ba2`
- **Type:** fix (UI / theme)
- **Areas:** web panel — every panel page's `<head>`, `css/variables.css`, `css/global.css`, `css/*.css`, `js/utils.js`, `js/app.js`, office pages

## Problem

Problems found by turning dark mode on and measuring each page's colours and text contrast in a real browser:

1. **Light flash on every page load.** Each page first painted in light mode, then switched to dark, and elements with a 0.2s `transition` visibly animated from white to dark.
2. **Secondary text too dim.** Timestamps, subtitles, "Total", hints and empty states were 2.9:1 on dark cards (minimum for small text is 4.5:1).
3. **Charts kept light-theme colours.** Grey tick labels, a near-white grid on the trend chart, an invisible black grid on the bar charts and white doughnut borders, on the Super Admin, CEO and CENRO analytics pages. They also did not change when the theme was toggled.
4. **Colours with no dark version:**
   - 7 badge types (Approved, Rejected, Others, Submitted, priority Low/Medium/High)
   - `--status-validation-bg` / `--status-approved-*`
   - the report-detail cards (`.rd-*`), hard-coded white in `report-details.html` and `monitoring.html`
   - the dashboard's purple notifications icon
   - the CEO/CENRO page colours `--office-color` / `--office-light`
5. **White text turned dark** (found while verifying). 26 places used `color: var(--white)` for white text on a coloured fill: primary and success buttons, active chips and toggles, the sidebar, toasts, badges and avatars. Dark mode redefines `--white` as the dark card colour, so that text became dark on dark. Example: the sidebar "SA" avatar measured 1.1:1.

## Cause

1. **Late theme.** `data-theme` was only set by `App.init()` on `DOMContentLoaded`, and no page set it earlier.
2. **Dim grey.** The dark value of `--gray-400` was `#5A6B85`.
3. **Canvas ignores CSS.** Chart.js draws on `<canvas>`, so CSS variables never reach it, and the pages hard-code light colours in their chart configs.
4. **Incomplete dark coverage.** Hard-coded hex colours and page-local variables were never given dark overrides, and the dark variable block missed three status variables.
5. **One variable, two jobs.** `--white` was used both as the surface colour and as the white-text colour.

## Changes

| File | Change |
|---|---|
| 27 panel pages (all except `index.html`) | A tiny inline script at the top of `<head>` applies a saved dark theme before anything is drawn. It uses the same `theme` localStorage key as `App.loadTheme`. Marked `cw-theme-early`. |
| `css/variables.css` | See the bullets after this table. |
| `css/global.css` | Dark versions of `.badge-approved`, `-rejected`, `-high`, `-others`, `-submitted`, `-low` and `-medium`. Shared dark rules for the `.rd-*` report-detail layout (only one of its three pages had them). Dark `.qa-notif` icon. In dark mode, blue fills with white text (`.btn-primary`, active toggle/chip, `.navbar-avatar`) use `--primary-dark` (`#2B6FE8`, 4.6:1) instead of the link blue (`#4D8EFF`, 3.2:1 under white text). |
| `css/{badges,buttons,charts,forms,global,navbar,sidebar,style,tables}.css`, `assign-office.html`, `index.html` | All 26 `color: var(--white)` changed to `color: var(--on-accent)`. Backgrounds and borders that use `--white` (the surface) are unchanged. |
| `js/utils.js` | New `CwChartTheme`. In dark mode it re-colours Chart.js axis ticks, grids, legends and doughnut borders from the CSS variables. In light mode it restores each chart's own original colours. A registered Chart.js plugin applies it to new charts; `refreshAll()` re-colours existing ones. |
| `js/app.js` | `toggleDarkMode()` calls `CwChartTheme.refreshAll()`. |
| `report-details.html`, `monitoring.html` | The "Notes (Optional)" label's inline `color:#111827` is now `var(--gray-900)`, as already used in the third copy of the page. |
| `offices/{ceo,cenro}/{analytics,inprogress,report-details}.html` | A dark override next to each page's `--office-color` / `--office-light`. CEO uses `#4D8EFF`; CENRO uses `#34D399` with translucent tints. |

**`css/variables.css` changes:**
- **Secondary text:** dark `--gray-400` `#5A6B85` → `#8494AC` and `--gray-500` `#7A8FA8` → `#93A4BA`. That is ≥4.5:1 on cards and panel surfaces, with the scale still ordered.
- **Missing status colours:** added dark `--status-validation-bg` and `--status-approved{,-bg,-border}`.
- **New `--on-accent: #FFFFFF`:** white for text and icons on coloured fills. It is never redefined for dark mode.

Light mode is unchanged. Every new rule is scoped to `[data-theme="dark"]`, `--on-accent` equals the old light `--white`, and the chart helper restores the original light colours. A fresh light load was compared with light → dark → light and was identical on every axis of every chart.

## Verification (real browser, local app)

- **Charts (Super Admin analytics):**
  - In dark mode on load, ticks are `#93A4BA`, the grid is a faint translucent blue-grey, and doughnut borders are the card colour.
  - Toggling to light restores the exact original colours; toggling to dark re-applies the dark ones.
- **Contrast scan in dark mode** (every text element vs. its background, plus light surfaces):
  - The Dashboard, Pending Reports and Monitoring pages: no light surfaces, and no text below 4.5:1 apart from the avatar, which is fixed below.
  - The Super Admin report-details page: the only items left are two at 4.5, the red "Confirm Reject" button at 4.2 (white on brand red) and a 3.2 that the `--primary-dark` rule addresses.
- **White-text fix:** after `--on-accent`, the sidebar avatar and primary button text measure `rgb(255,255,255)`.
- **CEO report-details:** the scan found office-coloured text at 2.5–2.8:1 and two pastel boxes. That led to the `--office-*` dark overrides. The page was **not re-measured after this fix**, because the user was using the browser pane at the time.
- **Scripts:** `js/utils.js` and `js/app.js` compile, and the served CSS and pages contain the new rules.
- **Not checked:** CENRO pages in dark mode in a browser, a pixel-level flash test (verified structurally: the theme script is the first thing in `<head>`, before every stylesheet), and the login page, which stays light by design.

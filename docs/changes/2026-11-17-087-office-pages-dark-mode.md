# Dark mode on the CEO / CENRO office pages

- **Date:** 2026-11-17
- **Commit:** see the index row (backfilled in the following commit)
- **Type:** fix (UI / theme)
- **Areas:** web panel — `css/global.css`, the `<head>` of all 27 panel pages (early theme script), `offices/{ceo,cenro}/report-details.html` and the office pages' office-colour overrides

## Problem

Signed in as the CEO with dark mode on, all 8 office pages were scanned for light surfaces and for text below 4.5:1 contrast against its background:

| Page | Findings |
|---|---|
| Dashboard | Light pastel stat-icon tiles (`#EFF6FF`, `#FFF7ED`, …); dark-blue icon at 2.5:1 |
| My Reports | Light status tile; "Medium" priority badge text at 2.9:1 |
| In Progress | Light `.active-stat-icon` tiles; orange icon at 2.6:1 |
| Resolved | Light green tile; green icon at 2.4:1 |
| Map | Active filter chip: white text on light blue at 3.2:1 |
| Analytics | Light `.summary-icon` tiles; active Weekly toggle at 3.2:1 |
| Report Details | Light blue tile and light modal icon; "Confirm" button (white on office colour) at 2.5:1 |
| Settings | clean |

## Cause

- **Hard-coded pastels with no dark versions.** The office pages set light pastel colours in two ways:
  - their own `<style>` classes: `.office-stat-icon.*`, `.active-stat-icon.*`, `.status-pill.*`, `.summary-icon.*`, `.priority-badge.*`, `.modal-icon-wrap`, `.success-banner`, `.resolved-badge`;
  - inline `style=""` attributes written by page scripts (the `statusBg` maps, and tiles in `resolved.html` and `report-details.html`).
- **One office colour for text and fills.** `--office-color` is used both for text on cards and for fills carrying white text. Change 085 brightened it for text in dark mode, which made white text on those fills unreadable, and worse still for CENRO green.
- **Older dark rules overriding 085's fix.** `global.css` had earlier `[data-theme="dark"] .chip.active` / `.toggle-btn.active` rules using `--primary`. They came later in the file, so they overrode 085's `--primary-dark` fix.
- **Missing office colours.** Most office pages (dashboard, map, reports, resolved, settings) don't define the office colours at all, and global CSS couldn't tell a CEO page from a CENRO page.

## Changes

| File | Change |
|---|---|
| All 27 panel pages (`cw-theme-early` script) | It now also sets `data-role` on `<html>` from `cw_role` before first paint, alongside `data-theme`. |
| `css/global.css` | See the bullets after this table. |
| `offices/{ceo,cenro}/{analytics,inprogress,report-details}.html` | Removed the per-page dark office-colour lines added in 085; they're now in `global.css`. |
| `offices/{ceo,cenro}/report-details.html` | The 7 white-text fills use `var(--office-fill, var(--office-color))`. Light mode is unchanged, because `--office-fill` is only defined for dark. |

**`css/global.css` changes:**
- **Office colours by role:**
  - `[data-theme="dark"][data-role="ceo"]`: `--office-color` `#4D8EFF` (text), `--office-light` (a translucent tint) and `--office-fill` `#2B6FE8` (fills under white text, 4.6:1).
  - `[data-role="cenro"]`: `#34D399`, a translucent tint, and `--office-fill` `#047857`.
- **Dark tints for the pastel classes:** each class above gets a translucent tint and a bright foreground (orange, amber, green, purple, red and the office colour).
- **Inline pastels:** caught by colour value (`[style*="#FFF7ED" i]`, and so on for 10 pastel colours) with `!important`. Icons inside those tiles inherit the tile's bright colour.
- **Older chip/toggle rules:** the earlier `.chip.active` and `.toggle-btn.active` dark rules now use `--primary-dark`.

## Verification (real browser, local app, signed in as CEO, dark mode)

- **Re-scanned after the fix:**
  - **Dashboard, My Reports, In Progress:** no light surfaces and no text under 4.5:1.
  - **Resolved:** one icon at 2.5:1 remained, because its own inline colour sat inside a fixed tile. The icon-inherit rule fixes it, but that page **wasn't re-scanned afterwards**.
- **Not re-scanned after the fix:** Map, Analytics, Report Details and Settings. The user started using the browser pane and switched the theme to light mid-scan. Every finding from their first scan maps to a specific rule above.
- **Not done:** CENRO pages. Scanning them would have meant signing in as CENRO, which signs the CEO session out of the user's pane. They share the same pages, classes and rules, with their own green `--office-*` values.
- **Light mode:** unaffected. Every new rule is scoped to `[data-theme="dark"]`, and `--office-fill` falls back to `--office-color`.

# CEO / CENRO dark mode: all 16 office pages verified, last two gaps fixed

- **Date:** 2026-11-17
- **Commit:** `6e2cab7`
- **Type:** fix + verification (follow-up to 087)
- **Areas:** web panel — `css/global.css`

## Problem

Change 087 left four CEO pages (Map, Analytics, Report Details, Settings) not re-checked after its fix, a fifth (Resolved) re-checked only before its last fix, and all 8 CENRO pages unscanned. Completing the scan found two last gaps:

1. **Green action buttons.** "Confirm Resolve" (both offices) and CENRO's "Save Update" are styled inline with `background:#10B981` and white text: 2.5:1.
2. **Grey "Unavailable" pill.** On the report-details error state it is written inline with `background:#F3F4F6; color:#6B7280`, so it stayed a light-grey pill in dark mode.

## Changes

| File | Change |
|---|---|
| `laravel/public/css/global.css` | `[data-theme="dark"] .btn[style*="background:#10B981" i]` uses `#047857` (white text 5.5:1). `[style*="background:#F3F4F6" i]` and `…#F9FAFB` use `--gray-100` with `--gray-700` text. Both are dark-only. |

## Verification (real browser, local app, dark mode, signed in as each role)

The same scan was run on every page: all visible elements, light backgrounds, and text contrast against the effective background.

| Role | Dashboard | My Reports | In Progress | Resolved | Map | Analytics | Report Details | Settings |
|---|---|---|---|---|---|---|---|---|
| CEO | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ (charts themed) | ✔ (after the green-button rule) | ✔ |
| CENRO | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ (charts themed) | ✔ (after the grey-pill rule) | ✔ |

✔ = no light surfaces and no text below 4.5:1.

- **Role colours (CENRO, dark):**
  - `--office-color` is `#34D399`;
  - `--office-light` is `rgba(16,185,129,0.15)`;
  - `--office-fill` is `#047857`.
- **Light mode, same page:** `--office-color` is the original `#10B981`, the green button is the original `#10B981`, cards are white, and `--office-fill` is undefined (it falls back to `--office-color`).
- **Limits:**
  - CENRO has no assigned reports in the local DB, so its lists and the report-details page were scanned in their empty/error states.
  - Its tiles, pills, badges, buttons and charts all rendered and passed.
- **Session:** the user's pane was returned to the CEO account in light mode afterwards.

# Three Dark Mode status tints sat just under 3:1 — Submitted, Assigned, Approved darkened

- **Date:** 2026-10-19
- **Commit:** `9525085`
- **Type:** fix
- **Areas:** Flutter

## Problem

In Dark Mode, the icon/text drawn in a status colour on its matching tint
background was below the **3:1** WCAG minimum for non-text/graphical objects:

| Status | accent on dark tint | before |
|---|---|---|
| Submitted | `#7C3AED` on `#2E1065` | **2.67:1** |
| Assigned | `#2563EB` on `#172554` | **2.84:1** |
| Approved | `#0E7C7B` on `#042F2E` | **2.88:1** |

The other four were already fine (Pending 6.97:1, In Progress 4.40:1, Resolved
4.52:1, Rejected 3.34:1), as was all of Light Mode. It shows everywhere
`context.c.statusColor` is drawn on `context.c.statusBg` — notification card
icon circles, status chips, Home summary tiles, report status badges. Known and
deliberately deferred on 2026-10-16 during [057](2026-10-16-057-dark-mode-contrast-home-notifications.md);
picked up as `docs/TASKS.md` task 3 and approved 2026-10-19.

## Cause

A palette-values problem, not a code bug: the three dark `statusXBg` tints in
`AppPalette.dark` were not dark enough for their (fixed, mid-dark) accent hues.
No existing darker token was wrong or missing — the task entry correctly said
this needs a values change, not a new token, and must not be folded into any
other task.

Design constraint found while picking the values: the purple/blue/teal accents
are intrinsically dark, so their contrast is **ceiling-capped** — even on pure
black, purple `#7C3AED` only reaches ~4.0:1 and blue `#2563EB` ~4.2:1. Matching
the 4.4–4.5:1 of Resolved/In Progress was therefore impossible without
abandoning the tint (black), so the target was the already-accepted band:
Rejected ships at 3.34:1 and was declared fine.

## Changes

| File | Change |
|---|---|
| `civ-main/lib/core/constants/app_colors.dart` | `AppPalette.dark`: `statusSubmittedBg` `#2E1065` → `#1C0A3D`, `statusAssignedBg` `#172554` → `#0E1632`, `statusApprovedBg` `#042F2E` → `#021C1C` (all ×0.6 of the old RGB), each line carrying a comment with the old and new ratio |

**3 lines, values only.** Light Mode untouched. `statusXBorder` tokens untouched
(re-checking them against the darker tints: AssignedBorder `#1D4ED8` = 2.52:1,
ApprovedBorder `#115E59` = 2.27:1 vs the new tints — decorative edges, slightly
*more* visible than before). White text drawn on these tints elsewhere stays
16.9–18.1:1. The three old hexes appeared nowhere else in `lib/` or `test/`
(grep-verified), and `theme_tokens_test.dart` checks lookup identity, not
values, so no test needed changing.

## Verification

- **Contrast**, computed with the WCAG 2.x relative-luminance formula on the
  exact token values: Submitted `#7C3AED` on `#1C0A3D` = **3.18:1**; Assigned
  `#2563EB` on `#0E1632` = **3.44:1**; Approved `#0E7C7B` on `#021C1C` =
  **3.53:1** — all ≥ 3:1, in the same band as the accepted Rejected 3.34:1.
  (Verified the formula reproduces the recorded "before" numbers 2.67 / 2.84 /
  2.88 first.)
- `flutter analyze --no-pub` → **109 issues, 0 errors** (baseline). Note: this
  run, and the tests below, were executed with both this change **and** the
  companion guest-toggle change (062) in the working tree; they are independent
  and were committed separately.
- `flutter test test/theme_tokens_test.dart` → **+4 All tests passed**.
- `flutter test` → **+4 −1**, only the documented pre-existing
  `widget_test.dart` failure.
- `flutter build web` → **`√ Built build\web`**.
- **Not verified:** the visual look of the new tints in a browser — Playwright
  was removed from this project and this session has no screen access; needs a
  human hand-check.

## Impact / how to test by hand

1. Flutter app (`RUN_APP.bat` or `flutter run -d chrome`), switch to **Dark
   Mode**.
2. Home summary tiles, notification cards, My Reports / track screens and the
   status-update stepper: check the **Submitted (purple), Assigned (blue) and
   Approved (teal)** icon circles/chips — the accent should read clearly on its
   tint now, and the tints should still be recognisably purple/blue/teal rather
   than black.
3. Compare against **Pending / In Progress / Resolved / Rejected** — same
   feel, no odd-one-out.
4. **Light Mode must look byte-identical** (palette light block untouched).
5. When the hand-check passes, check off task 3 in `docs/TASKS.md`.

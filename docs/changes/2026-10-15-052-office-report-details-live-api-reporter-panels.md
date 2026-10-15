# CEO/CENRO report details load from the API; reporter panels show name, address and phone

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** feature
- **Areas:** Laravel web admin / API

## Problem

The CEO and CENRO report-detail pages were **static prototypes**. Opening
`offices/cenro/report-details.html?ref=CW-2026-00125` (or the CEO equivalent)
rendered the incident fields and the reporter card from hardcoded sample maps,
so every office user saw the same made-up name, phone and address no matter
which report they opened. **Priority was never shown at all.**

Separately, the reporter panels did not show the reporter's real home address:

- The office "Citizen / Reporter Information" card showed a single hardcoded
  sample address string.
- The admin/monitoring "View Reporter Details" panel showed only the **home
  barangay**, even though the API already returned the citizen's street,
  purok, city and province captured at registration (change 048).

## Cause

- `laravel/public/offices/cenro/report-details.html:757` and
  `laravel/public/offices/ceo/report-details.html:793` read the `?ref=` query
  param and looked it up in local `REPORTS` / `CITIZENS` objects. There was no
  `Api.get` call anywhere in the page.
- `AdminCitizenReportController::show(int $id)` accepted **only a numeric id**,
  but the office pages link by **reference number** (`CW-2026-00125`). Even a
  direct API call with the reference would have failed (`int` type coercion →
  `TypeError`/500).
- `CitizenReport::toApiArray()` (`laravel/app/Models/CitizenReport.php:233`)
  already exposed `citizen.street / purok / barangay / city / province`, but the
  reporter panels rendered only `citizen.barangay` (admin/monitoring) or the
  sample string (office).

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php` | `show(int $id)` → `show(string $id)`, and it now accepts **either** the numeric id **or** the reference number: `ctype_digit($id) ? findOrFail((int) $id) : where('reference_number', $id)->firstOrFail()`. Lets `?ref=CW-…` links resolve. |
| `laravel/public/js/utils.js` | New `Utils.fullAddress(citizen)` helper — composes `street, purok, barangay, city, province`, skipping blanks, and returns `—` when empty. One source of truth for every reporter panel. |
| `laravel/public/offices/cenro/report-details.html` | Incident card gains a **Priority** row (`#infoPriority`). The script replaces the hardcoded maps with a live `Api.get('/api/admin/citizen-reports/' + ref or id)`, maps the API shape in `fromApi()` and renders it in `render()`; it **falls back** to the built-in sample record when there is no backend/token, so the page still previews. The reporter card now shows name, full home address and contact number. |
| `laravel/public/offices/ceo/report-details.html` | Same wiring, with its own defaults (Infrastructure sample, blue map pin). Removed the now-duplicate `const ref` line. |
| `laravel/public/report-details.html` | "View Reporter Details" panel is now exactly **Name, Home Address, Phone**. Home Address uses `Utils.fullAddress(citizen)` instead of just the barangay. |
| `laravel/public/monitoring.html` | Same reporter panel (inline monitoring view). |
| `laravel/public/monitoring-report-details.html` | Same reporter panel (dedicated monitoring detail page). |

Note: an intermediate revision of this change added the citizen **email** to the
API and the panels; it was reverted at the user's request — the reporter view is
deliberately **name, address, phone only**. `CitizenReport.php` is therefore
unchanged from HEAD.

## Verification

- `php -l laravel/app/Http/Controllers/Admin/AdminCitizenReportController.php`
  → `No syntax errors detected`.
- All five pages parsed with Python `html.parser`, and **every** inline
  `<script>` block passed `node --check` (Node v24.21.0) → `RESULT: ALL PASS`.
- `git diff --check` → clean (no whitespace errors).
- Repo-wide search confirms no `reporterDetailEmail` / `citizenEmail` /
  `citizen.email` remains in any reporter panel (the only `Email Address` hit is
  the unrelated staff-account form in `users.html`).

**Not verified live — and this matters:** the running `:8000` backend serves a
**different checkout** (`C:\Users\Administrator\Downloads\sereno_civmain\laravel`,
same HEAD `eed7639`), not this fresh clone, and this clone has no `laravel/.env`
or `laravel/vendor/`. So the one live API call that was made
(`GET /api/admin/citizen-reports/5`) exercised the **pre-change** code (numeric
id worked; reference-number lookup returned 500 on the old `int $id`). The new
reference-number path and the office-page fetch were verified by PHP lint and
static review only, not by a live browser click-through.

## Impact / how to test by hand

1. **CEO/CENRO:** sign in, open a report from the list. The Incident Information
   card (including **Priority**) and the Citizen / Reporter Information card
   (name, home address, contact number) should load from the API.
2. **Super Admin → Monitoring → View a report → View Reporter Details**, and the
   same panel on the Pending Reports detail view: confirm it shows exactly
   **Name, Home Address, Phone** (no email).
3. **Offline sanity:** with no token (or the backend down) both office pages
   still render the built-in sample record instead of erroring.

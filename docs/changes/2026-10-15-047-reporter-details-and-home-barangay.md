# Reporter details panel in admin report views + Home Barangay fix

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** feature
- **Areas:** Laravel web admin

## Problem

Admins and offices could only see the reporter's name (with a "Hidden from
public" badge) and had no way to reveal the rest of the reporter's contact
information when verifying a report.

Then, once the panel was added, the "Location" field was populated with the
report's `barangay` — which is **where the incident was reported**, not where
the citizen lives. Staff could mistake the incident location for the reporter's
residence.

## Cause

- The reporter card only rendered `reporterName`.
- The API already returns both locations separately:
  - `data.barangay` → incident location (`CitizenReport.barangay`)
  - `data.citizen.barangay` → reporter's residence (`Citizen.barangay`)
  - See `laravel/app/Models/CitizenReport.php:233-237`.
- The first revision of the panel used `data.barangay` (incident) for the
  reporter's location instead of `data.citizen.barangay`.

## Changes

| File | Change |
|---|---|
| `laravel/public/report-details.html` | Added a toggleable **View Reporter Details** panel (name, phone, home barangay) under the Reporter card; populated from `citizen.*`. |
| `laravel/public/monitoring-report-details.html` | Same reporter panel on the dedicated Monitoring detail page. |
| `laravel/public/monitoring.html` | Same reporter panel in the inline Monitoring report view. |
| `laravel/public/offices/cenro/report-details.html` | Added a **Citizen / Reporter Information** card (name, contact, email, home address) matching the admin layout. |
| `laravel/public/offices/ceo/report-details.html` | Same citizen/reporter card. |

Key detail: the reporter's "Home Barangay" uses `citizen.barangay` (residence),
while the incident location keeps using the report's `barangay`.

## Verification

- `html.parser` parse succeeded for all five pages.
- `node --check` passed for every inline script block in all five pages.
- `check_pc.ps1` → `RESULT: ALL 12 CHECKS PASSED`; git sync to `backup/main`.

Not verified: live browser click-through; the CEO/CENRO detail pages are
static prototypes, so their reporter data is sample data.

## Impact / how to test by hand

1. Super Admin → Monitoring → **View** a report → **View Reporter Details**.
   Confirm Name, phone, and Home Barangay (residence, not incident location).
2. Super Admin → Pending Reports → open a report → **View Reporter Details**.
3. CENRO/CEO → report details → confirm the **Citizen / Reporter Information**
   card shows the reporter's home address, distinct from the incident location.

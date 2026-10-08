# Change 028 — QA: login creds + users table from API

**Date:** 2026-10-08  
**Commit:** `HEAD` (uncommitted changes)  
**Type:** fix  
**Areas:** admin-web

## Problem
- Login demo note/auto-fill used wrong creds (`admin@civilwatch.gov.ph/admin123`); real seeded are `admin@civilwatch.ph/Admin@2026!` (+ CEO `ceo@civilwatch.ph/Ceo@2026!`).
- `users.html` rendered a hardcoded fake table (`usersData` with AD-2026 IDs, May 2026 dates).

## Cause
- Stale copy in `index.html` note + auto-fill.
- `users.html` kept demo rows after already fetching `/api/admin/users` for stats.

## Changes
| Path | What | Why |
|---|---|---|
| `laravel/public/index.html` | Correct demo note + auto-fill to `admin@civilwatch.ph / Admin@2026!`. | Matches seeded users in DatabaseSeeder. |
| `laravel/public/users.html` | Replace static usersData with API mapping: fetch `/api/admin/users`, map to UI shape (super_admin/ceo/cenro → Administrator), compute stats, render table; handle nulls/safe indexing. | Use real data; drop invented rows. |

## Verification
- Static HTML/JS review only.
- API call uses existing `cw_token` and same endpoint already used for stats.

## Impact
- Login hint is accurate; users page shows real users from DB.

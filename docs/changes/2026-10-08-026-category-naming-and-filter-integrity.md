# Change 026 — QA: category naming + filter integrity

**Date:** 2026-10-08  
**Commit:** `HEAD` (uncommitted changes)  
**Type:** fix  
**Areas:** admin-web

## Problem
- Category filters used `value="Environmental"` while API canonical is `Environment` (or vice versa), breaking exact matches.
- `categoryBadge` misclassified non-Infrastructure as "Environmental" (including `Others`).

## Cause
- `pending-reports.html` and `monitoring.html` catFilter option values didn’t align with canonical `Environment`.
- `utils.js::categoryBadge` returned "Environmental" for any non-Infrastructure.

## Changes
| Path | What | Why |
|---|---|---|
| `laravel/public/js/utils.js` | Canonical `categoryBadge`: `Environment`/`Environmental` → "Environmental"; `Others` → "Others". | Correct labeling; don’t flatten Others to Environmental. |
| `laravel/public/pending-reports.html` | `catFilter` values: `Environment`, add `Others`. | Match API values + allow filtering Others. |
| `laravel/public/monitoring.html` | `catFilter` values: `Environment`, add `Others`; `normCategory` already maps `environment`→`Environmental` (kept); filter uses both. | Improve robustness. |
| `laravel/public/gis-map.html` | Filter: accept `Environmental` or `Environment`. | Handle both forms. |

## Verification
- Static HTML/JS review only.
- Filtering logic checks exact match against `r.category` (API) or normalized forms.

## Impact
- Category dropdowns filter correctly; `Others` no longer shows as "Environmental".

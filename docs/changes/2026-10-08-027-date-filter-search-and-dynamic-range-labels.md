# Change 027 — QA: real date filter + search + dynamic range labels

**Date:** 2026-10-08  
**Commit:** `HEAD` (uncommitted changes)  
**Type:** fix  
**Areas:** admin-web

## Problem
- Date range was hardcoded/static in pending/monitoring/analytics/resolved.
- Search didn’t include `reference_number`.
- Labels didn’t reflect actual data ranges.

## Cause
- Static HTML text; no from/to inputs; applyFilter only used search/cat/brgy.
- Search strings missed `r.referenceNumber/reference_number`.

## Changes
| Path | What | Why |
|---|---|---|
| `laravel/public/pending-reports.html` | Add from/to `<input type="date">`, dynamic `#dateRangeText`, search includes `reference_number`; applyFilter applies date range (inclusive). | Real filtering, fix misleading label, include ref search. |
| `laravel/public/monitoring.html` | Add from/to date inputs + dynamic label; `applyFilter` handles date, search includes `ref`, `normCategory` fallback on matchCat. | Same as pending. |
| `laravel/public/analytics.html` | Replace static label with computed min–max of `submittedAt/created_at` from loaded reports. | Reflects actual dataset. |
| `laravel/public/resolved-reports.html` | Replace static label with computed min–max of `resolvedAt/resolved_at` from loaded reports. | Reflects resolved range. |

## Verification
- Static JS only. Filtering logic uses ISO-like `T00:00:00` start and `T23:59:59.999` end.
- No API changes.

## Impact
- Reports can be filtered by date range; search matches reference numbers.

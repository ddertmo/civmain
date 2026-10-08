# Change 029 — QA: polish + cleanup + docs

**Date:** 2026-10-08  
**Commit:** `HEAD` (uncommitted changes)  
**Type:** chore  
**Areas:** admin-web, docs

## Problem
- Missing "Clear Filters" on some pages; assign-office recommendation lacked explicit reason; orphaned mock JSON files under `public/data/`; no doc on map scope/state machine split.

## Changes
| Path | What | Why |
|---|---|---|
| `laravel/public/pending-reports.html` | Add "Clear Filters" button (clears inputs + re-applies). | Quick reset per QA. |
| `laravel/public/monitoring.html` | Add "Clear Filters" button. | Quick reset. |
| `laravel/public/assign-office.html` | Recommendation text now shows "Recommended because category = …". | Transparency per QA item #8. |
| `laravel/public/data/*.json` | Deleted `reports.json`, `analytics.json`, `barangays.json`. | Orphaned mocks; no references in codebase (verified). |
| `laravel/docs/SUPER_ADMIN.md` | Document status state machine + map scope (Pending Validation included on admin GIS), and category-vs-status coloring split (community mobile vs admin GIS). | Clarifies intended behavior (QA #3,#10,#11). |

## Verification
- Orphan-sweep: `rg -rn "reports\.json|analytics\.json|barangays\.json"` outside docs/graphify produced only `docs/DEVLOG.md` prose (not code).
- `php -l` clean for modified controller.
- Static edits only.

## Impact
- UX polish + removes misleading mock data.

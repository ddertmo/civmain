# Initial backup — civ-main + laravel

- **Date:** 2026-10-04
- **Commit:** `c1e9035`
- **Type:** chore
- **Areas:** Flutter / Laravel

## Problem

No version-control history existed for the CIVILWATCH project, and no remote
copy of either the mobile app or the backend.

## Cause

Never committed.

## Changes

| File | Change |
|---|---|
| `civ-main/` | Flutter citizen app — screens, services, models, Android + iOS shells |
| `laravel/` | Laravel 12 + Sanctum API, migrations, seeders, blade admin panel |
| `AGENTS.md` | agent operating notes for this repo |

374 files, +70,530 lines.

## Verification

`git status` clean; `git log --oneline` starts here.

## Impact / how to test by hand

Baseline reference. Nothing to test — this is the state everything after it is
measured against.

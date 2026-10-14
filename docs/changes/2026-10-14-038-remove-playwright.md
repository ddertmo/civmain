# Remove Playwright E2E

- **Date:** 2026-10-14
- **Commit:** `6e5704d`
- **Type:** chore
- **Areas:** docs / tooling

## Problem

Playwright ran on every app start (wired into `RUN_APP.bat` and
`START_ALL.bat`) and created repeated failures and loops. The user asked for it
to be removed.

## Cause

The project had earlier mandated Playwright E2E (`AGENTS.md`, `IMPORTANT.md`,
and changelog 033) and wired `RUN_TESTS.bat` into both boot scripts.

## Changes

| File | Change |
|---|---|
| `e2e/` | deleted (config, specs, helpers, tsconfig, README, gitignore) |
| `RUN_TESTS.bat` | deleted |
| `RUN_PLAYWRIGHT_UI.bat` | deleted (untracked) |
| `RUN_APP.bat` | removed the `call RUN_TESTS.bat` step |
| `START_ALL.bat` | removed the Playwright step |
| `AGENTS.md` | "playwright (required)" → "playwright (removed)" |
| `IMPORTANT.md` | rewritten to state Playwright was removed |

## Verification

- `Select-String RUN_APP.bat,START_ALL.bat 'playwright|RUN_TESTS'` → no matches.
- `git status` shows `D RUN_TESTS.bat` and the nine `e2e/` deletions.
- Everything is recoverable from git history if it is ever wanted again.

## Impact / how to test by hand

Run `RUN_APP.bat` — no test step runs; the app boots straight to Chrome.

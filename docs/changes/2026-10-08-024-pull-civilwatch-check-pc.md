# Pull the PC companion repo: civilwatch_check_pc into civmain

- **Date:** 2026-10-08
- **Commit:** `857a723`
- **Type:** docs
- **Areas:** docs / tooling

## Problem

The PC work produced a second, private repo — `dertmo01/civilwatch_check_pc`
(created 2026-10-07 as the "companion"/backup to `civmain`). Its `main`
carried one thing `civmain` did not: a root **`README.md`** (130 lines, the
PC-health-check usage doc). It also knew about `termux-custom`, another new
private backup. Successive sessions kept assuming "two repos", so the repo map
was stale and the companion's one unique file was stranded in the wrong repo.

## Cause

- The README was written into `civilwatch_check_pc`'s own commit
  (`docs(readme)` `87ceb63`) and never pushed to `civmain` — the scripts it
  documents are in civmain (016–021), but the documentation of them wasn't.
- `docs/SESSION_CONTEXT.md` §2 and `AGENTS.md` both hardcoded a **two-repo**
  model (`prc` ↔ `backup`, parent ↔ stale `origin`), so a fresh session would
  never look for the companion repo.

## Changes

| File | Change |
|---|---|
| `README.md` | **New (root).** The companion's PC health-check usage doc — merged in by pulling `civilwatch_check_pc/main` (fast-forward `4326203 → 857a723`). Covers `CHECK_PC.bat`, `check_pc.ps1`, `check_pc.sh`, and running from Termux with a `repo` PAT. |
| `docs/SESSION_CONTEXT.md` | §2 is now "**three repos, only one gets pushes**": adds the companion row (`civilwatch_check_pc.git`, private, remote name `civilwatch-check-pc`, its `pc-backup-original` branch, and the note that its `main` is civmain's tree + README, merged 2026-10-08). |
| `AGENTS.md` | Header bullet "two repos" → "**three repos, one push target**". |

No source code changed anywhere. `termux-custom` is documented as an unrelated
Termux-UI backup (not pulled in).

## Verification

Before merging, the only difference between the two repos was confirmed to be
the README (commit `857a723` vs `4326203`):

```
$ git fetch civilwatch-check-pc
$ git diff --name-status HEAD civilwatch-check-pc/main
A  README.md
```

Then the pull fast-forwarded cleanly (both repos share history):

```
$ git merge civilwatch-check-pc/main
Updating 4326203..857a723  (fast-forward)
 README.md | 130 ++++++++++++++++++++++++++++++++++++++++++++
 1 file changed, 130 insertions(+)
```

Remote state after the sync work:

```
$ git ls-remote civilwatch-check-pc
857a723  HEAD  refs/heads/main
584cd59        refs/heads/pc-backup-original      # companion's own history, preserved
```

Local tree clean afterwards; `README.md` present at the repo root. The
`civilwatch-check-pc` remote is registered locally for future fetches.

**Not verified:**

- The README's *content* (Termux PAT flow, PowerShell exit codes) was not
  executed on this machine — it came over as-written from the PC session. It
  documents scripts whose Windows/`C:\xampp` behaviour is likewise only
  reviewable here, not runnable (no Windows, no XAMPP).
- No Flutter/SDK-dependent checks were possible.

## Impact / how to test by hand

1. `git pull` from civmain on the PC → `README.md` appears at the repo root.
2. `git remote -v` in `~/Prd/CIVILWATCH/prc` → shows `backup` and
   `civilwatch-check-pc`.
3. `git fetch backup main` still reports `up to date` at `857a723`.
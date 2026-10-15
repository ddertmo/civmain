# Session start: fresh sessions continue from a short handoff, existing sessions keep their history

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** docs
- **Areas:** opencode config / docs

## Problem

Opening opencode in a fresh clone did not reliably "pick up where we left off",
and the mechanism that was supposed to do it made things worse:

- `AGENTS.md` said *"Before doing anything else, read `docs/SESSION_CONTEXT.md`"*
  unconditionally — so a **resumed** session was pushed to re-read stale notes.
- `docs/SESSION_CONTEXT.md` was a **326-line Termux-era dump**. It stated
  *"This machine: Termux on Android … no Flutter SDK, no Windows/XAMPP"*, which
  is **false on the Windows PC** and pointed a Windows session at the wrong
  environment. It also injected far more context than "where we stopped".

## Cause

The handoff was written for one machine (Termux) and read on all of them, with
no machine detection and no fresh-vs-resumed distinction:

- `AGENTS.md:1-31` — unconditional "read first" plus a Termux-only
  "environment limits" block.
- `docs/SESSION_CONTEXT.md` — portable project knowledge mixed with
  machine-specific limits and stale status ("through changelog 014").

## Changes

| File | Change |
|---|---|
| `docs/SESSION_CONTEXT.md` → `docs/PROJECT_CONTEXT.md` | `git mv` — the detailed, portable notes are preserved as `PROJECT_CONTEXT.md`, with a banner marking section 4 (machine/environment) as Termux-specific/historical. |
| `docs/SESSION_CONTEXT.md` (new) | Now a **short live handoff**: recent sessions (fixes/tasks) + next up. ~30 lines. No machine paths. |
| `AGENTS.md` | Section 1 rewritten: **read `SESSION_CONTEXT.md` only on a *fresh* session; a resumed session keeps its own history.** Adds a two-machine table (Windows PC vs Termux/Android) and "detect the platform first", pointing detail to `PROJECT_CONTEXT.md` / `SETUP_PC.md`. |
| `.opencode/skills/changelog/SKILL.md` | New step 4b: update the `## Recent sessions` block in `docs/SESSION_CONTEXT.md` each change; `git add` now includes it. The PHP-in-graph note is now machine-aware (present on Windows, absent on Termux). |
| `docs/icm/routing/ROUTING.md`, `docs/memory/projects/HUB-CIVILWATCH.md` | Repointed canonical docs to `PROJECT_CONTEXT.md`; constraints now name both machines. |

No `.opencode/opencode.json` change: it deliberately does **not** use the
`instructions` field, because that is injected into **every** session
(including resumed ones) — the opposite of the required behaviour.

## Verification

- `git status` shows the rename as `R docs/SESSION_CONTEXT.md -> docs/PROJECT_CONTEXT.md`
  (history preserved).
- `opencode debug config` (v2.0.29) in this repo lists
  `.opencode/opencode.json` and `.opencode` as loaded sources — confirms config
  travels with a clone. No `instructions` key was added.
- Repo-wide search for `SESSION_CONTEXT`/`PROJECT_CONTEXT` across tracked files:
  the only live pointers are `AGENTS.md`, the new `SESSION_CONTEXT.md`,
  `PROJECT_CONTEXT.md`, `ROUTING.md` and `HUB-CIVILWATCH.md` — all updated.
  Matches inside `docs/changes/*` are historical records and were left as-is.
- `check_pc.ps1` → all checks pass; git sync `HEAD == backup/main`.
- **Not testable here:** whether an LLM follows the "fresh session only" rule is
  a behavioural instruction, not a switch — there is no OpenCode hook that
  injects context at session start (only `experimental.session.compacting`), so
  this is the reliable, documented approach.

## Impact / how to test by hand

1. **Fresh session:** open opencode in the repo with no prior session. The agent
   should read `docs/SESSION_CONTEXT.md` and summarise recent work + next up.
2. **Resumed session:** pick an existing session with `/sessions` (`ctrl+x l`).
   The agent should **not** re-read the handoff; its own history stands.
3. **Machine safety:** on Termux, the Windows paths in `AGENTS.md` are clearly
   the other column; nothing in `SESSION_CONTEXT.md` assumes a machine.

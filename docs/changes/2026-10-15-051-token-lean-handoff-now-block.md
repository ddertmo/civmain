# Token-lean session start: read `## NOW` + the last 3 changelog rows, with research fallbacks

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** docs
- **Areas:** opencode config / docs

## Problem

The fresh-session handoff from 050 was still too heavy and could silently grow:

- A new session read a ~30-line "recent sessions" list that **accumulated** with
  every change instead of describing only the current state.
- Nothing said *how* to look something up when the handoff did not answer it, so
  the default was to read whole files — expensive, and it made sessions drift.
- There was no cap: a busy week could turn the handoff back into the 326-line
  dump that 050 removed.
- Comparing dates to decide "how much to re-check" guessed at something the
  changelog already states exactly.

## Cause

The handoff was written as a small **log** (recent entries) rather than a
**snapshot** (current state), and lookups had no defined order or budget. With
no source of truth for "latest", the reader had to pull everything.

## Changes

| File | Change |
|---|---|
| `docs/SESSION_CONTEXT.md` | Rewritten as a **snapshot**: a `## NOW` block (at / running / doing / next / blocked) + a pointer to the changelog. The accumulating "recent sessions" list is **gone** — the index already is that list. |
| `AGENTS.md` | Startup rule is now explicit: a fresh session reads **`## NOW` (~10 lines)** + the **last 3 rows** of `docs/changes/README.md`, then stops. Adds **lazy lookup** rules (graphify → docs → line, never dump files), **unknown-info fallback** (`@researcher` / `@explore`, then write it down + `graphify update .`), and the **`## NOW` budget** (≤ ~10 lines; overflow → `docs/TASKS.md` / `PROJECT_CONTEXT.md` with a one-line pointer). |
| `.opencode/agents/researcher.md` | **New** read-only subagent: searches graphify → docs → code → `git log` and returns a compact cited answer. Runs in a child session, so the main context only grows by the answer. `edit` is denied; only `graphify`/`git` read commands are allowed. |
| `.opencode/commands/recap.md` | **New** `/recap` command: prints only the `## NOW` block, from any session. |
| `.opencode/skills/changelog/SKILL.md` | Step 4b now **replaces** (not appends to) the `## NOW` block and enforces the line budget, moving overflow to `TASKS.md` / `PROJECT_CONTEXT.md`. `git add -A` replaces the scoped add so overflow docs are committed too. |

**Latest by default, earliest on request.** The changelog index is append-only
and is never read past its last 3 rows at startup; full history is read from the
top only when a task needs it. Startup cost is therefore **constant** no matter
how many changes accumulate.

## Verification

- `docs/SESSION_CONTEXT.md`: `## NOW` is 6 lines and has no `## Recent sessions`
  list; a fresh read is ~15 lines total (NOW + 3 index rows), down from ~326 in
  050's predecessor.
- `AGENTS.md` states the two-file startup read, the budget, and the fallbacks;
  no `.opencode/opencode.json` change (the `instructions` field stays unused so
  resumed sessions are not injected with context).
- `opencode debug agents` (v2.0.29) lists `researcher` as a **subagent** with
  `edit` denied and shell default-deny plus only `graphify *` / `git …` allowed —
  so the fallback really is read-only. This build has no command lister
  (`opencode debug` exposes only `agents` / `config` / `paths`), so `/recap` is
  verified by file presence and plain frontmatter.
- `check_pc.ps1` → all checks pass; `git status` clean; `HEAD == backup/main`.

## Impact / how to test by hand

1. **Fresh session (new clone):** the agent should read `## NOW`, skim the last
   3 changelog rows, and summarise state + next up — without reading the rest.
2. **Resumed session:** unchanged — it keeps its own history and reads neither.
3. **`/recap`:** run it in any session; it prints only the current state.
4. **Unknown question:** ask something not in the handoff/docs; the agent should
   delegate to `@researcher` and return a short cited answer.
5. **Overflow:** if `## NOW` ever exceeds ~10 lines, the changelog skill moves
   the detail to `docs/TASKS.md` / `docs/PROJECT_CONTEXT.md` and leaves a pointer.

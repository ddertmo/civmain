# One-page "what changed / what was removed" summary of the security audit, for new agents

- **Date:** 2026-11-17
- **Commit:** see the index row (backfilled in the following commit)
- **Type:** docs
- **Areas:** agent context (PROJECT_CONTEXT, SESSION_CONTEXT)

## Problem

The 2026-11-17 audit work is spread across six change files (076–081). A new agent reading only `## NOW` had no quick way to learn which files, endpoints, packages and behaviours were **removed or changed**. As a result it could re-add a deleted demo page, call a removed notification endpoint, or be surprised by:
- token expiry
- registration tickets
- rate-limit lockouts
- the enforced status workflow
- office scoping

## Changes

| File | Change |
|---|---|
| `docs/PROJECT_CONTEXT.md` | New section 11, "Security audit 2026-11-17 — what changed". See the bullets after this table. Section 10 is marked as a historical snapshot (changelog 014). |
| `docs/SESSION_CONTEXT.md` | `## NOW`: `at:` is now 083, and `doing:` points to PROJECT_CONTEXT section 11. Still 5 lines. |

**Section 11 contains:**
- a **Removed** table (what, where, why);
- **Changed behaviour** bullets;
- an **Added** list (migrations, tests);
- links to 076–082 for details.

## Verification

- `## NOW` stays within the `AGENTS.md` budget.
- Section 11 was checked against the change files 076–081 and the routes and models it names.
- No new garbled characters in the edited files (compared against HEAD).
- graphify is not installed on this machine, so `graphify update .` was not run.

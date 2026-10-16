# Record the pre-login Dark Mode contrast audit as an approved-pending task

- **Date:** 2026-10-16
- **Commit:** (see index row 059)
- **Type:** docs
- **Areas:** docs

## Problem

The sweep done for [058](2026-10-16-058-dark-mode-ink-profile-reports-widgets.md)
found that the **same** fixed-ink defect still affects the pre-login flow
(landing, OTP, forgot-password, and suspected login/register/splash/visitor/
track screens). That was outside 058's approved scope, so it was left unfixed.
The user then supplied a full future-task specification for it.

A specification that lives only in a chat transcript is lost to the next
session, and `AGENTS.md` says open work belongs in `docs/TASKS.md`.

## Cause

No code defect — a documentation gap. 058 had recorded the finding only as a
short bullet ("Pre-login flow still uses fixed brand ink in Dark Mode") with a
single table of 9 sites and no requirements or verification steps.

## Changes

| File | Change |
|---|---|
| `docs/TASKS.md` | Replaced that short entry with the full spec — **"Future task — Dark Mode contrast audit of the pre-login flow"**. Keeps the measured site list, re-organised under the user's headings (landing / OTP / forgot-password / to-audit), and adds the status gate ("do not implement until explicitly requested and approved"), the precondition (057+058 visually verified first), the 8 implementation requirements, and the verification steps. One home per fact — the old entry was rewritten, not duplicated. |
| `docs/SESSION_CONTEXT.md` | `## NOW` `next:` now points at the spec'd pre-login audit and states the "do not start unapproved" gate. |

**No `lib/` file was touched.** Only `flutter analyze` was re-run, to prove the
tree still compiles; nothing in the app changed.

## Verification

```
flutter analyze --no-pub -> 109 issues found. (0 errors)
```

Identical to the 057/058 baseline — expected, since this change edits Markdown
only. No tests were run because no Dart/Flutter code changed; the last full-suite
result (4 pass + the pre-existing splash-timer failure) still stands.

```bash
graphify update .   # docs are indexed, so the graph was refreshed
```

**Not verified:** nothing to verify visually — this change only records a
deferred task. The audit itself, and its visual checks, are for the future
session that picks the task up.

## Impact / how to test by hand

Nothing user-visible. To confirm the record, read the last entry of
[`docs/TASKS.md`](../TASKS.md) (`## [ ] Future task — Dark Mode contrast audit
of the pre-login flow`); it must carry the deferred status, the confirmed site
list, the approval gate and the requirements. **Do not start it without an
explicit request.**

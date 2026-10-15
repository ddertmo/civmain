---
name: changelog
description: "Use after every code change to write a per-change markdown file under docs/changes/, update the index, and commit + push it to the backup remote. Invoke whenever a fix, feature, refactor, doc edit or graph refresh has just been committed — or when the user says /changelog, 'document this change', 'make a markdown for this change', or asks what changed."
---

# /changelog

Write **one markdown file per change** into `docs/changes/`, register it in the
index, then commit and push so the history is readable on GitHub — not just in
`git log`.

## Usage

```
/changelog              # document the change just made (most recent commit)
/changelog <commit>     # document a specific commit, e.g. /changelog fc497bd
/changelog --backfill   # find commits with no docs/changes file and create them
/changelog --dry-run    # print the file you would write, change nothing
```

## Workflow

### 1. Identify the change

```bash
git log --oneline -3
git show --stat --format="" <commit>
git status --short
```

If there are staged/committed files but no doc yet, that is the change to
document. If several commits are undocumented, take the newest one — or use
`--backfill`.

### 2. Gather evidence before writing

Never invent a root cause. Read the actual diff and the surrounding code:

```bash
git show <commit>            # full diff
git show --name-only --format="" <commit>
```

If `graphify-out/graph.json` exists, scope the context first:

```bash
graphify query "<what broke / where is this used>"
graphify explain "<symbol>"
```

If the change touches Laravel PHP, check whether PHP is in the graph: it **is**
on the Windows PC (`tree_sitter_php` parses) but is **not** on Termux — if the
query returns nothing, read the files directly.

### 3. Write `docs/changes/YYYY-MM-DD-NNN-<slug>.slug>.md`

- **Date** from `git log -1 --format=%ad --date=short <commit>`
- **NNN** = next free sequence number (see `docs/changes/README.md`)
- **slug** = 3–6 kebab-case words, e.g. `008-report-visibility.md`
- **Commit** = short hash, backticks

Follow `docs/changes/TEMPLATE.md` exactly:

| Section | Content |
|---|---|
| front matter | Date, Commit, Type (`fix\|feature\|docs\|chore\|refactor\|graphify`), Areas |
| **Problem** | the *user-visible* symptom, not the code smell |
| **Cause** | root cause with `file:line` references |
| **Changes** | table of `path` → what changed and why |
| **Verification** | real commands + real output. State plainly what was **not** verified (no Flutter SDK, no Xcode, static review only) |
| **Impact / how to test by hand** | steps a human can follow on a device or in a browser |

Rules:

- One file per change. Do not batch unrelated fixes into one document.
- If a single commit carries two unrelated fixes (as `fc497bd` did), separate
  them with `---` and give each its own Problem/Cause/Verification block.
- Every claim must be checkable. If you could not run a test, say so instead of
  implying it passed.

### 4. Update the index

Append a row to `docs/changes/README.md`:

```markdown
| 009 | 2026-10-06 | `abc1234` | [One-line title](2026-10-06-009-slug.md) |
```

Keep it sorted newest-last and make sure the link path matches the file.

### 4b. Refresh the handoff's `## NOW` snapshot

You do **not** add a line to `docs/SESSION_CONTEXT.md` — you **replace** its
`## NOW` block with the current state:

- `at:` this change's date + which changelog number it is (the exact commit is
  the newest row of `docs/changes/README.md` — do not hardcode a hash you cannot
  know yet)
- `running:` what is up right now, or `—`
- `doing:` at most 2 lines
- `next:` at most 3 bullets
- `blocked:` at most 2 lines

**Hard budget: `## NOW` stays ≤ ~10 lines.** If it would exceed that, move the
detail to `docs/TASKS.md` (open/next items) or `docs/PROJECT_CONTEXT.md`
(durable facts) and leave a one-line pointer, e.g. `next: see docs/TASKS.md
(4 open)`.

Completed work lives in `docs/changes/` only — never in `## NOW`, and never as a
"recent sessions" list (the index already is that list). A fresh session reads
`## NOW` + the **last 3 index rows**; a resumed session reads neither.

### 5. Refresh the graph if code changed

```bash
export PATH="$HOME/.local/bin:$PATH"
graphify update .
```

Doc-only changes can skip this, but running it is cheap and keeps
`graphify-out/` consistent.

### 6. Commit and push

```bash
git add -A
git commit -m "docs(changes): <NNN> <short title>"
git push backup main
```

Verify the push landed:

```bash
git ls-remote backup main; git rev-parse HEAD   # must be identical
```

> `origin` is the upstream repo — **do not push to it**. `backup` is the push
> target.

### 7. Report back

Show the user: the file path, the commit hash, and one line confirming the
push.

## Backfill

```
/changelog --backfill
```

1. `git log --format='%h|%ad|%s' --date=short` for all commits
2. `ls docs/changes/` for hashes already covered
3. For each uncovered commit, run the workflow above (oldest first so the
   sequence numbers are chronological)
4. Rebuild the whole `README.md` index at the end so the table is ordered

## Anti-patterns

- **Do not** describe what you *intended* — describe what the diff actually did.
- **Do not** claim "all tests pass" when no test runner exists in this
  environment. List the checks you really ran.
- **Do not** restate the commit message verbatim; the doc must add the root
  cause and the verification trail that `git log` lacks.
- **Do not** push to `origin`.

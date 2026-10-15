## session context — read on a NEW session only

Startup read is deliberately tiny. On a **fresh session** (no prior messages),
read exactly two things and stop:

1. `docs/SESSION_CONTEXT.md` — the **`## NOW` block only** (~10 lines).
2. `docs/changes/README.md` — the **last 3 rows only** (latest changes).

**Latest by default, earliest only when asked.** The index is append-only and is
never read past those 3 rows at startup; read it from the top (earliest) only
if a task needs the full history.

**Resumed session (already has history)?** Skip both — your own history is the
context.

### lazy lookups — do not preload

- Answer from `## NOW` + the current context first.
- On a gap, run `graphify query "<q>"` → walk 1–2 hops → open only the specific
  doc/line. Never dump whole files or `GRAPH_REPORT.md`.
- Do a **thorough** search only when the quick pass misses.

### unknown info — use a subagent, then write it down

- **`@researcher`** — graphify → docs → code → `git log`, returns a compact
  cited answer. It runs in a child session, so this context stays small.
- **`@explore`** — built-in, for pure codebase file/keyword hunting.
- If the answer exists **nowhere**, write it into a doc (or a `docs/changes`
  note) and run `graphify update .` so the next session finds it.

### `## NOW` budget — a snapshot, never a log

`## NOW` is **rewritten in place** on every change, never appended. Hard caps:
`at:` 1 line · `running:` 1 line · `doing:` ≤ 2 · `next:` ≤ 3 · `blocked:` ≤ 2 ·
total ≤ ~10 lines.

If it would exceed that, collapse the overflow into a one-line pointer and put
the detail elsewhere — e.g. `next: see docs/TASKS.md (4 open)`. Open/next items
go to [`docs/TASKS.md`](docs/TASKS.md); durable facts go to
[`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md). Completed work lives in
`docs/changes/` only.

For detail, go to (do not duplicate these):

- **detailed / portable project notes** — [`docs/PROJECT_CONTEXT.md`](docs/PROJECT_CONTEXT.md)
  (architecture, credentials, running it, graphify). Its "Machine and
  environment" section is Termux-specific and historical.
- **what changed, per change** — [`docs/changes/README.md`](docs/changes/README.md)
- **how to run on the PC** — [`laravel/docs/SETUP_PC.md`](laravel/docs/SETUP_PC.md)

### two machines — detect before applying env-specific commands

This repo is edited from **two machines**; the limits differ:

| Machine | Platform | Environment |
|---|---|---|
| Windows PC | `win32` | XAMPP — PHP `C:\xampp\php\php.exe`, MySQL `C:\xampp\mysql\bin\mysql.exe`; Flutter `C:\Users\Administrator\Downloads\flutter\bin\flutter.bat`; Node available; `tree_sitter_php` parses, so Laravel PHP **can** be in the graph |
| Android / Termux | `android` | PHP / Composer / MariaDB only — **no** Flutter, Dart, Node, Windows or XAMPP; small disk (no installs); `/tmp` unwritable; Laravel PHP is **not** in the graph — read PHP files directly |

**Never assume the other machine's limits.** Detect the current platform, then
apply the matching column.

**Push target (both machines):** the working repo pushes to `backup`
(`dertmo01/civmain`); the parent `CIVILWATCH` has `origin`
(`SerenoLawrence/CIVILWATCH`), is stale, and must never be committed to.

## playwright (removed)

Playwright E2E was **removed** from this project at the user's request. Do **not**
run `npx playwright test`, do not re-add the `e2e/` suite, and do not add
Playwright tests to the code.

- `RUN_APP.bat` and `START_ALL.bat` no longer run any test step.
- `RUN_TESTS.bat` and `e2e/` were deleted (recoverable from git history if ever
  needed again).
- To verify a UI change, inspect the served HTML/JS/CSS directly, or load the
  page in a browser by hand. There is no automated browser suite.


## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Treat GRAPHIFY AS THE RETRIEVAL LAYER FIRST (graph-first context loading) when answering questions about the codebase, architecture, files, relationships, or project content. When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- ALWAYS run `graphify query "<question>"` first when graphify-out/graph.json exists to get a small, relevant subgraph. Do NOT read entire docs/files by default.
- Walk the graph 1-2 hops from returned nodes using `graphify path "<A>" "<B>"` or expand with `graphify explain "<concept>"` to surface only directly related context before answering.
- Prefer the smallest relevant set of nodes/notes (aim for minimal context, ~5 relevant notes/pages max). Do not dump the full vault/GRAPH_REPORT.md unless query/path/explain cannot surface enough context.
- Load relevant context via graph BEFORE synthesizing answers; answer using only that relevant subset when possible.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for canonical navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review when targeted queries are insufficient.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
- "One home per fact" — each fact lives in exactly one canonical location (file/note). Link to it rather than duplicating. Keep relationships in the graph.



## changelog

Every change gets its own markdown file in `docs/changes/`, indexed by
`docs/changes/README.md`, committed and pushed to the `backup` remote.

Rules:
- After finishing any fix, feature, refactor, doc edit or graph refresh, run the installed **changelog** skill (`.opencode/skills/changelog/SKILL.md`) before moving on. It writes `docs/changes/YYYY-MM-DD-NNN-<slug>.md`, updates the index, runs `graphify update .`, then commits and pushes.
- One file per change. One change per file — do not merge unrelated fixes.
- Document what the diff actually did plus the root cause and the real verification commands. If a check could not be run (no Flutter SDK, no Xcode), say so explicitly instead of implying it passed.
- Push target is `backup` (`https://github.com/dertmo01/civmain.git`), branch `main`. Never push to `origin`.
- After pushing, confirm `git ls-remote backup main` equals `git rev-parse HEAD`.

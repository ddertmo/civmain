## session context — read on a NEW session only

**Fresh session (no prior messages)?** Read
[`docs/SESSION_CONTEXT.md`](docs/SESSION_CONTEXT.md) first and continue from it.
It is the short handoff of what the last sessions did and what is next.

**Resumed session (already has history)?** Do **not** re-read it — the existing
conversation already is your context.

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

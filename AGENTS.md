## session context — read first

Before doing anything else, read [`docs/SESSION_CONTEXT.md`](docs/SESSION_CONTEXT.md).
It is the exported context of a prior opencode session and makes a new session
on another machine pick up where that one stopped.

It covers, and these are not repeated elsewhere:

- **three repos, one push target** — `prc/` pushes to `backup`
  (`dertmo01/civmain`); the parent `~/Prd/CIVILWATCH` has `origin`
  (`SerenoLawrence/CIVILWATCH`), is stale, and must never be committed to.
- **environment limits** — Termux/Android, no Flutter SDK / Dart / Android SDK /
  Windows / XAMPP / Node build, ~1 GB disk (no installs), `/tmp` unwritable
  (use `/data/data/com.termux/files/usr/tmp/opencode`), no LLM key so
  `graphify label` cannot run, `tree_sitter_php` unbuildable so Laravel PHP is
  **not** in the graph — read PHP files directly.
- **environment traps** — services die between tool calls; `pkill -f` matches
  your own shell (use `pgrep -f 'foo[trick]'`); `php artisan serve` ignores a
  `DB_DATABASE=` override, so pointing at another database means editing
  `.env` and restoring it with `cmp`.
- **credentials, live database contents, the canonical dump**
  (`laravel/civilwatch_FINAL.sql`), how to run on Android and on PC.
- **architecture facts that are easy to get wrong** — two admin UIs kept on
  purpose, `requireAuth()` client-side only on the HTML prototype, the
  `superadmin` role gate, the reject flow (reason lives in
  `report_activities.description`, not a report column), `concern` not `title`,
  the four role/enum values.
- **graphify usage** — heading text must be used as a literal search key,
  because markdown headings link only to their document root.
- **verification techniques** that work on this machine, and **what is still
  open** (Flutter verification path unchosen; next changelog number).

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

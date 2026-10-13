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

## playwright (required)

Playwright E2E is a **required** part of running this project. Read
[`IMPORTANT.md`](IMPORTANT.md) for the full rationale.

- The suite lives in `e2e/`. Run it with `RUN_TESTS.bat` (Windows) or
  `cd e2e && npx playwright test`.
- After any change to `laravel/`, the HTML/JS admin, or any citizen/web UI, run
  the suite and fix failures before calling the work done.
- Playwright produces the evidence opencode uses to find and fix bugs:
  screenshots, a trace, video, and console/network logs. Read the failing
  test's `test-results/<test>/trace.zip` with `npx playwright show-trace`.
- Playwright runs on the **PC only** — it cannot run on Termux/Android (no
  browser). If you are on Termux, state that the suite could not be run
  instead of implying it passed.
- The config auto-starts `php artisan serve` if the backend is not already up,
  and reuses an existing server otherwise.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

## changelog

Every change gets its own markdown file in `docs/changes/`, indexed by
`docs/changes/README.md`, committed and pushed to the `backup` remote.

Rules:
- After finishing any fix, feature, refactor, doc edit or graph refresh, run the installed **changelog** skill (`.opencode/skills/changelog/SKILL.md`) before moving on. It writes `docs/changes/YYYY-MM-DD-NNN-<slug>.md`, updates the index, runs `graphify update .`, then commits and pushes.
- One file per change. One change per file — do not merge unrelated fixes.
- Document what the diff actually did plus the root cause and the real verification commands. If a check could not be run (no Flutter SDK, no Xcode), say so explicitly instead of implying it passed.
- Push target is `backup` (`https://github.com/dertmo01/civmain.git`), branch `main`. Never push to `origin`.
- After pushing, confirm `git ls-remote backup main` equals `git rev-parse HEAD`.

# CIVILWATCH — Session context

Read this first. It is the distilled context of an opencode session so a new
session on another machine starts where the last one stopped.

Companion files: [`AGENTS.md`](../AGENTS.md) (hard rules),
[`laravel/docs/SETUP_PC.md`](laravel/docs/SETUP_PC.md) (how to run),
[`docs/changes/README.md`](changes/README.md) (what was done, per change).

---

## 1. What this project is

**CIVILWATCH** — a citizen reporting system for the City Government of Digos.
Citizens submit infrastructure/environment reports; admins validate, assign
them to a government office, and accept or reject them with a written reason.

Two codebases in one repo:

| Path | What | Stack |
|---|---|---|
| `civ-main/` | Mobile app | Flutter (Dart) |
| `laravel/` | Backend + two admin web UIs | Laravel 12, Sanctum, PHP 8.5.1 |

The owner is a student learning backend development. Their stated preference
(from `instruction.md`): explain step by step, do not assume Laravel knowledge,
show the actual error and the actual fix.

---

## 2. Repositories — there are two, and only one is ours

| Repo | Remote | Status |
|---|---|---|
| `~/Prd/CIVILWATCH/prc` | `backup` → `https://github.com/dertmo01/civmain.git` | **the working repo.** Push here. |
| `~/Prd/CIVILWATCH` (parent) | `origin` → `https://github.com/SerenoLawrence/CIVILWATCH.git` | stale, last commit 2026-09-29. **Never commit or push here.** It tracks `prc/`'s files in its index, so `git status` in the parent shows `prc/` changes — ignore them. |

`prc` has **no `origin`**, only `backup`. Rules:

- Push: `git push backup main` — never `origin`.
- Verify after every push: `git ls-remote backup main` must equal `git rev-parse HEAD`.
- Commit style: `fix(scope): …`, `feat(scope): …`, `docs(changes): …`.
- `co-authored-by: Claude Opus 4.8 <noreply@anthropic.com>` has been used on recent commits.

---

## 3. Hard rules (also in AGENTS.md)

1. **Changelog per change.** After every fix, feature, refactor, doc edit or
   graph refresh, run the `changelog` skill. It writes
   `docs/changes/YYYY-MM-DD-NNN-<slug>.md`, updates `docs/changes/README.md`,
   runs `graphify update .`, then commits and pushes.
   **Next sequence number:** take the last row number in
   [`docs/changes/README.md`](changes/README.md) and add one — do not hardcode
   it here. One file per change; do not batch.
2. **Graphify first.** For codebase questions run `graphify query "…"`,
   `graphify path A B`, `graphify explain "concept"` before grepping. After
   code changes run `graphify update .`.
3. **Say what you could not test.** There is no Flutter SDK, no Android SDK,
   no Windows/XAMPP and no Node build here. Never imply a test passed that
   was only reviewed statically.

---

## 4. Machine and environment

**This machine:** Termux on Android. Platform `android`.

| | |
|---|---|
| Available | PHP 8.5.1, Composer, MariaDB (own instance), `mariadb`/`mariadb-dump`, git, `~/.local/bin/graphify` |
| **Not** available | Flutter SDK, Dart, Java, Android SDK, emulator, Xcode, Windows, XAMPP, Node build |
| Disk | ~1 GB free of 45 GB — **no installs** |
| `/tmp` | **Permission denied.** Use `/data/data/com.termux/files/usr/tmp/opencode` |
| graph parsers | `tree_sitter_php` and most others **cannot build** on Termux. Laravel's 62 PHP files contribute nothing to the graph — **read PHP directly.** Dart and Markdown do parse. |
| LLM key | `GEMINI_API_KEY` / `GOOGLE_API_KEY` unset → **`graphify label` cannot run.** Community names in `.graphify_labels.json` go stale; clustering itself stays current. |

### Environment traps (all hit for real)

- **Services die between tool calls.** Restart inside the *same* command:

  ```bash
  setsid nohup mariadbd --user=$USER --datadir=$HOME/.local/var/mysql \
    --socket=$HOME/.local/var/mysql/mysql.sock --bind-address=127.0.0.1 \
    --innodb-buffer-pool-size=64M --performance-schema=OFF >/dev/null 2>&1 </dev/null & disown
  # wait for the socket with a loop before querying
  ```

- **`pkill -f "pattern"` kills your own shell**, because the pattern appears in
  your own command line. Use the bracket trick so the regex cannot match
  itself: `kill $(pgrep -f 'artisan serv[e]')`.
- **`php artisan serve` ignores a `DB_DATABASE=…` env override.** Symfony's
  `Process` rebuilds the child environment, so the value never arrives — the
  server silently keeps using `.env`. To point the app at another database you
  **must edit `.env`**: back it up (`cp .env .env.bak`), edit, test, then
  `cp .env.bak .env` and confirm with `cmp`.
- **Long-running commands** need an explicit `timeout` or they are cut at 60 s.

### MariaDB

```
socket   $HOME/.local/var/mysql/mysql.sock
user     root (no password)
database civilwatch          15 tables
```

Started by the command above; check with
`mariadb --socket=$HOME/.local/var/mysql/mysql.sock -u root -e "SELECT 1"`.

---

## 5. Credentials and data

| Role | Login | Password |
|---|---|---|
| Citizen | phone `+639171234567` | PIN `123456` |
| Super Admin | `admin@civilwatch.ph` | `Admin@2026!` |
| CEO (test) | `ceo@civilwatch.ph` | `Ceo@2026!` |

**Roles are `super_admin`, `ceo`, `cenro` only.** There is no `staff` role —
the prototype's "Staff" stat is therefore always 0 (pre-existing, out of scope).

Live database contents (2026-10-07): 2 users, 1 citizen, 3 citizen_reports
(2 Rejected + `is_public=0`, 1 Assigned + `is_public=1`), 9 report_activities,
6 citizen_notifications, 5 government_offices, 2 announcements, 12 migrations.

Canonical dump: **`laravel/civilwatch_FINAL.sql`** — 15 tables, `Rejected` in
the status ENUM, `CREATE DATABASE IF NOT EXISTS civilwatch`, phpMyAdmin
instructions in the footer. A convenience copy sits outside the repo at
`~/Prd/CIVILWATCH/civilwatch_FINAL.sql` (uncommitted there).

---

## 6. Running it

**Android (unchanged):** the menu launcher `~/bin/termux-help-menu` →
**CIVILWATCH · START WEB** runs
`cd ~/Prd/CIVILWATCH/prc/laravel && php artisan serve --host=127.0.0.1 --port=8000`
and opens Brave at `http://127.0.0.1:8000`.

**PC (XAMPP):** full walkthrough in
[`laravel/docs/SETUP_PC.md`](laravel/docs/SETUP_PC.md) — import the dump in
phpMyAdmin, `cp .env.example .env`, `key:generate`, `artisan serve`.

**URLs**

| URL | What |
|---|---|
| `/` | HTML/JS admin prototype (`public/index.html`) |
| `/admin/login` | Blade admin panel |
| `/api/...` | REST API |

**Flutter:** base URL is a build-time define — `--dart-define=API_BASE_URL=…`,
default `http://127.0.0.1:8000/api`. See `civ-main/lib/core/constants/api_constants.dart`.

---

## 7. Architecture facts that are easy to get wrong

- **There are two admin UIs and the decision is to keep both.** The HTML
  prototype is served from `routes/web.php:7` (reads `public/index.html`, **no
  server-side auth** — auth is `requireAuth()` in `js/app.js:153`) plus a
  catch-all at `routes/web.php:38` that serves any `public/*.html`. The Blade
  panel is under `auth` + `superadmin` middleware.
- **Role gate:** `app/Http/Middleware/EnsureSuperAdmin.php`, alias `superadmin`
  registered in `bootstrap/app.php`. Attached to `/api/admin/*`, `/admin/*` and
  the three `/api/users` mutations; **`GET /api/users` is deliberately open.**
  GET/HEAD/OPTIONS pass through; other verbs need `role === 'super_admin'`,
  otherwise JSON 403 on `api/*` or a flashed-error redirect.
- **Reject flow:** `POST /api/admin/citizen-reports/{id}/status` with
  `{"status":"Rejected","description":"…"}`. Missing reason → **422**. A
  non-rejectable status → **422**. On success `is_public` flips 1→0 (drops the
  row from the public community feed) and the reason is written to
  `report_activities.description`. **The reason is not a column on the report.**
- **Legacy subsystem removed:** the old `/api/reports*` block and the `Report`,
  `Notification`, `ReportTimeline`, `ReportAssignment`, `ReportPhoto` models are
  gone (route list 87 → 77). `AdminCitizenReportController` is the live one.
- **Citizen auth** uses the `auth:citizen` Sanctum guard; admin uses
  `auth:sanctum`. They are separate guards.
- **Report field names:** the short title is **`concern`**, not `title`.
  `category` ∈ `Infrastructure|Environment|Others`;
  `severity` ∈ `Minor|Moderate|Severe`;
  `status` ∈ `Submitted|Pending Validation|Assigned to Office|In Progress|Resolved|Rejected`.
- **CEO/CENRO pages make zero API calls** — they are static shells. Do not
  "fix" them by adding fetches without asking.
- **`composer setup`** now does install → `.env` → `key:generate` →
  `migrate --seed --force` → `storage:link`. npm lives in a separate
  `composer build`; **no served view uses Vite** (only stock
  `resources/views/welcome.blade.php` has `@vite`).
- **`public/storage` is gitignored**, so a fresh clone needs
  `php artisan storage:link` or uploaded photos 404.

---

## 8. Graphify

- Output at `graphify-out/`. As of 2026-10-07: **2062 nodes / 2721 edges /
  142 communities / 152 wiki articles.**
- CLI: `export PATH="$HOME/.local/bin:$PATH"`.
- Useful commands: `graphify update .`, `graphify cluster-only .`,
  `graphify export wiki` (⚠ **not** `graphify . --wiki`),
  `graphify query "<q>" [--budget N]`, `graphify path "A" "B"`,
  `graphify explain "<node>"`.
- **Markdown heading nodes link only to their own document root.** So a prose
  question (`query "why can a CEO not reject a report"`) seeds BFS on code
  symbols and never reaches a doc. Use the heading text as a literal search
  key: `graphify query "Super Admin role gate EnsureSuperAdmin"`,
  `graphify explain "Role gate"`, `"Reject with notes"`,
  `"Drop the legacy /api/reports subsystem"`.
- Known searchable headings: `## Two admin UIs`, `## Reject with notes`,
  `## Pending queue`, `## Approve flow`, `## Role gate`, `## Notifications feed`,
  `## Analytics endpoints` (in `laravel/docs/SUPER_ADMIN.md`);
  `## One-time database import` etc. (in `laravel/docs/SETUP_PC.md`).
- PHP is **absent** from the graph (parser unbuildable) — querying for
  `EnsureSuperAdmin` or any controller returns 0 nodes. Read the file.

---

## 9. Verification techniques that actually work here

- **Test a SQL dump without touching the live database.** The dump contains
  `USE civilwatch;`, so `mariadb -D scratch < dump` would still redirect into
  the live DB. Strip the header first:

  ```bash
  awk 'BEGIN{s=0}
       /^CREATE DATABASE IF NOT EXISTS `civilwatch`/{s=1;next}
       s==1 && /;[ \t]*$/{s=0;next}
       s==1 {next}
       /^USE `civilwatch`;/{next}
       {print}' civilwatch_FINAL.sql | mariadb -u root civilwatch_test
  ```

  Then compare with a column-by-column
  `information_schema.COLUMNS … UNION ALL … | sort | uniq -u` (empty = identical).
- **Run the app against a scratch database:** back up `.env`, point
  `DB_DATABASE` at the scratch DB, restart the server on a free port, test,
  then restore `.env` and verify with `cmp`. Drop the scratch DB afterwards.
- **Dart cannot be compiled here.** Static review only — a string/comment
  stripping brace-and-paren counter, plus reading the diff. A reported imbalance
  was proven a pre-existing false positive by diffing against `HEAD`.
- **API smoke set:** `/api/ping`, `POST /api/login`, citizen register + login,
  `POST /api/mobile/reports`, admin status change with/without reason, a CEO
  write expecting 403, and an already-rejected report expecting 422.

---

## 10. Where things stand

Everything through **changelog 014** is committed and pushed to
`backup/main` at `2e1a5a6`; working tree clean.

Done: reject-with-notes end to end (migration → Laravel → prototype HTML →
Blade → 9 Dart files), dead analytics/notification/user-list endpoints
repaired, legacy `/api/reports` subsystem deleted, `super_admin` write gate,
`SUPER_ADMIN.md` reference + DEVLOG cross-links, and the PC/XAMPP setup path
(correct SQL dump, `.env.example`, `composer setup`, `SETUP_PC.md`).

**Open items:**

1. **Flutter verification is unresolved.** Every Dart change ships on static
   review only. Options: local Flutter on a machine with disk, Termux `dart`,
   or a GitHub Actions CI workflow. The owner has not chosen one.
2. **`graphify label` cannot run** without `GEMINI_API_KEY`/`GOOGLE_API_KEY`,
   so community names drift from the current clustering.
3. The parent repo (`SerenoLawrence/CIVILWATCH`) is stale and untouched.
4. `README_DOCUMENTATION.md` / `CIVILWATCH_MASTER.md` etc. live in the parent
   repo, outside `prc/`, and are not part of this workflow.

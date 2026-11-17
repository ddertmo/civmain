# Session status — read `## NOW` only

Fresh session: read **only the `## NOW` block below**, then the **last 3 rows**
of [`changes/README.md`](changes/README.md) (latest changes). Then stop. Latest
by default; read from the top of the index (earliest) only when a task needs it.

Resumed session (already has history): skip this file — your history is the
context. See `AGENTS.md` for the full startup rule and the budget below.

---

## NOW

- at:      2026-11-17 - latest change = 081; 076-081 = security audit fixes (all 4 severities) + first real test and MySQL migration runs
- running: this clone = C:\Users\Administrator\Downloads\civmain on a PC with portable PHP 8.3 + Composer + MariaDB 10.11 in C:\Users\Administrator\Downloads\tools (no XAMPP, Flutter or Node); app on http://127.0.0.1:8000, start commands in .claude/launch.json
- doing:   nothing in progress - audit follow-up done; php artisan test = 27/27 on SQLite and on MariaDB
- next:    see docs/TASKS.md "Security audit follow-up" (XAMPP migrate, rotate seed passwords, Flutter pub get/analyze/rebuild)
- blocked: Flutter changes from 076-079 are unverified until run on a machine with the Flutter SDK

Detailed/portable notes → [`PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md) · open work → [`TASKS.md`](TASKS.md)


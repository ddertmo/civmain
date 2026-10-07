# One-file XAMPP database import and PC setup guide

- **Date:** 2026-10-07
- **Commit:** `cdac1cb`
- **Type:** feature
- **Areas:** Laravel / docs

## Problem

There was no way to take the code from GitHub and get it running on a PC.
Three separate things blocked it:

1. The SQL dump that *was* pushed, `laravel/civilwatch.sql`, built the wrong
   database. It created `reports`, `notifications`, `report_timeline`,
   `report_assignments` and `report_photos`, and never created `citizens`,
   `citizen_reports`, `report_activities`, `citizen_notifications`,
   `government_offices`, `announcements` or `otp_codes`. Every query the app
   makes would have failed with "table doesn't exist".
2. `laravel/civilwatch_seed.sql` inserted only into those same dead tables.
3. `.env.example` shipped `DB_CONNECTION=sqlite` with the entire MySQL block
   commented out, so `composer setup` built a SQLite database and never went
   near XAMPP's MySQL.

The one dump that *was* correctly shaped — `~/Prd/CIVILWATCH/civilwatch_FINAL.sql`,
written for phpMyAdmin with `CREATE DATABASE IF NOT EXISTS` — lived outside
the repository, so it was never pushed and never reached the PC.

Secondary symptoms: `composer setup` ran `migrate` with no `--seed` (fresh
database, no admin account, nothing to log in as) and never ran
`storage:link`, so uploaded report photos returned 404.

## Cause

The two dumps in `laravel/` predate the report-model rework: they were
generated on 2026-08-10 / 2026-09-07 from before `Report` was replaced by
`CitizenReport`. Nothing regenerated them when the schema changed, and
nothing in CI or the README checked them, so they sat in the repo looking
authoritative while describing a database the code no longer uses.

`.env.example` came from the stock Laravel skeleton, whose default is SQLite.
Because `composer setup` copies `.env.example` verbatim, a PC following the
obvious path would have silently created `database/database.sqlite`.

## Changes

| File | Change |
|---|---|
| `laravel/civilwatch_FINAL.sql` | **New.** Generated from the live Termux database with `mariadb-dump --single-transaction --add-drop-table`. 15 tables, `status` ENUM containing `Rejected`, `CREATE DATABASE IF NOT EXISTS civilwatch` + `USE`, phpMyAdmin import steps in the footer, real credentials (`admin@civilwatch.ph` / `Admin@2026!`, `ceo@civilwatch.ph` / `Ceo@2026!`). `personal_access_tokens` is deliberately left empty — a fresh install should have nobody logged in. |
| `laravel/civilwatch.sql` | **Deleted.** Legacy schema. |
| `laravel/civilwatch_seed.sql` | **Deleted.** `DELETE FROM reports/notifications/report_timeline` against tables that no longer exist. |
| `laravel/.env.example` | `DB_CONNECTION=sqlite` → `mysql`; `DB_HOST`, `DB_PORT`, `DB_DATABASE=civilwatch`, `DB_USERNAME=root`, `DB_PASSWORD` uncommented. |
| `laravel/composer.json` | `setup`: `migrate --force` → `migrate --seed --force`, plus `@php artisan storage:link`. New `build` script holds `npm install` + `npm run build` (only the stock `welcome.blade.php` uses `@vite`). |
| `laravel/docs/SETUP_PC.md` | **New.** Prerequisites, database import, `.env`/`APP_KEY`, running the backend, Android/menu, admin credentials, citizen registration + report submission, Flutter on the PC, troubleshooting, and an explicit "Not verified" section. |
| `laravel/README.md` | CivilWatch setup block at the top pointing at the guide. |

Also synced to `~/Prd/CIVILWATCH/civilwatch_FINAL.sql` (convenience copy,
outside this repository — that file is left uncommitted in its own git repo).

## Verification

**Dump generation**

```
$ mariadb-dump --socket=... -u root --single-transaction --add-drop-table \
    --routines --triggers civilwatch > body.sql
exit=0   bytes=26271
CREATE TABLE list: announcements cache cache_locks citizen_notifications
  citizen_reports citizens failed_jobs government_offices job_batches jobs
  migrations otp_codes personal_access_tokens report_activities users   (15)
stray CREATE DATABASE / USE: 0     DROP TABLE IF EXISTS: 15
```

**Imported into a scratch database** (`civilwatch_test`) after stripping the
`CREATE DATABASE`/`USE` header — otherwise the `USE civilwatch;` inside the
file would have redirected the import into the live database:

```
$ ... awk '<strip header>' civilwatch_FINAL.sql | mariadb ... civilwatch_test
import exit=0     (no stderr)
```

Header statement validity checked separately against a throwaway schema:
`header SQL OK` (the throwaway database was dropped afterwards).

**Schema and data parity against the live database**

```
counts:                live  scratch
users                    2        2
citizens                 1        1
citizen_reports          3        3
report_activities        9        9
citizen_notifications    6        6
government_offices       5        5
announcements            2        2
migrations              12       12
personal_access_tokens  11        0   <- intentionally empty

column-by-column schema diff: (empty)
status ENUM: enum('Submitted','Pending Validation','Assigned to Office',
                  'In Progress','Resolved','Rejected')
```

**The app actually runs against that dump.** `.env` was backed up, pointed at
`civilwatch_test`, and the backend restarted on port 8001:

```
GET  /api/ping                                    -> {"success":true,...}
POST /api/mobile/auth/register                    -> 201, landed in scratch (live unchanged)
POST /api/mobile/reports  (concern, not title)    -> CW-2026-00004, Pending Validation
POST /api/admin/citizen-reports/4/status (no reason)   -> 422
POST /api/admin/citizen-reports/4/status (with reason) -> 200, status=Rejected, is_public=0
POST ... as ceo@civilwatch.ph                     -> 403 {"message":"Super administrator access is required."}
POST ... on an already-Rejected report            -> 422

report_activities 10/11/12: Report Submitted -> Pending Validation -> Rejected
   id 12 description = "Drainage already covered by an ongoing project."
citizen_notifications: "CW-2026-00004 — Rejected. Drainage already covered by an ong..."
```

`.env` was then restored **byte-identical** to its backup (`cmp` passed),
the backup removed, the scratch database dropped, and the test citizen that
had been written to the live database during an earlier attempt removed —
live is back to 1 citizen / 15 tables.

**Config and scripts**

```
$ php -r 'json_decode(file_get_contents("composer.json")); ...'   -> OK
setup: composer install | copy .env | key:generate |
       migrate --seed --force | storage:link
$ php artisan storage:link
   INFO  The [public/storage] link has been connected to [storage/app/public].
$ git ls-files | grep '\.sql$'      -> laravel/civilwatch_FINAL.sql   (only one)
$ git grep civilwatch_seed|laravel/civilwatch.sql  -> none
```

**Graph** — `graphify update .` + `cluster-only` + `export wiki`:
2055 nodes / 2714 edges / 138 communities / 148 wiki articles.
`laravel/docs/SETUP_PC.md` is indexed as its own community,
`CivilWatch — PC setup (XAMPP MySQL)`, and
`graphify query "PC setup XAMPP phpMyAdmin database import"` returns
`SETUP_PC.md`, `One-time database import` and the doc root.

**Not verified — stated plainly:**

- **phpMyAdmin on Windows** — no Windows machine here. The click-path is
  documented, and the file it imports was loaded into a scratch MariaDB and
  checked, but the browser import was not performed.
- **`flutter run`** — no Flutter SDK, no Android SDK, no disk space for one.
- **`composer build` / `npm run build`** — no Node build was run. Only
  confirmed by `git grep @vite` that no served view needs it.
- **XAMPP Apache as a vhost** — not part of this setup; Laravel's built-in
  server is used instead.
- **PHP is not in the graphify graph** (`tree_sitter_php` cannot build on
  Termux), so `laravel/composer.json` and the PHP behind this flow are read
  directly rather than queried.

## Impact / how to test by hand

**On the PC (XAMPP):**

1. `git clone https://github.com/dertmo01/civmain.git && cd civmain/laravel`
2. Start MySQL in the XAMPP Control Panel.
3. Open <http://localhost/phpmyadmin> → **Import** → choose
   `civilwatch_FINAL.sql` → **Go**.
4. `cp .env.example .env && php artisan key:generate`
5. `php artisan serve --host=127.0.0.1 --port=8000`
6. Open <http://127.0.0.1:8000> (HTML dashboard) or
   <http://127.0.0.1:8000/admin/login> (Blade panel).
7. Log in as `admin@civilwatch.ph` / `Admin@2026!` — expect the dashboard
   with 5 offices, 2 announcements and 3 reports (2 Rejected, 1 Assigned).
8. Register a citizen with a new mobile number + 6-digit PIN, submit a
   report, then reject it from the dashboard with a reason and confirm the
   reason appears in that report's timeline.

**On Android:** unchanged — your menu's **CIVILWATCH · START WEB** entry
still runs `php artisan serve` and opens Brave at `http://127.0.0.1:8000`.

**On the PC with Flutter:**

```bash
cd civ-main && flutter run -d chrome \
  --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
```

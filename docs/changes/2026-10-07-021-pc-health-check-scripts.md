# PC health-check scripts (Windows + Termux) and companion repo

- **Date:** 2026-10-07
- **Commit:** `dcc171c`
- **Type:** feature
- **Areas:** Laravel / docs

## Problem

After every reboot, or whenever something broke, the only way to know whether
the CivilWatch PC setup was healthy was to click through it one page at a time:
is Apache up, is MySQL up, does the admin page load, does the API answer, is
the photo CORS header still there, is the working tree pushed? The owner also
asked for a check he could run from his Android phone (Termux) while away from
the keyboard, and for a separate GitHub repo holding it.

## Cause

No check existed. The knowledge of what "healthy" means lived only as prose in
earlier changelogs (017 run scripts, 019 backend, 020 storage link + CORS) —
nothing executed it.

## Changes

| File | Change |
|---|---|
| `check_pc.ps1` | New: 12-check PowerShell health check — XAMPP processes (httpd/mysqld), pages `/`, `/admin/login`, `/phpmyadmin`, `/api/ping` (must contain "CivilWatch API"), community feed (proves the DB), backend `:8000`, `public/storage` symlink, report photo with `Access-Control-Allow-Origin: *`, mysql CLI counts (`civilwatch` ≥ 16 tables + citizens/reports), and git sync (`HEAD` == remote `main`, clean tree). Exit 0/1, per-failure fix hints, `-Repo` to point at another checkout (defaults to the script's own folder). |
| `check_pc.sh` | New: 5 HTTP checks through Apache port 80 (`/`, admin login, ping, community feed, phpMyAdmin) with fix hints. `./check_pc.sh <PC-LAN-IP>` runs it from Termux; port 8000 is intentionally not checked from outside because `php artisan serve` binds `127.0.0.1`. |
| `CHECK_PC.bat` | New: double-click wrapper — runs `check_pc.ps1` and pauses so the window stays readable. |
| companion repo | Same three files + `README.md` (Windows + Termux instructions, token setup for the private clone, firewall note, verified sample output) pushed to the new **private** repo `dertmo01/civilwatch_check_pc`, HEAD `87ceb63`. |

Two commits: `dcc171c` (add the scripts) and `f2a0c6e` (fix — the mysql query
must select the `civilwatch` database; without it `SELECT COUNT(*) FROM
citizens` failed with `ERROR 1046 (3D000) No database selected`).

## Verification

- PowerShell parse of `check_pc.ps1`: 0 errors.
- First run caught the bug in my own query (`FAIL Database civilwatch — ERROR
  1046 No database selected`); after `f2a0c6e`, run from the civmain checkout:

```
PASS  Apache (XAMPP) running             2 httpd process(es)
PASS  MySQL (XAMPP) running              1 mysqld process(es)
PASS  Website /                          HTTP 200
PASS  Admin login page                   HTTP 200
PASS  API ping (Laravel + PHP)           HTTP 200
PASS  Community feed (database works)    HTTP 200
PASS  phpMyAdmin                         HTTP 200
PASS  Backend :8000 (Flutter app)        HTTP 200
PASS  Storage link (public/storage)      linked to storage/app/public
PASS  Report photo + CORS                HTTP 200, ACAO=*
PASS  Database civilwatch                16 tables, 2 citizens, 4 reports
PASS  Git sync                           HEAD == backup/main (f2a0c6e)

RESULT: ALL 12 CHECKS PASSED            (exit 0)
```

- `bash check_pc.sh 127.0.0.1` under Git-bash: `RESULT: ALL 5 CHECKS PASSED`,
  exit 0 (all five URLs independently confirmed 200 first, ping body confirmed
  to contain `CivilWatch API`).
- Pushes verified with `git ls-remote` == `git rev-parse HEAD`: civmain
  `backup/main` after each push; `civilwatch_check_pc` `origin/main` `87ceb63`.

**Not verified:** running `check_pc.sh` from a real Android Termux device — no
phone on this machine; the LAN path depends on same-Wi-Fi + the firewall rule
documented in the README. `graphify update .` could not run — the graphify CLI
is not installed on this Windows machine, so `graphify-out/` does not include
these two new files.

## Impact / how to test by hand

1. Double-click `CHECK_PC.bat` in the civmain folder → expect
   `RESULT: ALL 12 CHECKS PASSED`.
2. Stop Apache in the XAMPP Control Panel → rerun → the first checks FAIL with
   the hint *"Open the XAMPP Control Panel and start Apache"*; restart and it
   passes again.
3. From Android Termux on the same Wi-Fi:
   `git clone <token>@github.com/dertmo01/civilwatch_check_pc.git`,
   `bash check_pc.sh <PC-IP>` → 5 PASS.

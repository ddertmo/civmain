# civilwatch_check_pc

> **Tests:** the Playwright `e2e/` suite and `RUN_TESTS.bat` were removed (see
> `AGENTS.md`). Backend tests run with
> `cd laravel && C:\xampp\php\php.exe artisan test` (includes the security
> regression tests from docs/changes 077/078).

Health checks for the **CivilWatch** local setup (XAMPP + Laravel + Flutter on
Windows). One command tells you what is running, what is broken, and how to fix
it — either **on the PC** or **from your Android phone (Termux)** over the same
Wi-Fi.

This is a companion to the main project repo (`dertmo01/civmain`), which also
contains a copy of these scripts at its root.

## Files

| File | Runs on | What it checks |
|---|---|---|
| `CHECK_PC.bat` | Windows (double-click) | Runs `check_pc.ps1` and waits for a key press |
| `check_pc.ps1` | Windows PowerShell | Everything: services, web pages, API, database counts, storage link, photo CORS, git sync |
| `check_pc.sh` | Termux / Linux / macOS | HTTP checks only (Apache, admin, API, database via community feed, phpMyAdmin) |

## Run it on the PC (full check)

In the **civmain** folder: double-click **`CHECK_PC.bat`**.

Or from a terminal:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File check_pc.ps1
# point it at a checkout somewhere else:
powershell -NoProfile -ExecutionPolicy Bypass -File check_pc.ps1 -Repo C:\Users\you\Documents\civmain
```

Exit code `0` = all checks passed, `1` = something failed (the failing checks
print a "How to fix" hint).

## Run it from Android Termux (HTTP check)

The GitHub repo is **private**, so first create a one-time access token:

1. On the phone (or PC) open: GitHub → **Settings → Developer settings →
   Personal access tokens → Tokens (classic) → Generate new token**
2. Scope: tick **`repo`** only → Generate → **copy the token**
3. On Termux:

```bash
pkg install git curl
git clone https://github.com/dertmo01/civilwatch_check_pc.git
cd civilwatch_check_pc
bash check_pc.sh 192.168.1.14        # <- your PC's IPv4 address
```

The first `git clone` asks for your GitHub **username** and the **token**
(your password alone will not work).

**Find the PC's IP:** on Windows run `ipconfig` and look for
`IPv4 Address . . . . . . . . . . . : 192.168.x.x`. Phone and PC must be on the
same Wi-Fi.

**If every check times out:** Windows Firewall is blocking Apache. Allow
"Apache HTTP Server" through the firewall (inbound rule, port 80).

> Why port 80 and not 8000? Apache serves the whole Laravel app (including
> `/api/...`) on port 80 and listens on the LAN, while `php artisan serve`
> (port 8000) is bound to `127.0.0.1` only — it is unreachable from a phone by
> design. The HTTP checks therefore go through Apache.

## The 12 Windows checks

1. Apache (XAMPP) running
2. MySQL (XAMPP) running
3. Website `/` → 200
4. Admin login page → 200
5. API `/api/ping` → 200 + "CivilWatch API" (proves PHP executes)
6. Community feed → 200 (proves the database works)
7. phpMyAdmin → 200
8. Backend `:8000` (what the Flutter app calls)
9. `public/storage` symlink exists
10. Report photo loads with `Access-Control-Allow-Origin: *` (CORS)
11. Database `civilwatch`: ≥ 16 tables + citizen/report counts
12. Git sync: local `HEAD` == remote `main`, working tree clean

The 5 Termux checks are items 3–7 (all through port 80).

## Expected output (verified 2026-10-07 on the real machine)

```text
CivilWatch PC health check  (2026-10-07 20:57:36)
Repo folder: C:\Users\Administrator\Documents\civmain

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

RESULT: ALL 12 CHECKS PASSED
```

`check_pc.sh 127.0.0.1` was also verified on the same machine:

```text
PASS  Website /                        HTTP 200
PASS  Admin login page                 HTTP 200
PASS  API ping (Laravel + PHP)         HTTP 200
PASS  Community feed (database)        HTTP 200
PASS  phpMyAdmin                       HTTP 200

RESULT: ALL 5 CHECKS PASSED
```

**Not tested:** running `check_pc.sh` from a real Android Termux device (no
phone available) — the script was run with Git-bash on the PC against
`127.0.0.1`; the LAN path depends on the phone and firewall note above.

## Common fixes

| Failing check | Fix |
|---|---|
| Apache | XAMPP Control Panel → Start Apache |
| MySQL / feed / database | XAMPP Control Panel → Start MySQL |
| Website / admin | `httpd.conf` DocumentRoot must be `.../civmain/laravel/public` |
| Backend :8000 | Double-click `RUN_APP.bat` (it starts `php artisan serve`) |
| Storage link | `cd laravel && php artisan storage:link` |
| Photo + CORS | CORS block at the bottom of `laravel/public/.htaccess` |
| Git sync | Commit, then `git push backup main` (never `origin`) |

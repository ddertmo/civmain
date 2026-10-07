# CivilWatch — PC setup (XAMPP MySQL)

How to take the code from GitHub and get it running on a Windows PC with
XAMPP: import one SQL file, start the server, log in as admin, register a
citizen, submit a report.

There are two machines in this project:

| | Android / Termux (development) | PC / XAMPP (testing) |
|---|---|---|
| Database | Termux MariaDB | XAMPP MySQL |
| Code sync | `git push backup main` | `git pull backup main` |
| Web UI | your Termux menu launcher → Brave | browser → `http://127.0.0.1:8000` |
| Flutter | not used (no disk space) | `flutter run -d chrome` |

---

## Prerequisites

- **PHP 8.2 or newer** (this project is developed on PHP 8.5.1)
- **Composer**
- **XAMPP** — only for its MySQL and phpMyAdmin. Apache is *not* required;
  Laravel runs on its own built-in server.
- **Flutter SDK** — only if you want to run the mobile app on the PC
- **Git**
- **Node.js is optional.** Nothing that gets served uses Vite — only the
  stock `resources/views/welcome.blade.php` references `@vite`. Skip it
  unless you touch that file.

---

## Get the code

```bash
git clone https://github.com/dertmo01/civmain.git
cd civmain/laravel
```

Already cloned? `git pull backup main`.

---

## One-time database import

This is the fast path — one file, no artisan commands.

1. Start **MySQL** from the XAMPP Control Panel.
2. Open <http://localhost/phpmyadmin>.
3. Click the **Import** tab.
4. Choose **`civilwatch_FINAL.sql`** (it is in `laravel/`).
5. Click **Go**.

The file creates the database for you (`CREATE DATABASE IF NOT EXISTS
civilwatch`), drops nothing you care about, and loads the schema plus seed
data:

- 15 tables (modern schema only — no legacy `reports`/`notifications`)
- `citizen_reports.status` ENUM **includes `Rejected`** (required for the
  reject-with-notes flow)
- 2 admin accounts, 5 government offices, 2 announcements
- 1 sample citizen, 3 sample reports (2 Rejected, 1 Assigned), 9 activities,
  6 notifications — so every screen has something to show

**Verify the import** (phpMyAdmin → SQL tab):

```sql
SELECT COUNT(*) FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = 'civilwatch';          -- expect 15

SELECT COLUMN_TYPE FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = 'civilwatch'
   AND TABLE_NAME  = 'citizen_reports'
   AND COLUMN_NAME = 'status';               -- must contain 'Rejected'
```

---

## .env and APP_KEY

`.env` is gitignored, so a fresh clone will not have one:

```bash
cp .env.example .env
php artisan key:generate
```

`.env.example` already points at MySQL/XAMPP:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civilwatch
DB_USERNAME=root
DB_PASSWORD=
```

XAMPP's default is `root` with an **empty password** — leave `DB_PASSWORD`
empty unless you changed it.

---

## Run the backend

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Then open:

- <http://127.0.0.1:8000> — the HTML/JS admin dashboard
- <http://127.0.0.1:8000/admin/login> — the Blade admin panel

If you will run the Flutter app from another device on your network, bind to
`0.0.0.0` instead of `127.0.0.1` and use your PC's LAN IPv4.

---

## Alternative: composer setup

If you would rather not click through phpMyAdmin:

```bash
composer setup
```

which runs `composer install`, copies `.env`, `key:generate`,
`migrate --seed --force`, and `storage:link`.

**You still need a database for it to connect to.** Create an empty
`civilwatch` database in phpMyAdmin first (SQL tab: `CREATE DATABASE
civilwatch;`). This path builds the schema from migrations and seeds it —
you get the admin account, offices and announcements, but **not** the sample
reports the SQL import provides.

Running `composer setup` *after* a SQL import is safe: every seeder uses
`firstOrCreate`, and the imported `migrations` table already records all 12
migrations, so nothing runs twice.

### Node is separate now

```bash
composer build    # npm install && npm run build
```

Only needed if you edit `resources/views/welcome.blade.php`.

---

## Android with your menu

Nothing changes on Android. Your Termux menu launcher
(`~/bin/termux-help-menu` → **CIVILWATCH · START WEB**) already runs:

```bash
cd "$HOME/Prd/CIVILWATCH/prc/laravel" && php artisan serve --host=127.0.0.1 --port=8000
```

then waits for port 8000 and opens Brave at `http://127.0.0.1:8000`.

The two machines are independent — each has its own database. Code moves
between them through GitHub; data does not.

---

## Admin login

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@civilwatch.ph` | `Admin@2026!` |
| CEO | `ceo@civilwatch.ph` | `Ceo@2026!` |

Only **super_admin** can write. CEO/CENRO can read every admin page but all
mutation endpoints return `403 Super administrator access is required.`

Dashboard → Reports lets you validate, assign and reject. Rejecting requires
a reason (the API answers `422` without one) and sets `is_public = 0`, which
removes the report from the public community feed.

---

## Citizen registration and report submission

No seed data needed — registration is self-service.

1. Open the app and register with a mobile number and a 6-digit PIN
   (minimum two-part full name, choose a barangay).
2. Sign in with the same number and PIN.
3. Submit a report: category, title, description, barangay, severity.
4. It appears in **My Reports** with a status badge, and in the admin
   dashboard once validated.

API, if you want to test with curl from another terminal:

```bash
# register
curl -s -X POST http://127.0.0.1:8000/api/mobile/auth/register \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"fullName":"Juan Dela Cruz","phone":"+639171234567","barangay":"San Jose","pin":"123456"}'

# log in
curl -s -X POST http://127.0.0.1:8000/api/mobile/auth/login \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"phone":"+639171234567","pin":"123456"}'
```

---

## Flutter on the PC

The base URL is chosen at build time with `--dart-define`; the default is
`http://127.0.0.1:8000/api`, which is exactly right when the backend runs on
the same machine.

```bash
cd civ-main
flutter pub get
flutter run -d chrome --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
```

Other targets:

```bash
# Android emulator (host loopback)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api

# Real device on the same WiFi — backend must be on --host=0.0.0.0
flutter run --dart-define=API_BASE_URL=http://<pc-lan-ip>:8000/api
```

---

## Troubleshooting

**`could not find driver` / SQLSTATE connection refused**
MySQL is not running, or `.env` still says `DB_CONNECTION=sqlite`. Start
MySQL in XAMPP and confirm the five `DB_*` lines.

**Access denied for user 'root'@'localhost'**
XAMPP's default root password is empty. If you set one, put it in
`DB_PASSWORD`.

**Import fails on `civilwatch` already existing**
Use the phpMyAdmin **Drop** on the old database first, or run the import
with "Ignore errors" unchecked — the dump uses `CREATE DATABASE IF NOT
EXISTS` and `DROP TABLE IF EXISTS`, so re-importing over itself is safe.

**Uploaded report photos return 404**
`php artisan storage:link` was not run. `composer setup` now does it.
On Windows, creating symlinks needs Administrator or Developer Mode; if it
fails, either run a terminal as Administrator or skip photo uploads.

**`SQLSTATE[HY000] [1045] Access denied` after `composer setup`**
`composer setup` connects to a database before you create it. Create an
empty `civilwatch` database first, or import `civilwatch_FINAL.sql` and skip
`composer setup` entirely.

**Port 8000 already in use**
```bash
php artisan serve --host=127.0.0.1 --port=8080
```
Remember to change `API_BASE_URL` to match if you use Flutter.

**A rejected report is still visible on the public map**
The status must be exactly `Rejected`; the row's `is_public` must be `0`.
Both are set together by the reject endpoint.

---

## Not verified

This guide was written inside Termux on Android. The following were **not**
executed on a real Windows PC and are documented from verified local
equivalents rather than from a PC run:

- the phpMyAdmin click-path (the dump itself **was** imported into a scratch
  database and checked — 15 tables, `Rejected` in the ENUM, row counts
  matching the live database)
- `flutter run` (no Flutter SDK on this machine)
- `composer build` / `npm run build` (no Node build performed; only confirmed
  that no served view references `@vite`)
- running under XAMPP Apache as a vhost (not part of this setup — Laravel's
  built-in server is used instead)

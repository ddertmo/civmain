# Migrations verified on MariaDB; rollback of the index migration fixed

- **Date:** 2026-11-17
- **Commit:** `(see git log)`
- **Type:** fix + verification
- **Areas:** Laravel (migrations)

## Problem

The new migrations from 077–079 had only run on SQLite, through the test suite. The MySQL-only parts had never run on a real MySQL/MariaDB server: the status `ENUM` changes, `->change()` on `otp_codes.code`, and the index guards on the imported dump.

Running everything on MariaDB found one bug. `migrate:rollback` of `2026_11_17_000002_add_performance_indexes` failed halfway with:

```
SQLSTATE[HY000]: General error: 1553 Cannot drop index 'cn_citizen_read_idx':
needed in a foreign key constraint
```

## Cause

When an index whose first column is a foreign-key column is added, here `citizen_notifications(citizen_id, is_read)`, MySQL/MariaDB silently drops the index it had created for the `citizen_id` foreign key and uses the new one instead. Dropping the new index then leaves the foreign key without an index, so the server refuses. `migrate` was never affected; only `down()` was.

## Changes

| File | Change |
|---|---|
| `laravel/database/migrations/2026_11_17_000002_add_performance_indexes.php` | `down()`: before dropping an index that starts with a foreign-key column (`FK_COLUMNS`), it adds a plain index on that column unless another index already starts with it (`keepForeignKeyIndex`, via `Schema::getIndexes`). |

## Verification (run on this machine)

**Server:** MariaDB 10.11.20 (portable zip from mariadb.org, SHA-256 verified), run temporarily on `127.0.0.1:3306` with a throwaway data folder and passwordless root, like XAMPP. It was shut down afterwards.

**1. Imported-dump path** (how START_ALL.bat sets up the PC):
- `civilwatch_FINAL.sql` was imported the same way the script does (filtering `M!999999` lines): 15 tables, 2 users, 1 citizen, 3 reports.
- `php artisan migrate` ran the 7 pending migrations, all DONE:
  - `create_sessions_table`
  - `add_approved_status`
  - `add_priority`
  - `add_address_to_citizens`
  - `add_notifications_read_at`
  - `add_performance_indexes`
  - `widen_otp_codes_code`
- Schema checked afterwards:
  - `status` enum now includes `Approved` and `Rejected`
  - `priority` enum, `street`/`purok`/`province`, `notifications_read_at` and `otp_codes.code varchar(64)` are present
  - all 7 new indexes are present
  - row counts unchanged
- A second `migrate` reported "Nothing to migrate".

**2. Fresh empty database** (`civilwatch_test`):
- The full test suite ran against MariaDB: **OK (27 tests, 94 assertions)**. Each test migrates from scratch, so this covers every migration plus the MySQL-specific code paths: `lockForUpdate` transitions, the ENUM statuses and the hashed OTP column.
- `migrate` (20 migrations) → `migrate:rollback --step=3` → `migrate` → `migrate:reset` → `migrate`, with no errors after this fix.
- After the rollback, the `citizen_id` foreign key keeps `citizen_notifications_citizen_id_index`.

**3. SQLite** (default `php artisan test`): still 27 passed, 94 assertions.

**Not covered:** the exact MariaDB version on the XAMPP PC is unknown; XAMPP usually ships 10.4. Nothing used here is newer than what 10.4 supports. Still, run `php artisan migrate` once on the PC.

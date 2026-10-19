# Flutter registration returned 500 — `artisan migrate` had been dying on a record-less `sessions` table, so the citizens address columns never existed

- **Date:** 2026-10-19
- **Commit:** `67216c3`
- **Type:** fix
- **Areas:** Laravel

## Problem

`POST /api/mobile/auth/register` from the Flutter app returned **500** on every
attempt (user report with console trace, 2026-10-19). Reproduced verbatim with
the same payload via `Invoke-WebRequest`:

```
STATUS: 500
{"success":false,"message":"Registration failed. Please try again."}
```

Note: the user's console showed the text `error at creating account or
register` — that string exists **nowhere** in this repo (searched every PHP
file under `laravel/` and every Dart file under `civ-main/lib`); the live
server only ever returns the JSON above for this endpoint. The message text is
a red herring; the failure below is the real one.

## Cause

Two stacked problems:

1. **The controller inserts columns the table doesn't have.**
   `MobileAuthController@register` creates a citizen with `street`, `purok`
   and `province`, but `citizens` had only `id, full_name, email, phone,
   barangay, city, pin_hash, is_active, timestamps` — MySQL error **1054
   Unknown column** → `QueryException` → the method's safety-net catch
   (error 1054 ≠ 1062) → the generic 500. The columns were supposed to come
   from migration `2026_10_15_000002_add_address_to_citizens_table`.
2. **That migration (and two others) could never run.** `php artisan migrate`
   died on the *first* pending migration,
   `2026_10_07_193800_create_sessions_table`, with **1050 Base table already
   exists**: the database already contained a `sessions` table with no matching
   row in `migrations` (dump-restored DB whose bookkeeping lagged reality).
   Because migrate aborts on the first failure, every later migration —
   address columns, the `Approved` enum widening, `priority` — stayed Pending
   forever, silently.

## Changes

| File | Change |
|---|---|
| `laravel/database/migrations/2026_10_07_193800_create_sessions_table.php` | `up()` now returns early when `Schema::hasTable('sessions')` (8 lines, comment included) — same guard style the repo's other migrations already use, so a pre-existing table is recorded as ran instead of failing the whole run |

**Database state (not a diff, applied on this machine):** `php artisan migrate
--force` then ran all four pending migrations to completion:

- `create_sessions_table` — recorded, guarded
- `add_approved_status_to_citizen_reports` — `citizen_reports.status` ENUM now
  includes `'Approved'` (previously the approve flow would have been rejected
  by MySQL too)
- `add_priority_to_citizens…` (`add_priority_to_citizen_reports`) — `priority`
  enum column added
- `add_address_to_citizens_table` — `street`, `purok`, `province` added

Also created-and-cleaned: verification registered a test citizen (the user's
exact payload) and **deleted it again** (`citizens` back to its original 1 row,
token removed), so the user can still register from the app UI.

The tracked dump `laravel/civilwatch_FINAL.sql` still predates these columns —
with the guard, a fresh import + `artisan migrate` now completes instead of
dying at `sessions`, so the dump itself did not need regenerating.

## Verification

- **Before:** `SHOW COLUMNS FROM citizens` → no `street`/`purok`/`province`;
  `artisan migrate:status` → 3 Pending; POST register → **500**.
- **Fix:** `artisan migrate --force` → all 4 migrations **DONE**;
  `migrate:status` → **no Pending**.
- **After:** POST `/api/mobile/auth/register` with the exact console payload →
  **201** `{"success":true,...}` with token `14|8c1i…` and the citizen echoed
  back including `street:"avenue"`, `purok:"tres"`, `province:"Davao del Sur"`.
- Test row then removed (`SELECT COUNT(*) FROM citizens` → 1).
- `graphify update .` — **not run** (no Python/`graphify` on this Windows PC;
  documented per project convention).

## Impact / how to test by hand

1. Flutter app → Register screen → sign up with **any fresh phone number**
   (the number from the console is now free again — I cleaned up my test row).
2. Expected: registration succeeds, token issued, you land logged in as the
   new citizen.
3. Existing accounts are untouched (`citizens` had 1 row before and after).
4. Side benefit: report **approve** now works at DB level (enum widening), and
   `citizen_reports.priority` exists for whatever sets it.

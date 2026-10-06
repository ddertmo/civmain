# Gate every admin write behind super_admin

- **Date:** 2026-10-06
- **Commit:** `9bfeab2`
- **Type:** feature
- **Areas:** Laravel

## Problem

There was no server-side role check anywhere in the admin API or the admin
web panel. Any account that could log in could do anything an administrator
could — including creating new accounts with the `super_admin` role.

The `users` page comment already claimed the user endpoints were
"super_admin only", but no code enforced it.

## Cause

* `routes/api.php:80` put `/api/admin/*` behind `auth:sanctum` only — it
  proved *who you are*, never *what you may do*.
* `routes/web.php:16` put `/admin/*` behind `auth` only, same gap.
* The legacy `Route::post/put/delete('/users…')` routes had a `// Users
  (super_admin only)` comment and no middleware to go with it, so a `ceo`
  session could `POST /api/users` and mint a `super_admin`.
* `ReportController` did define a `requireRole()` helper, but it was
  unreachable (every call site errored) and it left with `3e21b70`.

The prototype HTML has a client-side `requireAuth()` at
`public/js/app.js:153`, but it only checks that `cw_token` exists in
`localStorage`. The catch-all at `routes/web.php:38` serves any
`public/*.html` with no session check at all.

CEO/CENRO pages make **zero** API calls (they are static shells), so gating
writes cannot break them; and gating only writes keeps every dashboard,
report list and chart readable for staff.

## Changes

| File | Change |
|---|---|
| `laravel/app/Http/Middleware/EnsureSuperAdmin.php` | **New.** Passes `GET`/`HEAD`/`OPTIONS`; for every other verb requires `$request->user()->role === 'super_admin'`, else JSON `{success:false, message:'Super administrator access is required.'}` 403 on `api/*` / `expectsJson()`, or `redirect()->back()->with('error', …)` elsewhere (the Blade layout already renders `session('error')`). |
| `laravel/bootstrap/app.php` | Registered as the `superadmin` alias. |
| `laravel/routes/api.php` | `/api/admin/*` group → `['auth:sanctum', 'superadmin']`; `->middleware('superadmin')` on the three `/api/users` mutation routes (listing stays readable). |
| `laravel/routes/web.php` | `/admin/*` group → `['auth', 'web', 'superadmin']`. |

Roles are a MySQL enum, `super_admin | ceo | cenro` — there is no `staff`.
Test account added: `ceo@civilwatch.ph` / `Ceo@2026!` (role `ceo`).

## Verification

`php artisan route:list --json` → 77 routes, **35** carry `EnsureSuperAdmin`.
Lint clean on all four touched files.

Live, with MariaDB + `php -S` restarted:

```
CEO (ceo@civilwatch.ph / Ceo@2026!)
  GET  /api/admin/citizen-reports   -> 200
  GET  /api/admin/users             -> 200
  GET  /api/analytics/summary       -> 200
  POST /api/admin/citizen-reports/1/status -> 403
       body: {"success":false,"message":"Super administrator access is required."}
  POST /api/admin/offices           -> 403
  POST /api/users                   -> 403
  PUT  /api/users/1                 -> 403
  DELETE /api/users/1               -> 403

super_admin (admin@civilwatch.ph / Admin@2026!)
  GET  /api/admin/citizen-reports   -> 200
  POST /api/admin/offices (invalid) -> 422   <- passed the gate, failed validation
  POST /api/admin/offices (valid)   -> 302, row created (id 6), then deleted
  POST /admin/offices   (invalid)   -> 302 back, no role error flashed

anonymous  POST /api/admin/offices  -> 401

Blade, logged in as ceo:
  GET  /admin/dashboard             -> 200
  GET  /admin/citizen-reports/1     -> 200
  POST /admin/citizen-reports/1/status -> 302 back to the same page,
       which renders "Super administrator access is required"

Data after every refused write (checked in SQL):
  citizen_reports 1/3 = Rejected,is_public 0; 2 = Assigned to Office,is_public 1
  report_activities = 9 rows (unchanged)
  users = 2 rows (admin super_admin, ceo ceo)
  government_offices = 5 rows (test row removed)
```

**Not verified:** `php artisan test` was not run — no covering test suite.
No Flutter SDK exists here; the app authenticates as a citizen against
`/api/mobile/*`, which is a separate guard (`auth:citizen`) and was not
changed. The prototype's client-side `requireAuth()` was not exercised in a
browser.

## Impact / how to test by hand

1. Sign in to `http://127.0.0.1:8000/` as `ceo@civilwatch.ph` / `Ceo@2026!`.
2. Browse — dashboards, reports, analytics all load (reads are open).
3. Any attempt to change something returns 403; from the Blade panel you get
   a red "Super administrator access is required." flash instead.
4. In DevTools, `fetch('/api/users',{method:'POST',headers:{Authorization:
   'Bearer '+localStorage.cw_token}})` should return 403.
5. Repeat as `admin@civilwatch.ph` and the same action succeeds.

# Unauthenticated admin pages returned 500 instead of redirecting to login

- **Date:** 2026-10-08
- **Commit:** `1f9c078`
- **Type:** fix
- **Areas:** Laravel

## Problem

A fresh PC setup (XAMPP + `civilwatch_FINAL.sql` + `.env.example`) served the
HTML prototype at `/` fine, but every protected Blade page returned **HTTP 500**
when opened without a session:

```
GET /admin/dashboard        -> 500
GET /admin/citizen-reports  -> 500
GET /admin/offices          -> 500
GET /api/analytics          -> 500   (no Accept header)
```

The error page said `Route [login] not defined`. `/admin/login` itself returned
200, so the login page worked — nothing could get *to* it. Only `/` and the
public API endpoints (`/api/ping`, `/api/mobile/*`) were reachable, which is
why the symptom was easy to mistake for a database or session problem.

## Cause

`laravel/vendor/.../Foundation/Configuration/ApplicationBuilder.php:278`
registers Laravel's default guest redirect:

```php
$middleware = (new Middleware)->redirectGuestsTo(fn () => route('login'));
```

This app defines **no route named `login`** — the Blade login is named
`admin.login` (`laravel/routes/web.php:15`). So the default callback throws
`Symfony\Component\Routing\Exception\RouteNotFoundException`.

The exception escapes from `Illuminate\Auth\Middleware\Authenticate::unauthenticated()`
(`Authenticate.php:104`):

```php
$request->expectsJson() ? null : $this->redirectTo($request)
```

`redirectTo()` invokes the callback *while building* the
`AuthenticationException` — i.e. **before** the exception handler runs. That is
why the `shouldRenderJsonWhen()` block already present in
`laravel/bootstrap/app.php` could not help: it only decides how to *render* an
exception that was successfully thrown, and this one was never reached. The
comment above that block claimed it prevented this error; it did not.

Whether a request hit the bug depended purely on the `Accept` header:

| Request | Path taken | Result |
|---|---|---|
| Browser, no `Accept: application/json` | `expectsJson()` false → calls `route('login')` | **500** |
| `Accept: application/json` | `expectsJson()` true → skips the callback | 401 (correct) |

So the JSON clients (Flutter, fetch with headers) worked while every real
browser visit to a protected page 500'd.

## Changes

| File | Change |
|---|---|
| `laravel/bootstrap/app.php` | Added `$middleware->redirectGuestsTo(fn () => route('admin.login'));` as the first statement of the `withMiddleware` callback, overriding the framework default with the route this app actually defines. |
| `laravel/bootstrap/app.php` | Rewrote the comment above `shouldRenderJsonWhen()` — it previously asserted this rule prevented `RouteNotFoundException`, which it cannot, because the throw happens in the auth middleware before the handler. Now states what the rule really controls and points at `redirectGuestsTo`. |

No route, controller, middleware or migration was modified. Behaviour of
already-working paths is unchanged.

## Verification

Run on Windows against `php artisan serve --host=127.0.0.1 --port=8000`,
XAMPP MariaDB 10.4.32 with `civilwatch_FINAL.sql` imported and
`php artisan migrate` run (the dump predates `create_sessions_table`).

**Before the fix** (`curl -s -o NUL -w "%{http_code} %{redirect_url}"`):

```
GET /admin/dashboard (no session)  -> 500
GET /api/analytics   (no Accept)   -> 500  {"message":"Route [login] not defined."}
GET /api/analytics   (Accept: json)-> 401  {"message":"Unauthenticated."}   <- already fine
```

**After the fix** — full endpoint sweep, 17 URLs:

```
GET / (HTML prototype)               200
GET /admin/login                     200
GET /admin/dashboard (guest)         302 http://127.0.0.1:8000/admin/login
GET /admin/citizen-reports (guest)   302 http://127.0.0.1:8000/admin/login
GET /admin/map (guest)               302 http://127.0.0.1:8000/admin/login
GET /admin/offices (guest)           302 http://127.0.0.1:8000/admin/login
GET /admin/announcements (guest)     302 http://127.0.0.1:8000/admin/login
GET /dashboard.html                  200
GET /api/ping                        200
GET /api/analytics (no Accept)       401
GET /api/analytics (Accept: json)    401
GET /api/admin/citizen-reports       401
GET /api/admin/offices               401
GET /api/admin/announcements         401
GET /api/mobile/announcements        200
GET /api/mobile/reports/community    200
GET /up                              200
```

302s were read with `curl` (it does not follow redirects); PowerShell's
`Invoke-WebRequest -MaximumRedirection 0` throws instead of returning the
status, so it only shows the followed 200.

**Authenticated and role-gate checks** (credentials from
`docs/SESSION_CONTEXT.md` §5):

```
POST /admin/login  admin@civilwatch.ph    -> 302 /admin/dashboard ; GET dashboard 200
POST /admin/login  ceo@civilwatch.ph      -> 302 /admin/dashboard
CEO GET /admin/{dashboard,citizen-reports,offices,announcements}  -> 200 200 200 200
POST /api/login    admin@civilwatch.ph    -> 200  {"token":"13|...","role":"super_admin"}
Bearer token GET /api/{analytics,admin/citizen-reports,admin/offices,
  admin/announcements,admin/users,analytics/by-category,
  admin/citizen-reports/summary}          -> 200 x7
CEO POST /api/admin/offices               -> 403   (superadmin write gate intact)
```

**Error log** was rotated (`laravel.log` → `laravel.log.bak`) after the fix so
post-fix failures could not hide among pre-fix ones; the full sweep above plus
the login/role tests produced **no `local.ERROR` entry at all** — no new log
file was created.

### Not verified

- **`graphify update .` was not run.** `graphify` (and Python/pip entirely) is
  absent from this PC — `python`, `python3`, `py`, `pip` all resolve to *not
  found*. `graphify-out/graph.json` therefore still reflects `1c755d4`, not
  `1f9c078`. Per `docs/SESSION_CONTEXT.md` §6b the missing install is
  `pip install graphifyy==0.9.76`.
- **No Flutter/Dart code was touched or run**, so the mobile app was not
  exercised; the `/api/*` 401/200 rows above are the API side only.
- **No browser was driven.** HTTP status codes were read with `curl`; the
  redirect target and page bodies were not visually inspected.

## Impact / how to test by hand

1. Start MySQL + Apache in XAMPP, then
   `cd laravel && php artisan serve --host=127.0.0.1 --port=8000`.
2. Open `http://127.0.0.1:8000/admin/dashboard` in a **fresh/incognito** window
   (no session cookie). **Before:** a Laravel "Route [login] not defined" 500
   page. **After:** you land on `/admin/login`.
3. Log in as `admin@civilwatch.ph` / `Admin@2026!` — you reach
   `/admin/dashboard`.
4. Log in as `ceo@civilwatch.ph` / `Ceo@2026!` — the four admin pages load, and
   a write (creating an office) is still refused with 403.
5. `curl http://127.0.0.1:8000/api/analytics` now prints
   `{"message":"Unauthenticated."}` with status 401 instead of a 500 page.

Anyone who had bookmarked a `/admin/*` URL, or whose session expired mid-visit,
was previously dumped on a 500 page with no way back to login.

# Report photos: missing storage link and CORS header

- **Date:** 2026-10-07
- **Commit:** `40b619b`
- **Type:** fix
- **Areas:** Web (admin/Flutter web)

## Problem

Submitting a report with a photo from the Flutter app (Chrome) logged two
errors for the uploaded file
`http://localhost/storage/report-photos/yu8fSvn4ouy6PO5KY0CBmn6VqStPY8zzmrU7O3pc.png`:

1. `... blocked by CORS policy: No 'Access-Control-Allow-Origin' header`
2. `GET ... net::ERR_FAILED 404 (Not Found)`

so the photo never rendered in the app.

## Cause

Two independent problems:

1. **404** — `php artisan storage:link` had never been run on this machine
   (fresh clone), so `laravel/public/storage` did not exist. Apache's
   DocumentRoot is `laravel/public`, and every `/storage/...` URL therefore
   hit a non-existent path. The uploaded file itself was fine, sitting in
   `laravel/storage/app/public/report-photos/`.
2. **CORS** — the Flutter web dev server runs on its own port (`:51502`), so
   the browser treats `http://localhost:80` (Apache) as a *different origin*
   and rejects the image XHR unless the response carries
   `Access-Control-Allow-Origin`. Apache sent no such header.

## Changes

| Where | Change |
|---|---|
| `laravel/public/.htaccess` | New `<IfModule mod_headers.c>` block: `Header set Access-Control-Allow-Origin "*"` for image extensions (`png|jpe?g|gif|webp|svg|ico`). |
| machine only | `php artisan storage:link` created the `public/storage` junction (gitignored, not part of the diff). |

## Verification

| Check | Result |
|---|---|
| `GET /storage/report-photos/yu8f….png` before fix | 404 + CORS block (user's console log) |
| `GET` same URL after `storage:link` | `HTTP 200`, 13716 bytes ✅ |
| `GET` with `Origin: http://localhost:51502` after `.htaccess` edit | `Access-Control-Allow-Origin: *` ✅ |
| Site regression after edit | `GET /` → `HTTP 200` ✅ (`.htaccess` is read per request) |
| `httpd -k graceful` | Failed: *no installed service "Apache2.4"* — Apache runs as a plain process here, not a Windows service. Harmless; not needed because `.htaccess` needs no restart. Stated honestly. |

**Not verified:** the Flutter app was not reloaded to re-fetch the image in
this session (the fix was verified at the HTTP level instead).

## Impact / how to test by hand

1. In the Flutter console press `R` (hot restart) or reload the Chrome tab.
2. Open the report CW-2026-00004 — its photo now loads instead of erroring.
3. After a reboot, nothing extra is needed: `storage:link` persists (it is a
   real junction on disk), and the `.htaccess` rule ships with the repo.

## Related note (no code change)

`GET /api/analytics/summary` returned 500 earlier: the route sits behind
`auth:sanctum`, and a request without a session **and** without
`Accept: application/json` makes Laravel try to redirect to a route named
`login`, which this app does not define → `Route [login] not defined`. The
admin SPA always sends JSON and a session cookie, so it works there; only
session-less probes hit this. Left as-is (documented, not fixed) to keep the
change single-purpose.

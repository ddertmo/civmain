# Require Playwright E2E: scaffold, run script and docs

- **Date:** 2026-10-13
- **Commit:** `<filled after push>`
- **Type:** feature
- **Areas:** tooling / docs / Laravel web admin

## Problem

Nothing in the project actually *ran* the web UIs and checked them. Starting
the stack opened Chrome and left it at that — every regression had to be found
by a person clicking around, and opencode had no repeatable way to reproduce a
bug before fixing it. There was no Playwright, no `e2e/`, and the run scripts
asserted nothing.

## Cause

The repo had no browser-automation / test-runner. The only tests were the two
stock Laravel PHPUnit examples and one Flutter `widget_test.dart`. The web
admin (HTML/JS at `/`, Blade at `/admin/login`) and the citizen API had no
end-to-end coverage, so "it works" was a guess.

## Changes

| File | Change |
|---|---|
| `IMPORTANT.md` | New. States Playwright E2E is required, explains what Playwright actually does (runs the browser, records pass/fail + screenshots + trace + console/network, which opencode then reads to fix bugs) and that it cannot run on Android/Termux. |
| `AGENTS.md` | New `## playwright (required)` section: run the suite after any web/`laravel/` change, read the trace on failure, note the PC-only limitation. |
| `e2e/package.json` | New isolated Playwright project (`@playwright/test`, `test`/`test:headed`/`test:debug`/`report` scripts). |
| `e2e/playwright.config.ts` | baseURL `http://127.0.0.1:8000`, chromium, list + HTML reporters, trace/screenshot/video on failure, `webServer` auto-starts `php artisan serve` (cwd `../laravel`, `reuseExistingServer`). |
| `e2e/tsconfig.json`, `e2e/.gitignore`, `e2e/README.md` | Project config, git-ignores for `node_modules`/reports, usage notes. |
| `e2e/tests/helpers.ts` | Admin UI login helper (localStorage token flow), API login helper, unique-phone generator. |
| `e2e/tests/admin.spec.ts` | HTML/JS admin sign-in → dashboard, dashboard widgets, pending reports; Blade `/admin/login` session sign-in. |
| `e2e/tests/community.spec.ts` | `/api/ping`, public community feed, guest redirect away from `dashboard.html`. |
| `e2e/tests/citizen.spec.ts` | Citizen register → login → submit report against `/api/mobile/*`; CEO write returns `403`. |
| `RUN_TESTS.bat` | New. Finds Node/PHP (XAMPP), installs deps/browsers if missing, runs the suite, prints the report hint on failure; optionally opens the report with `--keep-open`. Warns, never blocks. |
| `START_ALL.bat` | New step `[6/6] Playwright E2E` after the backend is up (calls `RUN_TESTS.bat`, warns on failure but keeps booting); steps renumbered `/5` → `/6`, Flutter section is step 7. |
| `README.md` | Prominent "Required when running CivilWatch" note linking `IMPORTANT.md` / `RUN_TESTS.bat`. |
| `laravel/docs/SETUP_PC.md` | New "Run the E2E tests (Playwright) — required" section, and a line in "Not verified". |

## Verification

Static review only. **The Playwright suite was not executed** — this machine is
Android/Termux, where Playwright has no browser binary (see `IMPORTANT.md`), and
no Node modules / browsers were installed. What was checked here:

- `node -v` / `npm -v` present (v26.4.0 / 11.20.0) so the config is well-formed
  Node/TS, but `npx playwright` is not installed and no Chromium exists.
- Selectors and endpoints were read from the real source before writing the
  specs: `public/index.html` (`#email`, `#password`, `#loginBtn`, redirect to
  `dashboard.html`), `resources/views/admin/login.blade.php`
  (`input[name="email"|"password"]`), `routes/api.php`, and the mobile
  controllers' validation rules (`category` in Infrastructure/Environment/
  Others, `concern`, `barangay` in the BARANGAYS list, `severity` in
  Minor/Moderate/Severe; register needs a two-word `fullName` and 6-digit
  `pin`).
- `php artisan test` was **not** re-run (PHP app not started in this session);
  no PHP files were changed.

The suite must be run on the PC to be considered verified:
`RUN_TESTS.bat` (or `cd e2e && npx playwright test`).

## Impact / how to test by hand

1. On the PC, start the stack (`START_ALL.bat`) or just `php artisan serve`.
2. Run `RUN_TESTS.bat`. It installs browsers on first run, then drives Chromium
   against `http://127.0.0.1:8000` and prints a pass/fail summary.
3. Deliberately break something (e.g. rename an admin route), re-run, and
   confirm the suite fails and writes a trace under `e2e/test-results/`.
4. `npx playwright show-trace e2e/test-results/<test>/trace.zip` to see the run,
   then fix and re-run until green.

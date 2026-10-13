# CivilWatch — Playwright E2E tests

End-to-end tests for the **web admin** (`http://127.0.0.1:8000`) and the
**citizen-facing API** the Flutter app calls.

> **Why this exists** is written up in [`../IMPORTANT.md`](../IMPORTANT.md).
> Read that first.

## Requirements (PC only)

- Node.js 18+ (`node -v`)
- The app dependencies installed (`laravel/vendor`, database imported)
- PHP on `PATH`, or `PHP_BIN` pointing at it (e.g.
  `C:\xampp\php\php.exe`)

Playwright **cannot** run on Android/Termux — it needs a real browser binary.

## First run

```bash
cd e2e
npm install
npx playwright install chromium
```

On Windows you can just double-click **`../RUN_TESTS.bat`**, which does all of
the above automatically and then runs the suite.

## Run the tests

```bash
cd e2e
npx playwright test              # headless
npx playwright test --headed     # watch it drive the browser
npx playwright show-report       # open the HTML report
```

If the backend is not already running, the config starts
`php artisan serve` for you and stops it afterwards.

Point the suite somewhere else:

```bash
E2E_BASE_URL=http://192.168.1.14  npx playwright test
```

## What is covered

| File | Area |
|---|---|
| `tests/admin.spec.ts` | HTML/JS admin login + dashboard, pending reports, Blade `/admin/login` |
| `tests/community.spec.ts` | `/api/ping`, public community feed, guest redirect |
| `tests/citizen.spec.ts` | citizen register → login → submit report; CEO write is `403` |

## Artifacts on failure

Chrome traces, screenshots and video are written to `test-results/`, and the
HTML report to `playwright-report/`. Both are git-ignored. The trace is the
useful one when opencode (or you) needs to see exactly what happened:

```bash
npx playwright show-trace test-results/<test>/trace.zip
```

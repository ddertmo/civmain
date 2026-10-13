# IMPORTANT — Playwright is required when running CivilWatch

**Playwright end-to-end tests are a required part of running this project.**
Do not consider the project "run" or a change "done" until the E2E suite has
been run. The suite lives in [`e2e/`](e2e/) and is driven by
[`RUN_TESTS.bat`](RUN_TESTS.bat) on Windows.

## Why this is here

CivilWatch has **three web surfaces** — the HTML/JS admin at `/`, the Blade
admin at `/admin/login`, and the Flutter web citizen app — plus a citizen API.
They are wired together by JavaScript, redirects, role gates and a
reject-with-a-reason flow. Small changes in one easily break another, and the
only way to *see* that is to actually drive the browser.

**Playwright** is a browser-automation and test-runner tool. It opens the real
pages, clicks and types like a person, checks the result, and records what
happened: pass/fail, **screenshots, a step-by-step trace, video, and the
browser's console and network log**.

Playwright does **not** fix bugs by itself. Its job is to *run the project and
show us exactly what happens*. Then opencode reads those artifacts, reproduces
the failure, fixes the code, and re-runs the suite to prove the fix. That is
the loop:

```
run (Playwright)  →  see what broke (trace / screenshot)  →  fix (opencode)  →  re-run (green)
```

Without this, "it works on my machine" is a guess. With it, every change is
verified against real pages before anyone loads the app by hand.

## Run it

On the PC:

```bat
RUN_TESTS.bat
```

or by hand:

```bash
cd e2e
npm install
npx playwright install chromium
npx playwright test
```

## When

- After any change to `laravel/`, the admin HTML/JS, or any citizen/web UI.
- Before saying a task is finished.
- When you want a bug reproduced reliably so it can be fixed.

## Not on the phone

Playwright **cannot run on Android/Termux** — there is no browser binary for
it. It runs on the Windows/PC side. If you are on Termux, say the E2E suite
could not be run instead of implying it passed.

See [`e2e/README.md`](e2e/README.md) for the details and coverage.

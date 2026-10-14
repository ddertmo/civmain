# IMPORTANT — Playwright has been removed

Playwright E2E is **no longer used** in this project. It was removed at the
user's request because running it on every change created noise and repeated
loops.

- Do **not** run `npx playwright test`.
- Do **not** re-add the `e2e/` suite or add Playwright tests to the code.
- `RUN_APP.bat` and `START_ALL.bat` do not run any test step.
- `RUN_TESTS.bat` and `e2e/` were deleted. They are recoverable from git history
  if they are ever wanted again.

## How UI changes are verified now

By inspecting the served HTML/JS/CSS directly and, when needed, loading the page
in a browser by hand. There is no automated browser suite.

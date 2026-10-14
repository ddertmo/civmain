# Approved reports leave Pending Reports

- **Date:** 2026-10-14
- **Commit:** `6e5704d`
- **Type:** fix
- **Areas:** Laravel (HTML/JS)

## Problem

After a report was approved it could still be listed under Pending Reports; it
should move out of Pending and appear under Monitoring.

## Cause

`laravel/public/pending-reports.html` merged the `Submitted` and
`Pending Validation` API responses and de-duplicated by id, but did not
re-assert the status afterwards. A row whose status had changed (cached or
racing response) could therefore survive in the list.

## Changes

| File | Change |
|---|---|
| `laravel/public/pending-reports.html` | the merged list is now filtered to `status === 'Submitted' \|\| status === 'Pending Validation'` |

## Verification

- Static review of the served `pending-reports.html`; the post-merge filter is
  present:
  ```
  return r.status === 'Submitted' || r.status === 'Pending Validation';
  ```
- Not exercised end-to-end (no browser suite after Playwright removal).

## Impact / how to test by hand

Approve a report, return to **Pending Reports** and refresh — it is gone; open
**Monitoring** — it is listed under Approved.

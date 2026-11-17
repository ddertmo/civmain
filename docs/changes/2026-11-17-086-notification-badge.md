# Notification badge: shows the count, hides at zero, clears once viewed

- **Date:** 2026-11-17
- **Commit:** see the index row (backfilled in the following commit)
- **Type:** fix (UI)
- **Areas:** web panel — `js/app.js` (shared by Super Admin, CEO and CENRO pages), `css/navbar.css`, `dashboard.html`

## Problem

1. **The bell showed a red dot with no number.** With no unread notifications, an empty red circle stayed on the bell. The dashboard's "Notifications" quick-action button did the same.
2. **Looking at the notifications didn't clear the red badge.** Opening the bell panel left the badge on until the user found and clicked "Mark all as read".

## Cause

1. **The badge was never hidden.** `.notif-badge` was always `display:flex`. At 0 unread the code only emptied its text, so the red circle stayed. The dashboard badge had an inline `display:flex`, which overrides `[hidden]`. The badge was also a fixed 18px wide, which crowded counts of 10 or more.
2. **Opening the panel didn't count as reading.** `openNotif()` only opened the panel. Read state was only sent to the server by the "Mark all as read" button.
3. **Duplicated badge code.** Three places (initial load, the 30s poll and the dashboard's own poll) set badge text separately.

## Changes

| File | Change |
|---|---|
| `laravel/public/js/app.js` | See the bullets after this table. |
| `laravel/public/css/navbar.css` | `.notif-badge` uses `min-width: 18px` with padding, so "2", "12" and "99+" fit. `.notif-badge[hidden]` and `.notif-badge:empty` use `display: none`. |
| `laravel/public/dashboard.html` | The quick-action badge starts hidden (`display:none`, `data-show-display="flex"`), is pill-shaped for wider counts, and is driven by `App.setUnreadCount`. |

**`js/app.js` changes:**
- **`setUnreadCount(n)`:** one function drives the bell badge, the dashboard quick-action badge and the count in the panel. At 0 the badges are hidden (`hidden`, plus inline `display:none`); above 99 they show "99+".
- **`openNotif()`:** opening the bell now marks the feed read for **this staff user** on the server (`POST /api/notifications/read-all`, per-user since 077) and clears the badge immediately. If the request fails, the badge comes back.
  - New items stay highlighted while the panel is open, so the user can still see what was new; the highlights clear on `closeNotif()`.
- **`markAllRead()`:** uses the same path, and shows an error toast if it fails.
- **30s poll:** skips the check while the panel is open, so the badge doesn't come back during viewing. If the server reports more unread than before, it reloads the list so the new items appear.

No backend change. The server already keeps read state per staff user (`users.notifications_read_at`, change 077), so one person viewing their bell does not clear anyone else's badge, and the citizens' own inboxes are untouched.

## Verification (real browser, local app, signed in as the CEO)

- With 0 unread, the badge is hidden (`display: none`, 0×0); no empty red dot.
- Two temporary test notifications were added to the local DB:
  - **On load:** the badge shows **"2"** (18×18), the server reports unread = 2, and 2 items are highlighted.
  - **Bell opened:** the badge is hidden, the server reports unread = **0**, and the 2 items stay highlighted.
  - **Panel closed:** the highlights clear.
  - **After a page reload:** the badge is still hidden and the 8 items show as read. The state is on the server.
- The test notifications were then deleted, and `php artisan test` still gives 27 passed (94 assertions).
- **Not covered:** the Flutter citizen app's notification screen. This change is the web panel used by the Super Admin, CEO and CENRO.

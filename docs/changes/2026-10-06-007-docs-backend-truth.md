# Docs: stop claiming the app has no backend

- **Date:** 2026-10-06
- **Commit:** `5db5ba6`
- **Type:** docs
- **Areas:** docs

## Problem

`civ-main/FEATURES.md` and `civ-main/INTEGRATION_CONTEXT.md` were written
before a backend existed and still asserted, in several places, that the app is
in-memory/prototype with fake auth, no photo upload and a demo status button.
Anyone reading them would conclude the Laravel API does not exist or is unused.

## Cause

The docs were never updated after `laravel/` was wired up through
`civ-main/lib/services/*`.

## Changes

| File | Change |
|---|---|
| `civ-main/FEATURES.md` | closing disclaimer replaced with the real backend description (`../laravel`, Laravel 12 + Sanctum, `--dart-define=API_BASE_URL`); §9 "Persistence: In-memory only" → server-backed + `SharedPreferences` token; "Dummy Data (pre-loaded)" table replaced by a **Data Sources** table mapping each dataset to its live endpoint and its `DummyData` fallback |
| `civ-main/FEATURES.md` | `Announcement` and `MapPin` model notes now say they are fed by `GET /api/mobile/announcements` and `GET /api/mobile/reports/community`, with `DummyData` only as fallback |
| `civ-main/INTEGRATION_CONTEXT.md` | added a **STATUS — SUPERSEDED** banner: the doc targeted a Supabase migration, the project now runs Laravel; sections 1.2, 1.3 and 8.x are historical |
| `civ-main/INTEGRATION_CONTEXT.md` | §1.2 Backend row → Laravel 12 + Sanctum; §1.3 "Zero backend connection" → describes the real services, base-URL override, and what is still dummy |
| `civ-main/INTEGRATION_CONTEXT.md` | §2.2 PIN **is** verified on login; §2.3 OTP goes to `/api/mobile/auth/{send,verify}-otp` (response still echoes the code — source comment says remove for production); §2.5 auth is real + Sanctum |
| `civ-main/INTEGRATION_CONTEXT.md` | §4.3 `geolocator ^13.0.2` is installed and used; §4.4 `image_picker ^1.1.2` present, multipart upload works; §4.5 reference number is **server-generated** |
| `civ-main/INTEGRATION_CONTEXT.md` | §5.5 demo Advance Status button marked **removed**; §6.1 notifications are API-backed (`citizen_notifications` table) |

Every claim was checked against the code before writing — e.g. government
offices are documented as *not* wired because `GET /api/admin/offices` is
admin-only and `AppState` has no offices loader.

## Verification

```bash
grep -n -i "no backend\|in-memory\|zero backend\|fake / local" civ-main/FEATURES.md civ-main/INTEGRATION_CONTEXT.md
# only the SUPERSEDED banner and intentional historical notes remain
```

## Impact / how to test by hand

Read `civ-main/FEATURES.md` §9 and `INTEGRATION_CONTEXT.md` §1.2–1.3 — they
should match what `lib/services/` actually does.

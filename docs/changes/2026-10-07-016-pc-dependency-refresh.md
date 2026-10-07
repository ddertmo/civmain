# Refresh Flutter and Composer dependencies for PC setup

- **Date:** 2026-10-07
- **Commit:** `994d6ed`
- **Type:** chore
- **Areas:** Flutter / Laravel

## Problem

The repo was freshly cloned onto a new Windows PC and nothing could run:
`civ-main/` had no resolved Flutter packages, and `laravel/` had no `vendor/`
at all, so `php artisan --version` would fail with
`vendor/autoload.php: No such file or directory`. Every future run also
required manual update steps before starting the project.

## Cause

`vendor/`, the pub cache and `.env` are machine-local and gitignored — a fresh
clone carries only `composer.lock`, `pubspec.lock` and `.env.example`, so each
machine must resolve dependencies once. Flutter was additionally not on this
machine's `PATH` (`flutter` → CommandNotFoundException), and `laravel/.env`
did not exist (only `.env.example`).

## Changes

| File | Change |
|---|---|
| `civ-main/pubspec.lock` | `flutter pub upgrade` resolved and locked 37 updated dependencies (`flutter_map` 8.3.1→8.3.2, `image_picker` 1.2.3→1.2.4, `geolocator_platform_interface` 4.2.8→4.4.0, `shared_preferences` 2.5.5→2.5.6, …). Within existing `^` constraints; 19 packages have newer major versions that are intentionally not taken. |
| `civ-main/analysis_options.yaml` | Flutter tool auto-upgraded it during `pub upgrade` to exclude `build/` and platform directories from analysis. |
| `laravel/composer.lock` | `composer update --no-interaction` installed `vendor/` (81 packages) and refreshed the lock; runtime is Laravel 12.69.3. |
| (machine, not committed) | Flutter SDK `C:\Users\Administrator\Downloads\flutter\bin` added to the user `PATH`; `laravel/.env` created from `.env.example` + `php artisan key:generate` (gitignored). |

## Verification

| Check | Command | Result |
|---|---|---|
| Flutter runs | `flutter --version` | `Flutter 3.47.0 • channel stable`, Dart 3.13.0 ✅ |
| Pub upgrade | `flutter pub upgrade` (in `civ-main/`) | `Changed 37 dependencies!` ✅ |
| Composer | `composer update --no-interaction` | 81 packages, `No security vulnerability advisories found` ✅ |
| Laravel boots | `php artisan --version` | `Laravel Framework 12.69.3` ✅ |
| App key | `php artisan key:generate --force` | `Application key set successfully.` ✅ |

**Not verified:** no `flutter run`/build was executed, no Flutter or PHPUnit
test suite was run — dependency resolution and CLI boot only.
**`graphify update .` could not run:** the graphify CLI is not installed on
this Windows machine (no `graphify` on PATH, no Python/npm/pipx install), so
`graphify-out/` remains the Termux export of 2026-10-07 and was not refreshed.

## Impact / how to test by hand

1. Open a terminal in `C:\Users\Administrator\Documents\civmain`.
2. `flutter --version` → prints 3.47.0 (was: CommandNotFoundException).
3. `cd laravel` then `php artisan --version` → `Laravel Framework 12.69.3`
   (was: missing `vendor/autoload.php`).

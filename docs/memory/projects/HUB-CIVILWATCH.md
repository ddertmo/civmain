# HUB: CIVILWATCH

## Goal
Citizen reporting system (Flutter + Laravel). Citizens submit reports; admins validate/assign with audit trail.

## Status
Active

## Key files
- laravel/app/Http/Controllers/* (Admin, Mobile)
- laravel/public/*.html (admin UI prototype)
- civ-main/ (Flutter app)
- docs/PROJECT_CONTEXT.md, docs/SESSION_CONTEXT.md, AGENTS.md

## Links
- Repo: /data/data/com.termux/files/home/Prd/CIVILWATCH/prc
- Graph: graphify-out/
- Changelog: docs/changes/README.md

## Sessions
- sessions/YYYY-MM-DD-<date>.md

## Constraints
Windows PC (XAMPP/Flutter) and Termux/Android — detect the machine; backup-only push (dertmo01/civmain), no Playwright, graph-first retrieval.

## Decisions
- Approved status is first-class (post-approve flow)
- One home per fact; link don't duplicate

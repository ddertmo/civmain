# graphify now runs on the PC — graph refreshed with SQL + PHP folded in

- **Date:** 2026-10-09
- **Commit:** `HEAD`
- **Type:** graphify
- **Areas:** graph / docs

## Problem

Changelog 032 recorded that the graphify CLI "could not be run: the graphify
CLI is not installed on this PC (only the graphify-out output files are present
in the repo)". That left this PC backup repo unable to refresh its own
knowledge graph after Flutter/Laravel changes, and the graph dated 2026-10-08
(2896 nodes). Additionally the SQL dump (`laravel/civilwatch_FINAL.sql`) was
not represented because `tree_sitter_sql` was missing.

## Cause

The environment had no Python tooling at all (no `python`, `pip` or `uv`), so
`graphifyy` (PyPI, double-y; imports/CLI as `graphify`) could not be installed.
The graph itself is committed, but every refresh had to happen on another
machine.

## Changes

| File | Change |
|---|---|
| `graphify-out/` | `graphify update .` run on the PC with `graphifyy==0.9.76` (pinned to `.graphify_version`). Rebuilt **2936 nodes / 4145 edges / 205 communities** (was 2896/4066/207). AST-only, no LLM API cost. |
| `graphify-out/cache/ast/v0.9.76-s5/*.json` | New extraction cache entries from the Windows rebuild (tree-sitter parsers install cleanly here — including `tree_sitter_sql`, so `civilwatch_FINAL.sql` now contributes nodes). |
| `graphify-out/2026-10-09/` | Curated-graph backup written by the update (same pattern as the tracked `2026-10-05` … `2026-10-08` dirs). |
| `docs/changes/README.md` | Index row 033 added. |
| `docs/SESSION_CONTEXT.md` | Graphify section updated: new node/edge/community totals and the PC CLI location. |

## Verification

```
graphify update .   -> Rebuilt: 2936 nodes, 4145 edges, 205 communities
                       (old: 2896 nodes, 4066 edges, 207 communities)
graphify explain "Role gate"
  -> Node: Role gate / laravel/docs/SUPER_ADMIN.md L126 / Type: document / Degree: 1
graphify query "Super Admin role gate EnsureSuperAdmin"
  -> 117 nodes found; first hits include
     laravel/app/Http/Middleware/EnsureSuperAdmin.php L20 and
     laravel/app/Http/Controllers/Admin/AdminWebController.php L14
```

**Not verified:** no `GEMINI_API_KEY`/`GOOGLE_API_KEY` on this machine, so
`graphify label` (LLM community naming) was not run — `update` renamed 62
communities by their hub instead, matching the note on this PC.

## Impact / how to test by hand

1. On this PC the CLI is
   `C:\Users\Administrator\Downloads\ls\graphify\.venv\Scripts\graphify.exe`
   (venv created with `uv`, package pinned to `0.9.76`).
2. `graphify query "<question>"`, `graphify path "A" "B"`, `graphify explain "X"`
   now work from this backup repo on the PC.
3. After future code changes run `graphify update .` here instead of waiting for
   a Termux session — PHP and SQL now parse on Windows.
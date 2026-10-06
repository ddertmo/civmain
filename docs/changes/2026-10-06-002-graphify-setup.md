# Graphify knowledge graph + opencode integration

- **Date:** 2026-10-06
- **Commit:** `ddbf5be`
- **Type:** graphify
- **Areas:** docs / graph

## Problem

No way to answer "where is X / what touches Y" without grepping the whole repo.
The Flutter screens, services and blade templates are scattered across two
subprojects (`civ-main/`, `laravel/`).

## Cause

Never built a graph.

## Changes

| File | Change |
|---|---|
| `AGENTS.md` | rules: run `graphify query/path/explain` before raw file reads; `graphify update .` after code changes |
| `.opencode/opencode.json` | registers `.opencode/plugins/graphify.js` |
| `.opencode/skills/graphify/` | the graphify skill + reference docs |
| `graphify-out/graph.json` | 1,892 nodes / 2,545 edges / 116 communities |
| `graphify-out/GRAPH_REPORT.md` | plain-language architecture report |

## Verification

```
graphify explain "ApiConstants"     # scoped subgraph, returns
graphify query "<question>"         # BFS, ~2k token budget
graphify path "<A>" "<B>"           # relationship between two symbols
```

**Known gap:** `tree_sitter_php` cannot be built on Termux
(`fatal error: 'tree_sitter/parser.h' file not found`), so the 119 Laravel PHP
files are **not** in the graph — only the 12 blade templates are. Kotlin has the
same problem. PHP questions must be answered by direct reads or
`graphify query` limited to docs/Flutter.

## Impact / how to test by hand

```bash
export PATH="$HOME/.local/bin:$PATH"
graphify query "how does report submission flow"
```

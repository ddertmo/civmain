# Graph-first retrieval rules + in-repo Obsidian/ICM memory structure

- **Date:** 2026-10-15
- **Commit:** `HEAD`
- **Type:** docs
- **Areas:** docs / graph / opencode config

## Problem

The agent's context loading was inconsistent. `AGENTS.md` told it to *consider*
graphify, but did not require loading only the minimal relevant subgraph, walk
1–2 hops, or treat the graph as the retrieval layer before answering. There was
also no single-source-of-truth ("one home per fact") rule, and no structured
place to store session notes / project status / routing like the
Obsidian + Graphify + ICM workflow the owner follows.

## Cause

No explicit context-minimization workflow in `AGENTS.md`; the graphify section
was advisory ("first run" list) rather than a mandated retrieval order. There
was no memory vault or ICM routing structure in the repo, so project status and
"where things live" were scattered across `docs/SESSION_CONTEXT.md` prose.

## Changes

| File | Change |
|---|---|
| `AGENTS.md` | Rewrote the `## graphify` section: graph-first context loading is now mandatory — `query` first, 1–2 hop `path`/`explain`, minimal relevant set (~5 notes/pages max), load context **before** answering, "one home per fact", only re-read what changed. |
| `docs/SESSION_CONTEXT.md` | Added a "Context-minimization (graph-first retrieval)" section mirroring the video workflow (memory = Obsidian, connections = Graphify, direction = ICM). |
| `docs/memory/` | New P-A-R-A vault mirror: `inbox/`, `projects/`, `areas/`, `resources/`, `archive/` + `README.md`. Includes `projects/HUB-CIVILWATCH.md`, `projects/sessions/2026-10-14-sync.md`, and a Zettelkasten note `resources/zettel-graph-first-retrieval.md`. |
| `docs/icm/` | New ICM (Interpretable Context Methodology) structure: `routing/ROUTING.md` (routing table to canonical locations), `clients/`, `projects/`, `references/`, `schema/`, `README.md`. |
| `docs/obsidian-setup.md` | Explains how the repo mirrors the Obsidian+Graphify+ICM workflow and how to open `docs/memory/` as a vault. |

Note: the GUI **Obsidian app cannot be installed** on Termux/Android via `pkg`
(no such package, ~1 GB disk, no installs). The vault folder is provided so the
owner can open it with the Obsidian app (Android/PC) if desired; opencode does
not require the GUI.

## Verification

- `git status --short` — only intended files changed/added before commit.
- `graphify update .` — **refused to overwrite** `graphify-out/graph.json`
  (new graph 2810 nodes vs existing 2936) because Termux lacks the
  `tree_sitter_php`/`tree_sitter_javascript` parsers the PC built the graph
  with. Left the PC-built graph intact; removed the orphaned untracked
  `graphify-out/cache/ast/*` files it created. **Graph not regenerated on this
  machine — by design.**
- Not verified: no Obsidian GUI (not installable here); no automated test
  runner for docs changes.

## Impact / how to test by hand

1. Read `AGENTS.md` `## graphify` — the graph-first order and "one home per
   fact" are explicit.
2. `ls docs/memory docs/icm` — P-A-R-A vault + ICM routing exist.
3. Open `docs/icm/routing/ROUTING.md` to see canonical locations.
4. For any codebase question, the agent should now run
   `graphify query "<question>"` first and answer from the small subgraph.

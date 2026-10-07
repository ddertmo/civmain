# Export session context for cross-machine handoff

- **Date:** 2026-10-07
- **Commit:** `9fdad84`
- **Type:** docs
- **Areas:** docs / graph

## Problem

The project is developed on Android/Termux and tested on a Windows PC with
XAMPP, but the working knowledge lived only inside one opencode session.
Starting opencode on the PC meant re-discovering everything the hard way:

- which of the two git repositories is ours (the parent
  `~/Prd/CIVILWATCH` has `origin = SerenoLawrence/CIVILWATCH` and must never
  be pushed to)
- that Termux has no Flutter SDK, no Android SDK, no Windows/XAMPP, ~1 GB
  disk, an unwritable `/tmp`, no LLM key, and parsers that cannot build
- the traps that had already been hit: services dying between tool calls,
  `pkill -f` killing the shell running it, and `php artisan serve` ignoring a
  `DB_DATABASE=` override
- the credentials, the live database contents and which SQL dump is canonical
- the architecture facts that keep being gotten wrong (two admin UIs kept on
  purpose, `concern` vs `title`, the reject reason living in
  `report_activities.description`, the missing `staff` role)
- how to query this graph effectively, and that Laravel PHP is not in it
- what is still open (Flutter verification path unchosen)

## Cause

Nothing recorded session knowledge. `AGENTS.md` held only the graphify and
changelog rules, `instruction.md` holds only the owner's preferences, and
`docs/changes/` records *what* changed but not *how to operate the project
safely on this machine*. A new session on a different machine started from
zero each time.

## Changes

| File | Change |
|---|---|
| `docs/SESSION_CONTEXT.md` | **New.** Ten sections: what the project is; the two repositories and the one push target; hard rules; machine/environment incl. traps and MariaDB startup; credentials and live data; how to run on Android and PC; architecture facts that are easy to get wrong; graphify usage; verification techniques that work here; where things stand and what is open. |
| `AGENTS.md` | New `## session context — read first` section at the top pointing at the file, summarising the six things it contains so a fresh session knows why to read it. |
| `graphify-out/` | Refreshed so the document is queryable. |

## Verification

```
$ git show --stat --format="" 9fdad84
 AGENTS.md               |  32 ++++++
 docs/SESSION_CONTEXT.md | 266 ++++++++++++++++++++++++++++++++++++++++++++++++
 2 files changed, 298 insertions(+)
```

Graph refresh (`graphify update .` + `cluster-only .` + `export wiki`):

```
$ grep -m1 "nodes ·" graphify-out/GRAPH_REPORT.md
- 2084 nodes · 2744 edges · 148 communities (122 shown, 26 thin omitted)
$ graphify export wiki | tail -2
Wiki: 158 articles written to graphify-out/wiki/
```

The document is reachable and its headings are usable as literal search keys:

```
$ graphify query "session context environment traps credentials" --budget 8
Graph: graphify-out/graph.json (2084 nodes) | Traversal: BFS depth=2
Start: ['environment', 'Environment traps (all hit for real)',
        'SESSION_CONTEXT.md',
        'CIVILWATCH — Citizen App Integration Context',
        '5. Credentials and data'] | 138 nodes found

NODE Environment traps (all hit for real) [src=docs/SESSION_CONTEXT.md loc=L78
   community=CIVILWATCH — Session context]
NODE SESSION_CONTEXT.md                   [src=docs/SESSION_CONTEXT.md loc=L1
   community=CIVILWATCH — Session context]
NODE 5. Credentials and data              [src=docs/SESSION_CONTEXT.md loc=L112
   community=CIVILWATCH — Session context]
```

Also confirmed `AGENTS.md` remains valid markdown with all original sections
intact (graphify, changelog) and that the pointer link resolves:

```
$ ls docs/SESSION_CONTEXT.md     -> present
```

**Not verified:**

- **The actual opencode-on-PC run.** No Windows machine and no second opencode
  session exists here, so "a new session reads this and behaves the same" was
  not exercised end to end. What was verified is that the file is in the
  pushed repo at a path `AGENTS.md` references, and that it is indexed in the
  graph.
- **`graphify label`** still cannot run (no `GEMINI_API_KEY`/
  `GOOGLE_API_KEY`), so the 135 community names in
  `.graphify_labels.json` are stale relative to the current clustering.
- **PHP remains absent from the graph** (`tree_sitter_php` unbuildable on
  Termux) — every PHP claim in the document was written by reading the files,
  not by querying the graph.

## Impact / how to test by hand

1. On the PC: `git pull backup main`, then open `docs/SESSION_CONTEXT.md` —
   it should be the first thing you read.
2. Confirm `AGENTS.md` opens with `## session context — read first` and links
   to it.
3. Start opencode in the repo. It loads `AGENTS.md` automatically and is
   pointed at the context document before any other work.
4. Spot-check one claim: run
   `graphify query "reject flow reason location"` and confirm it returns
   `docs/SESSION_CONTEXT.md` sections alongside the SUPER_ADMIN reference.

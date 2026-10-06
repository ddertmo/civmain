# Super admin reference, DEVLOG cross-links, graph refresh

- **Date:** 2026-10-06
- **Commit:** `2e58906`
- **Type:** docs
- **Areas:** docs / graph

## Problem

Four things about the admin backend were answerable only by reading a lot of
code, and two of them were actively misread:

1. Nobody could tell from the repo that there are **two** separate admin
   interfaces — a static HTML prototype at `/` and a Laravel Blade panel at
   `/admin/login` — that they do not share code, and that both are meant to
   stay.
2. `laravel/DEVLOG.md` (269 lines) and `laravel/docs/DEVLOG.md` (290 lines)
   look like duplicates. They are not: one is the backend reference, the
   other is the project history. Nothing said so.
3. The reject-with-notes flow, the pending queue and the role gate were
   documented nowhere, so the next person would re-derive them from source.
4. The knowledge graph was stale after the day's Dart changes, and
   `graphify-out/wiki/` did not exist, so there was no browsable entry point
   into the graph.

Markdown heading nodes are linked **only to their own document root** — they
carry no edges to code. `graphify explain "Role gate"` reports degree 1 (its
one edge is `[contains]` from the document root) and `SUPER_ADMIN.md` reports
degree 3. So a prose question seeds the BFS on code symbols and never reaches
the doc; a filename or an exact heading phrase is what seeds it. Headings are
therefore written as literal search keys.

## Cause

Documentation drift. `laravel/DEVLOG.md` was written when the backend was
built; `laravel/docs/DEVLOG.md` was written for the course deliverable; neither
knows the other exists. The admin prototype was built separately from the
Blade panel and the split was never written down. `graphify update .` had not
been run since commit `98e02f5`.

## Changes

| File | Change |
|---|---|
| `laravel/docs/SUPER_ADMIN.md` | **New.** Reference with headings written as search keys: `## Two admin UIs`, `## Reject with notes`, `## Pending queue`, `## Approve flow`, `## Role gate`, `## Notifications feed`, `## Analytics endpoints`, `## Verification commands`. Includes the two-UI comparison table, the numbered reject path, the status/state table, both test accounts, and an explicit "Not verified" section. |
| `laravel/DEVLOG.md` | Header note: this is the **backend reference**; history is in `docs/DEVLOG.md`; link to `docs/SUPER_ADMIN.md`. |
| `laravel/docs/DEVLOG.md` | Header note: this is the **project history**; backend reference is in `../DEVLOG.md`; link to `SUPER_ADMIN.md`. |
| `graphify-out/` | `graphify update .` then `graphify export wiki` then `graphify cluster-only .` |

Graph delta: **1983 → 2004 nodes**, **2632 → 2663 edges**, **129 → 139
communities**; `graphify export wiki` wrote 149 articles plus
`graphify-out/wiki/index.md`. (The commit message for this change quotes the
old numbers incorrectly — 1983/2496/139; the figures here and the output of
`git show HEAD~1:graphify-out/GRAPH_REPORT.md` are authoritative.)

`RejectionNotice`, `IncidentReport.isRejected` and `EnsureSuperAdmin` are now
reachable from the graph. Laravel PHP still contributes nothing:
`tree_sitter_php` cannot build on Termux, so all 62 `.php` files are skipped
and must be read directly.

## Verification

```
graphify update .        -> Rebuilt: 2004 nodes, 2663 edges, 139 communities
graphify cluster-only .  -> Done - 139 communities
graphify export wiki     -> Wiki: 149 articles written to graphify-out/wiki/
                            graphify-out/wiki/index.md -> agent entry point
graphify query "reject a report with a reason"
       -> 62 nodes, start: ['report', 'RejectionNotice', 'rejection_notice.dart']
          first hit: civ-main/lib/widgets/timeline/rejection_notice.dart L15
ls graphify-out/wiki | grep SUPER_ADMIN
       -> Super_Admin_—_Reject_with_Notes,_Pending_Queue,_Approve_Flow.md
graphify explain "Role gate"
       -> Node: Role gate / laravel/docs/SUPER_ADMIN.md L126 / Type: document
          Degree: 1, <-- Super Admin — ... [contains] [EXTRACTED]
graphify query "Super Admin role gate EnsureSuperAdmin"
       -> 17 nodes; first four are SUPER_ADMIN.md L1, Role gate L126,
          Reject with Notes ... L1, Analytics endpoints L178
graphify explain "RejectionNotice"
       -> civ-main/lib/widgets/timeline/rejection_notice.dart L15, degree 2
git show HEAD~1:graphify-out/GRAPH_REPORT.md | head -1
       -> - 1983 nodes · 2632 edges · 129 communities (104 shown, 25 thin omitted)
```

A **prose** question does *not* reach the doc — `graphify query "why can a CEO
not reject a report"` returns 72 nodes rooted on `report` /
`rejection_notice.dart` / `CEO Office` and never touches `SUPER_ADMIN.md`.
That is expected given the degree figures above, and is the reason the
headings are phrased as search keys rather than as prose.

**Not verified:** no LLM API key is set in this environment, so
`graphify label` was not run — `update` reported "community set changed since
labeling (129 saved labels, 139 communities now; renamed 25 community(ies) by
their hub)". The 139 community *names* in `graphify-out/.graphify_labels.json`
are therefore partly stale even though the clustering is current. Setting
`GEMINI_API_KEY` or `GOOGLE_API_KEY` and re-running `graphify label` would
fix it; no file in this commit depends on those names.

No code changed in this commit, so nothing needed a runtime check.

## Impact / how to test by hand

1. `cd prc && export PATH="$HOME/.local/bin:$PATH"`
2. `graphify query "Super Admin role gate EnsureSuperAdmin"` → should return
   `laravel/docs/SUPER_ADMIN.md` nodes first. A prose question will *not*
   reach it (markdown heading nodes have no edges to code); use
   `graphify explain "Role gate"` to jump straight to
   `laravel/docs/SUPER_ADMIN.md L126`.
3. `graphify explain "RejectionNotice"` → should explain the Flutter widget
   and its neighbours.
4. Open `graphify-out/wiki/index.md` and follow a community article, or open
   `graphify-out/graph.html` in a browser.
5. Read `laravel/docs/SUPER_ADMIN.md` top to bottom — it should be enough to
   operate the admin panel without opening the source.

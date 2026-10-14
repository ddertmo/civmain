---
type: zettel
tags: [graphify, context-minimization, icm]
date: 2026-10-14
summary: Prefer graph-first retrieval (query -> 1-2 hops) over reading entire docs
---
# One home per fact; graph-first retrieval

Key points:
- Load minimal relevant subset via graphify (query/path/explain) before answering.
- ~5 relevant notes/pages max; don't dump full vault/GRAPH_REPORT.md.
- Link don't duplicate. Relationships live in graph.
- After changes, run `graphify update .`.

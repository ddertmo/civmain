## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

## changelog

Every change gets its own markdown file in `docs/changes/`, indexed by
`docs/changes/README.md`, committed and pushed to the `backup` remote.

Rules:
- After finishing any fix, feature, refactor, doc edit or graph refresh, run the installed **changelog** skill (`.opencode/skills/changelog/SKILL.md`) before moving on. It writes `docs/changes/YYYY-MM-DD-NNN-<slug>.md`, updates the index, runs `graphify update .`, then commits and pushes.
- One file per change. One change per file — do not merge unrelated fixes.
- Document what the diff actually did plus the root cause and the real verification commands. If a check could not be run (no Flutter SDK, no Xcode), say so explicitly instead of implying it passed.
- Push target is `backup` (`https://github.com/dertmo01/civmain.git`), branch `main`. Never push to `origin`.
- After pushing, confirm `git ls-remote backup main` equals `git rev-parse HEAD`.

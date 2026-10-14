// graphify OpenCode plugin (V2)
// Injects a knowledge graph reminder before bash tool calls when the graph exists.
//
// IMPORTANT: keep the reminder string free of backticks and $(...) constructs.
// The hook prepends `echo "<reminder>" ...` to the user's bash command;
// backticks inside the double-quoted echo trigger bash command substitution,
// which both corrupts tool output and silently executes the very graphify
// command we are only suggesting. Plain words render fine in opencode's TUI.
import { existsSync } from "fs";
import { join } from "path";

export default {
  id: "graphify",
  async setup(ctx) {
    let reminded = false;
    const directory = ctx.location.directory;

    await ctx.tool.hook("execute.before", (event) => {
      if (reminded) return;
      if (!existsSync(join(directory, "graphify-out", "graph.json"))) return;
      if (event.tool !== "bash") return;

      const input =
        event && typeof event.input === "object" && event.input !== null ? event.input : null;

      // OpenCode V2 passes tool arguments through event.input.
      if (input && typeof input.command === "string") {
        input.command =
          'echo "[graphify] knowledge graph at graphify-out/. For focused questions, run graphify query with your question (scoped subgraph, usually much smaller than GRAPH_REPORT.md) instead of grepping raw files. Read GRAPH_REPORT.md only for broad architecture context." ; ' +
          input.command;
        reminded = true;
        return;
      }

      // Be tolerant of shapes where the command is nested under args.
      if (input && input.args && typeof input.args.command === "string") {
        input.args.command =
          'echo "[graphify] knowledge graph at graphify-out/. For focused questions, run graphify query with your question (scoped subgraph, usually much smaller than GRAPH_REPORT.md) instead of grepping raw files. Read GRAPH_REPORT.md only for broad architecture context." ; ' +
          input.args.command;
        reminded = true;
        return;
      }

      // Last-resort fallback for any V2 shape exposing output.args.command.
      const output =
        event && typeof event.output === "object" && event.output !== null ? event.output : null;
      if (output && output.args && typeof output.args.command === "string") {
        output.args.command =
          'echo "[graphify] knowledge graph at graphify-out/. For focused questions, run graphify query with your question (scoped subgraph, usually much smaller than GRAPH_REPORT.md) instead of grepping raw files. Read GRAPH_REPORT.md only for broad architecture context." ; ' +
          output.args.command;
        reminded = true;
      }
    });
  },
};
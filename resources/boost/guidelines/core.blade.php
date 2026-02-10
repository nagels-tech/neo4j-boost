## Neo4j Boost

This package provides a fake MCP (Model Context Protocol) server for Neo4j, useful for testing and agent development without a real Neo4j instance.

### Features

- **MCP endpoint**: `POST /mcp` — JSON-RPC 2.0 (methods: `initialize`, `tools/list`, `tools/call`).
- **Tools** (all fake): `cypher_query` (args: `query`), `graph_stats` (no args), `node_lookup` (args: `id`).
- **Config**: Publish with `php artisan vendor:publish --tag=neo4j-boost-config`. Tools are defined in `config/neo4j-boost.php`.

### Usage

- To use as an MCP server, point an HTTP MCP client at the app’s `/mcp` URL.
- Tool list and schemas come from `config('neo4j-boost.tools')`. Add or edit tools in the published config.
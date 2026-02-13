## Neo4j Boost

This package integrates the official [Neo4j MCP](https://github.com/neo4j/mcp/releases) server into Laravel so you can use Neo4j tools from MCP clients (Cursor, Claude, etc.).

### Install the binary

Run once after installing the package:

```bash
php artisan neo4j-boost:install-mcp
```

This downloads the Neo4j MCP binary for your platform (Linux x86_64, Darwin, Windows, etc.) from GitHub releases.

### Run the MCP server

Use in your MCP client config (e.g. `.mcp.json`):

```json
"neo4j": {
  "command": "php",
  "args": ["artisan", "neo4j-boost:mcp"]
}
```

Set `NEO4J_URI`, `NEO4J_USERNAME`, and `NEO4J_PASSWORD` in your `.env`, or configure a `neo4j` connection in `config/database.php`.

### Config

Publish with `php artisan vendor:publish --tag=neo4j-boost-config`. Options in `config/neo4j-boost.php`: `neo4j_mcp.version`, `neo4j_mcp.binary_path`, `neo4j_mcp.platform_asset`.

## Neo4j Boost

This package integrates the official [Neo4j MCP](https://github.com/neo4j/mcp/releases) server into Laravel so you can use Neo4j tools from MCP clients (Cursor, Claude, etc.).

### HTTP only

The package talks to the Neo4j MCP server over **HTTP only**. Run the Neo4j MCP server elsewhere (e.g. Docker) with HTTP transport, then set in `.env`:

```env
NEO4J_MCP_URL=http://localhost:8080/mcp
NEO4J_MCP_USERNAME=neo4j   # optional
NEO4J_MCP_PASSWORD=...     # optional
```

### Cursor config — one MCP server only

Run:

```bash
php artisan neo4j-boost:cursor-config
```

When Laravel Boost is present, this ensures **one** MCP server (**laravel-boost**) in `.cursor/mcp.json`, with all Boost and Neo4j tools. A separate neo4j-boost server is not added.

### Run the MCP server

- **With Laravel Boost:** Use a **single** MCP server: run `php artisan boost:mcp`. This package adds the official Neo4j tools (get-schema, read-cypher, write-cypher, list-gds-procedures) to that server. All tools (Boost + Neo4j) are in one server. Run `neo4j-boost:cursor-config` to keep `.cursor/mcp.json` with only the laravel-boost entry.
- **Without Boost:** Run `php artisan neo4j-boost:cursor-config` so `.cursor/mcp.json` has the single `neo4j-boost` HTTP server.

Set `NEO4J_URI`, `NEO4J_USERNAME`, and `NEO4J_PASSWORD` where the Neo4j MCP server runs (and in Laravel if you use the Neo4j driver).

**GDS (list-gds-procedures):** Install the Graph Data Science plugin in Neo4j. With Docker, set `NEO4J_PLUGINS: '["apoc", "graph-data-science"]'`, `NEO4J_dbms_security_procedures_unrestricted: 'apoc.*,gds.*'`, and `NEO4J_dbms_security_procedures_allowlist: 'apoc.*,gds.*'`.

### Config

Publish with `php artisan vendor:publish --tag=neo4j-boost-config`. Options in `config/neo4j-boost.php`: `http.url`, `http.username`, `http.password`.

### Cursor: "Loading tools" stuck

- Open your **Laravel app folder** (the project where you ran `composer require neo4j/laravel-boost`) as the Cursor workspace, not the neo4j-boost package folder.
- If `.cursor/mcp.json` is missing, run `php artisan neo4j-boost:cursor-config` to create it.
- Ensure the Neo4j MCP server is running at the URL set in `NEO4J_MCP_URL` and that it is started with HTTP transport.

## Neo4j Boost

This package integrates the official [Neo4j MCP](https://github.com/neo4j/mcp/releases) server into Laravel so you can use Neo4j tools from MCP clients (Cursor, Claude, etc.).

### Install the binary

Run once after installing the package:

```bash
php artisan neo4j-boost:install-mcp
```

This downloads the Neo4j MCP binary for your platform (Linux x86_64, Darwin, Windows, etc.) from GitHub releases. It also creates or updates `.cursor/mcp.json` in the app root with the neo4j-boost server (merged with any existing MCP servers). Use `--no-cursor-config` to skip writing the Cursor config.

To only create/update the Cursor MCP config without (re)downloading the binary:

```bash
php artisan neo4j-boost:cursor-config
```

### Run the MCP server

- **With Laravel Boost:** Use a single MCP server: run `php artisan boost:mcp`. This package adds the official Neo4j tools (get-schema, read-cypher, write-cypher, list-gds-procedures) to Boost’s server automatically. Tools use **stdio** (local binary) or **HTTP** (remote MCP URL) depending on `config/neo4j-boost.transport`.
- **Without Boost:** Use the standalone server. Open this Laravel app folder in Cursor and enable the neo4j-boost MCP server. The config in `.cursor/mcp.json` uses `command: "php"`, `args: ["artisan", "neo4j-boost:mcp"]`.

Set `NEO4J_URI`, `NEO4J_USERNAME`, and `NEO4J_PASSWORD` in your `.env`, or configure a `neo4j` connection in `config/database.php`.

**GDS (list-gds-procedures):** Install the Graph Data Science plugin in Neo4j. With Docker, set `NEO4J_PLUGINS: '["apoc", "graph-data-science"]'`, `NEO4J_dbms_security_procedures_unrestricted: 'apoc.*,gds.*'`, and `NEO4J_dbms_security_procedures_allowlist: 'apoc.*,gds.*'`.

### Config

Publish with `php artisan vendor:publish --tag=neo4j-boost-config`. Options in `config/neo4j-boost.php`: `transport` (stdio | http), `neo4j_mcp.*` (binary), `http.url` / `http.username` / `http.password` (when transport is http).

### Cursor: "Loading tools" stuck

- Open your **Laravel app folder** (the project where you ran `composer require neo4j/laravel-boost`) as the Cursor workspace, not the neo4j-boost package folder. The MCP server must run with the app as the current working directory.
- If `.cursor/mcp.json` is missing, run `php artisan neo4j-boost:cursor-config` to create it (or re-run `neo4j-boost:install-mcp`).
- Server logs (and any errors) are written to `storage/logs/neo4j-mcp.log` in the app. Check that file if the server fails to load tools.

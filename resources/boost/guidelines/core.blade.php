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

Open this Laravel app folder in Cursor and enable the neo4j-boost MCP server. The config in `.cursor/mcp.json` uses `command: "php"`, `args: ["artisan", "neo4j-boost:mcp"]`.

Set `NEO4J_URI`, `NEO4J_USERNAME`, and `NEO4J_PASSWORD` in your `.env`, or configure a `neo4j` connection in `config/database.php`.

### Config

Publish with `php artisan vendor:publish --tag=neo4j-boost-config`. Options in `config/neo4j-boost.php`: `neo4j_mcp.version`, `neo4j_mcp.binary_path`, `neo4j_mcp.platform_asset`.

### Cursor: "Loading tools" stuck

- Open your **Laravel app folder** (the project where you ran `composer require neo4j/laravel-boost`) as the Cursor workspace, not the neo4j-boost package folder. The MCP server must run with the app as the current working directory.
- If `.cursor/mcp.json` is missing, run `php artisan neo4j-boost:cursor-config` to create it (or re-run `neo4j-boost:install-mcp`).
- Server logs (and any errors) are written to `storage/logs/neo4j-mcp.log` in the app. Check that file if the server fails to load tools.

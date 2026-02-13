# Neo4j Laravel Boost

Laravel integration for the [official Neo4j MCP server](https://github.com/neo4j/mcp/releases). Use Neo4j tools (get-schema, read-cypher, write-cypher, etc.) from MCP clients like Cursor or Claude.

**Requirements:** PHP 8.1+, Laravel 11 or 12.

---

## Installation

### 1. Install the package

```bash
composer require neo4j/laravel-boost
```

### 2. Install the Neo4j MCP binary

Run once in your Laravel app:

```bash
php artisan neo4j-boost:install-mcp
```

This will:

- Download the Neo4j MCP binary for your platform (Linux x86_64, Linux arm64, Darwin, Windows) from GitHub releases
- Extract it to `storage/app/neo4j-mcp/`
- Create or update `.cursor/mcp.json` in your app root so Cursor can use the server (existing MCP servers are preserved)

To skip writing the Cursor config:

```bash
php artisan neo4j-boost:install-mcp --no-cursor-config
```

To only create or update the Cursor config without downloading the binary:

```bash
php artisan neo4j-boost:cursor-config
```

### 3. Configure Neo4j connection

Add to your `.env` (or configure a `neo4j` connection in `config/database.php`):

```env
NEO4J_URI=bolt://localhost:7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=your-password
```

The MCP server will start without a running Neo4j instance; you need these for running Cypher and schema tools.

---

## Using with Cursor

1. Open your **Laravel application folder** (the project where you ran `composer require`) as the Cursor workspace—not the neo4j-boost package directory.
2. Reload Cursor or open MCP settings so it picks up `.cursor/mcp.json`.
3. Enable the **neo4j-boost** MCP server. Tools (e.g. get-schema, read-cypher, write-cypher) should appear when the server is connected.

---

## Artisan commands

| Command | Description |
|--------|-------------|
| `php artisan neo4j-boost:install-mcp` | Download/install the MCP binary and optionally update `.cursor/mcp.json` |
| `php artisan neo4j-boost:cursor-config` | Create or update `.cursor/mcp.json` only (merge with existing servers) |
| `php artisan neo4j-boost:mcp` | Run the MCP server (stdio). Used by Cursor via `.cursor/mcp.json`; stderr is logged to `storage/logs/neo4j-mcp.log` |

---

## Configuration

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=neo4j-boost-config
```

Edit `config/neo4j-boost.php`:

- **`neo4j_mcp.version`** – Release tag (e.g. `v1.4.0`) to download from GitHub
- **`neo4j_mcp.binary_path`** – Absolute path to the binary; `null` uses `storage/app/neo4j-mcp/neo4j-mcp`
- **`neo4j_mcp.platform_asset`** – Override platform (e.g. `Linux_x86_64`, `Darwin_arm64`); `null` auto-detects

---

## Troubleshooting

- **"Could not open input file: artisan"** or **"Loading tools" stuck**  
  Cursor must run the MCP command from your Laravel app directory. Open the **Laravel app folder** as the workspace and ensure `.cursor/mcp.json` exists (run `php artisan neo4j-boost:cursor-config` if needed).

- **Server errors**  
  Check `storage/logs/neo4j-mcp.log` in your Laravel app. You can set `NEO4J_LOG_LEVEL=debug` in `.env` for more verbose logging.

- **GDS errors in the log**  
  Messages like "Unknown function 'gds.version'" are expected if Neo4j does not have the GDS plugin installed. The MCP server still runs and can execute standard Cypher.

---

## License

MIT.

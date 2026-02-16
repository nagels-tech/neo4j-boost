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

### 4. (Optional) Enable GDS for `list-gds-procedures`

The **list-gds-procedures** tool requires the [Graph Data Science](https://neo4j.com/docs/graph-data-science/current/) (GDS) plugin in Neo4j. Without it, that tool will error; other tools (get-schema, read-cypher, write-cypher) still work.

**Docker:** enable the GDS and APOC plugins and allow procedures:

```yaml
# docker-compose.yml (neo4j service)
neo4j:
  image: neo4j:5-community
  environment:
    NEO4J_AUTH: neo4j/your-password
    NEO4J_PLUGINS: '["apoc", "graph-data-science"]'
    NEO4J_dbms_security_procedures_unrestricted: 'apoc.*,gds.*'
    NEO4J_dbms_security_procedures_allowlist: 'apoc.*,gds.*'
  ports:
    - "7474:7474"
    - "7687:7687"
```

**Non-Docker:** install the GDS plugin for your Neo4j version and configure procedure allowlists as in the [Neo4j GDS docs](https://neo4j.com/docs/graph-data-science/current/installation/).

### 5. (Optional) Use HTTP transport

If the Neo4j MCP server runs elsewhere (e.g. in Docker with HTTP on port 8080), set:

```env
NEO4J_MCP_TRANSPORT=http
NEO4J_MCP_URL=http://localhost:8080/mcp
# Optional Basic Auth:
NEO4J_MCP_USERNAME=neo4j
NEO4J_MCP_PASSWORD=your-password
```

Then the package sends `tools/call` over HTTP to that URL. You do **not** need to install the binary locally when using HTTP (only for the standalone `neo4j-boost:mcp` stdio server or when transport is `stdio`).

---

## Single MCP server with Laravel Boost

If your app uses [Laravel Boost](https://github.com/laravel/boost), you can expose **both** Boost tools and the **official** Neo4j MCP tools from **one** MCP server (no HTTP proxy, no second server).

1. Install both packages and the Neo4j binary:

   ```bash
   composer require laravel/boost neo4j/laravel-boost
   php artisan neo4j-boost:install-mcp
   ```

2. Use **one** Cursor MCP entry that runs Laravel Boost:

   ```json
   "mcpServers": {
     "laravel-boost": {
       "command": "php",
       "args": ["artisan", "boost:mcp"]
     }
   }
   ```

3. This package automatically adds its Neo4j tools to Boost’s tool list when Boost is present. You get Boost tools (search-docs, browser-logs, database, etc.) **and** the official Neo4j tools (get-schema, read-cypher, write-cypher, list-gds-procedures) in the same server. Neo4j tools use either **stdio** (local binary) or **HTTP** (remote MCP server) depending on config (see Configuration).

If you do **not** use Laravel Boost, use the standalone Neo4j MCP server (see below): `.cursor/mcp.json` with `neo4j-boost` running `php artisan neo4j-boost:mcp`.

---

## Using with Cursor

1. Open your **Laravel application folder** (the project where you ran `composer require`) as the Cursor workspace—not the neo4j-boost package directory.
2. Reload Cursor or open MCP settings so it picks up `.cursor/mcp.json`.
3. Enable the MCP server: **laravel-boost** (if using Boost) or **neo4j-boost** (standalone). Tools (e.g. get-schema, read-cypher, write-cypher) should appear when the server is connected.

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

- **`transport`** – `stdio` (default) or `http`. With `stdio`, the package runs the neo4j-mcp binary locally and talks over stdin/stdout. With `http`, it sends `tools/call` to a remote MCP server (e.g. neo4j-mcp in Docker with `--neo4j-transport-mode http`).
- **`neo4j_mcp.version`** – Release tag (e.g. `v1.4.0`) to download from GitHub (used when transport is `stdio`).
- **`neo4j_mcp.binary_path`** – Absolute path to the binary; `null` uses `storage/app/neo4j-mcp/neo4j-mcp`
- **`neo4j_mcp.platform_asset`** – Override platform (e.g. `Linux_x86_64`, `Darwin_arm64`); `null` auto-detects
- **`http.url`** – When transport is `http`, the MCP endpoint (e.g. `http://localhost:8080/mcp`). Env: `NEO4J_MCP_URL`.
- **`http.username`** / **`http.password`** – Optional Basic Auth for the HTTP endpoint. Env: `NEO4J_MCP_USERNAME`, `NEO4J_MCP_PASSWORD` (fallback to `NEO4J_USERNAME` / `NEO4J_PASSWORD`).

---

## Troubleshooting

- **"Could not open input file: artisan"** or **"Loading tools" stuck**  
  Cursor must run the MCP command from your Laravel app directory. Open the **Laravel app folder** as the workspace and ensure `.cursor/mcp.json` exists (run `php artisan neo4j-boost:cursor-config` if needed).

- **Server errors**  
  Check `storage/logs/neo4j-mcp.log` in your Laravel app. You can set `NEO4J_LOG_LEVEL=debug` in `.env` for more verbose logging.

- **GDS errors in the log**  
  Messages like "Unknown function 'gds.version'" mean Neo4j does not have the GDS plugin. Install it and set procedure allowlists (see **Enable GDS** above). The MCP server still runs and standard Cypher (get-schema, read-cypher, write-cypher) works without GDS.

---

## License

MIT.

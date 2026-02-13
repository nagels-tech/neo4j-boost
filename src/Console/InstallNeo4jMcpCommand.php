<?php

namespace Neo4j\LaravelBoost\Console;

use Illuminate\Console\Command;
use Neo4j\LaravelBoost\Neo4jMcpInstaller;

class InstallNeo4jMcpCommand extends Command
{
    protected $signature = 'neo4j-boost:install-mcp
                            {--force : Re-download even if binary already exists}';

    protected $description = 'Download and install the official Neo4j MCP binary from GitHub releases';

    public function handle(Neo4jMcpInstaller $installer): int
    {
        if ($installer->isInstalled() && ! $this->option('force')) {
            $this->info('Neo4j MCP binary already installed at: ' . $installer->getBinaryPath());
            return self::SUCCESS;
        }

        $asset = $installer->getPlatformAssetName();
        if ($asset === null) {
            $this->error('Unsupported platform. Set config neo4j-boost.neo4j_mcp.platform_asset (e.g. Linux_x86_64).');
            return self::FAILURE;
        }

        $this->info('Downloading ' . $asset . ' ...');
        try {
            $path = $installer->install();
            $this->info('Neo4j MCP binary installed at: ' . $path);
            $this->line('Add to your MCP client config (e.g. .mcp.json):');
            $this->line('  "neo4j": { "command": "php", "args": ["artisan", "neo4j-boost:mcp"] }');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}

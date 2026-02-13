<?php

namespace Neo4j\LaravelBoost\Console;

use Illuminate\Console\Command;
use Neo4j\LaravelBoost\CursorMcpConfig;
use Neo4j\LaravelBoost\Neo4jMcpInstaller;

class InstallNeo4jMcpCommand extends Command
{
    protected $signature = 'neo4j-boost:install-mcp
                            {--force : Re-download even if binary already exists}
                            {--no-cursor-config : Skip creating/updating .cursor/mcp.json}';

    protected $description = 'Download and install the official Neo4j MCP binary from GitHub releases';

    public function handle(Neo4jMcpInstaller $installer): int
    {
        if ($installer->isInstalled() && ! $this->option('force')) {
            $this->info('Neo4j MCP binary already installed at: ' . $installer->getBinaryPath());
            $this->writeCursorConfigIfRequested();
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
            $this->writeCursorConfigIfRequested();
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function writeCursorConfigIfRequested(): void
    {
        if ($this->option('no-cursor-config')) {
            return;
        }
        if (CursorMcpConfig::writeOrMerge(base_path())) {
            $this->info('Created/updated ' . CursorMcpConfig::getPath(base_path()) . ' for Cursor MCP.');
        } else {
            $this->warn('Could not write .cursor/mcp.json. Add the neo4j-boost server to your MCP config manually.');
        }
    }
}

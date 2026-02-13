<?php

namespace Neo4j\LaravelBoost\Console;

use Illuminate\Console\Command;
use Neo4j\LaravelBoost\Neo4jMcpInstaller;

class Neo4jMcpCommand extends Command
{
    protected $signature = 'neo4j-boost:mcp';

    protected $description = 'Run the official Neo4j MCP server (stdio). Use in .mcp.json: "command": "php", "args": ["artisan", "neo4j-boost:mcp"]';

    public function handle(Neo4jMcpInstaller $installer): int
    {
        if (! $installer->isInstalled()) {
            $this->error('Neo4j MCP binary not installed. Run: php artisan neo4j-boost:install-mcp');
            return self::FAILURE;
        }

        $binary = $installer->getBinaryPath();
        $env = array_merge(
            getenv(),
            [
                'NEO4J_URI' => config('database.connections.neo4j.uri', env('NEO4J_URI', 'bolt://localhost:7687')),
                'NEO4J_USERNAME' => config('database.connections.neo4j.username', env('NEO4J_USERNAME', 'neo4j')),
                'NEO4J_PASSWORD' => config('database.connections.neo4j.password', env('NEO4J_PASSWORD', '')),
            ]
        );

        $descriptors = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];
        $proc = @proc_open(
            [$binary],
            $descriptors,
            $pipes,
            null,
            array_filter($env, fn ($v) => $v !== false && $v !== null)
        );
        if (! is_resource($proc)) {
            $this->error('Failed to start Neo4j MCP process.');
            return self::FAILURE;
        }
        $exit = proc_close($proc);
        return $exit >= 0 ? (int) $exit : self::SUCCESS;
    }
}

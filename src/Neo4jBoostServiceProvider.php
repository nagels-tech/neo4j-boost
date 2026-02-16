<?php

namespace Neo4j\LaravelBoost;

use Illuminate\Support\ServiceProvider;
use Neo4j\LaravelBoost\Console\CursorConfigCommand;
use Neo4j\LaravelBoost\Console\InstallNeo4jMcpCommand;
use Neo4j\LaravelBoost\Console\Neo4jMcpCommand;
use Neo4j\LaravelBoost\Contracts\Neo4jMcpClientInterface;

class Neo4jBoostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/neo4j-boost.php', 'neo4j-boost');

        $this->app->singleton(Neo4jMcpClientInterface::class, function ($app) {
            $transport = config('neo4j-boost.transport', 'stdio');
            if ($transport === 'http') {
                return new Neo4jHttpClient;
            }
            return new Neo4jBinaryClient($app->make(Neo4jMcpInstaller::class));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/neo4j-boost.php' => config_path('neo4j-boost.php'),
        ], 'neo4j-boost-config');

        $this->mergeBoostToolsWhenBoostPresent();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallNeo4jMcpCommand::class,
                Neo4jMcpCommand::class,
                CursorConfigCommand::class,
            ]);
        }
    }

    /**
     * When Laravel Boost is present, add our Neo4j tools to boost.mcp.tools.include
     * so one MCP server (boost:mcp) exposes both Boost and official Neo4j tools.
     * Gate on Boost being installed (ToolRegistry) rather than config file existence.
     */
    private function mergeBoostToolsWhenBoostPresent(): void
    {
        if (! class_exists(\Laravel\Mcp\Server\Tool::class)) {
            return;
        }
        if (! class_exists(\Laravel\Boost\Mcp\ToolRegistry::class)) {
            return;
        }

        $ourTools = [
            \Neo4j\LaravelBoost\Boost\Tools\GetSchemaTool::class,
            \Neo4j\LaravelBoost\Boost\Tools\ReadCypherTool::class,
            \Neo4j\LaravelBoost\Boost\Tools\WriteCypherTool::class,
            \Neo4j\LaravelBoost\Boost\Tools\ListGdsProceduresTool::class,
        ];

        $include = config('boost.mcp.tools.include', []);
        $merged = array_values(array_unique(array_merge($include, $ourTools)));
        config(['boost.mcp.tools.include' => $merged]);
    }
}

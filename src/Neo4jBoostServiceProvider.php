<?php

namespace Neo4j\LaravelBoost;

use Illuminate\Support\ServiceProvider;
use Neo4j\LaravelBoost\Console\CursorConfigCommand;
use Neo4j\LaravelBoost\Console\InstallNeo4jMcpCommand;
use Neo4j\LaravelBoost\Console\Neo4jMcpCommand;

class Neo4jBoostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/neo4j-boost.php', 'neo4j-boost');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/neo4j-boost.php' => config_path('neo4j-boost.php'),
        ], 'neo4j-boost-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallNeo4jMcpCommand::class,
                Neo4jMcpCommand::class,
                CursorConfigCommand::class,
            ]);
        }
    }
}

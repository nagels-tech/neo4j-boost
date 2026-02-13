<?php

namespace Neo4j\LaravelBoost;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class Neo4jBoostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/neo4j-boost.php', 'neo4j-boost');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/mcp.php');

        $this->publishes([
            __DIR__ . '/../config/neo4j-boost.php' => config_path('neo4j-boost.php'),
        ], 'neo4j-boost-config');
    }
}

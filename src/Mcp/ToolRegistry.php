<?php

namespace Neo4j\LaravelBoost\Mcp;

class ToolRegistry
{
    public static function all(): array
    {
        return config('neo4j-boost.tools', []) ?? [];
    }
}
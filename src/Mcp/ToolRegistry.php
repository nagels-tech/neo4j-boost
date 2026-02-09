<?php

namespace NagelsTech\Neo4jBoost\Mcp;

class ToolRegistry
{
    public static function all(): array
    {
        return config('neo4j-boost.tools', []) ?? [];
    }
}
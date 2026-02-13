<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Official Neo4j MCP binary (from https://github.com/neo4j/mcp/releases)
    |--------------------------------------------------------------------------
    */
    'neo4j_mcp' => [
        'version' => 'v1.4.0',
        'binary_path' => null, // null = use storage_path('app/neo4j-mcp/neo4j-mcp'); set to absolute path to override.
        'platform_asset' => null, // null = auto-detect (Linux_x86_64, Linux_arm64, Darwin_*, Windows_*). Override e.g. 'Linux_x86_64'.
    ],
];

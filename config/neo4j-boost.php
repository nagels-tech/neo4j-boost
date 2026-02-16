<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transport: stdio | http
    |--------------------------------------------------------------------------
    | stdio = run the neo4j-mcp binary locally and talk over stdin/stdout.
    | http  = send tools/call to a remote MCP server (e.g. Docker with --neo4j-transport-mode http).
    */
    'transport' => env('NEO4J_MCP_TRANSPORT', 'stdio'),

    /*
    |--------------------------------------------------------------------------
    | Official Neo4j MCP binary (from https://github.com/neo4j/mcp/releases)
    |--------------------------------------------------------------------------
    | Used when transport is 'stdio'.
    */
    'neo4j_mcp' => [
        'version' => 'v1.4.0',
        'binary_path' => null, // null = use storage_path('app/neo4j-mcp/neo4j-mcp'); set to absolute path to override.
        'platform_asset' => null, // null = auto-detect (Linux_x86_64, Linux_arm64, Darwin_*, Windows_*). Override e.g. 'Linux_x86_64'.
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP transport (when transport = 'http')
    |--------------------------------------------------------------------------
    | URL of the MCP server (e.g. neo4j-mcp in Docker with HTTP on port 8080).
    | Optional Basic Auth (e.g. Neo4j username/password if required by the server).
    */
    'http' => [
        'url' => env('NEO4J_MCP_URL', 'http://localhost:8080/mcp'),
        'username' => env('NEO4J_MCP_USERNAME', env('NEO4J_USERNAME')),
        'password' => env('NEO4J_MCP_PASSWORD', env('NEO4J_PASSWORD')),
    ],
];

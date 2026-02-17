<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP MCP server
    |--------------------------------------------------------------------------
    | URL of the Neo4j MCP server (e.g. neo4j-mcp in Docker with HTTP on port 8080).
    | Optional Basic Auth (e.g. Neo4j username/password if required by the server).
    */
    'http' => [
        'url' => env('NEO4J_MCP_URL', 'http://localhost:8080/mcp'),
        'username' => env('NEO4J_MCP_USERNAME', env('NEO4J_USERNAME')),
        'password' => env('NEO4J_MCP_PASSWORD', env('NEO4J_PASSWORD')),
    ],
];

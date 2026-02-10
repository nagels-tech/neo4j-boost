<?php

return [
    'tools' => [
        [
            'name' => 'cypher_query',
            'description' => 'Executes a Cypher query (fake)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string']
                ],
                'required' => ['query']
            ]
        ],
        [
            'name' => 'graph_stats',
            'description' => 'Returns fake Neo4j graph statistics',
            'inputSchema' => [
                'type' => 'object'
            ]
        ],
        [
            'name' => 'node_lookup',
            'description' => 'Looks up a node by ID (fake)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer']
                ]
            ]
        ]
    ]
];

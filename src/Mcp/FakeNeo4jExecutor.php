<?php

namespace NagelsTech\Neo4jBoost\Mcp;

class FakeNeo4jExecutor
{
    public static function execute(string $tool, array $args): array
    {
        return match ($tool) {
            'cypher_query' => self::cypher($args),
            'graph_stats' => self::stats(),
            'node_lookup' => self::node($args),
            default => [
                'content' => [[
                    'type' => 'text',
                    'text' => "Unknown Neo4j tool: {$tool}"
                ]]
            ],
        };
    }

    protected static function cypher(array $args): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => 'FAKE RESULT for Cypher: ' . ($args['query'] ?? 'N/A')
            ]]
        ];
    }

    protected static function stats(): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => 'Nodes: 42, Relationships: 1337, Labels: 7'
            ]]
        ];
    }

    protected static function node(array $args): array
    {
        return [
            'content' => [[
                'type' => 'json',
                'data' => [
                    'id' => $args['id'] ?? 1,
                    'labels' => ['FakeNode'],
                    'properties' => ['name' => 'Bogus Neo']
                ]
            ]]
        ];
    }
}

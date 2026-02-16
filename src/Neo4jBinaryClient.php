<?php

namespace Neo4j\LaravelBoost;

use Neo4j\LaravelBoost\Contracts\Neo4jMcpClientInterface;

/**
 * Runs the official Neo4j MCP binary and sends JSON-RPC (stdio).
 * Used by Boost tool classes to call get-schema, read-cypher, write-cypher, etc.
 */
class Neo4jBinaryClient implements Neo4jMcpClientInterface
{
    private const INIT_ID = 1;
    private const TOOL_CALL_ID = 2;

    public function __construct(
        private Neo4jMcpInstaller $installer
    ) {}

    /**
     * Call an MCP tool on the neo4j-mcp binary. Spawns the process, sends initialize + tools/call, returns result.
     *
     * @param  array<string, mixed>  $arguments  Tool arguments (e.g. ['query' => '...', 'params' => []])
     * @return array<string, mixed>  MCP result (e.g. ['content' => [...], 'isError' => false])
     *
     * @throws \RuntimeException If binary not installed or process/response invalid.
     */
    public function callTool(string $toolName, array $arguments = []): array
    {
        if (! $this->installer->isInstalled()) {
            throw new \RuntimeException('Neo4j MCP binary not installed. Run: php artisan neo4j-boost:install-mcp');
        }

        $binary = $this->installer->getBinaryPath();
        $env = $this->buildEnv();

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open(
            [$binary],
            $descriptors,
            $pipes,
            base_path(),
            $env
        );

        if (! is_resource($proc)) {
            throw new \RuntimeException('Failed to start Neo4j MCP process.');
        }

        try {
            $stdin = $pipes[0];
            $stdout = $pipes[1];
            $stderr = $pipes[2];

            // Send initialize
            $this->writeRequest($stdin, self::INIT_ID, 'initialize', [
                'protocolVersion' => '2024-11-05',
                'capabilities' => new \stdClass,
                'clientInfo' => [
                    'name' => 'neo4j-laravel-boost',
                    'version' => '1.0',
                ],
            ]);

            $initResponse = $this->readResponse($stdout, self::INIT_ID);
            if (isset($initResponse['error'])) {
                $msg = $initResponse['error']['message'] ?? json_encode($initResponse['error']);
                throw new \RuntimeException('Neo4j MCP initialize failed: ' . $msg);
            }

            // Send tools/call
            $params = [
                'name' => $toolName,
                'arguments' => $arguments === [] ? new \stdClass : $this->objectsForEmptyArrays($arguments),
            ];
            $this->writeRequest($stdin, self::TOOL_CALL_ID, 'tools/call', $params);

            $callResponse = $this->readResponse($stdout, self::TOOL_CALL_ID);
            if (isset($callResponse['error'])) {
                $msg = $callResponse['error']['message'] ?? json_encode($callResponse['error']);
                throw new \RuntimeException('Neo4j MCP tool call failed: ' . $msg);
            }

            return $callResponse['result'] ?? [];
        } finally {
            foreach ($pipes as $p) {
                if (is_resource($p)) {
                    fclose($p);
                }
            }
            proc_close($proc);
        }
    }

    /** @return array<string, string> */
    private function buildEnv(): array
    {
        $base = [
            'NEO4J_URI' => config('database.connections.neo4j.uri', env('NEO4J_URI', 'bolt://localhost:7687')),
            'NEO4J_USERNAME' => config('database.connections.neo4j.username', env('NEO4J_USERNAME', 'neo4j')),
            'NEO4J_PASSWORD' => config('database.connections.neo4j.password', env('NEO4J_PASSWORD', '')),
            'NEO4J_LOG_LEVEL' => env('NEO4J_LOG_LEVEL', 'error'),
            'NEO4J_TELEMETRY' => env('NEO4J_TELEMETRY', 'false'),
        ];
        $existing = array_filter(getenv(), fn ($v) => $v !== false && $v !== null);
        return array_merge($existing, $base);
    }

    /** @param array<string, mixed> $params */
    private function writeRequest($stream, int $id, string $method, array $params): void
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ];
        $line = json_encode($payload) . "\n";
        if (fwrite($stream, $line) === false) {
            throw new \RuntimeException('Failed to write to Neo4j MCP stdin.');
        }
        fflush($stream);
    }

    /**
     * Read JSON-RPC response with id matching $expectedId. Skips notifications (no id) and
     * out-of-order responses so we do not treat the wrong message as success.
     *
     * @return array<string, mixed>
     */
    private function readResponse($stream, int $expectedId): array
    {
        $maxAttempts = 100;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $line = fgets($stream);
            if ($line === false) {
                throw new \RuntimeException('Neo4j MCP did not respond.');
            }
            $decoded = json_decode(trim($line), true);
            if (! is_array($decoded)) {
                throw new \RuntimeException('Neo4j MCP returned invalid JSON.');
            }
            // Notifications have no id; skip them
            if (! array_key_exists('id', $decoded)) {
                continue;
            }
            // Only accept the response that matches our request id
            if ((int) $decoded['id'] === $expectedId) {
                return $decoded;
            }
        }

        throw new \RuntimeException('Neo4j MCP did not return a response for request id ' . $expectedId . '.');
    }

    /** @param array<string, mixed> $arr */
    private function objectsForEmptyArrays(array $arr): array|object
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $v === [] ? new \stdClass : $this->objectsForEmptyArrays($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

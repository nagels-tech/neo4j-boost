<?php

namespace Neo4j\LaravelBoost;

use Illuminate\Support\Facades\Http;
use Neo4j\LaravelBoost\Contracts\Neo4jMcpClientInterface;

/**
 * HTTP client for the Neo4j MCP server.
 * Sends JSON-RPC tools/call to the configured URL (e.g. neo4j-mcp in Docker with HTTP transport).
 */
class Neo4jHttpClient implements Neo4jMcpClientInterface
{
    private const int TIMEOUT = 60;
    private const int TOOL_REQUEST_ID = 1;

    public function callTool(string $toolName, array $arguments = []): array
    {
        $url = config('neo4j-boost.http.url', 'http://localhost:8080/mcp');
        $username = config('neo4j-boost.http.username');
        $password = config('neo4j-boost.http.password');

        $payload = [
            'jsonrpc' => '2.0',
            'id' => self::TOOL_REQUEST_ID,
            'method' => 'tools/call',
            'params' => [
                'name' => $toolName,
                'arguments' => $arguments === [] ? new \stdClass : $this->ensureObjectsForEmptyArrays($arguments),
            ],
        ];

        $request = Http::timeout(self::TIMEOUT)
            ->acceptJson()
            ->asJson();

        if ($username !== null && $password !== null) {
            $request = $request->withBasicAuth($username, $password);
        }

        $response = $request->post($url, $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Neo4j MCP HTTP request failed (status ' . $response->status() . '). ' . trim((string) $response->body())
            );
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new \RuntimeException('Neo4j MCP HTTP: invalid JSON response.');
        }

        if (isset($body['error'])) {
            $message = is_array($body['error']) && isset($body['error']['message'])
                ? $body['error']['message']
                : (string) json_encode($body['error']);
            throw new \RuntimeException('Neo4j MCP HTTP: ' . $message);
        }

        return $body['result'] ?? [];
    }

    /** @param array<string, mixed> $payload */
    private function ensureObjectsForEmptyArrays(array $payload): array|object
    {
        $out = [];
        foreach ($payload as $k => $v) {
            if (is_array($v)) {
                $out[$k] = $v === [] ? new \stdClass : $this->ensureObjectsForEmptyArrays($v);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

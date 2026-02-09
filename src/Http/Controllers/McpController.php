<?php

namespace NagelsTech\Neo4jBoost\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use NagelsTech\Neo4jBoost\Support\JsonRpc;
use NagelsTech\Neo4jBoost\Mcp\ToolRegistry;
use NagelsTech\Neo4jBoost\Mcp\FakeNeo4jExecutor;

class McpController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->json()->all();

        return match ($payload['method'] ?? null) {
            'initialize' => $this->initialize($payload),
            'tools/list' => $this->toolsList($payload),
            'tools/call' => $this->toolsCall($payload),
            default => JsonRpc::error($payload['id'] ?? null, 'Method not found'),
        };
    }

    protected function initialize(array $payload)
    {
        $id = $payload['id'] ?? 1;
        return JsonRpc::result($id, [
            'protocolVersion' => '2024-11-05',
            'serverInfo' => [
                'name' => 'Neo4j Boost',
                'vendor' => 'Nagels Tech',
                'version' => 'dev',
            ],
            'capabilities' => [
                'tools' => (object) [],
                'resources' => (object) [],
            ],
        ]);
    }

    protected function toolsList(array $payload)
    {
        return JsonRpc::result($payload['id'] ?? null, [
            'tools' => ToolRegistry::all(),
        ]);
    }

    protected function toolsCall(array $payload)
    {
        return JsonRpc::result(
            $payload['id'],
            FakeNeo4jExecutor::execute(
                $payload['params']['name'] ?? '',
                $payload['params']['arguments'] ?? []
            )
        );
    }
}

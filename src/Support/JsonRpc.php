<?php

namespace NagelsTech\Neo4jBoost\Support;

class JsonRpc
{
    public static function result($id, $result)
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => $result,
        ]);
    }

    public static function error($id, $message)
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => -32601,
                'message' => $message,
            ],
        ]);
    }

    public static function success($id, $result)
    {
        return self::result($id, [
            'success' => true,
            'result' => $result,
        ]);
    }
}

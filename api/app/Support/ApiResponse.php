<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Consistent response envelope for the whole API:
 *   { "success": bool, "data": mixed|null, "error": object|null, "meta"?: object }
 */
class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(mixed $data = null, int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function error(string $message, int $status = 400, mixed $details = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }
}

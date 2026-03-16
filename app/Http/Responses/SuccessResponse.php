<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class SuccessResponse
{
    public static function make(
        mixed $data = null,
        ?string $message = null,
        int $statusCode = 200,
        ?array $meta = null,
        array $extra = []
    ): JsonResponse {
        $payload = [];

        if ($message !== null) {
            $payload['message'] = $message;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        if ($extra !== []) {
            $payload = array_merge($payload, $extra);
        }

        return response()->json($payload, $statusCode);
    }
}

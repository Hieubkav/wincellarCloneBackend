<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ErrorResponse
{
    /**
     * Create a standardized error response.
     */
    public static function make(
        ErrorType $type,
        string $message,
        ?array $details = null,
        int $statusCode = 500
    ): JsonResponse {
        return response()->json([
            'error' => $type->value,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'path' => request()->path(),
            'correlation_id' => request()->header('X-Correlation-ID') ?? \Illuminate\Support\Str::uuid()->toString(),
            'details' => $details,
        ], $statusCode);
    }

    /**
     * Create a validation error response (422).
     */
    public static function validation(array $errors): JsonResponse
    {
        $formatted = [];
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $message) {
                $formatted[] = [
                    'field' => $field,
                    'message' => $message,
                    'value' => request()->input($field),
                ];
            }
        }

        return self::make(
            ErrorType::VALIDATION,
            'Request validation failed',
            ['errors' => $formatted],
            422
        );
    }

    /**
     * Create a not found error response (404).
     */
    public static function notFound(string $resource, string $identifier): JsonResponse
    {
        return self::make(
            ErrorType::NOT_FOUND,
            "$resource not found",
            ['identifier' => $identifier],
            404
        );
    }

    /**
     * Create a bad request error response (400).
     */
    public static function badRequest(string $message, ?array $details = null): JsonResponse
    {
        return self::make(
            ErrorType::BAD_REQUEST,
            $message,
            $details,
            400
        );
    }

    /**
     * Create a conflict error response (409).
     */
    public static function conflict(string $message, ?array $details = null): JsonResponse
    {
        return self::make(
            ErrorType::CONFLICT,
            $message,
            $details,
            409
        );
    }

    /**
     * Create an internal server error response (500).
     */
    public static function internalError(string $message = 'An unexpected error occurred'): JsonResponse
    {
        return self::make(
            ErrorType::INTERNAL_ERROR,
            $message,
            null,
            500
        );
    }

    /**
     * Create a rate limit exceeded error response (429).
     */
    public static function rateLimitExceeded(int $retryAfter = 60): JsonResponse
    {
        return self::make(
            ErrorType::RATE_LIMIT,
            'Too many requests. Please slow down.',
            ['retry_after' => $retryAfter],
            429
        );
    }
}

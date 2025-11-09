<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorResponse;
use App\Http\Responses\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    /**
     * Create a new API exception instance.
     */
    public function __construct(
        public ErrorType $type,
        string $message,
        public ?array $details = null,
        public int $statusCode = 500
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return ErrorResponse::make(
            $this->type,
            $this->getMessage(),
            $this->details,
            $this->statusCode
        );
    }

    /**
     * Create a not found exception.
     */
    public static function notFound(string $resource, string $identifier): self
    {
        return new self(
            ErrorType::NOT_FOUND,
            "$resource not found",
            ['identifier' => $identifier],
            404
        );
    }

    /**
     * Create a bad request exception.
     */
    public static function badRequest(string $message, ?array $details = null): self
    {
        return new self(
            ErrorType::BAD_REQUEST,
            $message,
            $details,
            400
        );
    }

    /**
     * Create a conflict exception.
     */
    public static function conflict(string $message, ?array $details = null): self
    {
        return new self(
            ErrorType::CONFLICT,
            $message,
            $details,
            409
        );
    }

    /**
     * Create an unauthorized exception.
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(
            ErrorType::UNAUTHORIZED,
            $message,
            null,
            401
        );
    }

    /**
     * Create a forbidden exception.
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(
            ErrorType::FORBIDDEN,
            $message,
            null,
            403
        );
    }
}

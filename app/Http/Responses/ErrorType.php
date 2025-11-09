<?php

namespace App\Http\Responses;

enum ErrorType: string
{
    case VALIDATION = 'ValidationError';
    case NOT_FOUND = 'NotFound';
    case CONFLICT = 'Conflict';
    case BAD_REQUEST = 'BadRequest';
    case UNAUTHORIZED = 'Unauthorized';
    case FORBIDDEN = 'Forbidden';
    case INTERNAL_ERROR = 'InternalServerError';
    case RATE_LIMIT = 'RateLimitExceeded';
}

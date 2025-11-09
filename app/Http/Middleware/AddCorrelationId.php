<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddCorrelationId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get correlation ID from header or generate new one
        $correlationId = $request->header('X-Correlation-ID') ?? Str::uuid()->toString();

        // Set it in the request for later use
        $request->headers->set('X-Correlation-ID', $correlationId);

        // Process the request
        $response = $next($request);

        // Add correlation ID to response headers
        if (method_exists($response, 'header')) {
            $response->header('X-Correlation-ID', $correlationId);
        }

        return $response;
    }
}

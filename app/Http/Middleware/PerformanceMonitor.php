<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request and monitor performance.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Process the request
        $response = $next($request);

        // Calculate metrics
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsage = round((memory_get_usage(true) - $startMemory) / 1024 / 1024, 2);

        // Add performance headers
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $memoryUsage . 'MB');
        $response->headers->set('X-Memory-Peak', round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB');

        // Log slow requests (>1000ms)
        if ($executionTime > 1000) {
            Log::channel('api')->warning('Slow API request detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'execution_time_ms' => $executionTime,
                'memory_usage_mb' => $memoryUsage,
                'status_code' => $response->getStatusCode(),
                'correlation_id' => $request->header('X-Correlation-ID'),
            ]);
        }

        // Log all API requests in production
        if (app()->environment('production') || env('LOG_API_REQUESTS', false)) {
            Log::channel('api')->info('API Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'execution_time_ms' => $executionTime,
                'memory_usage_mb' => $memoryUsage,
                'correlation_id' => $request->header('X-Correlation-ID'),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}

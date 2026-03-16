<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Cache\LockProvider;

class HealthController extends Controller
{
    /**
     * Get comprehensive health check status.
     */
    public function __invoke(): JsonResponse
    {
        $startTime = microtime(true);

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn ($check) => $check['status'] === 'healthy');
        $overallStatus = $allHealthy ? 'healthy' : 'degraded';

        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'version' => [
                'api' => 'v1',
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
            ],
            'services' => $checks,
            'runtime' => [
                'cache_default_store' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_connection' => config('queue.default'),
            ],
            'performance' => [
                'response_time_ms' => $responseTime,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ],
            '_links' => [
                'self' => [
                    'href' => route('api.v1.health'),
                    'method' => 'GET',
                ],
                'api_docs' => [
                    'href' => url('/api/documentation'),
                    'method' => 'GET',
                ],
            ],
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            DB::connection()->getDatabaseName();

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'response_time_ms' => $responseTime,
                'connection' => config('database.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check cache connectivity.
     */
    protected function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_'.now()->timestamp;
            $testValue = 'test';

            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache operations successful',
                    'response_time_ms' => $responseTime,
                    'driver' => config('cache.default'),
                    'supports_tags' => Cache::supportsTags(),
                    'supports_locks' => $this->cacheSupportsLocks(),
                    'redis' => [
                        'default' => $this->checkRedisConnection('default'),
                        'cache' => $this->checkRedisConnection('cache'),
                    ],
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Cache read/write mismatch',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache operations failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Cache error',
            ];
        }
    }

    protected function checkRedisConnection(string $connection): array
    {
        try {
            $startTime = microtime(true);
            $client = Redis::connection($connection);
            $response = $client->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => is_string($response) ? $response : 'PONG',
                'response_time_ms' => $responseTime,
                'connection' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Redis connection failed',
                'connection' => $connection,
                'error' => app()->environment('local') ? $e->getMessage() : 'Redis error',
            ];
        }
    }

    protected function cacheSupportsLocks(): bool
    {
        $store = Cache::getStore();

        return $store instanceof LockProvider;
    }

    /**
     * Check storage accessibility.
     */
    protected function checkStorage(): array
    {
        try {
            $startTime = microtime(true);
            $disk = Storage::disk('public');

            // Check if disk is accessible
            $exists = $disk->exists('.');

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => 'Storage accessible',
                'response_time_ms' => $responseTime,
                'disk' => 'public',
                'accessible' => $exists,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage access failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Storage error',
            ];
        }
    }
}

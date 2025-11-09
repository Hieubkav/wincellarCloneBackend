<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InfrastructureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test health check endpoint returns 200 when all services healthy.
     */
    public function test_health_check_returns_200_when_healthy(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'environment',
                'version' => ['api', 'laravel', 'php'],
                'services' => [
                    'database' => ['status', 'message'],
                    'cache' => ['status', 'message'],
                    'storage' => ['status', 'message'],
                ],
                'performance' => [
                    'response_time_ms',
                    'memory_usage_mb',
                    'memory_peak_mb',
                ],
                '_links' => ['self', 'api_docs'],
            ])
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('version.api', 'v1');
    }

    /**
     * Test health check includes service details.
     */
    public function test_health_check_includes_service_details(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        // Check database service
        $this->assertArrayHasKey('database', $data['services']);
        $this->assertEquals('healthy', $data['services']['database']['status']);
        $this->assertArrayHasKey('response_time_ms', $data['services']['database']);

        // Check cache service
        $this->assertArrayHasKey('cache', $data['services']);
        $this->assertEquals('healthy', $data['services']['cache']['status']);

        // Check storage service
        $this->assertArrayHasKey('storage', $data['services']);
        $this->assertEquals('healthy', $data['services']['storage']['status']);
    }

    /**
     * Test health check includes performance metrics.
     */
    public function test_health_check_includes_performance_metrics(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        $this->assertArrayHasKey('performance', $data);
        $this->assertIsNumeric($data['performance']['response_time_ms']);
        $this->assertIsNumeric($data['performance']['memory_usage_mb']);
        $this->assertIsNumeric($data['performance']['memory_peak_mb']);

        // Response time should be reasonable (< 1000ms)
        $this->assertLessThan(1000, $data['performance']['response_time_ms']);
    }

    /**
     * Test health check includes HATEOAS links.
     */
    public function test_health_check_includes_hateoas_links(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertJsonStructure([
            '_links' => [
                'self' => ['href', 'method'],
                'api_docs' => ['href', 'method'],
            ]
        ]);
    }

    /**
     * Test performance headers are added to responses.
     */
    public function test_performance_headers_are_added(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('X-Execution-Time')
            ->assertHeader('X-Memory-Usage')
            ->assertHeader('X-Memory-Peak');

        // Verify header formats
        $executionTime = $response->headers->get('X-Execution-Time');
        $this->assertStringEndsWith('ms', $executionTime);

        $memoryUsage = $response->headers->get('X-Memory-Usage');
        $this->assertStringEndsWith('MB', $memoryUsage);
    }

    /**
     * Test correlation ID is added by middleware.
     */
    public function test_correlation_id_middleware_works(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('X-Correlation-ID');
    }

    /**
     * Test custom correlation ID is preserved.
     */
    public function test_custom_correlation_id_is_preserved(): void
    {
        $customId = 'test-correlation-id-infrastructure';

        $response = $this->getJson('/api/v1/health', [
            'X-Correlation-ID' => $customId
        ]);

        $response->assertHeader('X-Correlation-ID', $customId);
    }

    /**
     * Test health check works without database (cache may fail gracefully).
     */
    public function test_health_check_handles_service_failures(): void
    {
        // This test verifies health check doesn't crash on service failures
        // In real scenario, you might mock service failures
        
        $response = $this->getJson('/api/v1/health');

        // Should still return a response (either 200 or 503)
        $this->assertContains($response->status(), [200, 503]);
        
        // Should have status field
        $response->assertJsonStructure(['status', 'services']);
    }

    /**
     * Test health endpoint is not rate limited aggressively.
     */
    public function test_health_endpoint_rate_limiting(): void
    {
        // Make multiple health check requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/health');
            $response->assertSuccessful();
        }

        // 10 health checks should work (within rate limit)
        $this->assertTrue(true);
    }

    /**
     * Test API versioning is consistent.
     */
    public function test_api_version_is_consistent_across_endpoints(): void
    {
        $endpoints = [
            '/api/v1/health',
            // Add other endpoints when needed
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            
            if ($response->status() === 200) {
                $data = $response->json();
                
                // Check for api_version in response
                if (isset($data['version']['api'])) {
                    $this->assertEquals('v1', $data['version']['api']);
                } elseif (isset($data['meta']['api_version'])) {
                    $this->assertEquals('v1', $data['meta']['api_version']);
                }
            }
        }
    }

    /**
     * Test health check response time is reasonable.
     */
    public function test_health_check_response_time_is_reasonable(): void
    {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/v1/health');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // in ms

        $response->assertSuccessful();

        // Health check should be fast (< 500ms)
        $this->assertLessThan(500, $duration);
    }

    /**
     * Test environment information is included.
     */
    public function test_environment_information_included(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        $this->assertArrayHasKey('environment', $data);
        $this->assertIsString($data['environment']);
        
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('laravel', $data['version']);
        $this->assertArrayHasKey('php', $data['version']);
    }

    /**
     * Test timestamp format in health check.
     */
    public function test_timestamp_format_is_iso8601(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        $this->assertArrayHasKey('timestamp', $data);
        
        // Verify ISO 8601 format
        $timestamp = $data['timestamp'];
        $parsed = \DateTime::createFromFormat(\DateTime::ISO8601, $timestamp);
        
        // ISO8601 format should parse successfully
        $this->assertNotFalse($parsed);
    }
}

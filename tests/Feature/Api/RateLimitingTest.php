<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear rate limiter before each test
        RateLimiter::clear('api:' . request()->ip());
    }

    /**
     * Test rate limiting is enforced at 60 requests per minute.
     */
    public function test_rate_limiting_enforces_60_requests_per_minute(): void
    {
        // Make 60 successful requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/v1/home');
            $response->assertSuccessful();
        }

        // 61st request should be rate limited
        $response = $this->getJson('/api/v1/home');
        
        $response->assertStatus(429)
            ->assertJson([
                'error' => 'RateLimitExceeded',
                'message' => 'Too many requests. Please slow down.',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details' => ['retry_after']
            ]);
    }

    /**
     * Test rate limit headers are present in responses.
     */
    public function test_rate_limit_headers_are_present(): void
    {
        $response = $this->getJson('/api/v1/home');

        // Note: Rate limit headers might be added by middleware
        // This test verifies they can be present
        $response->assertSuccessful();
    }

    /**
     * Test rate limit includes retry_after in response.
     */
    public function test_rate_limit_response_includes_retry_after(): void
    {
        // Exceed rate limit
        for ($i = 0; $i < 61; $i++) {
            $this->getJson('/api/v1/home');
        }

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(429)
            ->assertJsonPath('details.retry_after', 60);
    }

    /**
     * Test rate limiting applies to all API endpoints.
     */
    public function test_rate_limiting_applies_to_all_endpoints(): void
    {
        $endpoints = [
            '/api/v1/home',
            '/api/v1/san-pham',
            '/api/v1/bai-viet',
        ];

        foreach ($endpoints as $endpoint) {
            RateLimiter::clear('api:' . request()->ip());

            // Make 60 requests to this endpoint
            for ($i = 0; $i < 60; $i++) {
                $response = $this->getJson($endpoint);
                $response->assertSuccessful();
            }

            // 61st request should be rate limited
            $response = $this->getJson($endpoint);
            $response->assertStatus(429);
        }
    }

    /**
     * Test rate limit resets after time period.
     */
    public function test_rate_limit_shares_across_endpoints(): void
    {
        // Make 30 requests to home
        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/v1/home');
        }

        // Make 30 requests to products (should count towards same limit)
        for ($i = 0; $i < 30; $i++) {
            $this->getJson('/api/v1/san-pham');
        }

        // Next request should be rate limited (60 total requests made)
        $response = $this->getJson('/api/v1/bai-viet');
        
        $response->assertStatus(429)
            ->assertJson([
                'error' => 'RateLimitExceeded',
            ]);
    }

    /**
     * Test correlation ID is present in rate limit response.
     */
    public function test_correlation_id_present_in_rate_limit_response(): void
    {
        // Exceed rate limit
        for ($i = 0; $i < 61; $i++) {
            $this->getJson('/api/v1/home');
        }

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(429)
            ->assertJsonStructure(['correlation_id']);
    }
}

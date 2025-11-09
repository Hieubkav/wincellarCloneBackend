<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validation error returns 422 with standard format.
     */
    public function test_validation_error_returns_422_with_standard_format(): void
    {
        $response = $this->getJson('/api/v1/san-pham?price_min=abc');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details' => [
                    'errors' => [
                        '*' => ['field', 'message', 'value']
                    ]
                ]
            ])
            ->assertJson([
                'error' => 'ValidationError',
                'message' => 'Request validation failed',
            ]);
    }

    /**
     * Test not found returns 404 with standard format.
     */
    public function test_not_found_returns_404_with_standard_format(): void
    {
        $response = $this->getJson('/api/v1/san-pham/non-existent-slug');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details' => ['identifier']
            ])
            ->assertJson([
                'error' => 'NotFound',
            ]);
    }

    /**
     * Test bad request returns 400 for invalid range.
     */
    public function test_bad_request_returns_400_for_invalid_price_range(): void
    {
        $response = $this->getJson('/api/v1/san-pham?price_min=5000000&price_max=1000000');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details'
            ])
            ->assertJson([
                'error' => 'BadRequest',
                'message' => 'Invalid price range',
            ]);
    }

    /**
     * Test bad request returns 400 for invalid alcohol range.
     */
    public function test_bad_request_returns_400_for_invalid_alcohol_range(): void
    {
        $response = $this->getJson('/api/v1/san-pham?alcohol_min=50&alcohol_max=10');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'BadRequest',
                'message' => 'Invalid alcohol range',
            ]);
    }

    /**
     * Test correlation ID is present in response.
     */
    public function test_correlation_id_is_present_in_response(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertHeader('X-Correlation-ID');
    }

    /**
     * Test custom correlation ID is preserved.
     */
    public function test_custom_correlation_id_is_preserved(): void
    {
        $customId = 'test-correlation-id-123';

        $response = $this->getJson('/api/v1/home', [
            'X-Correlation-ID' => $customId
        ]);

        $response->assertHeader('X-Correlation-ID', $customId);
    }

    /**
     * Test product not found error includes slug in details.
     */
    public function test_product_not_found_includes_slug_in_details(): void
    {
        $slug = 'non-existent-product';

        $response = $this->getJson("/api/v1/san-pham/{$slug}");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'NotFound',
                'message' => 'Product not found',
                'details' => [
                    'identifier' => $slug
                ]
            ]);
    }

    /**
     * Test article not found error.
     */
    public function test_article_not_found_returns_404(): void
    {
        $response = $this->getJson('/api/v1/bai-viet/non-existent-article');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'NotFound',
                'message' => 'Article not found',
            ]);
    }

    /**
     * Test successful request returns 200 without error fields.
     */
    public function test_successful_request_does_not_have_error_fields(): void
    {
        Product::factory()->create([
            'slug' => 'test-product',
            'active' => true,
        ]);

        $response = $this->getJson('/api/v1/san-pham/test-product');

        $response->assertStatus(200)
            ->assertJsonMissing(['error'])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                ]
            ]);
    }
}

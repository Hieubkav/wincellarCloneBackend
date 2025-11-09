<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product list includes HATEOAS links.
     */
    public function test_product_list_includes_hateoas_links(): void
    {
        Product::factory()->create(['active' => true]);

        $response = $this->getJson('/api/v1/san-pham');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        '_links' => [
                            'self',
                            'list',
                        ]
                    ]
                ],
                'meta' => [
                    'pagination',
                    'sorting',
                    'filtering',
                    'api_version',
                    'timestamp'
                ],
                '_links' => [
                    'self',
                    'first',
                    'last'
                ]
            ]);
    }

    /**
     * Test product detail includes additional fields not in list.
     */
    public function test_product_detail_includes_conditional_fields(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'active' => true,
            'description' => 'Detailed description',
        ]);

        $response = $this->getJson("/api/v1/san-pham/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',  // Only in detail view
                    'breadcrumbs',  // Only in detail view
                    'meta',         // Only in detail view
                    '_links' => [
                        'self',
                        'list',
                        'related'
                    ]
                ]
            ])
            ->assertJsonPath('data.description', 'Detailed description');
    }

    /**
     * Test product list does not include detail-only fields.
     */
    public function test_product_list_excludes_detail_fields(): void
    {
        Product::factory()->create([
            'active' => true,
            'description' => 'Should not appear in list',
        ]);

        $response = $this->getJson('/api/v1/san-pham');

        $response->assertStatus(200)
            ->assertJsonMissing(['description' => 'Should not appear in list'])
            ->assertJsonMissing(['breadcrumbs'])
            ->assertJsonMissing(['grape_terms'])
            ->assertJsonMissing(['origin_terms']);
    }

    /**
     * Test pagination meta structure is standardized.
     */
    public function test_pagination_meta_is_standardized(): void
    {
        Product::factory()->count(5)->create(['active' => true]);

        $response = $this->getJson('/api/v1/san-pham?per_page=2&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => [
                    'pagination' => [
                        'page',
                        'per_page',
                        'total',
                        'last_page',
                        'has_more'
                    ],
                    'api_version',
                    'timestamp'
                ]
            ])
            ->assertJsonPath('meta.pagination.page', 1)
            ->assertJsonPath('meta.pagination.per_page', 2)
            ->assertJsonPath('meta.pagination.total', 5);
    }

    /**
     * Test HATEOAS next/prev links work correctly.
     */
    public function test_hateoas_pagination_links(): void
    {
        Product::factory()->count(10)->create(['active' => true]);

        // First page
        $response = $this->getJson('/api/v1/san-pham?per_page=3&page=1');
        $response->assertStatus(200)
            ->assertJsonStructure(['_links' => ['next', 'last', 'first']])
            ->assertJsonMissing(['_links' => ['prev']]);

        // Middle page
        $response = $this->getJson('/api/v1/san-pham?per_page=3&page=2');
        $response->assertStatus(200)
            ->assertJsonStructure(['_links' => ['prev', 'next']]);

        // Last page
        $lastPage = ceil(10 / 3);
        $response = $this->getJson("/api/v1/san-pham?per_page=3&page={$lastPage}");
        $response->assertStatus(200)
            ->assertJsonStructure(['_links' => ['prev', 'first', 'last']])
            ->assertJsonMissing(['_links' => ['next']]);
    }

    /**
     * Test article list includes HATEOAS links.
     */
    public function test_article_list_includes_hateoas_links(): void
    {
        Article::factory()->create(['active' => true]);

        $response = $this->getJson('/api/v1/bai-viet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        '_links' => [
                            'self',
                            'list'
                        ]
                    ]
                ],
                'meta' => [
                    'pagination',
                    'sorting',
                    'filtering',
                    'api_version'
                ],
                '_links'
            ]);
    }

    /**
     * Test article detail includes conditional fields.
     */
    public function test_article_detail_includes_conditional_fields(): void
    {
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'active' => true,
            'content' => 'Full article content',
        ]);

        $response = $this->getJson("/api/v1/bai-viet/{$article->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',      // Only in detail view
                    'gallery',      // Only in detail view
                    'meta',         // Only in detail view
                    'updated_at',   // Only in detail view
                    '_links'
                ]
            ])
            ->assertJsonPath('data.content', 'Full article content');
    }

    /**
     * Test API version is included in all responses.
     */
    public function test_api_version_in_responses(): void
    {
        Product::factory()->create(['slug' => 'test', 'active' => true]);

        // List response
        $response = $this->getJson('/api/v1/san-pham');
        $response->assertJsonPath('meta.api_version', 'v1');

        // Detail response
        $response = $this->getJson('/api/v1/san-pham/test');
        $response->assertJsonPath('meta.api_version', 'v1');
    }

    /**
     * Test timestamp is included in all responses.
     */
    public function test_timestamp_in_responses(): void
    {
        Product::factory()->create(['slug' => 'test', 'active' => true]);

        // List response
        $response = $this->getJson('/api/v1/san-pham');
        $response->assertJsonStructure(['meta' => ['timestamp']]);

        // Detail response
        $response = $this->getJson('/api/v1/san-pham/test');
        $response->assertJsonStructure(['meta' => ['timestamp']]);
    }

    /**
     * Test filtering meta reflects query parameters.
     */
    public function test_filtering_meta_reflects_query_params(): void
    {
        Product::factory()->create(['active' => true]);

        $response = $this->getJson('/api/v1/san-pham?price_min=100000&price_max=500000&q=test');

        $response->assertStatus(200)
            ->assertJsonPath('meta.filtering.price_min', 100000)
            ->assertJsonPath('meta.filtering.price_max', 500000)
            ->assertJsonPath('meta.filtering.q', 'test');
    }

    /**
     * Test self link in resource points to correct URL.
     */
    public function test_self_link_points_to_correct_url(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product-123',
            'active' => true
        ]);

        $response = $this->getJson("/api/v1/san-pham/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data._links.self.href', route('api.v1.products.show', ['slug' => $product->slug]))
            ->assertJsonPath('data._links.self.method', 'GET');
    }

    /**
     * Test contextual links are included based on data.
     */
    public function test_contextual_links_based_on_data(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'active' => true
        ]);

        $response = $this->getJson("/api/v1/san-pham/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '_links' => [
                        'self',
                        'list',
                        'related'
                    ]
                ]
            ]);
    }
}

<?php

namespace App\Services\Api\V1\Home;

use App\Models\Article;
use App\Models\CatalogTerm;
use App\Models\HomeComponent;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Collection;

class HomeComponentResources
{
    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Product> $products
     * @param \Illuminate\Support\Collection<int, \App\Models\Article> $articles
     * @param \Illuminate\Support\Collection<int, \App\Models\Image> $images
     * @param \Illuminate\Support\Collection<int, \App\Models\CatalogTerm> $terms
     */
    public function __construct(
        private readonly Collection $products,
        private readonly Collection $articles,
        private readonly Collection $images,
        private readonly Collection $terms,
        private readonly \Closure $missingLogger,
    ) {
    }

    public function product(HomeComponent $component, int $id): ?Product
    {
        $product = $this->products->get($id);

        if (!$product) {
            ($this->missingLogger)($component, 'product', $id);
        }

        return $product;
    }

    public function article(HomeComponent $component, int $id): ?Article
    {
        $article = $this->articles->get($id);

        if (!$article) {
            ($this->missingLogger)($component, 'article', $id);
        }

        return $article;
    }

    public function image(HomeComponent $component, int $id): ?Image
    {
        $image = $this->images->get($id);

        if (!$image) {
            ($this->missingLogger)($component, 'image', $id);
        }

        return $image;
    }

    public function term(HomeComponent $component, int $id): ?CatalogTerm
    {
        $term = $this->terms->get($id);

        if (!$term) {
            ($this->missingLogger)($component, 'catalog_term', $id);
        }

        return $term;
    }

    public function mapProductSummary(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percent' => $product->discount_percent,
            'show_contact_cta' => $product->should_show_contact_cta,
            'cover_image_url' => $product->cover_image_url,
        ];
    }

    public function mapArticleSummary(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'cover_image_url' => $article->cover_image_url,
            'published_at' => optional($article->created_at)->toIso8601String(),
        ];
    }

    public function mapTermSummary(CatalogTerm $term): array
    {
        return [
            'id' => $term->id,
            'name' => $term->name,
            'slug' => $term->slug,
            'group' => $term->group ? [
                'id' => $term->group->id,
                'code' => $term->group->code,
                'name' => $term->group->name,
            ] : null,
            'icon_type' => $term->icon_type,
            'icon_value' => $term->icon_value,
        ];
    }

    public function mapImage(Image $image, ?string $overrideAlt = null): array
    {
        return [
            'id' => $image->id,
            'url' => $image->url,
            'alt' => $overrideAlt ?: $image->alt,
            'width' => $image->width,
            'height' => $image->height,
        ];
    }

    public function defaultProductHref(Product $product): string
    {
        return '/san-pham/'.$product->slug;
    }

    public function defaultArticleHref(Article $article): string
    {
        return '/bai-viet/'.$article->slug;
    }

    public function payload(HomeComponent $component, array $config): array
    {
        return [
            'id' => $component->id,
            'type' => $component->type,
            'order' => $component->order,
            'config' => $config,
            'updated_at' => optional($component->updated_at)->toIso8601String(),
        ];
    }
}

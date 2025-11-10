<?php

namespace App\Support\Product;

use App\Models\CatalogTerm;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductOutput
{
    /**
     * @return array<string, mixed>
     */
    public static function listItem(Product $product): array
    {
        $coverImageUrl = $product->cover_image_url;
        if ($coverImageUrl && !self::fileExists($coverImageUrl)) {
            $coverImageUrl = '/placeholder/wine-bottle.svg';
        }

        $gallery = $product->gallery_for_output->map(function ($image) {
            if ($image['url'] && !self::fileExists($image['url'])) {
                $image['url'] = '/placeholder/wine-bottle.svg';
            }
            return $image;
        })->all();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percent' => $product->discount_percent,
            'show_contact_cta' => $product->should_show_contact_cta,
            'main_image_url' => $coverImageUrl,
            'gallery' => $gallery,
            'brand_term' => self::transformTerm($product->primaryTerm('brand')),
            'country_term' => self::transformTerm($product->primaryTerm('origin')),
            'alcohol_percent' => $product->alcohol_percent,
            'volume_ml' => $product->volume_ml,
            'badges' => $product->badges ?? [],
            'category' => $product->categories->first() ? [
                'id' => $product->categories->first()->id,
                'name' => $product->categories->first()->name,
                'slug' => $product->categories->first()->slug,
            ] : null,
            'type' => $product->type ? [
                'id' => $product->type->id,
                'name' => $product->type->name,
                'slug' => $product->type->slug,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function suggestion(Product $product): array
    {
        $coverImageUrl = $product->cover_image_url;
        if ($coverImageUrl && !self::fileExists($coverImageUrl)) {
            $coverImageUrl = '/placeholder/wine-bottle.svg';
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percent' => $product->discount_percent,
            'show_contact_cta' => $product->should_show_contact_cta,
            'main_image_url' => $coverImageUrl,
            'brand_term' => self::transformTerm($product->primaryTerm('brand')),
            'country_term' => self::transformTerm($product->primaryTerm('origin')),
            'category' => $product->categories->first() ? [
                'id' => $product->categories->first()->id,
                'name' => $product->categories->first()->name,
                'slug' => $product->categories->first()->slug,
            ] : null,
            'type' => $product->type ? [
                'id' => $product->type->id,
                'name' => $product->type->name,
                'slug' => $product->type->slug,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(Product $product): array
    {
        $grapeTerms = self::transformTerms($product->termsByGroup('grape'));
        $originTerms = self::transformTerms($product->termsByGroup('origin'));

        $coverImageUrl = $product->cover_image_url;
        if ($coverImageUrl && !self::fileExists($coverImageUrl)) {
            $coverImageUrl = '/placeholder/wine-bottle.svg';
        }

        $gallery = $product->gallery_for_output->map(function ($image) {
            if ($image['url'] && !self::fileExists($image['url'])) {
                $image['url'] = '/placeholder/wine-bottle.svg';
            }
            return $image;
        })->all();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price,
            'original_price' => $product->original_price,
            'discount_percent' => $product->discount_percent,
            'show_contact_cta' => $product->should_show_contact_cta,
            'cover_image_url' => $coverImageUrl,
            'gallery' => $gallery,
            'brand_term' => self::transformTerm($product->primaryTerm('brand')),
            'country_term' => self::transformTerm($product->primaryTerm('origin')),
            'grape_terms' => $grapeTerms,
            'origin_terms' => $originTerms,
            'alcohol_percent' => $product->alcohol_percent,
            'volume_ml' => $product->volume_ml,
            'badges' => $product->badges ?? [],
            'category' => $product->categories->first() ? [
                'id' => $product->categories->first()->id,
                'name' => $product->categories->first()->name,
                'slug' => $product->categories->first()->slug,
            ] : null,
            'type' => $product->type ? [
                'id' => $product->type->id,
                'name' => $product->type->name,
                'slug' => $product->type->slug,
            ] : null,
            'breadcrumbs' => self::buildBreadcrumbs($product),
            'meta' => [
                'title' => $product->meta_title,
                'description' => $product->meta_description,
            ],
        ];
    }

    private static function transformTerm(?CatalogTerm $term): ?array
    {
        if (!$term) {
            return null;
        }

        return [
            'id' => $term->id,
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, CatalogTerm> $terms
     * @return array<int, array{id:int,name:string,slug:string}>
     */
    private static function transformTerms(Collection $terms): array
    {
        return $terms
            ->map(fn (CatalogTerm $term) => self::transformTerm($term))
            ->filter()
            ->values()
            ->all();
    }

    /**
    * @return array<int, array{label:string,href:string}>
    */
    private static function buildBreadcrumbs(Product $product): array
    {
    $breadcrumbs = [];

    if ($category = $product->categories->first()) {
    $breadcrumbs[] = [
    'label' => $category->name,
    'href' => '/san-pham/'.$category->slug,
    ];
    }

    if ($product->type) {
    $breadcrumbs[] = [
    'label' => $product->type->name,
    'href' => '/san-pham?type='.$product->type->slug,
    ];
    }

    if ($brand = $product->primaryTerm('brand')) {
    $breadcrumbs[] = [
    'label' => $brand->name,
    'href' => '/san-pham?brand='.$brand->slug,
    ];
    }

    return $breadcrumbs;
    }

    private static function fileExists(string $url): bool
    {
        // Remove domain if present, assume localhost:8000
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return false;
        }

        // Assuming storage/app/public is symlinked to public/storage
        $fullPath = public_path($path);
        return file_exists($fullPath);
    }
}

<?php

namespace App\Services\Api\V1\Home;

use App\Models\Article;
use App\Models\CatalogTerm;
use App\Models\HomeComponent;
use App\Models\Image;
use App\Models\Product;
use App\Services\Api\V1\Home\Transformers\BrandShowcaseTransformer;
use App\Services\Api\V1\Home\Transformers\CategoryGridTransformer;
use App\Services\Api\V1\Home\Transformers\CollectionShowcaseTransformer;
use App\Services\Api\V1\Home\Transformers\DefaultComponentTransformer;
use App\Services\Api\V1\Home\Transformers\DualBannerTransformer;
use App\Services\Api\V1\Home\Transformers\EditorialSpotlightTransformer;
use App\Services\Api\V1\Home\Transformers\FavouriteProductsTransformer;
use App\Services\Api\V1\Home\Transformers\HeroCarouselTransformer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HomeComponentAssembler
{
    private HeroCarouselTransformer $heroCarousel;
    private DualBannerTransformer $dualBanner;
    private CategoryGridTransformer $categoryGrid;
    private FavouriteProductsTransformer $favouriteProducts;
    private CollectionShowcaseTransformer $collectionShowcase;
    private EditorialSpotlightTransformer $editorialSpotlight;
    private BrandShowcaseTransformer $brandShowcase;
    private DefaultComponentTransformer $defaultTransformer;

    public function __construct(
        ?HeroCarouselTransformer $heroCarousel = null,
        ?DualBannerTransformer $dualBanner = null,
        ?CategoryGridTransformer $categoryGrid = null,
        ?FavouriteProductsTransformer $favouriteProducts = null,
        ?CollectionShowcaseTransformer $collectionShowcase = null,
        ?EditorialSpotlightTransformer $editorialSpotlight = null,
        ?BrandShowcaseTransformer $brandShowcase = null,
        ?DefaultComponentTransformer $defaultTransformer = null,
    ) {
        $this->heroCarousel = $heroCarousel ?? new HeroCarouselTransformer();
        $this->dualBanner = $dualBanner ?? new DualBannerTransformer();
        $this->categoryGrid = $categoryGrid ?? new CategoryGridTransformer();
        $this->favouriteProducts = $favouriteProducts ?? new FavouriteProductsTransformer();
        $this->collectionShowcase = $collectionShowcase ?? new CollectionShowcaseTransformer();
        $this->editorialSpotlight = $editorialSpotlight ?? new EditorialSpotlightTransformer();
        $this->brandShowcase = $brandShowcase ?? new BrandShowcaseTransformer();
        $this->defaultTransformer = $defaultTransformer ?? new DefaultComponentTransformer();
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\HomeComponent> $components
     * @return array<int, array<string, mixed>>
     */
    public function build(Collection $components): array
    {
        if ($components->isEmpty()) {
            return [];
        }

        $referenceIds = $this->collectReferenceIds($components);
        $resources = $this->resolveResources($referenceIds);

        $resourceBag = new HomeComponentResources(
            $resources['products'],
            $resources['articles'],
            $resources['images'],
            $resources['terms'],
            fn (HomeComponent $component, string $type, int $id) => $this->logMissing($component, $type, $id),
        );

        $payload = [];

        foreach ($components as $component) {
            $transformed = $this->transformComponent($component, $resourceBag);

            if ($transformed !== null) {
                $payload[] = $transformed;
            }
        }

        return $payload;
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\HomeComponent> $components
     * @return array{
     *     products: array<int>,
     *     articles: array<int>,
     *     images: array<int>,
     *     terms: array<int>
     * }
     */
    private function collectReferenceIds(Collection $components): array
    {
        $ids = [
            'products' => [],
            'articles' => [],
            'images' => [],
            'terms' => [],
        ];

        foreach ($components as $component) {
            $config = is_array($component->config) ? $component->config : [];

            $ids['products'] = array_merge($ids['products'], $this->extractIds($config, 'product_id'));
            $ids['articles'] = array_merge($ids['articles'], $this->extractIds($config, 'article_id'));
            $ids['images'] = array_merge($ids['images'], $this->extractIds($config, 'image_id'));
            $ids['terms'] = array_merge($ids['terms'], $this->extractIds($config, 'term_id'));
        }

        return array_map(
            fn (array $values) => array_values(array_unique(array_filter(array_map(
                fn ($value) => $this->toPositiveInt($value),
                $values
            )))),
            $ids
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return int[]
     */
    private function extractIds(array $data, string $key): array
    {
        $results = [];

        foreach ($data as $currentKey => $value) {
            if ($currentKey === $key) {
                $intValue = $this->toPositiveInt($value);
                if ($intValue !== null) {
                    $results[] = $intValue;
                }
                continue;
            }

            if (is_array($value)) {
                $results = array_merge($results, $this->extractIds($value, $key));
            }
        }

        return $results;
    }

    private function toPositiveInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $intValue = (int) $value;

            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    /**
     * @param array{
     *     products: array<int>,
     *     articles: array<int>,
     *     images: array<int>,
     *     terms: array<int>
     * } $referenceIds
     * @return array{
     *     products: \Illuminate\Support\Collection<int, \App\Models\Product>,
     *     articles: \Illuminate\Support\Collection<int, \App\Models\Article>,
     *     images: \Illuminate\Support\Collection<int, \App\Models\Image>,
     *     terms: \Illuminate\Support\Collection<int, \App\Models\CatalogTerm>
     * }
     */
    private function resolveResources(array $referenceIds): array
    {
        $products = empty($referenceIds['products'])
            ? collect()
            : Product::query()
                ->with(['coverImage'])
                ->active()
                ->whereIn('id', $referenceIds['products'])
                ->get()
                ->keyBy('id');

        $articles = empty($referenceIds['articles'])
            ? collect()
            : Article::query()
                ->with(['coverImage'])
                ->active()
                ->whereIn('id', $referenceIds['articles'])
                ->get()
                ->keyBy('id');

        $images = empty($referenceIds['images'])
            ? collect()
            : Image::query()
                ->whereIn('id', $referenceIds['images'])
                ->whereNull('deleted_at')
                ->where('active', true)
                ->get()
                ->keyBy('id');

        $terms = empty($referenceIds['terms'])
            ? collect()
            : CatalogTerm::query()
                ->with(['group'])
                ->active()
                ->whereIn('id', $referenceIds['terms'])
                ->get()
                ->keyBy('id');

        return compact('products', 'articles', 'images', 'terms');
    }

    private function transformComponent(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        return match ($component->type) {
            'HeroCarousel' => $this->heroCarousel->transform($component, $resources),
            'DualBanner' => $this->dualBanner->transform($component, $resources),
            'CategoryGrid' => $this->categoryGrid->transform($component, $resources),
            'FavouriteProducts' => $this->favouriteProducts->transform($component, $resources),
            'CollectionShowcase' => $this->collectionShowcase->transform($component, $resources),
            'EditorialSpotlight' => $this->editorialSpotlight->transform($component, $resources),
            'BrandShowcase' => $this->brandShowcase->transform($component, $resources),
            default => $this->defaultTransformer->transform($component, $resources),
        };
    }

    private function logMissing(HomeComponent $component, string $type, int $id): void
    {
        Log::warning('Home component reference missing', [
            'component_id' => $component->id,
            'component_type' => $component->type,
            'reference_type' => $type,
            'reference_id' => $id,
        ]);
    }
}

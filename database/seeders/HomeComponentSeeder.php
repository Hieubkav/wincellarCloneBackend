<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeComponentSeeder extends Seeder
{
    public function run(): void
    {
        $context = SeederContext::get();

        Schema::disableForeignKeyConstraints();
        DB::table('home_components')->truncate();
        Schema::enableForeignKeyConstraints();

        if (!$context->flag('seed_home_components', false)) {
            return;
        }

        $now = $context->now();

        $heroSlides = $this->buildHeroSlides($context);
        $favouriteProducts = $this->buildFavouriteProducts();
        $editorial = $this->buildEditorialSpotlight();
        $collection = $this->buildCollectionShowcase();

        $components = array_values(array_filter([
            $heroSlides ? [
                'id' => 1,
                'type' => 'HeroCarousel',
                'config' => json_encode(['slides' => $heroSlides]),
                'order' => 0,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] : null,
            $favouriteProducts ? [
                'id' => 2,
                'type' => 'FavouriteProducts',
                'config' => json_encode(['title' => 'Sản phẩm nổi bật', 'products' => $favouriteProducts]),
                'order' => 1,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] : null,
            $collection ? [
                'id' => 3,
                'type' => 'CollectionShowcase',
                'config' => json_encode($collection),
                'order' => 2,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] : null,
            $editorial ? [
                'id' => 4,
                'type' => 'EditorialSpotlight',
                'config' => json_encode(['title' => 'Góc kiến thức', 'articles' => $editorial]),
                'order' => 3,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ] : null,
        ]));

        if (!empty($components)) {
            DB::table('home_components')->insert($components);
        }
    }

    private function buildHeroSlides(SeederContext $context): array
    {
        $images = DB::table('images')
            ->select('id', 'model_id', 'alt')
            ->where('model_type', $context->modelClass('product'))
            ->where('order', 0)
            ->limit(4)
            ->get();

        if ($images->isEmpty()) {
            return [];
        }

        $slides = [];
        foreach ($images as $image) {
            $slug = DB::table('products')->where('id', $image->model_id)->value('slug');
            if (!$slug) {
                continue;
            }

            $slides[] = [
                'image_id' => $image->id,
                'alt' => $image->alt,
                'href' => "/san-pham/{$slug}",
            ];
        }

        return $slides;
    }

    private function buildFavouriteProducts(): array
    {
        $products = DB::table('products')
            ->select('id', 'slug', 'badges')
            ->where('active', true)
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        $items = [];
        foreach ($products as $product) {
            $badges = $product->badges ? json_decode($product->badges, true) : [];
            $items[] = [
                'product_id' => $product->id,
                'href' => "/san-pham/{$product->slug}",
                'badge' => $badges[0] ?? null,
            ];
        }

        return $items;
    }

    private function buildCollectionShowcase(): ?array
    {
        $products = DB::table('products')
            ->select('id', 'slug')
            ->where('active', true)
            ->limit(6)
            ->get();

        if ($products->count() < 3) {
            return null;
        }

        return [
            'title' => 'Bộ sưu tập Sommelier Selection',
            'subtitle' => 'Chắt lọc 6 chai vang được yêu thích nhất',
            'description' => 'Danh sách được chọn lọc bởi đội ngũ sommelier của Wincellar nhằm mang lại trải nghiệm cân bằng cho các bữa tiệc.',
            'ctaLabel' => 'Xem tất cả',
            'ctaHref' => '/san-pham/goi-y/sommelier-selection',
            'tone' => 'wine',
            'products' => $products->map(fn ($product) => [
                'product_id' => $product->id,
                'href' => "/san-pham/{$product->slug}",
                'badge' => 'HOT',
            ])->all(),
        ];
    }

    private function buildEditorialSpotlight(): array
    {
        $articles = DB::table('articles')
            ->select('id', 'slug')
            ->where('active', true)
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        if ($articles->isEmpty()) {
            return [];
        }

        return $articles
            ->map(fn ($article) => [
                'article_id' => $article->id,
                'href' => "/bai-viet/{$article->slug}",
            ])
            ->all();
    }
}


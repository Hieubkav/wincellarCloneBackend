<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $context = SeederContext::get();
        $faker = $context->faker();

        Schema::disableForeignKeyConstraints();
        DB::table('product_grapes')->truncate();
        DB::table('product_regions')->truncate();
        DB::table('products')->truncate();
        Schema::enableForeignKeyConstraints();

        // Xóa ảnh cũ gắn với product để tránh order duplicate.
        DB::table('images')
            ->where('model_type', $context->modelClass('product'))
            ->delete();

        $categories = DB::table('product_categories')->select('id', 'name')->get();
        $types = DB::table('product_types')->select('id', 'name')->get();
        $brands = DB::table('brands')->select('id', 'name')->get();
        $countries = DB::table('countries')->select('id', 'name')->get();
        $regions = DB::table('regions')->select('id', 'country_id', 'name')->get()->groupBy('country_id');
        $grapes = DB::table('grapes')->select('id', 'name')->get();

        $productCount = $context->count('products', 120);
        $imageRange = $context->range('product_images', [1, 3]);
        $grapeRange = $context->range('product_grapes', [2, 4]);
        $regionRange = $context->range('product_regions', [1, 2]);

        $productRows = [];
        $imageRows = [];
        $productGrapes = [];
        $productRegions = [];
        $productId = 1;

        for ($i = 0; $i < $productCount; $i++, $productId++) {
            $category = $categories->random();
            $type = $types->random();
            $brand = $brands->random();
            $country = $countries->random();
            $regionCollection = $regions->get($country->id, collect());

            $name = $this->generateProductName($context, $brand->name, $type->name);
            $slug = $context->uniqueSlug('products', $name);

            $price = $context->randomPrice();
            $originalPrice = $price;
            $discounted = random_int(1, 100) <= 35;
            if ($discounted) {
                $originalPrice = (int) round($price * random_int(110, 145) / 100);
            }

            $badges = $context->randomBadges();
            if ($discounted && ($badges === null || !in_array('SALE', $badges, true))) {
                $badges = array_merge($badges ?? [], ['SALE']);
            }

            $selectedRegions = $this->pickRegions($regionCollection, $regionRange);
            $primaryRegionId = $selectedRegions[0]['region_id'] ?? null;

            $selectedGrapes = $this->pickGrapes($grapes, $grapeRange);

            $productRows[] = [
                'id' => $productId,
                'name' => $name,
                'slug' => $slug,
                'brand_id' => $brand->id,
                'product_category_id' => $category->id,
                'type_id' => $type->id,
                'country_id' => $country->id,
                'region_id' => $primaryRegionId,
                'description' => $faker->paragraphs(random_int(2, 4), true),
                'price' => $price,
                'original_price' => $originalPrice,
                'alcohol_percent' => $context->randomAlcohol(),
                'volume_ml' => $context->randomVolume(),
                'badges' => $badges ? json_encode($badges) : null,
                'active' => random_int(1, 100) > 7,
                'meta_title' => "{$name} | Wincellar",
                'meta_description' => $faker->sentence(20),
                'created_at' => $context->now(),
                'updated_at' => $context->now(),
            ];

            foreach ($selectedGrapes as $indexGrape => $grapeId) {
                $productGrapes[] = [
                    'product_id' => $productId,
                    'grape_id' => $grapeId,
                    'order' => $indexGrape === 0 ? 0 : $indexGrape + 1,
                ];
            }

            foreach ($selectedRegions as $regionData) {
                $productRegions[] = [
                    'product_id' => $productId,
                    'region_id' => $regionData['region_id'],
                    'order' => $regionData['order'],
                ];
            }

            $imageTotal = max($imageRange[0], random_int($imageRange[0], max($imageRange[0], $imageRange[1])));
            for ($imgIndex = 0; $imgIndex < $imageTotal; $imgIndex++) {
                $imageRows[] = [
                    'id' => $context->nextImageId(),
                    'file_path' => "products/{$slug}-" . ($imgIndex + 1) . '.jpg',
                    'disk' => 'public',
                    'alt' => "{$name} hình " . ($imgIndex + 1),
                    'width' => 1600,
                    'height' => 2000,
                    'mime' => 'image/jpeg',
                    'model_type' => $context->modelClass('product'),
                    'model_id' => $productId,
                    'order' => $imgIndex === 0 ? 0 : $imgIndex + 1,
                    'active' => true,
                    'extra_attributes' => json_encode([
                        'palette' => $faker->safeColorName(),
                        'shot' => $imgIndex === 0 ? 'cover' : 'detail',
                    ]),
                    'created_at' => $context->now(),
                    'updated_at' => $context->now(),
                ];
            }

            if ($productId % self::CHUNK_SIZE === 0) {
                $this->flush($productRows, $imageRows, $productGrapes, $productRegions);
            }
        }

        $this->flush($productRows, $imageRows, $productGrapes, $productRegions);

        $faker->unique(true);
    }

    private function flush(array &$products, array &$images, array &$grapes, array &$regions): void
    {
        if (!empty($products)) {
            DB::table('products')->insert($products);
            $products = [];
        }

        if (!empty($images)) {
            DB::table('images')->insert($images);
            $images = [];
        }

        if (!empty($grapes)) {
            DB::table('product_grapes')->insert($grapes);
            $grapes = [];
        }

        if (!empty($regions)) {
            DB::table('product_regions')->insert($regions);
            $regions = [];
        }
    }

    private function generateProductName(SeederContext $context, string $brandName, string $typeName): string
    {
        $faker = $context->faker();
        $vintage = random_int(1995, 2023);
        $descriptor = $faker->randomElement(['Reserve', 'Signature', 'Estate', 'Grand Cru', 'Limited Edition', 'Classic']);

        return trim("{$brandName} {$descriptor} {$typeName} {$vintage}");
    }

    /**
     * @return array<int>
     */
    private function pickGrapes($grapes, array $range): array
    {
        $available = $grapes->count();
        if ($available === 0) {
            return [];
        }

        $min = max(1, $range[0]);
        $max = max($min, $range[1]);
        $target = min($available, random_int($min, $max));

        $selected = $grapes->random($target);
        if ($selected instanceof \Illuminate\Support\Collection) {
            return $selected->pluck('id')->all();
        }

        return [$selected->id];
    }

    /**
     * @return array<int, array{region_id:int,order:int}>
     */
    private function pickRegions($regions, array $range): array
    {
        if ($regions->count() === 0) {
            return [];
        }

        $min = max(1, $range[0]);
        $max = max($min, $range[1]);
        $take = min($regions->count(), random_int($min, $max));

        $selected = $regions->random($take);
        $selectedIds = $selected instanceof \Illuminate\Support\Collection
            ? $selected->pluck('id')->unique()->values()
            : collect([$selected->id]);

        $result = [];
        foreach ($selectedIds as $index => $regionId) {
            $result[] = [
                'region_id' => $regionId,
                'order' => $index === 0 ? 0 : $index + 1,
            ];
        }

        return $result;
    }
}

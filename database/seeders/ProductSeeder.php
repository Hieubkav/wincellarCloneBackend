<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    public function run(): void
    {
        $context = SeederContext::get();
        $faker = $context->faker();
        $now = $context->now();

        Schema::disableForeignKeyConstraints();
        DB::table('product_term_assignments')->truncate();
        DB::table('products')->truncate();
        Schema::enableForeignKeyConstraints();

        // Xóa ảnh cũ gắn với product để tránh order duplicate.
        DB::table('images')
            ->where('model_type', $context->modelClass('product'))
            ->delete();

        $categories = DB::table('product_categories')->select('id', 'name', 'slug')->get();
        $types = DB::table('product_types')->select('id', 'name', 'slug')->get();
        $taxonomy = $this->loadTaxonomy();

        $productCount = $context->count('products', 150);
        $imageRange = $context->range('product_images', [1, 3]);
        $productRows = [];
        $imageRows = [];
        $termAssignments = [];
        $productId = 1;

        for ($i = 0; $i < $productCount; $i++, $productId++) {
            $category = $categories->random();
            $type = $types->random();

            $isAccessory = $this->isAccessoryProduct($category->slug, $type->slug);
            $isWine = $this->isWineType($type->slug);

            $brand = $this->pickRandom($taxonomy['brand']);
            $originCountry = $this->pickRandom($taxonomy['origin_countries']);
            $originRegions = $this->pickTermsWithinRange(
                $context->range('product_terms.origin_region', [0, 2]),
                $taxonomy['origin_regions']->get($originCountry->id) ?? collect()
            );

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

            $productRows[] = [
                'id' => $productId,
                'name' => $name,
                'slug' => $slug,
                'product_category_id' => $category->id,
                'type_id' => $type->id,
                'description' => $faker->paragraphs(random_int(2, 4), true),
                'price' => $price,
                'original_price' => $originalPrice,
                'alcohol_percent' => $isAccessory ? null : $context->randomAlcohol(),
                'volume_ml' => $isAccessory ? null : $context->randomVolume(),
                'badges' => $badges ? json_encode($badges) : null,
                'active' => random_int(1, 100) > 7,
                'meta_title' => "{$name} | Wincellar",
                'meta_description' => $faker->sentence(20),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Term assignments
            $termAssignments[] = $this->makeAssignment($productId, $brand->id, true, 0, $now);

            $termAssignments[] = $this->makeAssignment($productId, $originCountry->id, true, 0, $now, [
                'context' => 'origin_country',
            ]);

            foreach ($originRegions as $index => $region) {
                $termAssignments[] = $this->makeAssignment($productId, $region->id, $index === 0 && !$isAccessory, $index + 1, $now, [
                    'context' => 'origin_region',
                ]);
            }

            if ($isWine) {
                $grapeTerms = $this->pickTermsWithinRange(
                    $context->range('product_terms.grape', [2, 4]),
                    $taxonomy['grape']
                );

                foreach ($grapeTerms as $index => $term) {
                    $termAssignments[] = $this->makeAssignment($productId, $term->id, $index === 0, $index, $now, [
                        'context' => 'grape_composition',
                        'percentage' => $faker->numberBetween(10, 70),
                    ]);
                }
            }

            $flavorTerms = $this->pickTermsWithinRange(
                $context->range('product_terms.flavor_profile', [1, 3]),
                $taxonomy['flavor_profile']
            );

            foreach ($flavorTerms as $index => $term) {
                $termAssignments[] = $this->makeAssignment($productId, $term->id, false, $index, $now, [
                    'context' => 'flavor_profile',
                ]);
            }

            if ($isAccessory) {
                $accessoryTerms = $this->pickTermsWithinRange(
                    $context->range('product_terms.accessory_type', [1, 2]),
                    $taxonomy['accessory_type']
                );

                foreach ($accessoryTerms as $index => $term) {
                    $termAssignments[] = $this->makeAssignment($productId, $term->id, $index === 0, $index, $now, [
                        'context' => 'accessory_type',
                    ]);
                }

                $materialTerms = $this->pickTermsWithinRange(
                    $context->range('product_terms.material', [1, 2]),
                    $taxonomy['material']
                );

                foreach ($materialTerms as $index => $term) {
                    $termAssignments[] = $this->makeAssignment($productId, $term->id, $index === 0, $index, $now, [
                        'context' => 'material',
                    ]);
                }
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
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($productId % self::CHUNK_SIZE === 0) {
                $this->flush($productRows, $imageRows, $termAssignments);
            }
        }

        $this->flush($productRows, $imageRows, $termAssignments);

        $faker->unique(true);
    }

    private function flush(array &$products, array &$images, array &$assignments): void
    {
        if (!empty($products)) {
            DB::table('products')->insert($products);
            $products = [];
        }

        if (!empty($images)) {
            DB::table('images')->insert($images);
            $images = [];
        }

        if (!empty($assignments)) {
            DB::table('product_term_assignments')->insert($assignments);
            $assignments = [];
        }
    }

    /**
     * @return array{
     *     brand: Collection<int, object>,
     *     origin_countries: Collection<int, object>,
     *     origin_regions: Collection<int, Collection<int, object>>,
     *     grape: Collection<int, object>,
     *     accessory_type: Collection<int, object>,
     *     material: Collection<int, object>,
     *     flavor_profile: Collection<int, object>
     * }
     */
    private function loadTaxonomy(): array
    {
        $terms = DB::table('catalog_terms')
            ->join('catalog_attribute_groups', 'catalog_terms.group_id', '=', 'catalog_attribute_groups.id')
            ->select(
                'catalog_terms.id',
                'catalog_terms.name',
                'catalog_terms.slug',
                'catalog_terms.parent_id',
                'catalog_terms.metadata',
                'catalog_attribute_groups.code as group_code'
            )
            ->where('catalog_terms.is_active', true)
            ->orderBy('catalog_terms.position')
            ->get()
            ->map(function ($term) {
                $term->metadata = $term->metadata ? json_decode($term->metadata, true) : [];
                return $term;
            });

        $origin = $terms->where('group_code', 'origin');

        return [
            'brand' => $terms->where('group_code', 'brand')->values(),
            'origin_countries' => $origin->whereNull('parent_id')->values(),
            'origin_regions' => $origin->whereNotNull('parent_id')->groupBy('parent_id'),
            'grape' => $terms->where('group_code', 'grape')->values(),
            'accessory_type' => $terms->where('group_code', 'accessory_type')->values(),
            'material' => $terms->where('group_code', 'material')->values(),
            'flavor_profile' => $terms->where('group_code', 'flavor_profile')->values(),
        ];
    }

    private function generateProductName(SeederContext $context, string $brandName, string $typeName): string
    {
        $faker = $context->faker();
        $vintage = random_int(1995, 2023);
        $descriptor = $faker->randomElement(['Reserve', 'Signature', 'Estate', 'Grand Cru', 'Limited Edition', 'Classic']);

        return trim("{$brandName} {$descriptor} {$typeName} {$vintage}");
    }

    private function isAccessoryProduct(string $categorySlug, string $typeSlug): bool
    {
        return Str::contains($categorySlug, ['phu-kien', 'ly', 'dung-cu'])
            || Str::contains($typeSlug, ['ly', 'dung-cu', 'decanter']);
    }

    private function isWineType(string $typeSlug): bool
    {
        return Str::contains($typeSlug, ['vang', 'wine', 'sparkling', 'champagne']);
    }

    /**
     * @param \Illuminate\Support\Collection<int, object> $pool
     */
    private function pickRandom(Collection $pool)
    {
        if ($pool->isEmpty()) {
            throw new \RuntimeException('Taxonomy pool is empty.');
        }

        return $pool->random();
    }

    /**
     * @param array<int, int> $range
     * @param \Illuminate\Support\Collection<int, object> $pool
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function pickTermsWithinRange(array $range, Collection $pool): Collection
    {
        $min = max(0, $range[0] ?? 0);
        $max = max($min, $range[1] ?? $min);

        if ($pool->isEmpty() || $max === 0) {
            return collect();
        }

        $take = min($pool->count(), random_int($min, $max));
        if ($take === 0) {
            return collect();
        }

        $selected = $pool->random($take);

        return $selected instanceof Collection ? $selected->values() : collect([$selected]);
    }

    private function makeAssignment(
        int $productId,
        int $termId,
        bool $isPrimary,
        int $position,
        \DateTimeInterface $timestamp,
        array $extra = []
    ): array {
        return [
            'product_id' => $productId,
            'term_id' => $termId,
            'is_primary' => $isPrimary,
            'position' => $position,
            'extra' => empty($extra) ? null : json_encode($extra),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}

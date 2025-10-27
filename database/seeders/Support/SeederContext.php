<?php

namespace Database\Seeders\Support;

use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Giữ cấu hình seed theo profile (sample/large) và cung cấp helper slug, random dữ liệu.
 */
class SeederContext
{
    private const PROFILES = [
        'sample' => [
            'label' => 'sample',
            'counts' => [
                'brands' => 12,
                'product_categories' => 6,
                'product_types' => 8,
                'countries' => 8,
                'regions_per_country' => 5,
                'grapes' => 20,
                'products' => 120,
                'articles' => 18,
            ],
            'ranges' => [
                'product_images' => [2, 4],
                'product_grapes' => [2, 4],
                'product_regions' => [1, 2],
            ],
            'flags' => [
                'seed_home_components' => true,
                'seed_menus' => true,
            ],
        ],
        'large' => [
            'label' => 'large',
            'counts' => [
                'brands' => 40,
                'product_categories' => 10,
                'product_types' => 16,
                'countries' => 12,
                'regions_per_country' => 12,
                'grapes' => 40,
                'products' => 100000,
                'articles' => 120,
            ],
            'ranges' => [
                'product_images' => [1, 2],
                'product_grapes' => [2, 5],
                'product_regions' => [1, 3],
            ],
            'flags' => [
                'seed_home_components' => false,
                'seed_menus' => true,
            ],
        ],
    ];

    private static ?self $instance = null;

    private array $profile;

    private Generator $faker;

    private array $slugCounters = [];

    private int $imageSequence = 1;

    private function __construct()
    {
        $dataset = env('SEED_DATASET', 'sample');
        $this->profile = self::PROFILES[$dataset] ?? self::PROFILES['sample'];
        $this->profile['label'] = $this->profile['label'] ?? $dataset;
        $this->faker = FakerFactory::create('vi_VN');
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public function dataset(): string
    {
        return (string) ($this->profile['label'] ?? 'sample');
    }

    public function faker(): Generator
    {
        return $this->faker;
    }

    public function count(string $key, int $default = 0): int
    {
        return (int) data_get($this->profile, "counts.$key", $default);
    }

    public function range(string $key, array $default = [1, 1]): array
    {
        return data_get($this->profile, "ranges.$key", $default);
    }

    public function flag(string $key, bool $default = false): bool
    {
        return (bool) data_get($this->profile, "flags.$key", $default);
    }

    public function now(): Carbon
    {
        return Carbon::now();
    }

    public function uniqueSlug(string $scope, string $text): string
    {
        $base = Str::slug($text);
        if ($base === '') {
            $base = 'item';
        }

        $counters = &$this->slugCounters[$scope];
        if (!isset($counters[$base])) {
            $counters[$base] = 0;
            return $base;
        }

        $counters[$base]++;

        return "{$base}-{$counters[$base]}";
    }

    public function nextImageId(): int
    {
        return $this->imageSequence++;
    }

    public function modelClass(string $alias): string
    {
        return Arr::get([
            'product' => 'App\\Models\\Product',
            'article' => 'App\\Models\\Article',
            'brand' => 'App\\Models\\Brand',
            'home_component' => 'App\\Models\\HomeComponent',
            'menu' => 'App\\Models\\Menu',
            'social_link' => 'App\\Models\\SocialLink',
            'settings' => 'App\\Models\\Setting',
        ], $alias, $alias);
    }

    public function randomBadges(): ?array
    {
        $badges = collect(['SALE', 'HOT', 'NEW', 'LIMITED'])
            ->shuffle()
            ->take(random_int(0, 2))
            ->values()
            ->all();

        return empty($badges) ? null : $badges;
    }

    public function randomAlcohol(): ?float
    {
        return random_int(110, 160) / 10;
    }

    public function randomVolume(): ?int
    {
        return collect([375, 500, 700, 750, 1000, 1500])->random();
    }

    public function randomPrice(): int
    {
        return random_int(180_000, 6_500_000);
    }
}


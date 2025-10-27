<?php

namespace Database\Seeders;

use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use Carbon\Carbon;
use Database\Seeders\Support\SeederContext;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LookupTableSeeder extends Seeder
{
    public function run(): void
    {
        $context = SeederContext::get();
        $faker = $context->faker();
        $now = $context->now();

        Schema::disableForeignKeyConstraints();
        DB::table('product_term_assignments')->truncate();
        DB::table('products')->truncate();
        DB::table('catalog_terms')->truncate();
        DB::table('catalog_attribute_groups')->truncate();
        DB::table('product_types')->truncate();
        DB::table('product_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->seedCategories($context, $now);
        $this->seedTypes($context, $now);

        $groups = $this->seedAttributeGroups($context, $now);
        $this->seedBrandTerms($context, $faker, $now, $groups['brand']);
        $originMap = $this->seedOriginTerms($context, $faker, $now, $groups['origin']);
        $this->seedGrapeTerms($context, $now, $groups['grape']);
        $this->seedAccessoryTerms($context, $now, $groups['accessory_type']);
        $this->seedMaterialTerms($context, $now, $groups['material']);
        $this->seedFlavorTerms($context, $now, $groups['flavor_profile']);

        // Lưu mapping origin vào metadata chung (để ProductSeeder có thể sử dụng nếu cần).
        $originConfig = array_merge(
            $groups['origin']->display_config ?? [],
            [
                'dataset' => 'hierarchy',
                'country_term_ids' => array_values($originMap['countries']),
            ]
        );

        DB::table('catalog_attribute_groups')
            ->where('id', $groups['origin']->id)
            ->update(['display_config' => json_encode($originConfig)]);
    }

    private function seedCategories(SeederContext $context, Carbon $now): void
    {
        $categories = [
            'Rượu vang',
            'Rượu mạnh',
            'Bia craft',
            'Phô mai & charcuterie',
            'Combo quà tặng',
            'Dụng cụ & phụ kiện bar',
            'Ly & decanter',
            'Đặc sản vùng miền',
        ];

        $target = $context->count('product_categories', count($categories));
        $faker = $context->faker();

        while (count($categories) < $target) {
            $categories[] = ucfirst($faker->unique()->words(3, true));
        }

        $rows = [];
        foreach ($categories as $index => $name) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'slug' => $context->uniqueSlug('product_categories', $name),
                'description' => $index < 5 ? "Danh mục {$name} phục vụ nhu cầu trải nghiệm & quà tặng cao cấp." : null,
                'order' => $index,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_categories')->insert($rows);
    }

    private function seedTypes(SeederContext $context, Carbon $now): void
    {
        $types = [
            'Vang đỏ',
            'Vang trắng',
            'Vang hồng',
            'Sparkling',
            'Dessert wine',
            'Fortified wine',
            'Single Malt',
            'Blended Scotch',
            'Bourbon',
            'Cognac',
            'Rum',
            'Gin',
            'Craft Lager',
            'IPA',
            'Stout',
            'Ly pha lê',
            'Dụng cụ bar chuyên nghiệp',
        ];

        $target = $context->count('product_types', count($types));
        $faker = $context->faker();

        while (count($types) < $target) {
            $types[] = ucfirst($faker->unique()->word());
        }

        $rows = [];
        foreach ($types as $index => $name) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'slug' => $context->uniqueSlug('product_types', $name),
                'description' => $index < 10 ? "Phân nhóm {$name} phục vụ filter nâng cao." : null,
                'order' => $index,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_types')->insert($rows);
    }

    /**
     * @return array{brand: CatalogAttributeGroup, origin: CatalogAttributeGroup, grape: CatalogAttributeGroup, accessory_type: CatalogAttributeGroup, material: CatalogAttributeGroup, flavor_profile: CatalogAttributeGroup}
     */
    private function seedAttributeGroups(SeederContext $context, Carbon $now): array
    {
        $groups = [
            [
                'code' => 'brand',
                'name' => 'Thương hiệu',
                'filter_type' => 'single',
                'is_primary' => true,
                'position' => 0,
                'display_config' => ['icon' => 'lucide:factory'],
            ],
            [
                'code' => 'origin',
                'name' => 'Xuất xứ',
                'filter_type' => 'hierarchy',
                'is_primary' => true,
                'position' => 1,
                'display_config' => ['icon' => 'lucide:globe-2', 'show_flag' => true],
            ],
            [
                'code' => 'grape',
                'name' => 'Giống nho',
                'filter_type' => 'multi',
                'is_primary' => false,
                'position' => 2,
                'display_config' => ['icon' => 'lucide:leaf'],
            ],
            [
                'code' => 'accessory_type',
                'name' => 'Loại phụ kiện',
                'filter_type' => 'multi',
                'is_primary' => false,
                'position' => 3,
                'display_config' => ['icon' => 'lucide:box'],
            ],
            [
                'code' => 'material',
                'name' => 'Chất liệu chính',
                'filter_type' => 'multi',
                'is_primary' => false,
                'position' => 4,
                'display_config' => ['icon' => 'lucide:layers'],
            ],
            [
                'code' => 'flavor_profile',
                'name' => 'Hương vị',
                'filter_type' => 'tag',
                'is_primary' => false,
                'position' => 5,
                'display_config' => ['icon' => 'lucide:sparkles'],
            ],
        ];

        $rows = [];
        foreach ($groups as $index => $group) {
            $rows[] = [
                'id' => $index + 1,
                'code' => $group['code'],
                'name' => $group['name'],
                'filter_type' => $group['filter_type'],
                'is_filterable' => true,
                'is_primary' => $group['is_primary'],
                'position' => $group['position'],
                'display_config' => json_encode($group['display_config']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_attribute_groups')->insert($rows);

        /** @var Collection<string, CatalogAttributeGroup> $groupCollection */
        $groupCollection = CatalogAttributeGroup::query()
            ->whereIn('code', collect($groups)->pluck('code'))
            ->get()
            ->keyBy('code');

        return $groupCollection->all();
    }

    /**
     * @return array{countries: array<string,int>, regions: array<int, array<int>>}
     */
    private function seedOriginTerms(SeederContext $context, Generator $faker, Carbon $now, CatalogAttributeGroup $group): array
    {
        $dataset = $this->originDataset();
        $countries = [];
        $regions = [];
        $position = 0;

        foreach ($dataset as $data) {
            $slug = $context->uniqueSlug('catalog_terms.origin', $data['name']);
            $countryId = DB::table('catalog_terms')->insertGetId([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'icon_type' => 'emoji',
                'icon_value' => $data['flag'] ?? '🌍',
                'metadata' => json_encode([
                    'type' => 'country',
                    'code' => $data['code'],
                    'continent' => $data['continent'] ?? null,
                ]),
                'is_active' => true,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $countries[$data['code']] = $countryId;

            $regionPosition = 0;
            foreach ($data['regions'] as $regionName) {
                $regionSlug = $context->uniqueSlug('catalog_terms.origin', "{$data['code']}-{$regionName}");
                $regionId = DB::table('catalog_terms')->insertGetId([
                    'group_id' => $group->id,
                    'parent_id' => $countryId,
                    'name' => $regionName,
                    'slug' => $regionSlug,
                    'description' => $faker->sentence(12),
                    'icon_type' => 'lucide',
                    'icon_value' => 'map-pinned',
                    'metadata' => json_encode([
                        'type' => 'region',
                        'country_code' => $data['code'],
                    ]),
                    'is_active' => true,
                    'position' => $regionPosition++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $regions[$countryId][] = $regionId;
            }
        }

        return [
            'countries' => $countries,
            'regions' => $regions,
        ];
    }

    private function seedBrandTerms(SeederContext $context, Generator $faker, Carbon $now, CatalogAttributeGroup $group): void
    {
        $brands = $this->brandDataset();
        $target = $context->count('terms.brand', count($brands));

        while (count($brands) < $target) {
            $brands[] = $faker->unique()->company();
        }

        $position = 0;
        foreach ($brands as $name) {
            $slug = $context->uniqueSlug('catalog_terms.brand', $name);

            DB::table('catalog_terms')->insert([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $name,
                'slug' => $slug,
                'description' => $faker->sentence(16),
                'icon_type' => 'lucide',
                'icon_value' => 'factory',
                'metadata' => json_encode([
                    'type' => 'brand',
                    'founded_year' => random_int(1850, 2015),
                ]),
                'is_active' => true,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedGrapeTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $grapes = $this->grapeDataset();
        $target = $context->count('terms.grape', count($grapes));
        $faker = $context->faker();

        while (count($grapes) < $target) {
            $grapes[] = ucfirst($faker->unique()->word());
        }

        foreach ($grapes as $index => $name) {
            DB::table('catalog_terms')->insert([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms.grape', $name),
                'description' => $index < 20 ? "{$name} nổi bật với cấu trúc cân bằng và tiềm năng ủ lâu." : null,
                'icon_type' => 'lucide',
                'icon_value' => 'grape',
                'metadata' => json_encode([
                    'type' => 'grape',
                    'skin_color' => $index % 2 === 0 ? 'black' : 'white',
                ]),
                'is_active' => true,
                'position' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedAccessoryTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $types = [
            ['name' => 'Ly vang đỏ', 'icon' => 'lucide:wine', 'metadata' => ['usage' => 'wine']],
            ['name' => 'Ly vang trắng', 'icon' => 'lucide:glass-water', 'metadata' => ['usage' => 'wine']],
            ['name' => 'Ly whisky', 'icon' => 'lucide:glass', 'metadata' => ['usage' => 'spirit']],
            ['name' => 'Ly cocktail', 'icon' => 'lucide:martini', 'metadata' => ['usage' => 'cocktail']],
            ['name' => 'Bình decanter', 'icon' => 'lucide:bottle', 'metadata' => ['usage' => 'wine']],
            ['name' => 'Shaker', 'icon' => 'lucide:wand', 'metadata' => ['usage' => 'bar_tool']],
            ['name' => 'Dụng cụ đo', 'icon' => 'lucide:ruler', 'metadata' => ['usage' => 'bar_tool']],
            ['name' => 'Khui chuyên nghiệp', 'icon' => 'lucide:sparkle', 'metadata' => ['usage' => 'bar_tool']],
        ];

        $target = $context->count('terms.accessory_type', count($types));
        $faker = $context->faker();

        while (count($types) < $target) {
            $types[] = [
                'name' => ucfirst($faker->unique()->words(2, true)),
                'icon' => 'lucide:package',
                'metadata' => ['usage' => 'misc'],
            ];
        }

        foreach ($types as $index => $type) {
            DB::table('catalog_terms')->insert([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $type['name'],
                'slug' => $context->uniqueSlug('catalog_terms.accessory_type', $type['name']),
                'description' => $faker->sentence(14),
                'icon_type' => 'lucide',
                'icon_value' => $type['icon'],
                'metadata' => json_encode($type['metadata']),
                'is_active' => true,
                'position' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedMaterialTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $materials = [
            ['name' => 'Pha lê', 'icon' => 'lucide:sparkles', 'metadata' => ['safety' => 'handwash']],
            ['name' => 'Thủy tinh cao cấp', 'icon' => 'lucide:glass-water', 'metadata' => ['safety' => 'dishwasher_safe']],
            ['name' => 'Thép không gỉ', 'icon' => 'lucide:hammer', 'metadata' => ['safety' => 'dishwasher_safe']],
            ['name' => 'Gỗ sồi', 'icon' => 'lucide:trees', 'metadata' => ['safety' => 'handwash']],
            ['name' => 'Da thuộc', 'icon' => 'lucide:wallet', 'metadata' => ['safety' => 'dry_clean']],
            ['name' => 'Silicone', 'icon' => 'lucide:circle-dashed', 'metadata' => ['safety' => 'dishwasher_safe']],
        ];

        $target = $context->count('terms.material', count($materials));
        $faker = $context->faker();

        while (count($materials) < $target) {
            $materials[] = [
                'name' => ucfirst($faker->unique()->word()),
                'icon' => 'lucide:layers',
                'metadata' => ['safety' => $faker->randomElement(['dishwasher_safe', 'handwash'])],
            ];
        }

        foreach ($materials as $index => $material) {
            DB::table('catalog_terms')->insert([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $material['name'],
                'slug' => $context->uniqueSlug('catalog_terms.material', $material['name']),
                'description' => $index < 4 ? "{$material['name']} tạo nên texture sang trọng cho sản phẩm." : null,
                'icon_type' => 'lucide',
                'icon_value' => $material['icon'],
                'metadata' => json_encode($material['metadata']),
                'is_active' => true,
                'position' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedFlavorTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $flavors = [
            ['name' => 'Trái cây chín', 'color' => '#b71540'],
            ['name' => 'Hoa trắng', 'color' => '#ffd460'],
            ['name' => 'Gia vị ấm', 'color' => '#82589f'],
            ['name' => 'Gỗ sồi', 'color' => '#a0522d'],
            ['name' => 'Khoáng chất', 'color' => '#487eb0'],
            ['name' => 'Thảo mộc tươi', 'color' => '#44bd32'],
        ];

        $target = $context->count('terms.flavor_profile', count($flavors));
        $faker = $context->faker();

        while (count($flavors) < $target) {
            $flavors[] = [
                'name' => ucfirst($faker->unique()->words(2, true)),
                'color' => $faker->hexColor(),
            ];
        }

        foreach ($flavors as $index => $flavor) {
            DB::table('catalog_terms')->insert([
                'group_id' => $group->id,
                'parent_id' => null,
                'name' => $flavor['name'],
                'slug' => $context->uniqueSlug('catalog_terms.flavor_profile', $flavor['name']),
                'description' => $faker->sentence(10),
                'icon_type' => 'emoji',
                'icon_value' => '✨',
                'metadata' => json_encode(['color' => $flavor['color']]),
                'is_active' => true,
                'position' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * @return array<int, array{name: string, code: string, flag?: string, continent?: string, description?: string, regions: array<int, string>}>
     */
    private function originDataset(): array
    {
        return [
            [
                'name' => 'Pháp',
                'code' => 'FR',
                'flag' => '🇫🇷',
                'continent' => 'Europe',
                'description' => 'Cái nôi của terroir Bordeaux, Burgundy và Champagne.',
                'regions' => ['Bordeaux', 'Burgundy', 'Champagne', 'Rhône Valley', 'Loire Valley', 'Provence'],
            ],
            [
                'name' => 'Ý',
                'code' => 'IT',
                'flag' => '🇮🇹',
                'continent' => 'Europe',
                'description' => 'Đa dạng phong cách từ Piemonte đến Sicily.',
                'regions' => ['Toscana', 'Piemonte', 'Veneto', 'Sicilia', 'Puglia', 'Friuli'],
            ],
            [
                'name' => 'Tây Ban Nha',
                'code' => 'ES',
                'flag' => '🇪🇸',
                'continent' => 'Europe',
                'regions' => ['Rioja', 'Ribera del Duero', 'Priorat', 'Rías Baixas', 'Toro'],
            ],
            [
                'name' => 'Mỹ',
                'code' => 'US',
                'flag' => '🇺🇸',
                'continent' => 'North America',
                'regions' => ['Napa Valley', 'Sonoma', 'Willamette', 'Columbia Valley', 'Finger Lakes'],
            ],
            [
                'name' => 'Úc',
                'code' => 'AU',
                'flag' => '🇦🇺',
                'continent' => 'Oceania',
                'regions' => ['Barossa Valley', 'Margaret River', 'Hunter Valley', 'Yarra Valley', 'McLaren Vale'],
            ],
            [
                'name' => 'Chile',
                'code' => 'CL',
                'flag' => '🇨🇱',
                'continent' => 'South America',
                'regions' => ['Maipo Valley', 'Colchagua', 'Casablanca', 'Bio-Bio'],
            ],
            [
                'name' => 'Việt Nam',
                'code' => 'VN',
                'flag' => '🇻🇳',
                'continent' => 'Asia',
                'regions' => ['Đà Lạt', 'Thủ Đức', 'Long Biên', 'Trung Sơn'],
            ],
            [
                'name' => 'Đức',
                'code' => 'DE',
                'flag' => '🇩🇪',
                'continent' => 'Europe',
                'regions' => ['Mosel', 'Rheingau', 'Pfalz', 'Nahe'],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function brandDataset(): array
    {
        return [
            'Château Margaux',
            'Château Lafite Rothschild',
            'Penfolds',
            'Torres',
            'Francis Ford Coppola Winery',
            'Domaine Ott',
            'Cloudy Bay',
            'Hennessy',
            'Glenfiddich',
            'Macallan',
            'Hibiki',
            'Jack Daniel\'s',
            'Johnnie Walker',
            'Highland Park',
            'Moët & Chandon',
            'Veuve Clicquot',
            'Dom Pérignon',
            'Rémy Martin',
            'Martell',
            'Campari',
            'Tanqueray',
            'Bombay Sapphire',
            'Belvedere',
            'Grey Goose',
            'Baileys',
            'Guinness',
            'BrewDog',
            'Sierra Nevada',
            'Lagunitas',
            'Stone Brewing',
            'Pasteur Street Brewing',
            'Heart of Darkness Brewery',
            'The Glenlivet',
            'Yamazaki',
            'Kavalan',
            'Torres Brandy',
            'Flor de Caña',
            'Ron Zacapa',
            'Jose Cuervo',
            'Patrón',
            'Riedel',
            'Spiegelau',
            'Zalto',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function grapeDataset(): array
    {
        return [
            'Cabernet Sauvignon',
            'Merlot',
            'Pinot Noir',
            'Syrah',
            'Grenache',
            'Tempranillo',
            'Sangiovese',
            'Malbec',
            'Zinfandel',
            'Nebbiolo',
            'Cabernet Franc',
            'Petit Verdot',
            'Carmenère',
            'Barbera',
            'Touriga Nacional',
            'Chardonnay',
            'Sauvignon Blanc',
            'Riesling',
            'Viognier',
            'Gewürztraminer',
            'Semillon',
            'Pinot Grigio',
            'Albariño',
            'Chenin Blanc',
            'Moscato',
            'Muscat Ottonel',
            'Furmint',
            'Grüner Veltliner',
            'Marsanne',
            'Roussanne',
            'Verdejo',
            'Dolcetto',
            'Gamay',
            'Lambrusco',
            'Tannat',
            'Aglianico',
            'Lagrein',
            'Viura',
            'Monastrell',
        ];
    }
}

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
        DB::table('catalog_attribute_group_product_type')->truncate();
        Schema::enableForeignKeyConstraints();

        $typesBySlug = $this->seedTypes($context, $now);
        $this->seedCategories($context, $now, $typesBySlug);

        $groups = $this->seedAttributeGroups($context, $now);
        $this->attachAttributeGroupsToTypes($typesBySlug, $groups, $now);
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

    /**
     * @param array<string, object> $typesBySlug
     */
    private function seedCategories(SeederContext $context, Carbon $now, array $typesBySlug): void
    {
        $categories = [
            ['name' => 'Rượu vang đỏ', 'type_slug' => 'vang_sampanh'],
            ['name' => 'Rượu vang trắng', 'type_slug' => 'vang_sampanh'],
            ['name' => 'Rượu vang hồng', 'type_slug' => 'vang_sampanh'],
            ['name' => 'Sâm panh & sparkling', 'type_slug' => 'vang_sampanh'],
            ['name' => 'Rượu mạnh', 'type_slug' => 'ruou_manh'],
            ['name' => 'Sake / Soju / Umeshu', 'type_slug' => 'sake_soju_umeshu'],
            ['name' => 'Combo quà tặng', 'type_slug' => 'phu_kien_khac'],
            ['name' => 'Dụng cụ & phụ kiện bar', 'type_slug' => 'phu_kien_khac'],
            ['name' => 'Ly & decanter', 'type_slug' => 'phu_kien_khac'],
            ['name' => 'Đặc sản vùng miền', 'type_slug' => 'phu_kien_khac'],
        ];

        $target = $context->count('product_categories', count($categories));
        $faker = $context->faker();

        while (count($categories) < $target) {
            $categories[] = [
                'name' => ucfirst($faker->unique()->words(3, true)),
                'type_slug' => 'phu_kien_khac',
            ];
        }

        $rows = [];
        foreach ($categories as $index => $category) {
            $typeId = $typesBySlug[$category['type_slug']]->id ?? null;

            $rows[] = [
                'id' => $index + 1,
                'name' => $category['name'],
                'slug' => $context->uniqueSlug('product_categories', $category['name']),
                'description' => $index < 5 ? "Danh mục {$category['name']} phục vụ nhu cầu trải nghiệm & quà tặng cao cấp." : null,
                'order' => $index,
                'active' => true,
                'type_id' => $typeId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_categories')->insert($rows);
    }

    /**
     * @return array<string, object>
     */
    private function seedTypes(SeederContext $context, Carbon $now): array
    {
        $types = [
            ['slug' => 'vang_sampanh', 'name' => 'Rượu vang & Sâm panh', 'description' => 'Rượu vang, sparkling, champagne', 'order' => 0],
            ['slug' => 'ruou_manh', 'name' => 'Rượu mạnh', 'description' => 'Whisky, Cognac, Rum, Gin, Tequila...', 'order' => 1],
            ['slug' => 'sake_soju_umeshu', 'name' => 'Sake / Soju / Umeshu', 'description' => 'Đồ uống gạo & trái cây chưng cất/ủ', 'order' => 2],
            ['slug' => 'phu_kien_khac', 'name' => 'Phụ kiện & Khác', 'description' => 'Ly, decanter, dụng cụ bar, quà tặng', 'order' => 3],
        ];

        $rows = [];
        foreach ($types as $index => $type) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => $type['description'],
                'order' => $type['order'],
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_types')->insert($rows);

        return collect($rows)
            ->keyBy('slug')
            ->map(fn (array $row) => (object) $row)
            ->all();
    }

    /**
     * @param array<string, object> $typesBySlug
     * @param array{brand: CatalogAttributeGroup, origin: CatalogAttributeGroup, grape: CatalogAttributeGroup, accessory_type: CatalogAttributeGroup, material: CatalogAttributeGroup, flavor_profile: CatalogAttributeGroup} $groups
     */
    private function attachAttributeGroupsToTypes(array $typesBySlug, array $groups, Carbon $now): void
    {
        $map = [
            'vang_sampanh' => ['brand', 'origin', 'grape', 'flavor_profile'],
            'ruou_manh' => ['brand', 'origin', 'flavor_profile'],
            'sake_soju_umeshu' => ['brand', 'origin', 'flavor_profile'],
            'phu_kien_khac' => ['brand', 'accessory_type', 'material'],
        ];

        $rows = [];
        foreach ($map as $typeSlug => $groupCodes) {
            $type = $typesBySlug[$typeSlug] ?? null;
            if (!$type) {
                continue;
            }

            foreach ($groupCodes as $position => $code) {
                $group = $groups[$code] ?? null;
                if (!$group) {
                    continue;
                }

                $rows[] = [
                    'group_id' => $group->id,
                    'type_id' => $type->id,
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('catalog_attribute_group_product_type')->insert($rows);
        }
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
                'filter_type' => 'chon_don',
                'is_primary' => true,
                'position' => 0,
                'display_config' => ['icon' => 'lucide:factory'],
            ],
            [
                'code' => 'origin',
                'name' => 'Xuất xứ',
                'filter_type' => 'chon_nhieu',
                'is_primary' => true,
                'position' => 1,
                'display_config' => ['icon' => 'lucide:globe-2', 'show_flag' => true],
            ],
            [
                'code' => 'grape',
                'name' => 'Giống nho',
                'filter_type' => 'chon_nhieu',
                'is_primary' => false,
                'position' => 2,
                'display_config' => ['icon' => 'lucide:leaf'],
            ],
            [
                'code' => 'accessory_type',
                'name' => 'Loại phụ kiện',
                'filter_type' => 'chon_nhieu',
                'is_primary' => false,
                'position' => 3,
                'display_config' => ['icon' => 'lucide:box'],
            ],
            [
                'code' => 'material',
                'name' => 'Chất liệu chính',
                'filter_type' => 'chon_nhieu',
                'is_primary' => false,
                'position' => 4,
                'display_config' => ['icon' => 'lucide:layers'],
            ],
            [
                'code' => 'flavor_profile',
                'name' => 'Hương vị',
                'filter_type' => 'chon_nhieu',
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
                'position' => $group['position'],
                'display_config' => $group['display_config'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_attribute_groups')->insert($rows);

        return [
            'brand' => CatalogAttributeGroup::find(1),
            'origin' => CatalogAttributeGroup::find(2),
            'grape' => CatalogAttributeGroup::find(3),
            'accessory_type' => CatalogAttributeGroup::find(4),
            'material' => CatalogAttributeGroup::find(5),
            'flavor_profile' => CatalogAttributeGroup::find(6),
        ];
    }

    private function seedBrandTerms(SeederContext $context, Generator $faker, Carbon $now, CatalogAttributeGroup $group): void
    {
        $brands = [
            'Château Margaux',
            'Château Lafite',
            'Penfolds',
            'Screaming Eagle',
            'Dom Pérignon',
            'Macallan',
            'Yamazaki',
            'Glenfiddich',
            'Hennessy',
            'Jack Daniel’s',
            'Tanqueray',
            'Hendrick’s',
            'Zalto',
            'Riedel',
            'Coravin',
        ];

        $target = $context->count('catalog_terms.brand', count($brands));
        while (count($brands) < $target) {
            $brands[] = ucfirst($faker->unique()->word());
        }

        $rows = [];
        foreach ($brands as $index => $name) {
            $rows[] = [
                'id' => $context->nextTermId(),
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_terms')->insert($rows);
    }

    /**
     * @return array{countries: array<int, int>, regions: array<int, array<int, int>>}
     */
    private function seedOriginTerms(SeederContext $context, Generator $faker, Carbon $now, CatalogAttributeGroup $group): array
    {
        $countries = [
            'France',
            'Italy',
            'Spain',
            'United States',
            'Australia',
            'Chile',
            'Argentina',
            'Japan',
            'Scotland',
            'Ireland',
            'Mexico',
        ];

        $countryRows = [];
        $countryIds = [];
        foreach ($countries as $index => $name) {
            $id = $context->nextTermId();
            $countryRows[] = [
                'id' => $id,
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $countryIds[$name] = $id;
        }

        DB::table('catalog_terms')->insert($countryRows);

        // Regions per country (subset, không cần đủ để demo)
        $regionSeed = [
            'France' => ['Bordeaux', 'Burgundy', 'Champagne', 'Loire'],
            'Italy' => ['Tuscany', 'Piedmont', 'Veneto'],
            'Spain' => ['Rioja', 'Ribera del Duero', 'Priorat'],
            'United States' => ['Napa Valley', 'Sonoma'],
            'Japan' => ['Niigata', 'Yamagata', 'Kyoto'],
            'Scotland' => ['Islay', 'Highlands', 'Speyside'],
        ];

        $regionRows = [];
        $regionIds = [];
        foreach ($regionSeed as $country => $regions) {
            $countryId = $countryIds[$country] ?? null;
            if (!$countryId) {
                continue;
            }

            foreach ($regions as $index => $region) {
                $id = $context->nextTermId();
                $regionRows[] = [
                    'id' => $id,
                    'group_id' => $group->id,
                    'name' => $region,
                    'slug' => $context->uniqueSlug('catalog_terms', "{$region}-{$country}"),
                    'parent_id' => $countryId,
                    'position' => $index,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $regionIds[$countryId][] = $id;
            }
        }

        if (!empty($regionRows)) {
            DB::table('catalog_terms')->insert($regionRows);
        }

        return [
            'countries' => array_values($countryIds),
            'regions' => $regionIds,
        ];
    }

    private function seedGrapeTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $grapes = [
            'Cabernet Sauvignon',
            'Merlot',
            'Pinot Noir',
            'Syrah',
            'Grenache',
            'Chardonnay',
            'Sauvignon Blanc',
            'Riesling',
            'Sémillon',
            'Tempranillo',
            'Sangiovese',
            'Nebbiolo',
            'Malbec',
            'Pinot Grigio',
        ];

        $rows = [];
        foreach ($grapes as $index => $name) {
            $rows[] = [
                'id' => $context->nextTermId(),
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_terms')->insert($rows);
    }

    private function seedAccessoryTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $types = [
            'Dụng cụ mở rượu',
            'Bơm hút chân không',
            'Bộ rót rượu / aerator',
            'Đá lạnh inox / whiskey stone',
            'Khay đá / khuôn đá',
            'Đèn tử ngoại bảo quản rượu',
            'Phụ kiện barista',
            'Hộp quà / giỏ quà',
        ];

        $rows = [];
        foreach ($types as $index => $name) {
            $rows[] = [
                'id' => $context->nextTermId(),
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_terms')->insert($rows);
    }

    private function seedMaterialTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $materials = [
            'Pha lê',
            'Thủy tinh',
            'Inox',
            'Gỗ sồi',
            'Da',
            'Thép carbon',
            'Nhựa Tritan',
            'Silicone',
        ];

        $rows = [];
        foreach ($materials as $index => $name) {
            $rows[] = [
                'id' => $context->nextTermId(),
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_terms')->insert($rows);
    }

    private function seedFlavorTerms(SeederContext $context, Carbon $now, CatalogAttributeGroup $group): void
    {
        $flavors = [
            'Trái cây đỏ',
            'Trái cây đen',
            'Trái cây nhiệt đới',
            'Cam chanh',
            'Hoa trắng',
            'Mật ong',
            'Gia vị',
            'Khói / than bùn',
            'Socola',
            'Caramel',
            'Vanilla',
            'Thảo mộc',
            'Hạt / hạnh nhân',
        ];

        $rows = [];
        foreach ($flavors as $index => $name) {
            $rows[] = [
                'id' => $context->nextTermId(),
                'group_id' => $group->id,
                'name' => $name,
                'slug' => $context->uniqueSlug('catalog_terms', $name),
                'position' => $index,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('catalog_terms')->insert($rows);
    }
}

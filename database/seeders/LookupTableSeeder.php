<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
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
        DB::table('product_grapes')->truncate();
        DB::table('product_regions')->truncate();
        DB::table('products')->truncate();
        DB::table('brands')->truncate();
        DB::table('grapes')->truncate();
        DB::table('regions')->truncate();
        DB::table('countries')->truncate();
        DB::table('product_types')->truncate();
        DB::table('product_categories')->truncate();
        DB::table('images')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->seedCategories($context, $now);
        $this->seedTypes($context, $now);
        $countryIds = $this->seedCountriesAndRegions($context, $faker, $now);
        $this->seedGrapes($context, $now);
        $this->seedBrands($context, $faker, $now);

        // Uống các ID đã seed để ProductSeeder có thể dùng lại.
        DB::table('countries')->whereIn('id', $countryIds)->update(['updated_at' => Carbon::now()]);
    }

    private function seedCategories(SeederContext $context, Carbon $now): void
    {
        $categories = [
            'Rượu vang',
            'Rượu mạnh',
            'Bia thủ công',
            'Giỏ quà & hampers',
            'Thịt nguội & phô mai',
            'Đồ uống không cồn',
            'Phụ kiện bar',
            'Combo tiệc',
            'Quà doanh nghiệp',
            'Đặc sản vùng miền',
        ];

        $target = $context->count('product_categories', count($categories));
        $faker = $context->faker();

        while (count($categories) < $target) {
            $categories[] = $faker->unique()->words(3, true);
        }

        $rows = [];
        foreach ($categories as $index => $name) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'slug' => $context->uniqueSlug('product_categories', $name),
                'description' => $index < 5 ? "Danh mục {$name} phục vụ nhu cầu tiệc và quà tặng." : null,
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
            'Craft Lager',
            'IPA',
            'Stout',
            'Gin',
            'Non-alcoholic spirits',
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
                'description' => $index < 6 ? "Phân nhóm {$name} cho filter nâng cao." : null,
                'order' => $index,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('product_types')->insert($rows);
    }

    /**
     * @return array<int> danh sách country id đã seed.
     */
    private function seedCountriesAndRegions(SeederContext $context, \Faker\Generator $faker, Carbon $now): array
    {
        $countries = [
            ['name' => 'France', 'code' => 'FR', 'regions' => ['Bordeaux', 'Burgundy', 'Champagne', 'Loire Valley', 'Rhône Valley', 'Alsace']],
            ['name' => 'Italy', 'code' => 'IT', 'regions' => ['Tuscany', 'Piedmont', 'Veneto', 'Sicily', 'Lombardy', 'Friuli']],
            ['name' => 'Spain', 'code' => 'ES', 'regions' => ['Rioja', 'Ribera del Duero', 'Priorat', 'Rías Baixas', 'Catalunya', 'Navarra']],
            ['name' => 'United States', 'code' => 'US', 'regions' => ['Napa Valley', 'Sonoma', 'Willamette', 'Finger Lakes', 'Paso Robles', 'Washington State']],
            ['name' => 'Australia', 'code' => 'AU', 'regions' => ['Barossa Valley', 'Margaret River', 'Hunter Valley', 'Yarra Valley', 'McLaren Vale', 'Coonawarra']],
            ['name' => 'Chile', 'code' => 'CL', 'regions' => ['Maipo Valley', 'Colchagua', 'Casablanca', 'Aconcagua', 'Itata', 'Limarí']],
            ['name' => 'Argentina', 'code' => 'AR', 'regions' => ['Mendoza', 'Patagonia', 'Salta', 'San Juan', 'La Rioja', 'Catamarca']],
            ['name' => 'Germany', 'code' => 'DE', 'regions' => ['Mosel', 'Rheingau', 'Pfalz', 'Franconia', 'Nahe', 'Baden']],
            ['name' => 'New Zealand', 'code' => 'NZ', 'regions' => ['Marlborough', 'Central Otago', 'Hawke\'s Bay', 'Martinborough', 'Nelson', 'Auckland']],
            ['name' => 'Portugal', 'code' => 'PT', 'regions' => ['Douro', 'Dão', 'Alentejo', 'Vinho Verde', 'Bairrada', 'Lisboa']],
            ['name' => 'South Africa', 'code' => 'ZA', 'regions' => ['Stellenbosch', 'Paarl', 'Swartland', 'Walker Bay', 'Franschhoek', 'Constantia']],
            ['name' => 'Japan', 'code' => 'JP', 'regions' => ['Yamanashi', 'Nagano', 'Hokkaido', 'Tohoku', 'Kyushu', 'Okayama']],
        ];

        $targetCountries = min($context->count('countries', count($countries)), count($countries));
        $countries = array_slice($countries, 0, $targetCountries);

        $regionTarget = max(1, $context->count('regions_per_country', 5));

        $countryRows = [];
        $regionRows = [];
        $countryIds = [];
        $countryId = 1;
        $regionId = 1;

        foreach ($countries as $country) {
            $countryRows[] = [
                'id' => $countryId,
                'name' => $country['name'],
                'code' => $country['code'],
                'slug' => $context->uniqueSlug('countries', $country['name']),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $regions = $country['regions'];
            while (count($regions) < $regionTarget) {
                $regions[] = $faker->unique()->city();
            }
            $regions = array_slice($regions, 0, $regionTarget);

            foreach ($regions as $order => $regionName) {
                $regionRows[] = [
                    'id' => $regionId,
                    'country_id' => $countryId,
                    'name' => $regionName,
                    'slug' => $context->uniqueSlug('regions', "{$regionName}-{$country['code']}"),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $regionId++;
            }

            $countryIds[] = $countryId;
            $countryId++;
            $faker->unique(true);
        }

        DB::table('countries')->insert($countryRows);
        DB::table('regions')->insert($regionRows);

        return $countryIds;
    }

    private function seedGrapes(SeederContext $context, Carbon $now): void
    {
        $grapes = [
            'Cabernet Sauvignon',
            'Merlot',
            'Pinot Noir',
            'Syrah',
            'Grenache',
            'Malbec',
            'Tempranillo',
            'Sangiovese',
            'Nebbiolo',
            'Barbera',
            'Cabernet Franc',
            'Petit Verdot',
            'Chardonnay',
            'Sauvignon Blanc',
            'Riesling',
            'Viognier',
            'Gewürztraminer',
            'Pinot Gris',
            'Albariño',
            'Moscato',
            'Torrontés',
            'Carmenere',
            'Zinfandel',
            'Touriga Nacional',
            'Semillon',
            'Chenin Blanc',
            'Fiano',
            'Assyrtiko',
            'Grüner Veltliner',
            'Verdejo',
            'Dolcetto',
            'Gamay',
            'Lambrusco',
            'Tannat',
            'Aglianico',
            'Lagrein',
            'Marsanne',
            'Roussanne',
            'Viura',
            'Monastrell',
        ];

        $target = $context->count('grapes', count($grapes));
        $faker = $context->faker();

        while (count($grapes) < $target) {
            $grapes[] = ucfirst($faker->unique()->word());
        }

        $rows = [];
        foreach ($grapes as $index => $name) {
            $rows[] = [
                'id' => $index + 1,
                'name' => $name,
                'slug' => $context->uniqueSlug('grapes', $name),
                'description' => $index < 15 ? "{$name} thường cho hương vị nổi bật, phù hợp pairing đa dạng." : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('grapes')->insert($rows);
    }

    private function seedBrands(SeederContext $context, \Faker\Generator $faker, Carbon $now): void
    {
        $brands = [
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
        ];

        $target = $context->count('brands', count($brands));

        while (count($brands) < $target) {
            $brands[] = $faker->unique()->company();
        }

        $brandRows = [];
        $imageRows = [];

        foreach ($brands as $index => $name) {
            $id = $index + 1;
            $slug = $context->uniqueSlug('brands', $name);
            $imageId = $context->nextImageId();

            $brandRows[] = [
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => $faker->sentence(12),
                'logo_image_id' => $imageId,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $imageRows[] = [
                'id' => $imageId,
                'file_path' => "brands/{$slug}.jpg",
                'disk' => 'public',
                'alt' => "{$name} logo",
                'width' => 512,
                'height' => 512,
                'mime' => 'image/jpeg',
                'model_type' => $context->modelClass('brand'),
                'model_id' => $id,
                'order' => 0,
                'active' => true,
                'extra_attributes' => json_encode(['source' => 'seeder']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('images')->insert($imageRows);
        DB::table('brands')->insert($brandRows);
    }
}

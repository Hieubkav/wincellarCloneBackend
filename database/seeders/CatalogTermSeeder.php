<?php

namespace Database\Seeders;

use App\Models\CatalogTerm;
use Illuminate\Database\Seeder;

class CatalogTermSeeder extends Seeder
{
    /**
     * Seed catalog terms for all attribute groups.
     */
    public function run(): void
    {
        // 1. Thương hiệu (brand) - group_id = 1
        $brands = [
            ['name' => 'Penfolds', 'group_id' => 1],
            ['name' => 'Château Margaux', 'group_id' => 1],
            ['name' => 'Robert Mondavi', 'group_id' => 1],
            ['name' => 'Antinori', 'group_id' => 1],
            ['name' => 'Torres', 'group_id' => 1],
            ['name' => 'Cloudy Bay', 'group_id' => 1],
            ['name' => 'Catena Zapata', 'group_id' => 1],
            ['name' => 'Louis Roederer', 'group_id' => 1],
            ['name' => 'Moët & Chandon', 'group_id' => 1],
            ['name' => 'Dom Pérignon', 'group_id' => 1],
        ];

        // 2. Xuất xứ (origin) - group_id = 2
        $origins = [
            ['name' => 'Bordeaux, Pháp', 'group_id' => 2],
            ['name' => 'Burgundy, Pháp', 'group_id' => 2],
            ['name' => 'Champagne, Pháp', 'group_id' => 2],
            ['name' => 'Tuscany, Ý', 'group_id' => 2],
            ['name' => 'Rioja, Tây Ban Nha', 'group_id' => 2],
            ['name' => 'Napa Valley, Mỹ', 'group_id' => 2],
            ['name' => 'Mendoza, Argentina', 'group_id' => 2],
            ['name' => 'Barossa Valley, Úc', 'group_id' => 2],
        ];

        // 3. Giống nho (grape) - group_id = 3
        $grapes = [
            ['name' => 'Cabernet Sauvignon', 'group_id' => 3],
            ['name' => 'Merlot', 'group_id' => 3],
            ['name' => 'Pinot Noir', 'group_id' => 3],
            ['name' => 'Syrah/Shiraz', 'group_id' => 3],
            ['name' => 'Chardonnay', 'group_id' => 3],
            ['name' => 'Sauvignon Blanc', 'group_id' => 3],
            ['name' => 'Riesling', 'group_id' => 3],
            ['name' => 'Tempranillo', 'group_id' => 3],
            ['name' => 'Sangiovese', 'group_id' => 3],
            ['name' => 'Malbec', 'group_id' => 3],
            ['name' => 'Grenache', 'group_id' => 3],
            ['name' => 'Nebbiolo', 'group_id' => 3],
        ];

        // 4. Loại phụ kiện (accessory_type) - group_id = 4
        $accessories = [
            ['name' => 'Ly rượu vang', 'group_id' => 4],
            ['name' => 'Decanter', 'group_id' => 4],
            ['name' => 'Bộ mở rượu', 'group_id' => 4],
            ['name' => 'Túi đựng rượu', 'group_id' => 4],
            ['name' => 'Kệ để rượu', 'group_id' => 4],
            ['name' => 'Tủ bảo quản rượu', 'group_id' => 4],
            ['name' => 'Phụ kiện bia', 'group_id' => 4],
            ['name' => 'Khay đá & Dụng cụ pha chế', 'group_id' => 4],
        ];

        // 5. Chất liệu chính (material) - group_id = 5
        $materials = [
            ['name' => 'Thủy tinh cao cấp', 'group_id' => 5],
            ['name' => 'Inox 304', 'group_id' => 5],
            ['name' => 'Gỗ sồi', 'group_id' => 5],
            ['name' => 'Da thật', 'group_id' => 5],
            ['name' => 'Nhựa PP cao cấp', 'group_id' => 5],
        ];

        // 6. Hương vị (flavor_profile) - group_id = 6
        $flavors = [
            ['name' => 'Trái cây đỏ (Cherry, Raspberry)', 'group_id' => 6],
            ['name' => 'Trái cây đen (Blackberry, Plum)', 'group_id' => 6],
            ['name' => 'Cam quýt (Citrus)', 'group_id' => 6],
            ['name' => 'Hoa trắng (White Flowers)', 'group_id' => 6],
            ['name' => 'Vani & Kem', 'group_id' => 6],
            ['name' => 'Sô-cô-la & Cà phê', 'group_id' => 6],
            ['name' => 'Gia vị (Pepper, Clove)', 'group_id' => 6],
            ['name' => 'Khoáng chất (Mineral)', 'group_id' => 6],
            ['name' => 'Mật ong & Trái cây nhiệt đới', 'group_id' => 6],
            ['name' => 'Khói & Gỗ sồi (Oak, Smoke)', 'group_id' => 6],
        ];

        // Merge tất cả và seed
        $allTerms = array_merge($brands, $origins, $grapes, $accessories, $materials, $flavors);

        foreach ($allTerms as $term) {
            CatalogTerm::create([
                'group_id' => $term['group_id'],
                'name' => $term['name'],
                'is_active' => true,
                // slug và position sẽ tự động được generate bởi Observer
            ]);
        }

        $this->command->info('✅ Seeded ' . count($allTerms) . ' catalog terms successfully!');
    }
}

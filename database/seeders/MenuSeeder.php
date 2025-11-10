<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuBlock;
use App\Models\MenuBlockItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Seed menu data based on frontend header.data.ts structure
     */
    public function run(): void
    {
        // Clear existing menu data
        MenuBlockItem::query()->delete();
        MenuBlock::query()->delete();
        Menu::query()->delete();

        // 1. Trang chủ (Standard menu)
        Menu::create([
            'title' => 'Trang chủ',
            'type' => 'standard',
            'href' => '/',
            'order' => 1,
            'active' => true,
        ]);

        // 2. Rượu vang (Mega menu)
        $ruouVang = Menu::create([
            'title' => 'Rượu vang',
            'type' => 'mega',
            'href' => '/',
            'order' => 2,
            'active' => true,
        ]);

        // Block 1: Theo loại rượu
        $theoLoai = MenuBlock::create([
            'menu_id' => $ruouVang->id,
            'title' => 'Theo loại rượu',
            'order' => 1,
            'active' => true,
        ]);

        $loaiItems = [
            ['label' => 'Rượu vang đỏ', 'href' => '/', 'badge' => 'HOT'],
            ['label' => 'Rượu vang trắng', 'href' => '/'],
            ['label' => 'Rượu vang sủi', 'href' => '/'],
            ['label' => 'Champagne (Sâm panh)', 'href' => '/'],
            ['label' => 'Rượu vang hồng', 'href' => '/'],
            ['label' => 'Rượu vang ngọt', 'href' => '/'],
            ['label' => 'Rượu vang cường hóa', 'href' => '/'],
            ['label' => 'Rượu vang không cồn', 'href' => '/'],
            ['label' => 'Rượu vang Organic', 'href' => '/'],
            ['label' => 'Tất cả rượu vang', 'href' => '/'],
        ];

        foreach ($loaiItems as $index => $item) {
            MenuBlockItem::create([
                'menu_block_id' => $theoLoai->id,
                'label' => $item['label'],
                'href' => $item['href'],
                'badge' => $item['badge'] ?? null,
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 2: Theo quốc gia
        $theoQuocGia = MenuBlock::create([
            'menu_id' => $ruouVang->id,
            'title' => 'Theo quốc gia',
            'order' => 2,
            'active' => true,
        ]);

        $quocGiaItems = [
            'Pháp', 'Ý', 'Tây Ban Nha', 'Chile', 'Mỹ', 'Úc',
            'New Zealand', 'Argentina', 'Bồ Đào Nha', 'Đức', 'Nam Phi',
        ];

        foreach ($quocGiaItems as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $theoQuocGia->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 3: Theo giống nho
        $theoGiongNho = MenuBlock::create([
            'menu_id' => $ruouVang->id,
            'title' => 'Theo giống nho',
            'order' => 3,
            'active' => true,
        ]);

        $giongNhoItems = [
            'Cabernet Sauvignon', 'Merlot', 'Syrah (Shiraz)', 'Pinot Noir',
            'Malbec', 'Montepulciano D\'Abruzzo', 'Negroamaro', 'Primitivo',
            'Chardonnay', 'Sauvignon Blanc', 'Riesling', 'Tìm giống nho',
        ];

        foreach ($giongNhoItems as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $theoGiongNho->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 4: Theo vùng nổi tiếng
        $theoVung = MenuBlock::create([
            'menu_id' => $ruouVang->id,
            'title' => 'Theo vùng nổi tiếng',
            'order' => 4,
            'active' => true,
        ]);

        $vungItems = [
            'Bordeaux', 'Bourgogne (Pháp)', 'Tuscany', 'Puglia',
            'Piedmont (Ý)', 'California (Mỹ)', 'Champagne (Pháp)',
        ];

        foreach ($vungItems as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $theoVung->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // 3. Rượu mạnh (Mega menu)
        $ruouManh = Menu::create([
            'title' => 'Rượu mạnh',
            'type' => 'mega',
            'href' => '/',
            'order' => 3,
            'active' => true,
        ]);

        // Block 1: Loại rượu
        $loaiRuouManh = MenuBlock::create([
            'menu_id' => $ruouManh->id,
            'title' => 'Loại rượu',
            'order' => 1,
            'active' => true,
        ]);

        $loaiRuouManhItems = [
            'Rượu Whisky', 'Rượu Cognac', 'Rượu Rum',
            'Rượu Gin', 'Rượu Vermouth', 'Rượu Whisky Single Malt',
        ];

        foreach ($loaiRuouManhItems as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $loaiRuouManh->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 2: Thương hiệu (Cột 1)
        $thuongHieu1 = MenuBlock::create([
            'menu_id' => $ruouManh->id,
            'title' => 'Thương hiệu (Cột 1)',
            'order' => 2,
            'active' => true,
        ]);

        $thuongHieu1Items = [
            'GlenAllachie', 'Tamdhu', 'Glengoyne', 'Kilchoman',
            'Meikle Tòir', 'Glen Moray', 'Thomas Hine & Co',
            'Cognac Lhéraud', 'Rosebank',
        ];

        foreach ($thuongHieu1Items as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $thuongHieu1->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 3: Thương hiệu (Cột 2)
        $thuongHieu2 = MenuBlock::create([
            'menu_id' => $ruouManh->id,
            'title' => 'Thương hiệu (Cột 2)',
            'order' => 3,
            'active' => true,
        ]);

        $thuongHieu2Items = [
            'Hunter Laing', 'That Boutique-Y Whisky Company', 'Kill Devil',
            'Cadenhead\'s', 'The Ileach', 'The Original Islay Rum',
            'Silver Seal', 'MacNair\'s',
        ];

        foreach ($thuongHieu2Items as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $thuongHieu2->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // Block 4: Quà tặng
        $quaTang = MenuBlock::create([
            'menu_id' => $ruouManh->id,
            'title' => 'Quà tặng',
            'order' => 4,
            'active' => true,
        ]);

        MenuBlockItem::create([
            'menu_block_id' => $quaTang->id,
            'label' => 'Quà tặng rượu mạnh',
            'href' => '/',
            'order' => 1,
            'active' => true,
        ]);

        // 4. Sản phẩm khác (Mega menu)
        $sanPhamKhac = Menu::create([
            'title' => 'Sản phẩm khác',
            'type' => 'mega',
            'href' => '/',
            'order' => 4,
            'active' => true,
        ]);

        // Block: Danh mục
        $danhMuc = MenuBlock::create([
            'menu_id' => $sanPhamKhac->id,
            'title' => 'Danh mục',
            'order' => 1,
            'active' => true,
        ]);

        $danhMucItems = ['Bia', 'Trà', 'Bánh'];

        foreach ($danhMucItems as $index => $label) {
            MenuBlockItem::create([
                'menu_block_id' => $danhMuc->id,
                'label' => $label,
                'href' => '/',
                'order' => $index + 1,
                'active' => true,
            ]);
        }

        // 5. Liên hệ (Standard menu)
        Menu::create([
            'title' => 'Liên hệ',
            'type' => 'standard',
            'href' => '/',
            'order' => 5,
            'active' => true,
        ]);

        $this->command->info('Menu data seeded successfully!');
    }
}

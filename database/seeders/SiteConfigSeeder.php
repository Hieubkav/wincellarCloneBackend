<?php

namespace Database\Seeders;

use Database\Seeders\Support\SeederContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteConfigSeeder extends Seeder
{
    public function run(): void
    {
        $context = SeederContext::get();
        $now = $context->now();

        Schema::disableForeignKeyConstraints();
        DB::table('settings')->truncate();
        DB::table('social_links')->truncate();
        DB::table('menus')->truncate();
        DB::table('menu_blocks')->truncate();
        DB::table('menu_block_items')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('images')
            ->whereIn('model_type', [
                $context->modelClass('settings'),
                $context->modelClass('menu'),
                $context->modelClass('social_link'),
            ])
            ->delete();

        $logoImageId = $context->nextImageId();
        $faviconImageId = $context->nextImageId();

        DB::table('images')->insert([
            [
                'id' => $logoImageId,
                'file_path' => 'settings/logo.png',
                'disk' => 'public',
                'alt' => 'Wincellar Logo',
                'width' => 512,
                'height' => 160,
                'mime' => 'image/png',
                'model_type' => $context->modelClass('settings'),
                'model_id' => 1,
                'order' => 0,
                'active' => true,
                'extra_attributes' => json_encode(['source' => 'seeder']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $faviconImageId,
                'file_path' => 'settings/favicon.ico',
                'disk' => 'public',
                'alt' => 'Wincellar Favicon',
                'width' => 64,
                'height' => 64,
                'mime' => 'image/x-icon',
                'model_type' => $context->modelClass('settings'),
                'model_id' => 1,
                'order' => 1,
                'active' => true,
                'extra_attributes' => json_encode(['source' => 'seeder']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('settings')->insert([
            'id' => 1,
            'logo_image_id' => $logoImageId,
            'favicon_image_id' => $faviconImageId,
            'site_name' => 'Wincellar Boutique',
            'hotline' => '+84 938 123 456',
            'address' => '12 Nguyễn Siêu, Quận 1, TP.HCM',
            'hours' => '09:00 - 21:00 (Hằng ngày)',
            'email' => 'hello@wincellar.vn',
            'meta_default_title' => 'Wincellar Boutique - Fine Wines & Spirits',
            'meta_default_description' => 'Khám phá bộ sưu tập rượu vang, bia craft, charcuterie và quà tặng cao cấp tại Wincellar.',
            'meta_default_keywords' => 'wine, spirits, craft beer, hamper, wincellar',
            'extra' => json_encode([
                'contact_person' => 'Nguyễn Văn An',
                'map_embed' => 'https://maps.google.com/?q=12+Nguyen+Sieu',
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->seedSocialLinks($context, $now);
        if ($context->flag('seed_menus', true)) {
            $this->seedMenus($context, $now);
        }
    }

    private function seedSocialLinks(SeederContext $context, $now): void
    {
        $links = [
            ['platform' => 'Facebook', 'url' => 'https://facebook.com/wincellar', 'order' => 1],
            ['platform' => 'Instagram', 'url' => 'https://instagram.com/wincellar', 'order' => 2],
            ['platform' => 'YouTube', 'url' => 'https://youtube.com/@wincellar', 'order' => 3],
            ['platform' => 'Zalo', 'url' => 'https://zalo.me/0938123456', 'order' => 4],
        ];

        $rows = [];
        foreach ($links as $index => $link) {
            $rows[] = [
                'id' => $index + 1,
                'platform' => $link['platform'],
                'url' => $link['url'],
                'icon_image_id' => null,
                'order' => $link['order'],
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('social_links')->insert($rows);
    }

    private function seedMenus(SeederContext $context, $now): void
    {
        $categories = DB::table('product_categories')
            ->select('id', 'name', 'slug')
            ->orderBy('order')
            ->limit(6)
            ->get();

        $countries = DB::table('countries')
            ->select('id', 'name', 'slug')
            ->limit(6)
            ->get();

        $menus = [
            [
                'id' => 1,
                'title' => 'Trang chủ',
                'type' => 'standard',
                'href' => '/',
                'config' => null,
                'order' => 0,
            ],
            [
                'id' => 2,
                'title' => 'Sản phẩm',
                'type' => 'mega',
                'href' => '/san-pham',
                'config' => json_encode(['layout' => 'three-columns', 'badge' => 'HOT']),
                'order' => 1,
            ],
            [
                'id' => 3,
                'title' => 'Bài viết',
                'type' => 'standard',
                'href' => '/bai-viet',
                'config' => null,
                'order' => 2,
            ],
            [
                'id' => 4,
                'title' => 'Liên hệ',
                'type' => 'standard',
                'href' => '/lien-he',
                'config' => null,
                'order' => 3,
            ],
        ];

        foreach ($menus as &$menu) {
            $menu['active'] = true;
            $menu['created_at'] = $now;
            $menu['updated_at'] = $now;
        }
        unset($menu);

        DB::table('menus')->insert($menus);

        $blocks = [];
        $items = [];
        $blockId = 1;
        $itemId = 1;

        // Block 1: danh mục nổi bật.
        $blocks[] = [
            'id' => $blockId,
            'menu_id' => 2,
            'title' => 'Danh mục nổi bật',
            'order' => 0,
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        foreach ($categories as $index => $category) {
            $items[] = [
                'id' => $itemId++,
                'menu_block_id' => $blockId,
                'label' => $category->name,
                'href' => "/san-pham/danh-muc/{$category->slug}",
                'badge' => $index === 0 ? 'HOT' : null,
                'meta' => json_encode(['category_id' => $category->id]),
                'order' => $index,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Block 2: quốc gia tiêu biểu.
        $blockId++;
        $blocks[] = [
            'id' => $blockId,
            'menu_id' => 2,
            'title' => 'Theo quốc gia',
            'order' => 1,
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        foreach ($countries as $index => $country) {
            $items[] = [
                'id' => $itemId++,
                'menu_block_id' => $blockId,
                'label' => $country->name,
                'href' => "/san-pham/quoc-gia/{$country->slug}",
                'badge' => null,
                'meta' => json_encode(['country_id' => $country->id]),
                'order' => $index,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Block 3: CTA liên hệ.
        $blockId++;
        $blocks[] = [
            'id' => $blockId,
            'menu_id' => 2,
            'title' => 'Hỗ trợ tư vấn',
            'order' => 2,
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $items[] = [
            'id' => $itemId++,
            'menu_block_id' => $blockId,
            'label' => 'Đặt lịch tasting riêng',
            'href' => 'tel:+84938123456',
            'badge' => 'NEW',
            'meta' => json_encode(['type' => 'cta_contact', 'placement' => 'header']),
            'order' => 0,
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('menu_blocks')->insert($blocks);
        DB::table('menu_block_items')->insert($items);
    }
}


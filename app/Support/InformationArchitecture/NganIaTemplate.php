<?php

namespace App\Support\InformationArchitecture;

use App\Models\CatalogTerm;
use App\Models\Menu;
use App\Models\ProductCategory;
use App\Models\ProductType;
use Illuminate\Support\Collection;

class NganIaTemplate
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function groups(): array
    {
        return [
            [
                'key' => 'core',
                'label' => 'Core',
                'items' => [
                    ['label' => 'Trang chủ', 'path' => '/', 'source' => 'core'],
                    ['label' => 'Sản phẩm', 'path' => '/san-pham', 'source' => 'core'],
                    ['label' => 'Liên hệ', 'path' => '/lien-he', 'source' => 'core'],
                ],
            ],
            [
                'key' => 'products',
                'label' => 'Sản phẩm',
                'items' => [
                    self::productNode('Rượu vang', 'ruou-vang', [
                        ['Vang đỏ', 'vang-do', 'category'],
                        ['Vang trắng', 'vang-trang', 'category'],
                        ['Vang hồng', 'vang-hong', 'category'],
                        ['Vang sủi', 'vang-sui', 'category'],
                        ['Champagne / Sâm panh', 'champagne', 'category'],
                        ['Vang ngọt', 'vang-ngot', 'category'],
                        ['Vang cường hóa', 'vang-cuong-hoa', 'category'],
                        ['Vang không cồn', 'vang-khong-con', 'category'],
                        ['Vang organic', 'vang-organic', 'term'],
                        ['Pháp', 'phap', 'term'],
                        ['Ý', 'y', 'term'],
                        ['Tây Ban Nha', 'tay-ban-nha', 'term'],
                        ['Chile', 'chile', 'term'],
                        ['Úc', 'uc', 'term'],
                        ['Mỹ', 'my', 'term'],
                        ['Argentina', 'argentina', 'term'],
                        ['New Zealand', 'new-zealand', 'term'],
                        ['Dưới 500k', 'duoi-500k', 'price_preset'],
                        ['500k - 1 triệu', '500k-1-trieu', 'price_preset'],
                        ['1 - 2 triệu', '1-2-trieu', 'price_preset'],
                        ['2 - 5 triệu', '2-5-trieu', 'price_preset'],
                        ['Trên 5 triệu', 'tren-5-trieu', 'price_preset'],
                        ['Tiệc tối', 'tiec-toi', 'term'],
                        ['Sinh nhật', 'sinh-nhat', 'term'],
                        ['Quà biếu', 'qua-bieu', 'term'],
                        ['Lễ Tết', 'le-tet', 'term'],
                    ]),
                    self::productNode('Rượu mạnh', 'ruou-manh', [
                        ['Whisky', 'whisky', 'category'],
                        ['Single Malt', 'whisky/single-malt', 'term'],
                        ['Blended', 'whisky/blended', 'term'],
                        ['Bourbon', 'whisky/bourbon', 'term'],
                        ['Japanese Whisky', 'whisky/japanese-whisky', 'term'],
                        ['Cognac', 'cognac', 'category'],
                        ['Gin', 'gin', 'category'],
                        ['Sake / Soju / Umeshu', 'sake-soju-umeshu', 'category'],
                        ['Rượu mạnh khác', 'khac', 'category'],
                        ['Dưới 1 triệu', 'duoi-1-trieu', 'price_preset'],
                        ['1 - 3 triệu', '1-3-trieu', 'price_preset'],
                        ['3 - 5 triệu', '3-5-trieu', 'price_preset'],
                        ['Trên 5 triệu', 'tren-5-trieu', 'price_preset'],
                        ['Quà biếu', 'qua-bieu', 'term'],
                        ['Sự kiện', 'su-kien', 'term'],
                        ['Doanh nghiệp', 'doanh-nghiep', 'term'],
                    ]),
                    self::productNode('Phụ kiện', 'phu-kien', [
                        ['Ly rượu vang', 'ly-ruou-vang', 'category'],
                        ['Decanter', 'decanter', 'category'],
                        ['Dụng cụ khui vang', 'dung-cu-khui-vang', 'category'],
                        ['Phụ kiện cao cấp', 'phu-kien-cao-cap', 'category'],
                    ]),
                ],
            ],
            self::staticHub('thuong-hieu', 'Thương hiệu', [
                ['Thương hiệu nổi bật', 'noi-bat'],
            ]),
            self::staticHub('bo-suu-tap', 'Bộ sưu tập', [
                ['Bán chạy', 'ban-chay'],
                ['Hàng mới về', 'hang-moi-ve'],
                ['Khuyến mãi', 'khuyen-mai'],
                ['Cao cấp', 'cao-cap'],
                ['Uống hằng ngày', 'uong-hang-ngay'],
                ['Theo mùa', 'theo-mua'],
            ]),
            self::staticHub('qua-tang', 'Quà tặng', [
                ['Quà tặng doanh nghiệp', 'doanh-nghiep'],
                ['Quà tặng rượu vang', 'ruou-vang'],
                ['Quà tặng rượu mạnh', 'ruou-manh'],
                ['Quà Tết', 'tet'],
                ['Hộp quà / túi quà', 'hop-tui-qua'],
            ]),
            self::staticHub('kien-thuc', 'Kiến thức', [
                ['Cho người mới bắt đầu', 'cho-nguoi-moi-bat-dau'],
                ['Kiến thức cơ bản', 'co-ban'],
                ['Kiến thức chuyên sâu', 'chuyen-sau'],
                ['Thưởng thức & phục vụ', 'thuong-thuc-phuc-vu'],
                ['Bảo quản rượu', 'bao-quan'],
                ['Kết hợp món ăn', 'ket-hop-mon-an'],
                ['Kiến thức vang Pháp', 'vang-phap'],
                ['Kiến thức vang Ý', 'vang-y'],
                ['Kiến thức whisky', 'whisky'],
            ]),
            self::staticHub('dich-vu', 'Dịch vụ', [
                ['Đặt hàng doanh nghiệp', 'dat-hang-doanh-nghiep'],
                ['In logo / tên doanh nghiệp', 'in-logo-ten-doanh-nghiep'],
                ['Tư vấn chọn quà', 'tu-van-chon-qua'],
                ['Tặng quà từ xa', 'tang-qua-tu-xa'],
            ]),
            self::staticHub('cua-hang', 'Hệ thống cửa hàng', [
                ['Danh sách cửa hàng', 'danh-sach'],
                ['Giờ mở cửa', 'gio-mo-cua'],
            ]),
            self::staticHub('ho-tro', 'Hỗ trợ khách hàng', [
                ['Câu hỏi thường gặp', 'faq'],
                ['Giao hàng & vận chuyển', 'giao-hang-van-chuyen'],
                ['Đổi trả & hoàn tiền', 'doi-tra-hoan-tien'],
                ['Phương thức thanh toán', 'thanh-toan'],
                ['Cam kết chính hãng', 'cam-ket-chinh-hang'],
                ['Chính sách bảo mật', 'chinh-sach-bao-mat'],
                ['Điều khoản & điều kiện', 'dieu-khoan-dieu-kien'],
            ]),
            self::staticHub('gioi-thieu', 'Giới thiệu', [
                ['Về Thiên Kim Wine', 've-thien-kim-wine'],
                ['Câu chuyện thương hiệu', 'cau-chuyen-thuong-hieu'],
                ['Vì sao chọn chúng tôi', 'vi-sao-chon-chung-toi'],
                ['Chứng nhận / giấy phép', 'chung-nhan-giay-phep'],
            ]),
            [
                'key' => 'news-events',
                'label' => 'Tin tức & sự kiện',
                'items' => [
                    ['label' => 'Tin tức', 'path' => '/tin-tuc', 'source' => 'static_hub'],
                    ['label' => 'Sự kiện', 'path' => '/su-kien', 'source' => 'static_hub'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function compliance(): array
    {
        $menuHrefs = self::menuHrefs();
        $typeSlugs = ProductType::query()->where('active', true)->pluck('slug')->all();
        $categorySlugs = ProductCategory::query()->where('active', true)->pluck('slug')->all();
        $termSlugs = CatalogTerm::query()->where('is_active', true)->pluck('slug')->all();

        $typeAliases = [
            'ruou-vang' => ['ruou-vang', 'ruou-vang-sam-panh', 'vang_sampanh'],
            'ruou-manh' => ['ruou-manh', 'ruou_manh'],
            'phu-kien' => ['phu-kien', 'phu_kien_khac'],
        ];

        $items = collect(self::flattenGroups())->map(function (array $item) use ($menuHrefs, $typeSlugs, $categorySlugs, $termSlugs, $typeAliases) {
            $source = $item['source'] ?? 'static_hub';
            $path = $item['path'];
            $menuCovered = $menuHrefs->contains($path);
            $resolvable = true;
            $message = 'Đã có trang tương ứng.';

            if ($source === 'product_type' || str_starts_with($source, 'product_') || $source === 'price_preset') {
                $typeSlug = $item['type_slug'] ?? null;
                $childSlug = $item['child_slug'] ?? null;
                $aliases = $typeSlug ? ($typeAliases[$typeSlug] ?? [$typeSlug]) : [];
                $typeResolved = collect($aliases)->contains(fn ($alias) => in_array($alias, $typeSlugs, true));
                $childResolved = true;

                if ($source === 'product_category') {
                    $childResolved = in_array($childSlug, $categorySlugs, true);
                }

                if ($source === 'product_term') {
                    $childResolved = collect(explode('/', (string) $childSlug))
                        ->every(fn ($slug) => in_array($slug, $termSlugs, true) || in_array($slug, $categorySlugs, true));
                }

                $resolvable = $typeResolved && $childResolved;
                $message = $resolvable
                    ? 'Đường dẫn khớp với dữ liệu hiện có.'
                    : 'Thiếu dữ liệu hoặc đường dẫn chưa khớp với sơ đồ đề xuất.';
            }

            $severity = $resolvable ? ($menuCovered ? 'pass' : 'warning') : 'missing';

            return [
                'label' => $item['label'],
                'path' => $path,
                'group' => $item['group'],
                'source' => $source,
                'menu_covered' => $menuCovered,
                'resolvable' => $resolvable,
                'severity' => $severity,
                'message' => $message,
                'route_payload' => $item['route_payload'] ?? null,
            ];
        })->values();

        $total = $items->count();
        $passed = $items->where('severity', 'pass')->count();
        $warnings = $items->where('severity', 'warning')->count();
        $missing = $items->where('severity', 'missing')->count();
        $score = $total > 0 ? (int) round((($passed + ($warnings * 0.5)) / $total) * 100) : 0;

        return [
            'score' => $score,
            'summary' => [
                'total' => $total,
                'passed' => $passed,
                'warnings' => $warnings,
                'missing' => $missing,
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function flattenGroups(): array
    {
        $rows = [];
        foreach (self::groups() as $group) {
            foreach ($group['items'] as $item) {
                $rows[] = [...$item, 'group' => $group['label']];
                foreach (($item['children'] ?? []) as $child) {
                    $rows[] = [...$child, 'group' => $group['label']];
                }
            }
        }

        return $rows;
    }

    /**
     * @return Collection<int, string>
     */
    private static function menuHrefs(): Collection
    {
        $menus = Menu::query()->pluck('href');
        $items = Menu::query()
            ->with('blocks.items:id,menu_block_id,href')
            ->get()
            ->flatMap(fn (Menu $menu) => $menu->blocks->flatMap(fn ($block) => $block->items->pluck('href')));

        return $menus->merge($items)
            ->filter()
            ->map(fn ($href) => rtrim((string) $href, '/') ?: '/')
            ->unique()
            ->values();
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string}>  $children
     * @return array<string, mixed>
     */
    private static function productNode(string $label, string $typeSlug, array $children): array
    {
        return [
            'label' => $label,
            'path' => "/san-pham/{$typeSlug}",
            'source' => 'product_type',
            'type_slug' => $typeSlug,
            'route_payload' => ['typeSlug' => $typeSlug],
            'children' => array_map(fn ($child) => [
                'label' => $child[0],
                'path' => "/san-pham/{$typeSlug}/{$child[1]}",
                'source' => $child[2] === 'category' ? 'product_category' : $child[2],
                'type_slug' => $typeSlug,
                'child_slug' => $child[1],
                'route_payload' => ['typeSlug' => $typeSlug, 'childSlug' => $child[1]],
            ], $children),
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $children
     * @return array<string, mixed>
     */
    private static function staticHub(string $slug, string $label, array $children): array
    {
        return [
            'key' => $slug,
            'label' => $label,
            'items' => [[
                'label' => $label,
                'path' => "/{$slug}",
                'source' => 'static_hub',
                'route_payload' => ['hub' => $slug],
                'children' => array_map(fn ($child) => [
                    'label' => $child[0],
                    'path' => "/{$slug}/{$child[1]}",
                    'source' => 'static_child',
                    'route_payload' => ['hub' => $slug, 'slug' => $child[1]],
                ], $children),
            ]],
        ];
    }
}

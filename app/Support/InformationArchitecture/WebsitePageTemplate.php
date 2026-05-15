<?php

namespace App\Support\InformationArchitecture;

use App\Models\Article;
use App\Models\Menu;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Support\Content\ArticleContentCatalog;
use Illuminate\Support\Collection;

class WebsitePageTemplate
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
                    ['label' => 'Bài viết', 'path' => '/bai-viet', 'source' => 'core'],
                    ['label' => 'Liên hệ', 'path' => '/lien-he', 'source' => 'core'],
                ],
            ],
            [
                'key' => 'products',
                'label' => 'Sản phẩm',
                'items' => self::productNodes(),
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
        $contentSlotCounts = self::contentSlotCounts();

        $items = collect(self::flattenGroups())->map(function (array $item) use ($menuHrefs, $contentSlotCounts) {
            $source = $item['source'] ?? 'static_hub';
            $path = $item['path'];
            $menuCovered = $menuHrefs->contains($path);
            $resolvable = true;
            $message = 'Đã có trang tương ứng.';

            if ($source === 'product_type' || $source === 'product_category') {
                $message = 'Đường dẫn lấy từ nhóm sản phẩm và danh mục hiện có.';
            }

            if ($source === 'static_child') {
                $slot = ltrim($path, '/');
                if (in_array($slot, ArticleContentCatalog::slotKeys(), true)) {
                    $resolvable = ($contentSlotCounts[$slot] ?? 0) > 0;
                    $message = $resolvable
                        ? 'Đã có bài viết thật cho trang này.'
                        : 'Trang đã có đường dẫn, nhưng chưa gắn bài viết thật.';
                }
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
     * @return array<string, int>
     */
    private static function contentSlotCounts(): array
    {
        $counts = array_fill_keys(ArticleContentCatalog::slotKeys(), 0);

        Article::query()
            ->where('active', true)
            ->whereNotNull('content_slots')
            ->select(['id', 'content_slots'])
            ->chunkById(100, function (Collection $articles) use (&$counts): void {
                foreach ($articles as $article) {
                    foreach (($article->content_slots ?? []) as $slot) {
                        if (array_key_exists($slot, $counts)) {
                            $counts[$slot]++;
                        }
                    }
                }
            });

        return $counts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function productNodes(): array
    {
        return ProductType::query()
            ->active()
            ->with(['categories' => fn ($query) => $query->active()->orderBy('order')->orderBy('name')])
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductType $type) => self::productNode($type))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function productNode(ProductType $type): array
    {
        return [
            'label' => $type->name,
            'path' => "/san-pham/{$type->slug}",
            'source' => 'product_type',
            'type_slug' => $type->slug,
            'route_payload' => ['typeSlug' => $type->slug],
            'children' => $type->categories->map(fn (ProductCategory $category) => [
                'label' => $category->name,
                'path' => "/san-pham/{$type->slug}/{$category->slug}",
                'source' => 'product_category',
                'type_slug' => $type->slug,
                'child_slug' => $category->slug,
                'route_payload' => ['typeSlug' => $type->slug, 'childSlug' => $category->slug],
            ])->values()->all(),
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

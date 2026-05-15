<?php

namespace App\Support\InformationArchitecture;

use App\Models\Article;
use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use App\Models\Menu;
use App\Models\ProductFilterGroup;
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
            self::staticHub('thuong-hieu', 'Thương hiệu'),
            self::staticHub('bo-suu-tap', 'Bộ sưu tập'),
            self::staticHub('qua-tang', 'Quà tặng'),
            self::staticHub('kien-thuc', 'Kiến thức'),
            self::staticHub('dich-vu', 'Dịch vụ'),
            self::staticHub('cua-hang', 'Hệ thống cửa hàng'),
            self::staticHub('ho-tro', 'Hỗ trợ khách hàng'),
            self::staticHub('gioi-thieu', 'Giới thiệu'),
            [
                'key' => 'news-events',
                'label' => 'Tin tức & sự kiện',
                'items' => [
                    ['label' => 'Tin tức', 'path' => '/tin-tuc', 'source' => 'static_hub'],
                    ['label' => 'Sự kiện', 'path' => '/su-kien', 'source' => 'static_hub'],
                ],
            ],
            [
                'key' => 'dynamic-sources',
                'label' => 'Nguồn động',
                'items' => self::dynamicSourceNodes(),
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

            if ($source === 'dynamic_source') {
                $menuCovered = true;
                $message = $item['message'] ?? 'Nguồn route được sinh từ dữ liệu thật.';
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
    private static function dynamicSourceNodes(): array
    {
        return [
            [
                'label' => 'Nhóm sản phẩm & danh mục',
                'path' => '/admin/product-types',
                'source' => 'dynamic_source',
                'message' => ProductType::query()->active()->count().' nhóm sản phẩm active. Route public: /san-pham/{nhom} và /san-pham/{nhom}/{danh-muc}.',
            ],
            [
                'label' => 'Thuộc tính & giá trị lọc',
                'path' => '/admin/attribute-groups',
                'source' => 'dynamic_source',
                'message' => CatalogAttributeGroup::query()->where('is_filterable', true)->count().' nhóm filter, '.CatalogTerm::query()->active()->count().' giá trị active. Route public: /san-pham/{slug-nhom}/{slug-gia-tri}.',
            ],
            [
                'label' => 'Bộ lọc SEO / bộ sưu tập',
                'path' => '/admin/filter-presets',
                'source' => 'dynamic_source',
                'message' => ProductFilterGroup::query()->active()->count().' nhóm preset active. Route public theo route_prefix + slug nhóm + slug preset.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function staticHub(string $slug, string $label): array
    {
        return [
            'key' => $slug,
            'label' => $label,
            'items' => [[
                'label' => $label,
                'path' => "/{$slug}",
                'source' => 'static_hub',
                'route_payload' => ['hub' => $slug],
            ]],
        ];
    }
}

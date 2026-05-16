<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CatalogAttributeGroup;
use App\Models\Menu;
use App\Models\MenuBlock;
use App\Models\MenuBlockItem;
use App\Models\MenuItem;
use App\Models\ProductFilterGroup;
use App\Models\ProductType;
use App\Support\Content\ArticleContentCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminMenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $includeItems = $request->boolean('with_items');
        $includeTree = $request->boolean('with_tree');
        $sortable = ['order', 'title', 'active', 'created_at'];
        $sortBy = $request->input('sort_by', 'order');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'order';
        }

        $query = Menu::query()
            ->select([
                'id',
                'title',
                'type',
                'href',
                'semantic_type',
                'route_payload',
                'order',
                'active',
                'created_at',
                'updated_at',
            ])
            ->withCount('blocks')
            ->withCount('items')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->input('q').'%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($includeItems) {
            $query->with([
                'blocks' => fn ($q) => $q->select(['id', 'menu_id', 'title', 'order', 'active']),
                'blocks.items' => fn ($q) => $q->select(['id', 'menu_block_id', 'label', 'href', 'semantic_type', 'route_payload', 'badge', 'order', 'active']),
            ]);
        }

        if ($includeTree) {
            $query->with([
                'items' => fn ($q) => $q->select(['id', 'menu_id', 'parent_id', 'label', 'href', 'semantic_type', 'route_payload', 'badge', 'icon', 'depth', 'order', 'active', 'open_in_new_tab']),
            ]);
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $menus = $query->paginate($perPage);

        $data = $includeItems
            ? $menus->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'type' => $m->type,
                'href' => $m->href,
                'semantic_type' => $m->semantic_type,
                'route_payload' => $m->route_payload,
                'order' => $m->order,
                'active' => $m->active,
                'blocks' => $m->blocks->map(fn ($b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'order' => $b->order,
                    'active' => $b->active,
                    'items' => $b->items->map(fn ($i) => [
                        'id' => $i->id,
                        'label' => $i->label,
                        'href' => $i->href,
                        'semantic_type' => $i->semantic_type,
                        'route_payload' => $i->route_payload,
                        'badge' => $i->badge,
                        'order' => $i->order,
                        'active' => $i->active,
                    ]),
                ]),
                'created_at' => $m->created_at?->toIso8601String(),
                'updated_at' => $m->updated_at?->toIso8601String(),
            ])
            : $menus->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'type' => $m->type,
                'href' => $m->href,
                'semantic_type' => $m->semantic_type,
                'route_payload' => $m->route_payload,
                'order' => $m->order,
                'active' => $m->active,
                'blocks_count' => $m->blocks_count,
                'items_count' => $m->items_count,
                'items' => $includeTree ? $this->formatMenuItems($m->items) : null,
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $menus->currentPage(),
                'last_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $menu = Menu::with(['blocks.items', 'items'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $menu->id,
                'title' => $menu->title,
                'type' => $menu->type,
                'href' => $menu->href,
                'semantic_type' => $menu->semantic_type,
                'route_payload' => $menu->route_payload,
                'order' => $menu->order,
                'active' => $menu->active,
                'blocks' => $menu->blocks->map(fn ($b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'order' => $b->order,
                    'active' => $b->active,
                    'items' => $b->items->map(fn ($i) => [
                        'id' => $i->id,
                        'label' => $i->label,
                        'href' => $i->href,
                        'semantic_type' => $i->semantic_type,
                        'route_payload' => $i->route_payload,
                        'badge' => $i->badge,
                        'order' => $i->order,
                        'active' => $i->active,
                    ]),
                ]),
                'items' => $this->formatMenuItems($menu->items),
                'created_at' => $menu->created_at?->toIso8601String(),
                'updated_at' => $menu->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'href' => ['nullable', 'string', 'max:255'],
            'semantic_type' => ['nullable', 'string', 'max:50'],
            'route_payload' => ['nullable', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['active'] = $validated['active'] ?? true;
        $validated['order'] = $validated['order'] ?? Menu::max('order') + 1;

        $menu = Menu::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $menu->id],
            'message' => 'Tạo menu thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'href' => ['nullable', 'string', 'max:255'],
            'semantic_type' => ['nullable', 'string', 'max:50'],
            'route_payload' => ['nullable', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $menu->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật menu thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa menu thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:menus,id'],
        ]);

        $count = Menu::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} menu",
            'count' => $count,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thứ tự thành công',
        ]);
    }

    public function bulkSaveItems(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);
        $validated = $request->validate([
            'items' => ['present', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.client_id' => ['nullable', 'string', 'max:80'],
            'items.*.parent_id' => ['nullable'],
            'items.*.parent_client_id' => ['nullable', 'string', 'max:80'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.href' => ['nullable', 'string', 'max:255'],
            'items.*.semantic_type' => ['nullable', 'string', 'max:50'],
            'items.*.route_payload' => ['nullable', 'array'],
            'items.*.badge' => ['nullable', 'string', 'max:50'],
            'items.*.icon' => ['nullable', 'string', 'max:80'],
            'items.*.depth' => ['required', 'integer', 'min:0', 'max:4'],
            'items.*.order' => ['required', 'integer', 'min:0'],
            'items.*.active' => ['boolean'],
            'items.*.open_in_new_tab' => ['boolean'],
        ]);

        $items = array_values($validated['items']);
        $this->assertValidTree($items);

        $saved = DB::transaction(function () use ($menu, $items) {
            $existingIds = $menu->items()->pluck('id')->all();
            $keptIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
            $clientIdMap = [];
            $idMap = [];

            $menu->items()->whereIn('id', array_diff($existingIds, $keptIds))->delete();

            foreach ($items as $index => $item) {
                $id = isset($item['id']) ? (int) $item['id'] : null;
                $node = $id ? $menu->items()->find($id) : null;
                $payload = [
                    'menu_id' => $menu->id,
                    'parent_id' => null,
                    'label' => $item['label'],
                    'href' => $item['href'] ?? null,
                    'semantic_type' => $item['semantic_type'] ?? null,
                    'route_payload' => $item['route_payload'] ?? null,
                    'badge' => $item['badge'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'depth' => (int) $item['depth'],
                    'order' => (int) ($item['order'] ?? $index),
                    'active' => $item['active'] ?? true,
                    'open_in_new_tab' => $item['open_in_new_tab'] ?? false,
                ];

                $node = $node ? tap($node)->update($payload) : MenuItem::create($payload);
                $idMap[$index] = $node->id;

                if (! empty($item['client_id'])) {
                    $clientIdMap[$item['client_id']] = $node->id;
                }
            }

            foreach ($items as $index => $item) {
                $parentId = $this->resolveParentId($item, $clientIdMap);

                if ($parentId !== null) {
                    MenuItem::where('id', $idMap[$index])->update(['parent_id' => $parentId]);
                }
            }

            $this->bumpCacheVersion();

            return $menu->items()->orderBy('order')->orderBy('id')->get();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu menu 5 cấp',
            'data' => $this->formatMenuItems($saved),
        ]);
    }

    public function routeSuggestions(): JsonResponse
    {
        return response()->json([
            'data' => $this->buildRouteSuggestionGroups(),
        ]);
    }

    // Menu Blocks
    public function storeBlock(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['menu_id'] = $menu->id;
        $validated['active'] = $validated['active'] ?? true;
        $validated['order'] = $validated['order'] ?? $menu->blocks()->max('order') + 1;

        $block = MenuBlock::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $block->id],
            'message' => 'Tạo block thành công',
        ], 201);
    }

    public function updateBlock(Request $request, int $menuId, int $blockId): JsonResponse
    {
        $block = MenuBlock::where('menu_id', $menuId)->findOrFail($blockId);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $block->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật block thành công',
        ]);
    }

    public function destroyBlock(int $menuId, int $blockId): JsonResponse
    {
        $block = MenuBlock::where('menu_id', $menuId)->findOrFail($blockId);
        $block->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa block thành công',
        ]);
    }

    // Menu Block Items
    public function storeItem(Request $request, int $blockId): JsonResponse
    {
        $block = MenuBlock::findOrFail($blockId);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'href' => ['nullable', 'string', 'max:255'],
            'semantic_type' => ['nullable', 'string', 'max:50'],
            'route_payload' => ['nullable', 'array'],
            'badge' => ['nullable', 'string', 'max:50'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['menu_block_id'] = $block->id;
        $validated['active'] = $validated['active'] ?? true;
        $validated['order'] = $validated['order'] ?? $block->items()->max('order') + 1;

        $item = MenuBlockItem::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $item->id],
            'message' => 'Tạo item thành công',
        ], 201);
    }

    public function updateItem(Request $request, int $blockId, int $itemId): JsonResponse
    {
        $item = MenuBlockItem::where('menu_block_id', $blockId)->findOrFail($itemId);

        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'href' => ['nullable', 'string', 'max:255'],
            'semantic_type' => ['nullable', 'string', 'max:50'],
            'route_payload' => ['nullable', 'array'],
            'badge' => ['nullable', 'string', 'max:50'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật item thành công',
        ]);
    }

    public function destroyItem(int $blockId, int $itemId): JsonResponse
    {
        $item = MenuBlockItem::where('menu_block_id', $blockId)->findOrFail($itemId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa item thành công',
        ]);
    }

    private function formatMenuItems($items): array
    {
        return $items
            ->sortBy([['order', 'asc'], ['id', 'asc']])
            ->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'menu_id' => $item->menu_id,
                'parent_id' => $item->parent_id,
                'label' => $item->label,
                'href' => $item->href,
                'semantic_type' => $item->semantic_type,
                'route_payload' => $item->route_payload,
                'badge' => $item->badge,
                'icon' => $item->icon,
                'depth' => $item->depth,
                'order' => $item->order,
                'active' => $item->active,
                'open_in_new_tab' => $item->open_in_new_tab,
            ])
            ->values()
            ->all();
    }

    private function assertValidTree(array $items): void
    {
        $previousDepth = 0;

        foreach ($items as $index => $item) {
            $depth = (int) $item['depth'];

            if ($index === 0 && $depth !== 0) {
                throw ValidationException::withMessages(['items' => 'Item đầu tiên phải ở tầng 1.']);
            }

            if ($depth > $previousDepth + 1) {
                throw ValidationException::withMessages(['items' => 'Menu không được nhảy tầng khi thiếu parent.']);
            }

            $previousDepth = $depth;
        }
    }

    private function resolveParentId(array $item, array $clientIdMap): ?int
    {
        if (! empty($item['parent_client_id']) && isset($clientIdMap[$item['parent_client_id']])) {
            return (int) $clientIdMap[$item['parent_client_id']];
        }

        if (! empty($item['parent_id']) && is_numeric($item['parent_id'])) {
            return (int) $item['parent_id'];
        }

        return null;
    }

    private function bumpCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
    }

    private function buildRouteSuggestionGroups(): array
    {
        $productTypes = ProductType::query()
            ->active()
            ->select(['name', 'slug'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $groups = [
            [
                'key' => 'core',
                'label' => 'Trang chính',
                'items' => [
                    $this->routeItem('Trang chủ', '/', 'core'),
                    $this->routeItem('Sản phẩm', '/san-pham', 'core'),
                    $this->routeItem('Bài viết', '/bai-viet', 'core'),
                    $this->routeItem('Liên hệ', '/lien-he', 'core'),
                ],
            ],
            [
                'key' => 'site-hubs',
                'label' => 'Trang hệ thống',
                'items' => [
                    $this->routeItem('Thương hiệu', '/thuong-hieu', 'site_hub'),
                    $this->routeItem('Bộ sưu tập', '/bo-suu-tap', 'site_hub'),
                    $this->routeItem('Cửa hàng', '/cua-hang', 'site_hub'),
                ],
            ],
            [
                'key' => 'content-hubs',
                'label' => 'Hub bài viết',
                'items' => collect(ArticleContentCatalog::categories())
                    ->map(fn (array $category) => $this->routeItem($category['label'], $this->articleCategoryHubPath($category['key']), 'content_hub', ['category' => $category['key']]))
                    ->values()
                    ->all(),
            ],
            [
                'key' => 'content-slots',
                'label' => 'Trang nội dung',
                'items' => collect(ArticleContentCatalog::slots())
                    ->map(fn (array $slot) => $this->routeItem($slot['label'], $slot['path'], 'content_slot', ['slot' => $slot['key']]))
                    ->values()
                    ->all(),
            ],
        ];

        $productTypes = ProductType::query()
            ->active()
            ->with(['categories' => fn ($query) => $query->active()->orderBy('order')->orderBy('name')])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $groups[] = [
            'key' => 'product-types',
            'label' => 'Nhóm sản phẩm',
            'items' => $productTypes
                ->map(fn (ProductType $type) => $this->routeItem($type->name, "/san-pham/{$type->slug}", 'product_type', ['type' => $type->slug]))
                ->values()
                ->all(),
        ];

        $groups[] = [
            'key' => 'product-categories',
            'label' => 'Danh mục sản phẩm',
            'items' => $productTypes
                ->flatMap(fn (ProductType $type) => $type->categories->map(fn ($category) => $this->routeItem(
                    "{$type->name} / {$category->name}",
                    "/san-pham/{$type->slug}/{$category->slug}",
                    'product_category',
                    ['type' => $type->slug, 'category' => $category->slug]
                )))
                ->values()
                ->all(),
        ];

        $groups[] = [
            'key' => 'attribute-filters',
            'label' => 'Thuộc tính lọc',
            'items' => CatalogAttributeGroup::query()
                ->where('is_filterable', true)
                ->with(['terms' => fn ($query) => $query->active()->orderBy('position')->orderBy('name')])
                ->orderBy('position')
                ->orderBy('name')
                ->get()
                ->flatMap(fn (CatalogAttributeGroup $group) => $group->terms->map(fn ($term) => $this->routeItem(
                    "{$group->name} / {$term->name}",
                    "/san-pham/{$group->slug}/{$term->slug}",
                    'attribute_filter',
                    ['attribute_group' => $group->slug, 'term' => $term->slug]
                )))
                ->values()
                ->all(),
        ];

        $groups[] = [
            'key' => 'filter-presets',
            'label' => 'Bộ lọc SEO',
            'items' => ProductFilterGroup::query()
                ->active()
                ->where('show_in_filters', true)
                ->with(['presets' => fn ($query) => $query->active()->orderBy('position')->orderBy('name')])
                ->orderBy('position')
                ->orderBy('name')
                ->get()
                ->flatMap(fn (ProductFilterGroup $group) => $productTypes
                    ->flatMap(fn (ProductType $type) => $group->presets->map(fn ($preset) => $this->routeItem(
                        "{$type->name} / {$group->name} / {$preset->name}",
                        "/san-pham/{$type->slug}/{$preset->slug}",
                        'filter_preset',
                        ['type' => $type->slug, 'group' => $group->slug, 'preset' => $preset->slug]
                    ))))
                ->values()
                ->all(),
        ];

        $articleItems = Article::query()
            ->where('active', true)
            ->select(['title', 'slug', 'category_key'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (Article $article) => $this->routeItem($article->title, $this->articleHref($article), 'article', ['slug' => $article->slug, 'category' => $article->category_key]))
            ->values()
            ->all();

        if ($articleItems !== []) {
            $groups[] = [
                'key' => 'articles',
                'label' => 'Bài viết',
                'items' => $articleItems,
            ];
        }

        return $groups;
    }

    private function routeItem(string $label, string $path, string $source, ?array $payload = null): array
    {
        return [
            'label' => $label,
            'path' => $path,
            'source' => $source,
            'route_payload' => $payload,
        ];
    }

    private function articleHref(Article $article): string
    {
        $hub = $this->articleCategoryHub($article->category_key);

        return $hub ? "/{$hub}/{$article->slug}" : "/bai-viet/{$article->slug}";
    }

    private function articleCategoryHubPath(?string $categoryKey): string
    {
        $hub = $this->articleCategoryHub($categoryKey);

        return $hub ? "/{$hub}" : '/bai-viet';
    }

    private function articleCategoryHub(?string $categoryKey): ?string
    {
        return [
            'kien-thuc' => 'kien-thuc',
            'chinh-sach' => 'ho-tro',
            'gioi-thieu' => 'gioi-thieu',
            'dich-vu' => 'dich-vu',
            'qua-tang' => 'qua-tang',
            'tin-tuc' => 'tin-tuc',
            'su-kien' => 'su-kien',
        ][$categoryKey] ?? null;
    }
}

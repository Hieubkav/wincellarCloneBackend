<?php

 namespace App\Http\Controllers\Api\V1\Admin;
 
 use App\Http\Controllers\Controller;
 use App\Models\Menu;
 use App\Models\MenuBlock;
 use App\Models\MenuBlockItem;
 use Illuminate\Http\JsonResponse;
 use Illuminate\Http\Request;
 use Illuminate\Validation\Rule;
 
 class AdminMenuController extends Controller
 {
     public function index(Request $request): JsonResponse
     {
         $query = Menu::query()
             ->withCount('blocks')
             ->orderBy('order')
             ->orderBy('id');
 
         if ($request->filled('q')) {
             $query->where('title', 'like', '%' . $request->input('q') . '%');
         }
 
         if ($request->filled('active')) {
             $query->where('active', $request->boolean('active'));
         }
 
         $perPage = min($request->integer('per_page', 20), 100);
         $menus = $query->paginate($perPage);
 
         return response()->json([
             'data' => $menus->map(fn($m) => [
                 'id' => $m->id,
                 'title' => $m->title,
                 'type' => $m->type,
                 'href' => $m->href,
                 'order' => $m->order,
                 'active' => $m->active,
                 'blocks_count' => $m->blocks_count,
                 'created_at' => $m->created_at?->toIso8601String(),
             ]),
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
         $menu = Menu::with(['blocks.items'])->findOrFail($id);
 
         return response()->json([
             'data' => [
                 'id' => $menu->id,
                 'title' => $menu->title,
                 'type' => $menu->type,
                 'href' => $menu->href,
                 'order' => $menu->order,
                 'active' => $menu->active,
                 'blocks' => $menu->blocks->map(fn($b) => [
                     'id' => $b->id,
                     'title' => $b->title,
                     'order' => $b->order,
                     'active' => $b->active,
                     'items' => $b->items->map(fn($i) => [
                         'id' => $i->id,
                         'label' => $i->label,
                         'href' => $i->href,
                         'badge' => $i->badge,
                         'order' => $i->order,
                         'active' => $i->active,
                     ]),
                 ]),
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
 }

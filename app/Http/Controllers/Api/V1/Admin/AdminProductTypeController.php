<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProductTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductType::query()
            ->withCount('products')
            ->withCount('attributeGroups')
            ->orderBy('order')
            ->orderBy('id');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $types = $query->paginate($perPage);

        return response()->json([
            'data' => $types->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'order' => $type->order,
                'active' => $type->active,
                'products_count' => $type->products_count,
                'attribute_groups_count' => $type->attribute_groups_count,
                'created_at' => $type->created_at?->toIso8601String(),
                'updated_at' => $type->updated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
                'per_page' => $types->perPage(),
                'total' => $types->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $type = ProductType::with(['attributeGroups' => function ($query) {
            $query->orderBy('catalog_attribute_group_product_type.position');
        }])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'order' => $type->order,
                'active' => $type->active,
                'created_at' => $type->created_at?->toIso8601String(),
                'updated_at' => $type->updated_at?->toIso8601String(),
                'attribute_groups' => $type->attributeGroups->map(fn ($group) => [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'filter_type' => $group->filter_type,
                    'icon_path' => $group->icon_path,
                    'position' => $group->pivot->position,
                ]),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_types,slug'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['active'] = $validated['active'] ?? true;

        $type = ProductType::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $type->id],
            'message' => 'Tạo phân loại thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $type = ProductType::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('product_types', 'slug')->ignore($id)],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $type->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật phân loại thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $type = ProductType::findOrFail($id);

        if ($type->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa phân loại đang có sản phẩm.',
            ], 422);
        }

        $type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa phân loại thành công',
        ]);
    }

    public function attachAttributeGroup(Request $request, int $id): JsonResponse
    {
        $type = ProductType::findOrFail($id);

        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:catalog_attribute_groups,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $type->attributeGroups()->syncWithoutDetaching([
            $validated['group_id'] => ['position' => $validated['position'] ?? 0],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã liên kết nhóm thuộc tính',
        ]);
    }

    public function detachAttributeGroup(int $id, int $groupId): JsonResponse
    {
        $type = ProductType::findOrFail($id);
        $type->attributeGroups()->detach($groupId);

        return response()->json([
            'success' => true,
            'message' => 'Đã gỡ liên kết nhóm thuộc tính',
        ]);
    }

    public function syncAttributeGroups(Request $request, int $id): JsonResponse
    {
        $type = ProductType::findOrFail($id);

        $validated = $request->validate([
            'groups' => ['required', 'array'],
            'groups.*.id' => ['required', 'integer', 'exists:catalog_attribute_groups,id'],
            'groups.*.position' => ['nullable', 'integer', 'min:0'],
        ]);

        $syncData = [];
        foreach ($validated['groups'] as $group) {
            $syncData[$group['id']] = ['position' => $group['position'] ?? 0];
        }

        $type->attributeGroups()->sync($syncData);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật danh sách nhóm thuộc tính',
        ]);
    }
}

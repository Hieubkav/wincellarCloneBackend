<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogAttributeGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCatalogAttributeGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CatalogAttributeGroup::query()
            ->with(['terms', 'productTypes'])
            ->orderBy('position')
            ->orderBy('id');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('q') . '%')
                  ->orWhere('code', 'like', '%' . $request->input('q') . '%');
            });
        }

        if ($request->filled('is_filterable')) {
            $query->where('is_filterable', $request->boolean('is_filterable'));
        }

        if ($request->filled('filter_type')) {
            $query->where('filter_type', $request->input('filter_type'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $groups = $query->paginate($perPage);

        return response()->json([
            'data' => $groups->map(function ($group) {
                $termIds = $group->terms->pluck('id')->toArray();
                
                // Count products that have any of these terms
                $productsCount = 0;
                if (!empty($termIds)) {
                    $productsCount = \App\Models\Product::whereHas('terms', function ($query) use ($termIds) {
                        $query->whereIn('catalog_terms.id', $termIds);
                    })->count();
                }

                return [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'filter_type' => $group->filter_type,
                    'input_type' => $group->input_type,
                    'is_filterable' => $group->is_filterable,
                    'position' => $group->position,
                    'icon_path' => $group->icon_path,
                    'terms_count' => $group->terms->count(),
                    'products_count' => $productsCount,
                    'product_types' => $group->productTypes->map(fn ($type) => [
                        'id' => $type->id,
                        'name' => $type->name,
                    ])->toArray(),
                    'created_at' => $group->created_at?->toIso8601String(),
                    'updated_at' => $group->updated_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $groups->currentPage(),
                'last_page' => $groups->lastPage(),
                'per_page' => $groups->perPage(),
                'total' => $groups->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $group = CatalogAttributeGroup::with(['terms', 'productTypes'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'filter_type' => $group->filter_type,
                'input_type' => $group->input_type,
                'is_filterable' => $group->is_filterable,
                'position' => $group->position,
                'display_config' => $group->display_config,
                'icon_path' => $group->icon_path,
                'terms' => $group->terms->map(fn ($term) => [
                    'id' => $term->id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'position' => $term->position,
                ])->toArray(),
                'product_types' => $group->productTypes->map(fn ($type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ])->toArray(),
                'created_at' => $group->created_at?->toIso8601String(),
                'updated_at' => $group->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:catalog_attribute_groups,code'],
            'name' => ['required', 'string', 'max:255'],
            'filter_type' => ['required', 'string', 'in:checkbox,radio,range,color'],
            'input_type' => ['nullable', 'string', 'in:select,text,number'],
            'is_filterable' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
            'display_config' => ['nullable', 'array'],
            'icon_path' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_filterable'] = $validated['is_filterable'] ?? true;

        $group = CatalogAttributeGroup::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $group->id],
            'message' => 'Tạo nhóm thuộc tính thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $group = CatalogAttributeGroup::findOrFail($id);
        
        // Convert empty strings to null for nullable fields
        $data = $request->all();
        foreach (['input_type', 'icon_path', 'position'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        $request->merge($data);

        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('catalog_attribute_groups', 'code')->ignore($id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'filter_type' => ['sometimes', 'string', 'in:checkbox,radio,range,color'],
            'input_type' => ['nullable', 'string', 'in:select,text,number'],
            'is_filterable' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
            'display_config' => ['nullable', 'array'],
            'icon_path' => ['nullable', 'string', 'max:255'],
        ]);

        $group->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật nhóm thuộc tính thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $group = CatalogAttributeGroup::findOrFail($id);

        if ($group->terms()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa nhóm thuộc tính đang có giá trị.',
            ], 422);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa nhóm thuộc tính thành công',
        ]);
    }
}

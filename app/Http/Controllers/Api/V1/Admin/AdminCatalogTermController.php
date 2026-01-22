<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogTerm;
use App\Models\CatalogAttributeGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminCatalogTermController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CatalogTerm::query()
            ->with('group')
            ->orderBy('position')
            ->orderBy('id');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->integer('group_id'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = min($request->integer('per_page', 100), 500);
        $terms = $query->paginate($perPage);

        return response()->json([
            'data' => $terms->map(fn ($term) => [
                'id' => $term->id,
                'group_id' => $term->group_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'icon_type' => $term->icon_type,
                'icon_value' => $term->icon_value,
                'is_active' => $term->is_active,
                'position' => $term->position,
                'group_name' => $term->group?->name,
                'created_at' => $term->created_at?->toIso8601String(),
                'updated_at' => $term->updated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $terms->currentPage(),
                'last_page' => $terms->lastPage(),
                'per_page' => $terms->perPage(),
                'total' => $terms->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $term = CatalogTerm::with('group')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $term->id,
                'group_id' => $term->group_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'icon_type' => $term->icon_type,
                'icon_value' => $term->icon_value,
                'metadata' => $term->metadata,
                'is_active' => $term->is_active,
                'position' => $term->position,
                'group_name' => $term->group?->name,
                'created_at' => $term->created_at?->toIso8601String(),
                'updated_at' => $term->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:catalog_attribute_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:catalog_terms,slug'],
            'description' => ['nullable', 'string'],
            'icon_type' => ['nullable', 'string', 'in:lucide,color,image'],
            'icon_value' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        if (!isset($validated['position'])) {
            $maxPosition = CatalogTerm::where('group_id', $validated['group_id'])->max('position');
            $validated['position'] = ($maxPosition ?? 0) + 1;
        }

        $term = CatalogTerm::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $term->id],
            'message' => 'Tạo giá trị thuộc tính thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $term = CatalogTerm::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('catalog_terms', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'icon_type' => ['nullable', 'string', 'in:lucide,color,image'],
            'icon_value' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $term->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật giá trị thuộc tính thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $term = CatalogTerm::findOrFail($id);

        if ($term->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa giá trị thuộc tính đang được sử dụng bởi sản phẩm.',
            ], 422);
        }

        $term->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa giá trị thuộc tính thành công',
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:catalog_terms,id'],
            'items.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            CatalogTerm::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thứ tự thành công',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 50);
        $query = ProductCategory::with('type')->withCount('products');

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type_id')) {
            $typeId = $request->integer('type_id');
            $query->where('type_id', $typeId);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $categories = $query->orderBy('order')->orderBy('name')->paginate($perPage);

        $data = $categories->items();
        foreach ($data as $category) {
            $category->type_name = $category->type?->name;
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $category = ProductCategory::with('type')->findOrFail($id);

        return response()->json(['data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'type_id' => 'nullable|exists:product_types,id',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = ProductCategory::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $category->id],
            'message' => 'Danh mục đã được tạo thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_categories')->ignore($category->id),
            ],
            'type_id' => 'nullable|exists:product_types,id',
            'order' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Danh mục đã được cập nhật thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = ProductCategory::findOrFail($id);

        $productsCount = $category->products()->count();
        if ($productsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa danh mục này vì có {$productsCount} sản phẩm đang sử dụng",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Danh mục đã được xóa thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:product_categories,id',
        ]);

        $categories = ProductCategory::whereIn('id', $validated['ids'])->get();

        $categoriesWithProducts = $categories->filter(function ($category) {
            return $category->products()->count() > 0;
        });

        if ($categoriesWithProducts->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa các danh mục có sản phẩm đang sử dụng',
            ], 422);
        }

        $count = ProductCategory::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Đã xóa {$count} danh mục thành công",
        ]);
    }
}

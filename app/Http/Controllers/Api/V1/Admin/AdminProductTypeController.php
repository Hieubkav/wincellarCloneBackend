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
            ->orderBy('order')
            ->orderBy('id');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->input('q') . '%');
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
        $type = ProductType::findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'order' => $type->order,
                'active' => $type->active,
                'created_at' => $type->created_at?->toIso8601String(),
                'updated_at' => $type->updated_at?->toIso8601String(),
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
}

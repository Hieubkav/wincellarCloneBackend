<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminHomeComponentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HomeComponent::query()
            ->orderBy('order', 'asc')
            ->orderBy('id', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $components = $query->paginate($perPage);

        return response()->json([
            'data' => $components->map(fn($c) => [
                'id' => $c->id,
                'type' => $c->type,
                'config' => $c->config,
                'order' => $c->order,
                'active' => $c->active,
                'created_at' => $c->created_at?->toIso8601String(),
                'updated_at' => $c->updated_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $components->currentPage(),
                'last_page' => $components->lastPage(),
                'per_page' => $components->perPage(),
                'total' => $components->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $component = HomeComponent::findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $component->id,
                'type' => $component->type,
                'config' => $component->config,
                'order' => $component->order,
                'active' => $component->active,
                'created_at' => $component->created_at?->toIso8601String(),
                'updated_at' => $component->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'config' => ['required', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['order'] = $validated['order'] ?? HomeComponent::max('order') + 1;
        $validated['active'] = $validated['active'] ?? true;

        $component = HomeComponent::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $component->id],
            'message' => 'Tạo thành phần trang chủ thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $component = HomeComponent::findOrFail($id);

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'max:100'],
            'config' => ['sometimes', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $component->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành phần trang chủ thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $component = HomeComponent::findOrFail($id);
        $component->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa thành phần trang chủ thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:home_components,id'],
        ]);

        $count = HomeComponent::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} thành phần",
            'count' => $count,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:home_components,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            HomeComponent::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thứ tự thành công',
        ]);
    }
}

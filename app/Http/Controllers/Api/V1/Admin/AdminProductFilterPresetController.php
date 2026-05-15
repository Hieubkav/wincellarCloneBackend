<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductFilterGroup;
use App\Models\ProductFilterPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProductFilterPresetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $groups = ProductFilterGroup::query()
            ->with(['presets'])
            ->when($request->filled('route_prefix'), fn ($query) => $query->where('route_prefix', $request->input('route_prefix')))
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $groups->map(fn (ProductFilterGroup $group) => $this->mapGroup($group))->values(),
        ]);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_filter_groups,slug'],
            'route_prefix' => ['nullable', 'string', 'max:80'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['name']);
        $validated['route_prefix'] = trim($validated['route_prefix'] ?? 'san-pham', '/');
        $validated['active'] = $validated['active'] ?? true;

        $group = ProductFilterGroup::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $group->id],
            'message' => 'Tạo nhóm bộ lọc thành công',
        ], 201);
    }

    public function updateGroup(Request $request, int $id): JsonResponse
    {
        $group = ProductFilterGroup::findOrFail($id);
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_filter_groups', 'slug')->ignore($group->id)],
            'route_prefix' => ['nullable', 'string', 'max:80'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        if (array_key_exists('slug', $validated) || array_key_exists('name', $validated)) {
            $validated['slug'] = Str::slug($validated['slug'] ?? $group->slug ?? $validated['name'] ?? $group->name);
        }
        if (array_key_exists('route_prefix', $validated)) {
            $validated['route_prefix'] = trim((string) $validated['route_prefix'], '/') ?: 'san-pham';
        }

        $group->update($validated);

        return response()->json(['success' => true, 'message' => 'Cập nhật nhóm bộ lọc thành công']);
    }

    public function destroyGroup(int $id): JsonResponse
    {
        ProductFilterGroup::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Xóa nhóm bộ lọc thành công']);
    }

    public function storePreset(Request $request, int $groupId): JsonResponse
    {
        $group = ProductFilterGroup::findOrFail($groupId);
        $validated = $this->validatePreset($request, $group);
        $validated['group_id'] = $group->id;
        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['name']);
        $validated['active'] = $validated['active'] ?? true;

        $preset = ProductFilterPreset::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $preset->id],
            'message' => 'Tạo bộ lọc thành công',
        ], 201);
    }

    public function updatePreset(Request $request, int $groupId, int $presetId): JsonResponse
    {
        $group = ProductFilterGroup::findOrFail($groupId);
        $preset = ProductFilterPreset::query()->where('group_id', $group->id)->findOrFail($presetId);
        $validated = $this->validatePreset($request, $group, $preset);

        if (array_key_exists('slug', $validated) || array_key_exists('name', $validated)) {
            $validated['slug'] = Str::slug($validated['slug'] ?? $preset->slug ?? $validated['name'] ?? $preset->name);
        }

        $preset->update($validated);

        return response()->json(['success' => true, 'message' => 'Cập nhật bộ lọc thành công']);
    }

    public function destroyPreset(int $groupId, int $presetId): JsonResponse
    {
        ProductFilterPreset::query()->where('group_id', $groupId)->findOrFail($presetId)->delete();

        return response()->json(['success' => true, 'message' => 'Xóa bộ lọc thành công']);
    }

    private function validatePreset(Request $request, ProductFilterGroup $group, ?ProductFilterPreset $preset = null): array
    {
        return $request->validate([
            'name' => [$preset ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_filter_presets', 'slug')
                    ->where('group_id', $group->id)
                    ->ignore($preset?->id),
            ],
            'filter_payload' => ['nullable', 'array'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);
    }

    private function mapGroup(ProductFilterGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'slug' => $group->slug,
            'route_prefix' => $group->route_prefix,
            'position' => $group->position,
            'active' => $group->active,
            'presets' => $group->presets->map(fn (ProductFilterPreset $preset) => [
                'id' => $preset->id,
                'group_id' => $preset->group_id,
                'name' => $preset->name,
                'slug' => $preset->slug,
                'filter_payload' => $preset->filter_payload ?? [],
                'seo_title' => $preset->seo_title,
                'seo_description' => $preset->seo_description,
                'content' => $preset->content,
                'position' => $preset->position,
                'active' => $preset->active,
            ])->values(),
        ];
    }
}

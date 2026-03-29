<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\SocialLink;
use App\Services\Media\MediaCanonicalService;
use App\Support\Media\MediaSemanticRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSocialLinkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sortable = ['order', 'platform', 'active', 'created_at'];
        $sortBy = $request->input('sort_by', 'order');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'order';
        }

        $query = SocialLink::query()
            ->select([
                'id',
                'platform',
                'url',
                'icon_image_id',
                'order',
                'active',
                'created_at',
            ])
            ->with('iconImage:id,file_path,disk,model_type,model_id')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'desc');

        if ($request->filled('platform')) {
            $query->where('platform', 'like', '%'.$request->input('platform').'%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $links = $query->paginate($perPage);

        return response()->json([
            'data' => $links->map(fn ($link) => [
                'id' => $link->id,
                'platform' => $link->platform,
                'url' => $link->url,
                'icon_image_id' => $link->icon_image_id,
                'icon_url' => $link->icon_url,
                'icon_canonical_url' => $link->icon_canonical_url,
                'order' => $link->order,
                'active' => $link->active,
                'created_at' => $link->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $links->currentPage(),
                'last_page' => $links->lastPage(),
                'per_page' => $links->perPage(),
                'total' => $links->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $link = SocialLink::with('iconImage')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $link->id,
                'platform' => $link->platform,
                'url' => $link->url,
                'icon_image_id' => $link->icon_image_id,
                'icon_url' => $link->icon_url,
                'icon_canonical_url' => $link->icon_canonical_url,
                'order' => $link->order,
                'active' => $link->active,
                'created_at' => $link->created_at?->toIso8601String(),
                'updated_at' => $link->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'icon_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $validated['order'] = $validated['order'] ?? SocialLink::max('order') + 1;
        $validated['active'] = $validated['active'] ?? true;

        $link = SocialLink::create($validated);
        $this->syncSocialIconSemantic($link);

        return response()->json([
            'success' => true,
            'data' => ['id' => $link->id],
            'message' => 'Tạo liên kết mạng xã hội thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $link = SocialLink::findOrFail($id);

        $validated = $request->validate([
            'platform' => ['sometimes', 'string', 'max:100'],
            'url' => ['sometimes', 'url', 'max:500'],
            'icon_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $link->update($validated);
        $this->syncSocialIconSemantic($link);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật liên kết mạng xã hội thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $link = SocialLink::findOrFail($id);
        $link->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa liên kết mạng xã hội thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:social_links,id'],
        ]);

        $count = SocialLink::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} liên kết",
            'count' => $count,
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:social_links,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            SocialLink::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thứ tự thành công',
        ]);
    }

    private function syncSocialIconSemantic(SocialLink $link): void
    {
        if (! $link->icon_image_id) {
            return;
        }

        $image = Image::find($link->icon_image_id);
        if (! $image) {
            return;
        }

        app(MediaCanonicalService::class)->ensureMetadata(
            $image,
            MediaSemanticRegistry::SOCIAL,
            $link->platform ?: MediaSemanticRegistry::SOCIAL
        );

        $image->saveQuietly();
    }
}

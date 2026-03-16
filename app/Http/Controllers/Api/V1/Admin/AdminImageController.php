<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminImageController extends Controller
{
    public function library(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 12), 1), 100);
        $search = trim((string) $request->query('search', ''));

        $query = Image::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $searchTerm = "%{$search}%";
                $q->where('alt', 'like', $searchTerm)
                    ->orWhere('file_path', 'like', $searchTerm);
            });
        }

        $images = $query->paginate($perPage);

        return response()->json([
            'data' => collect($images->items())->map(fn (Image $image) => [
                'id' => $image->id,
                'url' => $image->url ?? '/images/placeholder.png',
                'alt' => $image->alt ?? basename($image->file_path ?? ''),
                'name' => basename($image->file_path ?? ''),
                'mime' => $image->mime,
            ])->values(),
            'current_page' => $images->currentPage(),
            'last_page' => $images->lastPage(),
            'total' => $images->total(),
            'per_page' => $images->perPage(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $sortable = ['id', 'created_at', 'alt', 'file_path', 'active'];
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'id';
        }

        $query = Image::query()
            ->select([
                'id',
                'file_path',
                'alt',
                'width',
                'height',
                'mime',
                'model_type',
                'model_id',
                'order',
                'active',
                'created_at',
            ])
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('alt', 'like', '%'.$request->input('q').'%')
                    ->orWhere('file_path', 'like', '%'.$request->input('q').'%');
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->input('model_type'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $images = $query->paginate($perPage);

        return response()->json([
            'data' => $images->map(function ($img) {
                $usedBy = $this->getUsedByInfo($img);

                return [
                    'id' => $img->id,
                    'file_path' => $img->file_path,
                    'url' => $img->url,
                    'alt' => $img->alt,
                    'width' => $img->width,
                    'height' => $img->height,
                    'mime' => $img->mime,
                    'model_type' => $img->model_type,
                    'model_id' => $img->model_id,
                    'used_by' => $usedBy,
                    'order' => $img->order,
                    'active' => $img->active,
                    'created_at' => $img->created_at?->toIso8601String(),
                ];
            }),
            'meta' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
            ],
        ]);
    }

    public function batch(Request $request): JsonResponse
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));

        if (empty($ids)) {
            return response()->json(['data' => []]);
        }

        $images = Image::whereIn('id', $ids)->get()->keyBy('id');

        $data = collect($ids)
            ->map(fn ($id) => $images->get($id))
            ->filter()
            ->map(fn (Image $image) => [
                'id' => $image->id,
                'file_path' => $image->file_path,
                'url' => $image->url,
                'alt' => $image->alt,
                'width' => $image->width,
                'height' => $image->height,
                'mime' => $image->mime,
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    private function getUsedByInfo($image): ?array
    {
        if (! $image->model_type || ! $image->model_id) {
            return null;
        }

        try {
            if ($image->model_type === 'App\\Models\\Product') {
                $product = \App\Models\Product::find($image->model_id);
                if ($product) {
                    return [
                        'type' => 'product',
                        'label' => 'Sản phẩm',
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'url' => "/admin/products/{$product->id}/edit",
                    ];
                }
            } elseif ($image->model_type === 'App\\Models\\Article') {
                $article = \App\Models\Article::find($image->model_id);
                if ($article) {
                    return [
                        'type' => 'article',
                        'label' => 'Bài viết',
                        'name' => $article->title,
                        'slug' => $article->slug,
                        'url' => "/admin/articles/{$article->id}/edit",
                    ];
                }
            } elseif ($image->model_type === 'App\\Models\\Setting') {
                return [
                    'type' => 'setting',
                    'label' => 'Cài đặt',
                    'name' => 'Logo/Favicon/Watermark',
                    'slug' => null,
                    'url' => '/admin/settings',
                ];
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to get used_by info for image {$image->id}: ".$e->getMessage());
        }

        return null;
    }

    public function show(int $id): JsonResponse
    {
        $image = Image::findOrFail($id);
        $usedBy = $this->getUsedByInfo($image);

        return response()->json([
            'data' => [
                'id' => $image->id,
                'file_path' => $image->file_path,
                'url' => $image->url,
                'disk' => $image->disk,
                'alt' => $image->alt,
                'width' => $image->width,
                'height' => $image->height,
                'mime' => $image->mime,
                'model_type' => $image->model_type,
                'model_id' => $image->model_id,
                'used_by' => $usedBy,
                'order' => $image->order,
                'active' => $image->active,
                'extra_attributes' => $image->extra_attributes,
                'created_at' => $image->created_at?->toIso8601String(),
                'updated_at' => $image->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file_path' => ['required', 'string', 'max:500'],
            'disk' => ['nullable', 'string', 'max:50'],
            'alt' => ['nullable', 'string', 'max:255'],
            'width' => ['nullable', 'integer', 'min:1'],
            'height' => ['nullable', 'integer', 'min:1'],
            'mime' => ['nullable', 'string', 'max:100'],
            'model_type' => ['nullable', 'string', 'max:255'],
            'model_id' => ['nullable', 'integer'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'extra_attributes' => ['nullable', 'array'],
        ]);

        $validated['disk'] = $validated['disk'] ?? config('filesystems.default');
        $validated['active'] = $validated['active'] ?? true;

        $image = Image::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $image->id, 'url' => $image->url],
            'message' => 'Tạo hình ảnh thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $image = Image::findOrFail($id);

        $validated = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'extra_attributes' => ['nullable', 'array'],
        ]);

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hình ảnh thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $image = Image::findOrFail($id);
        $image->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa hình ảnh thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:images,id'],
        ]);

        $images = Image::whereIn('id', $validated['ids'])->get();

        foreach ($images as $image) {
            $image->forceDelete();
        }

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$images->count()} hình ảnh",
            'count' => $images->count(),
        ]);
    }
}

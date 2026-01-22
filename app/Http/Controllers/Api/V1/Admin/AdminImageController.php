<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminImageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Image::query()
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('alt', 'like', '%' . $request->input('q') . '%')
                  ->orWhere('file_path', 'like', '%' . $request->input('q') . '%');
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
            'data' => $images->map(fn($img) => [
                'id' => $img->id,
                'file_path' => $img->file_path,
                'url' => $img->url,
                'alt' => $img->alt,
                'width' => $img->width,
                'height' => $img->height,
                'mime' => $img->mime,
                'model_type' => $img->model_type,
                'model_id' => $img->model_id,
                'order' => $img->order,
                'active' => $img->active,
                'created_at' => $img->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $image = Image::findOrFail($id);

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

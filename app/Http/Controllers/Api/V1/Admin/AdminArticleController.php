<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->with(['coverImage'])
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $articles = $query->paginate($perPage);

        return response()->json([
            'data' => $articles->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'active' => $a->active,
                'cover_image_url' => $a->coverImage?->url,
                'published_at' => $a->published_at?->toIso8601String(),
                'created_at' => $a->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $article = Article::with(['coverImage', 'images'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'active' => $article->active,
                'cover_image_url' => $article->coverImage?->url,
                'images' => $article->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => $img->url,
                    'path' => $img->file_path,
                ]),
                'published_at' => $article->published_at?->toIso8601String(),
                'created_at' => $article->created_at?->toIso8601String(),
                'updated_at' => $article->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:articles,slug'],
            'content' => ['nullable', 'string'],
            'active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['active'] = $validated['active'] ?? true;
        $validated['published_at'] = $validated['published_at'] ?? now();

        $imagePaths = $validated['image_paths'] ?? [];
        unset($validated['image_paths']);

        $article = Article::create($validated);

        if (!empty($imagePaths)) {
            $article->syncImagesFromPaths($imagePaths);
        }

        return response()->json([
            'success' => true,
            'data' => ['id' => $article->id],
            'message' => 'Tạo bài viết thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $article = Article::findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('articles', 'slug')->ignore($id)],
            'content' => ['nullable', 'string'],
            'active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string'],
        ]);

        $imagePaths = $validated['image_paths'] ?? null;
        unset($validated['image_paths']);

        $article->update($validated);

        if ($imagePaths !== null) {
            $article->syncImagesFromPaths($imagePaths);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bài viết thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bài viết thành công',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:articles,id'],
        ]);

        $count = Article::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} bài viết",
            'count' => $count,
        ]);
    }
}

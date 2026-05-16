<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminArticleCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->integer('per_page', 50), 100);
        $query = ArticleCategory::query()
            ->withCount('articles')
            ->orderBy('position')
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $categories = $query->paginate($perPage);

        return response()->json([
            'data' => $categories->getCollection()->map(fn (ArticleCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'active' => $category->active,
                'position' => $category->position,
                'articles_count' => $category->articles_count,
                'created_at' => $category->created_at?->toIso8601String(),
                'updated_at' => $category->updated_at?->toIso8601String(),
            ])->values(),
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
        return response()->json(['data' => ArticleCategory::withCount('articles')->findOrFail($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:article_categories,slug'],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $validated['active'] = $validated['active'] ?? true;
        $validated['position'] = $validated['position'] ?? 0;

        $category = ArticleCategory::create($validated);

        return response()->json([
            'success' => true,
            'data' => ['id' => $category->id],
            'message' => 'Tạo danh mục bài viết thành công',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = ArticleCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('article_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục bài viết thành công',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = ArticleCategory::withCount('articles')->findOrFail($id);
        if ($category->articles_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa vì có {$category->articles_count} bài viết đang dùng danh mục này",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa danh mục bài viết thành công',
        ]);
    }
}

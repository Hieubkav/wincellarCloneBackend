<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Content\ArticleContentCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminArticleController extends Controller
{
    public function contentOptions(): JsonResponse
    {
        return response()->json([
            'data' => [
                'categories' => ArticleCategory::query()
                    ->active()
                    ->orderBy('position')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug', 'description'])
                    ->map(fn (ArticleCategory $category) => [
                        'id' => $category->id,
                        'key' => $category->slug,
                        'label' => $category->name,
                        'description' => $category->description ?? '',
                        'slug' => $category->slug,
                    ])
                    ->values(),
                'slots' => ArticleContentCatalog::slots(),
            ],
        ]);
    }

    public function listForSelect(Request $request): JsonResponse
    {
        $query = Article::query()
            ->where('active', true)
            ->with(['coverImage', 'articleCategory:id,name,slug'])
            ->orderBy('title', 'asc');

        // Support fetching by IDs for preview
        if ($request->filled('ids')) {
            $ids = array_filter(array_map('intval', explode(',', $request->input('ids'))));
            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            }
        } elseif ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->input('q').'%');
        }

        $limit = min($request->integer('limit', 50), 200);
        $articles = $query->limit($limit)->get();

        return response()->json([
            'data' => $articles->map(fn ($a) => [
                'value' => $a->id,
                'label' => $a->title.' (#'.$a->id.')',
                'category_key' => $a->category_key,
                'article_category_id' => $a->article_category_id,
                'article_category' => $a->articleCategory ? [
                    'id' => $a->articleCategory->id,
                    'name' => $a->articleCategory->name,
                    'slug' => $a->articleCategory->slug,
                ] : null,
                'content_slots' => $a->content_slots ?? [],
                'cover_image' => $a->coverImage ? [
                    'id' => $a->coverImage->id,
                    'url' => $a->coverImage->url,
                    'canonical_url' => $a->coverImage->canonical_url,
                    'alt' => $a->coverImage->alt,
                ] : null,
                'published_at' => $a->published_at?->toIso8601String(),
            ]),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $controllerStart = microtime(true);
        $sortable = ['id', 'title', 'published_at', 'active', 'created_at', 'category_key', 'article_category_id'];
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'id';
        }

        $query = Article::query()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'category_key',
                'article_category_id',
                'content_slots',
                'active',
                'published_at',
                'created_at',
            ])
            ->with(['coverImage:id,file_path,alt,disk,model_id,model_type', 'articleCategory:id,name,slug'])
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->input('q').'%');
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('category_key')) {
            $category = ArticleCategory::query()
                ->where('slug', $request->input('category_key'))
                ->first();
            $query->when($category, fn ($q) => $q->where('article_category_id', $category->id))
                ->when(! $category, fn ($q) => $q->where('category_key', $request->input('category_key')));
        }

        $perPage = min($request->integer('per_page', 20), 100);
        $queryStart = microtime(true);
        $articles = $query->paginate($perPage);
        $queryMs = (microtime(true) - $queryStart) * 1000;

        $transformStart = microtime(true);
        $items = $articles->map(fn ($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'slug' => $a->slug,
            'excerpt' => $a->excerpt,
            'category_key' => $a->category_key,
            'article_category_id' => $a->article_category_id,
            'article_category' => $a->articleCategory ? [
                'id' => $a->articleCategory->id,
                'name' => $a->articleCategory->name,
                'slug' => $a->articleCategory->slug,
            ] : null,
            'content_slots' => $a->content_slots ?? [],
            'active' => $a->active,
            'cover_image_url' => $a->cover_image_url,
            'cover_image_canonical_url' => $a->coverImage?->canonical_url,
            'published_at' => $a->published_at?->toIso8601String(),
            'created_at' => $a->created_at?->toIso8601String(),
        ]);
        $transformMs = (microtime(true) - $transformStart) * 1000;

        $meta = [
            'current_page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
            'per_page' => $articles->perPage(),
            'total' => $articles->total(),
        ];

        $audit = $this->buildAudit($request, $controllerStart, $queryMs, $transformMs);
        if ($audit) {
            $meta['audit'] = $audit;
        }

        return response()->json([
            'data' => $items,
            'meta' => $meta,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $controllerStart = microtime(true);
        $queryStart = microtime(true);
        $article = Article::with(['coverImage', 'images', 'articleCategory:id,name,slug'])->findOrFail($id);
        $queryMs = (microtime(true) - $queryStart) * 1000;

        $transformStart = microtime(true);
        $payload = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'category_key' => $article->category_key,
            'article_category_id' => $article->article_category_id,
            'article_category' => $article->articleCategory ? [
                'id' => $article->articleCategory->id,
                'name' => $article->articleCategory->name,
                'slug' => $article->articleCategory->slug,
            ] : null,
            'content_slots' => $article->content_slots ?? [],
            'content' => $article->content,
            'meta_title' => $article->meta_title,
            'meta_description' => $article->meta_description,
            'active' => $article->active,
            'cover_image_url' => $article->coverImage?->absolute_url ?? $article->cover_image_url,
            'cover_image_canonical_url' => $article->coverImage?->canonical_url,
            'images' => $article->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->absolute_url,
                'canonical_url' => $img->canonical_url,
                'canonical_key' => $img->canonical_key,
                'semantic_type' => $img->semantic_type,
                'path' => $img->file_path,
            ]),
            'published_at' => $article->published_at?->toIso8601String(),
            'created_at' => $article->created_at?->toIso8601String(),
            'updated_at' => $article->updated_at?->toIso8601String(),
        ];
        $transformMs = (microtime(true) - $transformStart) * 1000;

        $audit = $this->buildAudit($request, $controllerStart, $queryMs, $transformMs);
        $response = ['data' => $payload];
        if ($audit) {
            $response['meta'] = ['audit' => $audit];
        }

        return response()->json($response);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:articles,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_key' => ['nullable', 'string', 'max:255'],
            'article_category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'content_slots' => ['nullable', 'array'],
            'content_slots.*' => ['string', Rule::in(ArticleContentCatalog::slotKeys())],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['active'] = $validated['active'] ?? true;
        $validated['published_at'] = $validated['published_at'] ?? now();
        $validated['content_slots'] = array_values(array_unique($validated['content_slots'] ?? []));
        $validated = $this->resolveArticleCategory($validated);

        $imagePaths = $validated['image_paths'] ?? [];
        unset($validated['image_paths']);

        $article = Article::create($validated);

        if (! empty($imagePaths)) {
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
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_key' => ['nullable', 'string', 'max:255'],
            'article_category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'content_slots' => ['nullable', 'array'],
            'content_slots.*' => ['string', Rule::in(ArticleContentCatalog::slotKeys())],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'active' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['string'],
        ]);

        $imagePaths = $validated['image_paths'] ?? null;
        unset($validated['image_paths']);
        if (array_key_exists('content_slots', $validated)) {
            $validated['content_slots'] = array_values(array_unique($validated['content_slots'] ?? []));
        }
        $validated = $this->resolveArticleCategory($validated);

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

    private function buildAudit(Request $request, float $controllerStart, float $queryMs, float $transformMs): ?array
    {
        if (! $request->boolean('audit')) {
            return null;
        }

        $audit = $request->attributes->get('audit', []);
        $audit['query_ms'] = (int) round($queryMs);
        $audit['transform_ms'] = (int) round($transformMs);
        $audit['controller_ms'] = (int) round((microtime(true) - $controllerStart) * 1000);

        return $audit;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function resolveArticleCategory(array $validated): array
    {
        if (! empty($validated['article_category_id'])) {
            $category = ArticleCategory::find($validated['article_category_id']);
            $validated['category_key'] = $category?->slug;

            return $validated;
        }

        if (array_key_exists('category_key', $validated) && filled($validated['category_key'])) {
            $category = ArticleCategory::query()
                ->where('slug', $validated['category_key'])
                ->orWhere('name', $validated['category_key'])
                ->first();

            if ($category) {
                $validated['article_category_id'] = $category->id;
                $validated['category_key'] = $category->slug;
            }
        }

        if (array_key_exists('category_key', $validated) && blank($validated['category_key'])) {
            $validated['category_key'] = null;
            $validated['article_category_id'] = null;
        }

        return $validated;
    }
}

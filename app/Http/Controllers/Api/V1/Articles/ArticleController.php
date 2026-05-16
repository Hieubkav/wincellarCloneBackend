<?php

namespace App\Http\Controllers\Api\V1\Articles;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Articles\ArticleIndexRequest;
use App\Http\Resources\V1\ArticleCollection;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Content\ArticleContentCatalog;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): ArticleCollection
    {
        $query = Article::query()
            ->select('articles.*')
            ->with(['coverImage', 'articleCategory:id,name,slug'])
            ->active();

        // Support fetching by IDs (for home components preview)
        $ids = $request->input('ids');
        if ($ids) {
            // Handle comma-separated string or array
            if (is_string($ids)) {
                $ids = array_map('intval', array_filter(explode(',', $ids)));
            } elseif (is_array($ids)) {
                $ids = array_map('intval', array_filter($ids));
            }

            if (! empty($ids)) {
                $query->whereIn('articles.id', $ids);
            }
        }

        if ($request->filled('category_key')) {
            $this->applyCategoryFilter($query, $request->input('category_key'));
        }

        if ($request->filled('category_slug')) {
            $this->applyCategoryFilter($query, $request->input('category_slug'));
        }

        $this->applySorting($query, $request->input('sort'));

        $perPage = (int) $request->input('per_page', 12);
        $page = (int) $request->input('page', 1);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage, ['articles.*'], 'page', $page);

        return new ArticleCollection($paginator);
    }

    public function category(string $slug): JsonResponse
    {
        $category = ArticleCategory::query()
            ->select(['id', 'name', 'slug', 'description', 'active', 'position'])
            ->withCount(['articles' => fn ($query) => $query->active()])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $category) {
            throw ApiException::notFound('Article category', $slug);
        }

        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'active' => $category->active,
                'position' => $category->position,
                'articles_count' => $category->articles_count,
            ],
        ]);
    }

    public function show(string $slug): JsonResource
    {
        $article = Article::query()
            ->with(['coverImage', 'images', 'author', 'articleCategory:id,name,slug'])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $article) {
            throw ApiException::notFound('Article', $slug);
        }

        // Load related articles (3 most recent, excluding current)
        $relatedArticles = Article::query()
            ->select('articles.*')
            ->with(['coverImage', 'articleCategory:id,name,slug'])
            ->active()
            ->where('id', '!=', $article->id)
            ->when($article->article_category_id, fn ($query) => $query->where('article_category_id', $article->article_category_id))
            ->when(! $article->article_category_id && $article->category_key, fn ($query) => $query->where('category_key', $article->category_key))
            ->latest('created_at')
            ->limit(3)
            ->get();

        $article->setRelation('relatedArticles', $relatedArticles);

        return new ArticleResource($article);
    }

    public function showInCategory(string $categorySlug, string $slug): JsonResource
    {
        $article = Article::query()
            ->with(['coverImage', 'images', 'author', 'articleCategory:id,name,slug'])
            ->active()
            ->where('slug', $slug)
            ->whereHas('articleCategory', fn ($query) => $query->where('slug', $categorySlug)->active())
            ->first();

        if (! $article) {
            throw ApiException::notFound('Article', "{$categorySlug}/{$slug}");
        }

        $relatedArticles = Article::query()
            ->select('articles.*')
            ->with(['coverImage', 'articleCategory:id,name,slug'])
            ->active()
            ->where('id', '!=', $article->id)
            ->where('article_category_id', $article->article_category_id)
            ->latest('created_at')
            ->limit(3)
            ->get();

        $article->setRelation('relatedArticles', $relatedArticles);

        return new ArticleResource($article);
    }

    public function contentPage(string $section, string $slug): JsonResource
    {
        $slot = "{$section}/{$slug}";
        if (! in_array($slot, ArticleContentCatalog::slotKeys(), true)) {
            throw ApiException::notFound('Content page', $slot);
        }

        $article = Article::query()
            ->with(['coverImage', 'images', 'author', 'articleCategory:id,name,slug'])
            ->active()
            ->whereJsonContains('content_slots', $slot)
            ->latest('published_at')
            ->latest('created_at')
            ->first();

        if (! $article) {
            throw ApiException::notFound('Content page', $slot);
        }

        $relatedArticles = Article::query()
            ->select('articles.*')
            ->with(['coverImage', 'articleCategory:id,name,slug'])
            ->active()
            ->where('id', '!=', $article->id)
            ->when($article->article_category_id, fn ($query) => $query->where('article_category_id', $article->article_category_id))
            ->when(! $article->article_category_id && $article->category_key, fn ($query) => $query->where('category_key', $article->category_key))
            ->latest('created_at')
            ->limit(3)
            ->get();

        $article->setRelation('relatedArticles', $relatedArticles);

        return new ArticleResource($article);
    }

    private function applySorting(Builder $query, ?string $sort): void
    {
        $sortKey = $sort ?: '-created_at';

        $mapping = [
            'created_at' => ['created_at', 'asc'],
            '-created_at' => ['created_at', 'desc'],
            'title' => ['title', 'asc'],
            '-title' => ['title', 'desc'],
        ];

        $config = $mapping[$sortKey] ?? $mapping['-created_at'];

        $query->orderBy($config[0], $config[1]);
    }

    private function applyCategoryFilter(Builder $query, string $slug): void
    {
        $category = ArticleCategory::query()->where('slug', $slug)->first();

        if ($category) {
            $query->where('article_category_id', $category->id);

            return;
        }

        $query->where('category_key', $slug);
    }
}

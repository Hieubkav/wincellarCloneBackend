<?php

namespace App\Http\Controllers\Api\V1\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Articles\ArticleIndexRequest;
use App\Models\Article;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): JsonResponse
    {
        $query = Article::query()
            ->select('articles.*')
            ->with(['coverImage'])
            ->active();

        $this->applySorting($query, $request->input('sort'));

        $perPage = (int) $request->input('per_page', 12);
        $page = (int) $request->input('page', 1);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage, ['articles.*'], 'page', $page);

        $collection = $paginator->getCollection()->map(function (Article $article) {
            return $this->transformListArticle($article);
        });

        return response()->json([
            'data' => $collection,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'sort' => $request->input('sort', '-created_at'),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->with(['coverImage', 'images', 'author'])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->transformDetailArticle($article),
        ]);
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

    private function transformListArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'cover_image_url' => $article->cover_image_url,
            'published_at' => optional($article->created_at)->toIso8601String(),
        ];
    }

    private function transformDetailArticle(Article $article): array
    {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'cover_image_url' => $article->cover_image_url,
            'gallery' => $article->gallery_for_output->all(),
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
            ] : null,
            'published_at' => optional($article->created_at)->toIso8601String(),
            'meta' => [
                'title' => $article->meta_title,
                'description' => $article->meta_description,
            ],
        ];
    }
}

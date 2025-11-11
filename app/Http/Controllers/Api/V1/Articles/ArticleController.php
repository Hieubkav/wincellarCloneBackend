<?php

namespace App\Http\Controllers\Api\V1\Articles;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Articles\ArticleIndexRequest;
use App\Http\Resources\V1\ArticleCollection;
use App\Http\Resources\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleController extends Controller
{
    public function index(ArticleIndexRequest $request): ArticleCollection
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

        return new ArticleCollection($paginator);
    }

    public function show(string $slug): JsonResource
    {
        $article = Article::query()
            ->with(['coverImage', 'images', 'author'])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (!$article) {
            throw ApiException::notFound('Article', $slug);
        }

        // Load related articles (3 most recent, excluding current)
        $relatedArticles = Article::query()
            ->select('articles.*')
            ->with(['coverImage'])
            ->active()
            ->where('id', '!=', $article->id)
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
}

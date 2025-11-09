<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ArticleResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => $this->meta(),
            '_links' => $this->links(),
        ];
    }

    /**
     * Get the pagination meta data.
     */
    protected function meta(): array
    {
        $paginator = $this->resource;

        return [
            'pagination' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'sorting' => [
                'sort' => request()->input('sort', '-created_at'),
            ],
            'filtering' => [
                'author' => request()->input('author'),
                'q' => request()->input('q'),
            ],
            'api_version' => 'v1',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get HATEOAS navigation links.
     */
    protected function links(): array
    {
        $paginator = $this->resource;
        $params = request()->query();

        $links = [
            'self' => [
                'href' => route('api.v1.articles.index', $params),
                'method' => 'GET',
            ],
            'first' => [
                'href' => route('api.v1.articles.index', array_merge($params, ['page' => 1])),
                'method' => 'GET',
            ],
        ];

        if ($paginator->currentPage() > 1) {
            $links['prev'] = [
                'href' => route('api.v1.articles.index', array_merge($params, ['page' => $paginator->currentPage() - 1])),
                'method' => 'GET',
            ];
        }

        if ($paginator->hasMorePages()) {
            $links['next'] = [
                'href' => route('api.v1.articles.index', array_merge($params, ['page' => $paginator->currentPage() + 1])),
                'method' => 'GET',
            ];
        }

        $links['last'] = [
            'href' => route('api.v1.articles.index', array_merge($params, ['page' => $paginator->lastPage()])),
            'method' => 'GET',
        ];

        return $links;
    }
}

<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            
            // Conditional fields for detail view
            'content' => $this->when($request->routeIs('api.v1.articles.show'), $this->content),
            
            // Images
            'cover_image_url' => $this->cover_image_url ?: '/placeholder/article.svg',
            
            'gallery' => $this->when(
                $request->routeIs('api.v1.articles.show') && $this->relationLoaded('images'),
                $this->gallery_for_output
            ),
            
            // Author (detail view only)
            'author' => $this->when($request->routeIs('api.v1.articles.show') && $this->relationLoaded('author') && $this->author, [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            
            // Timestamps
            'published_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->when($request->routeIs('api.v1.articles.show'), $this->updated_at?->toIso8601String()),
            
            // SEO meta (detail view only)
            'meta' => $this->when($request->routeIs('api.v1.articles.show'), [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
            ]),
            
            // HATEOAS links
            '_links' => [
                'self' => [
                    'href' => route('api.v1.articles.show', ['slug' => $this->slug]),
                    'method' => 'GET',
                ],
                'list' => [
                    'href' => route('api.v1.articles.index'),
                    'method' => 'GET',
                ],
                'author' => $this->when($this->author, [
                    'href' => route('api.v1.articles.index', ['author' => $this->author_id]),
                    'method' => 'GET',
                ]),
                'related' => $this->when($request->routeIs('api.v1.articles.show'), [
                    'href' => route('api.v1.articles.index', ['per_page' => 6]),
                    'method' => 'GET',
                ]),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => 'v1',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}

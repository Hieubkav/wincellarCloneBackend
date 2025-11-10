<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ArticleObserver
{
    /**
     * Increment API cache version when article data changes
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
    }
    /**
     * Handle the Article "creating" event.
     * Tự động sinh slug và SEO fields khi tạo mới
     */
    public function creating(Article $article): void
    {
        if (empty($article->slug)) {
            $article->slug = $this->generateUniqueSlug($article->title);
        }

        $this->generateSeoFields($article);
    }

    /**
     * Handle the Article "updating" event.
     * Tự động cập nhật slug khi title thay đổi
     */
    public function updating(Article $article): void
    {
        if ($article->isDirty('title')) {
            $article->slug = $this->generateUniqueSlug($article->title, $article->id);
            $this->generateSeoFields($article);
        }
    }

    /**
     * Generate SEO fields if empty
     */
    private function generateSeoFields(Article $article): void
    {
        if (empty($article->meta_title)) {
            $article->meta_title = $article->title;
        }

        if (empty($article->meta_description) && !empty($article->excerpt)) {
            $article->meta_description = Str::limit($article->excerpt, 155);
        }
    }

    /**
     * Generate unique slug for article
     */
    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Article::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function created(Article $article): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(Article $article): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(Article $article): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(Article $article): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(Article $article): void
    {
        $this->incrementCacheVersion();
    }
}

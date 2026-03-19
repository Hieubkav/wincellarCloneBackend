<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    private static ?int $proxyCacheVersion = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_path',
        'disk',
        'alt',
        'width',
        'height',
        'mime',
        'model_type',
        'model_id',
        'order',
        'active',
        'extra_attributes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'int',
            'height' => 'int',
            'order' => 'int',
            'active' => 'bool',
            'extra_attributes' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Image $image): void {
            $image->ensureOrderValue();
        });

        static::saving(function (Image $image): void {
            $image->ensureOrderValue();

            if ($image->order === 0) {
                $image->reassignExistingCover();
            }
        });

        static::forceDeleted(function (Image $image): void {
            $image->detachPrimaryReferences();
            $image->deleteFile();
        });

        static::deleted(function (Image $image): void {
            if (! $image->isForceDeleting()) {
                // Soft delete branch: still detach references to avoid dangling FK.
                $image->detachPrimaryReferences();
            }
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get relative URL (for backward compatibility)
     *
     * @deprecated Use getAbsoluteUrlAttribute() instead
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        // Return relative path (kept for backward compatibility)
        return '/storage/'.ltrim($this->file_path, '/');
    }

    /**
     * Get absolute URL using service layer
     * THIS IS THE RECOMMENDED ACCESSOR
     */
    public function getAbsoluteUrlAttribute(): string
    {
        // Delegate to service layer (Facade pattern)
        return app(\App\Services\ImageFallbackService::class)
            ->getUrlWithFallback($this, $this->getImageType());
    }

    /**
     * Get proxy URL (recommended for product images with watermark protection)
     * Example: http://localhost:8000/api/v1/images/123?v=5
     *
     * Includes cache version param to bust Next.js/browser cache when watermark settings change
     */
    public function getProxyUrlAttribute(): string
    {
        $baseUrl = config('app.url');
        $cacheVersion = static::getProxyCacheVersion();

        return "{$baseUrl}/api/v1/images/{$this->id}?v={$cacheVersion}";
    }

    public static function primeProxyCacheVersion(?int $version = null): int
    {
        static::$proxyCacheVersion = $version ?? (int) \Cache::get('api_cache_version', 0);

        return static::$proxyCacheVersion;
    }

    private static function getProxyCacheVersion(): int
    {
        if (static::$proxyCacheVersion === null) {
            static::$proxyCacheVersion = (int) \Cache::get('api_cache_version', 0);
        }

        return static::$proxyCacheVersion;
    }

    /**
     * Determine image type from model relationship
     */
    private function getImageType(): string
    {
        if (! $this->model_type) {
            return 'default';
        }

        return match ($this->model_type) {
            'App\\Models\\Product' => 'product',
            'App\\Models\\Article' => 'article',
            'App\\Models\\CatalogTerm' => 'term',
            default => 'default',
        };
    }

    private function ensureOrderValue(): void
    {
        if ($this->order === null) {
            $this->order = $this->nextOrderValue();
        }
    }

    private function reassignExistingCover(): void
    {
        if (! $this->model_type || ! $this->model_id) {
            return;
        }

        $query = static::query()
            ->where('model_type', $this->model_type)
            ->where('model_id', $this->model_id)
            ->where('order', 0);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        $currentCover = $query->first();

        if ($currentCover) {
            $currentCover->order = $currentCover->nextOrderValue();
            $currentCover->saveQuietly();
        }
    }

    private function nextOrderValue(): int
    {
        if (! $this->model_type || ! $this->model_id) {
            return 1;
        }

        $maxOrder = static::query()
            ->where('model_type', $this->model_type)
            ->where('model_id', $this->model_id)
            ->max('order');

        if ($maxOrder === null) {
            return $this->order === 0 ? 0 : 1;
        }

        if ($this->order === 0) {
            return 0;
        }

        return $maxOrder + 1;
    }

    private function detachPrimaryReferences(): void
    {
        $affected = [];

        if ($this->isReferencedInSettings()) {
            $affected[] = 'settings';
        }

        if ($this->isReferencedInSocialLinks()) {
            $affected[] = 'social_links';
        }

        if (! empty($affected)) {
            \Log::info('Image references nullified on delete', [
                'image_id' => $this->id,
                'tables' => $affected,
            ]);
        }
    }

    private function isReferencedInSettings(): bool
    {
        $logoUpdated = \DB::table('settings')
            ->where('logo_image_id', $this->id)
            ->update([
                'logo_image_id' => null,
                'updated_at' => now(),
            ]);

        $faviconUpdated = \DB::table('settings')
            ->where('favicon_image_id', $this->id)
            ->update([
                'favicon_image_id' => null,
                'updated_at' => now(),
            ]);

        $ogImageUpdated = \DB::table('settings')
            ->where('og_image_id', $this->id)
            ->update([
                'og_image_id' => null,
                'updated_at' => now(),
            ]);

        $watermarkUpdated = \DB::table('settings')
            ->where('product_watermark_image_id', $this->id)
            ->update([
                'product_watermark_image_id' => null,
                'updated_at' => now(),
            ]);

        return ($logoUpdated + $faviconUpdated + $ogImageUpdated + $watermarkUpdated) > 0;
    }

    private function isReferencedInSocialLinks(): bool
    {
        $updated = \DB::table('social_links')
            ->where('icon_image_id', $this->id)
            ->update([
                'icon_image_id' => null,
                'updated_at' => now(),
            ]);

        return $updated > 0;
    }

    private function deleteFile(): void
    {
        if (! $this->disk || ! $this->file_path) {
            return;
        }

        \Storage::disk($this->disk)->delete($this->file_path);
    }
}

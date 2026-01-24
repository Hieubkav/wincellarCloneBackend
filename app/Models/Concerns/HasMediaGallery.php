<?php

namespace App\Models\Concerns;

use App\Models\Image;
use App\Support\Media\MediaConfig;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

trait HasMediaGallery
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Image>
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'model')
            ->orderBy('order');
    }

    /**
     * Get cover image (image with minimum order value).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\App\Models\Image>
     */
    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')
            ->orderBy('order', 'asc');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        // If images are already loaded, use first from collection (already ordered by 'order' asc)
        if ($this->relationLoaded('images')) {
            $firstImage = $this->getRelation('images')->first();
            if ($firstImage instanceof Image) {
                return $firstImage->url;
            }
        }

        // Otherwise, load cover image (min order)
        $cover = $this->relationLoaded('coverImage')
            ? $this->getRelation('coverImage')
            : $this->coverImage;

        if ($cover instanceof Image) {
            return $cover->url;
        }

        return MediaConfig::placeholder($this->mediaPlaceholderKey());
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function getGalleryForOutputAttribute(): Collection
    {
        $images = $this->relationLoaded('images')
            ? $this->getRelation('images')
            : $this->images;

        return $images->map(fn (Image $image) => [
            'id' => $image->id,
            'url' => $image->url,
            'alt' => $image->alt,
            'order' => $image->order,
            'width' => $image->width,
            'height' => $image->height,
        ]);
    }

    public function syncImagesFromPaths(array $paths): void
    {
        $paths = array_values(array_filter($paths));

        \Log::info('[syncImagesFromPaths] Model: '.get_class($this).'#'.$this->id);
        \Log::info('[syncImagesFromPaths] Received paths count: '.count($paths));
        \Log::info('[syncImagesFromPaths] Paths: '.json_encode($paths));
        \Log::info('[syncImagesFromPaths] Current images count: '.$this->images()->count());

        $this->images()->delete();

        foreach ($paths as $index => $path) {
            $this->images()->create([
                'file_path' => $path,
                'disk' => 'public',
                'order' => $index,
            ]);
        }

        \Log::info('[syncImagesFromPaths] After sync, images count: '.$this->images()->count());
    }

    protected function mediaPlaceholderKey(): string
    {
        return 'default';
    }
}

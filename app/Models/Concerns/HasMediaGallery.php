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
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\App\Models\Image>
     */
    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'model')
            ->where('order', 0);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
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

    protected function mediaPlaceholderKey(): string
    {
        return 'default';
    }
}

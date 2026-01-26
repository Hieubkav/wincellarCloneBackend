<?php

namespace App\Services;

use App\Contracts\ImageStorageStrategy;
use App\Models\Image;
use App\Services\ImageStorage\CDNStorageStrategy;
use App\Services\ImageStorage\LocalStorageStrategy;
use App\Services\ImageStorage\S3StorageStrategy;
use App\ValueObjects\MediaConfig;
use Illuminate\Support\Collection;

class ImageUrlService
{
    private Collection $strategies;

    public function __construct(
        private MediaConfig $config,
        LocalStorageStrategy $local,
        S3StorageStrategy $s3,
        CDNStorageStrategy $cdn
    ) {
        // Register strategies in priority order (highest first)
        $this->strategies = collect([
            $cdn,
            $s3,
            $local,
        ])->sortByDesc(fn(ImageStorageStrategy $s) => $s->getPriority());
    }

    /**
     * Get absolute URL for image using strategy pattern
     */
    public function getAbsoluteUrl(Image $image): ?string
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($image)) {
                $url = $strategy->getAbsoluteUrl($image);
                
                if ($url) {
                    return $url;
                }
            }
        }

        // No strategy could generate URL
        return null;
    }

    /**
     * Get placeholder URL for entity type
     */
    public function getPlaceholderUrl(string $type = 'default'): string
    {
        // Use first supporting strategy (usually CDN in production)
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports(new Image(['disk' => 'public']))) {
                return $strategy->getPlaceholderUrl($type);
            }
        }

        // Ultimate fallback
        return $this->strategies->last()->getPlaceholderUrl($type);
    }
}

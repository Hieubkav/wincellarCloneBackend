<?php

namespace App\Services;

use App\Models\Image;

class ImageFallbackService
{
    public function __construct(
        private ImageUrlService $urlService
    ) {}

    /**
     * Get image URL with fallback chain:
     * 1. Try actual image URL
     * 2. Try type-specific placeholder
     * 3. Default placeholder
     * 4. Transparent pixel (last resort)
     */
    public function getUrlWithFallback(?Image $image, string $type = 'default'): string
    {
        // Priority 1: Actual image
        if ($image) {
            $url = $this->urlService->getAbsoluteUrl($image);
            if ($url) {
                return $url;
            }
        }

        // Priority 2: Type-specific placeholder
        $placeholder = $this->urlService->getPlaceholderUrl($type);
        if ($placeholder) {
            return $placeholder;
        }

        // Priority 3: Default placeholder
        $default = $this->urlService->getPlaceholderUrl('default');
        if ($default) {
            return $default;
        }

        // Priority 4: Transparent pixel (SVG data URI - never fails)
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="1" height="1"%3E%3C/svg%3E';
    }

    /**
     * Batch process multiple images
     */
    public function getUrlsWithFallback(iterable $images, string $type = 'default'): array
    {
        $urls = [];
        
        foreach ($images as $image) {
            $urls[] = $this->getUrlWithFallback($image, $type);
        }

        return $urls;
    }
}

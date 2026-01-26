<?php

namespace App\Contracts;

use App\Models\Image;

interface ImageStorageStrategy
{
    /**
     * Generate absolute URL for image
     */
    public function getAbsoluteUrl(Image $image): ?string;

    /**
     * Generate absolute URL for placeholder
     */
    public function getPlaceholderUrl(string $type): string;

    /**
     * Check if this strategy supports the given image
     */
    public function supports(Image $image): bool;

    /**
     * Get strategy priority (higher = higher priority)
     */
    public function getPriority(): int;
}

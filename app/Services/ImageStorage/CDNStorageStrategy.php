<?php

namespace App\Services\ImageStorage;

use App\Contracts\ImageStorageStrategy;
use App\Models\Image;
use App\ValueObjects\MediaConfig;

class CDNStorageStrategy implements ImageStorageStrategy
{
    public function __construct(
        private MediaConfig $config
    ) {}

    public function getAbsoluteUrl(Image $image): ?string
    {
        if (!$image->file_path || !$this->config->shouldUseCdn()) {
            return null;
        }

        // CDN best practice: Always return absolute URL with CDN domain
        $cdnUrl = rtrim($this->config->cdnUrl, '/');
        $filePath = ltrim($image->file_path, '/');
        
        return "{$cdnUrl}/{$filePath}";
    }

    public function getPlaceholderUrl(string $type): string
    {
        $placeholderPath = $this->config->getPlaceholder($type);
        $cdnUrl = rtrim($this->config->cdnUrl, '/');
        
        return "{$cdnUrl}/" . ltrim($placeholderPath, '/');
    }

    public function supports(Image $image): bool
    {
        return $this->config->shouldUseCdn();
    }

    public function getPriority(): int
    {
        return 30; // Highest priority (use CDN if available)
    }
}

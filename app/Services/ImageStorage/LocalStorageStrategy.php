<?php

namespace App\Services\ImageStorage;

use App\Contracts\ImageStorageStrategy;
use App\Models\Image;
use App\ValueObjects\MediaConfig;
use Illuminate\Support\Facades\Storage;

class LocalStorageStrategy implements ImageStorageStrategy
{
    public function __construct(
        private MediaConfig $config
    ) {}

    public function getAbsoluteUrl(Image $image): ?string
    {
        if (!$image->file_path) {
            return null;
        }

        // Laravel best practice: Use Storage::url() for proper URL generation
        if ($image->disk && Storage::disk($image->disk)->exists($image->file_path)) {
            $url = Storage::disk($image->disk)->url($image->file_path);
            
            // Ensure absolute URL
            return $this->ensureAbsolute($url);
        }

        // Fallback: construct URL manually
        $baseUrl = $this->config->getBaseUrl('local');
        return rtrim($baseUrl, '/') . '/storage/' . ltrim($image->file_path, '/');
    }

    public function getPlaceholderUrl(string $type): string
    {
        $placeholderPath = $this->config->getPlaceholder($type);
        $baseUrl = $this->config->getBaseUrl('local');
        
        return rtrim($baseUrl, '/') . '/storage/' . ltrim($placeholderPath, '/');
    }

    public function supports(Image $image): bool
    {
        return $this->config->storageDriver === 'local' 
            && $image->disk === 'public';
    }

    public function getPriority(): int
    {
        return 10; // Lowest priority
    }

    private function ensureAbsolute(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $baseUrl = $this->config->getBaseUrl('local');
        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }
}

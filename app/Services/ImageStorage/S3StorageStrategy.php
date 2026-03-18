<?php

namespace App\Services\ImageStorage;

use App\Contracts\ImageStorageStrategy;
use App\Models\Image;
use App\ValueObjects\MediaConfig;
use Illuminate\Support\Facades\Storage;

class S3StorageStrategy implements ImageStorageStrategy
{
    public function __construct(
        private MediaConfig $config
    ) {}

    public function getAbsoluteUrl(Image $image): ?string
    {
        if (! $image->file_path) {
            return null;
        }

        // Laravel Storage facade handles S3 URL generation
        if ($image->disk === 's3' && Storage::disk('s3')->exists($image->file_path)) {
            // Returns absolute S3 URL or CloudFront URL if configured
            return Storage::disk('s3')->url($image->file_path);
        }

        return null;
    }

    public function getPlaceholderUrl(string $type): string
    {
        $placeholderPath = $this->config->getPlaceholder($type);

        // Check if placeholder exists in S3
        if (Storage::disk('s3')->exists($placeholderPath)) {
            return Storage::disk('s3')->url($placeholderPath);
        }

        // Fallback to local placeholder
        $baseUrl = $this->config->getBaseUrl('local');

        return rtrim($baseUrl, '/').'/storage/'.ltrim($placeholderPath, '/');
    }

    public function supports(Image $image): bool
    {
        return $image->disk === 's3';
    }

    public function getPriority(): int
    {
        return 20; // Medium priority
    }
}

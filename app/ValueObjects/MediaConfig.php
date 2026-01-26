<?php

namespace App\ValueObjects;

class MediaConfig
{
    private function __construct(
        public readonly string $storageDriver,
        public readonly array $baseUrls,
        public readonly array $paths,
        public readonly array $placeholders,
        public readonly bool $cdnEnabled,
        public readonly ?string $cdnUrl,
        public readonly string $cacheControl,
        public readonly bool $optimizationEnabled,
        public readonly int $imageQuality,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            storageDriver: config('media.storage_driver', 'local'),
            baseUrls: config('media.base_urls', []),
            paths: config('media.paths', []),
            placeholders: config('media.placeholders', []),
            cdnEnabled: config('media.cdn.enabled', false),
            cdnUrl: config('media.cdn.url'),
            cacheControl: config('media.cdn.cache_control', 'public, max-age=31536000'),
            optimizationEnabled: config('media.optimization.enabled', true),
            imageQuality: config('media.optimization.quality', 85),
        );
    }

    public function getBaseUrl(string $driver = null): string
    {
        $driver = $driver ?? $this->storageDriver;
        
        return $this->baseUrls[$driver] ?? $this->baseUrls['local'] ?? '';
    }

    public function getPlaceholder(string $type): string
    {
        return $this->placeholders[$type] ?? $this->placeholders['default'];
    }

    public function shouldUseCdn(): bool
    {
        return $this->cdnEnabled && !empty($this->cdnUrl);
    }

    public function getStoragePath(string $type = 'images'): string
    {
        return $this->paths[$type] ?? $this->paths['images'];
    }

    /**
     * Validate configuration on boot
     */
    public function validate(): void
    {
        if ($this->cdnEnabled && empty($this->cdnUrl)) {
            \Log::warning('CDN is enabled but MEDIA_CDN_URL is not set. Falling back to local storage.');
        }

        if (!in_array($this->storageDriver, ['local', 's3', 'cdn'])) {
            throw new \InvalidArgumentException("Invalid storage driver: {$this->storageDriver}");
        }
    }
}

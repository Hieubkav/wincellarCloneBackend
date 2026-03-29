<?php

namespace App\Services\Media;

use App\Models\Image;
use App\Support\Media\MediaSemanticRegistry;
use Illuminate\Support\Str;

class MediaCanonicalService
{
    private const BRAND_SUFFIX = 'thien-kim-wine';

    public function resolveSemanticType(Image $image, ?string $fallback = null): string
    {
        $semantic = MediaSemanticRegistry::normalize($image->semantic_type)
            ?? MediaSemanticRegistry::fromModelType($image->model_type)
            ?? MediaSemanticRegistry::normalize($fallback)
            ?? MediaSemanticRegistry::SHARED;

        return $semantic;
    }

    public function resolveCanonicalKey(Image $image): string
    {
        if (! empty($image->canonical_key)) {
            return $image->canonical_key;
        }

        if ($image->id) {
            return $this->encodeId($image->id);
        }

        return $this->generateRandomKey();
    }

    public function resolveCanonicalSlug(Image $image, ?string $fallbackBase = null): string
    {
        if (! empty($image->canonical_slug)) {
            return $image->canonical_slug;
        }

        $base = $image->alt
            ?? $this->basename($image->file_path)
            ?? $fallbackBase
            ?? $this->resolveSemanticType($image);

        return $this->buildSlug((string) $base);
    }

    public function getCanonicalUrl(Image $image): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $semantic = $this->resolveSemanticType($image);
        $key = $this->resolveCanonicalKey($image);
        $slug = $this->resolveCanonicalSlug($image, $semantic);

        return "{$baseUrl}/media/{$semantic}/{$key}/{$slug}";
    }

    public function ensureMetadata(Image $image, ?string $semanticType = null, ?string $slugBase = null): void
    {
        $semanticType = MediaSemanticRegistry::normalize($semanticType)
            ?? MediaSemanticRegistry::normalize($image->semantic_type)
            ?? MediaSemanticRegistry::fromModelType($image->model_type)
            ?? MediaSemanticRegistry::SHARED;

        if ($image->semantic_type !== $semanticType) {
            $image->semantic_type = $semanticType;
        }

        if (empty($image->canonical_key)) {
            $image->canonical_key = $this->resolveCanonicalKey($image);
        }

        if (empty($image->canonical_slug)) {
            $image->canonical_slug = $this->resolveCanonicalSlug($image, $slugBase);
        }
    }

    public function resolveByKey(string $key): ?Image
    {
        $image = Image::query()->where('canonical_key', $key)->first();

        if ($image) {
            return $image;
        }

        if (ctype_digit($key)) {
            return Image::find((int) $key);
        }

        $decodedId = $this->decodeKeyToId($key);

        return $decodedId ? Image::find($decodedId) : null;
    }

    private function basename(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        $base = pathinfo($filePath, PATHINFO_FILENAME);

        return $base !== '' ? $base : null;
    }

    private function buildSlug(string $base): string
    {
        $slug = Str::of($base)->slug('-')->toString();

        if ($slug === '') {
            $slug = 'media';
        }

        if (! Str::endsWith($slug, self::BRAND_SUFFIX)) {
            $slug = "{$slug}-".self::BRAND_SUFFIX;
        }

        return $slug;
    }

    private function encodeId(int $id): string
    {
        return strtolower(base_convert((string) $id, 10, 36));
    }

    private function decodeKeyToId(string $key): ?int
    {
        if (! preg_match('/^[0-9a-z]+$/', $key)) {
            return null;
        }

        $decoded = base_convert(strtolower($key), 36, 10);

        return ctype_digit($decoded) ? (int) $decoded : null;
    }

    private function generateRandomKey(): string
    {
        return strtolower((string) Str::ulid());
    }
}

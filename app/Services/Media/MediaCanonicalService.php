<?php

namespace App\Services\Media;

use App\Models\Image;
use App\Support\Media\MediaSemanticRegistry;
use Illuminate\Support\Str;

class MediaCanonicalService
{
    private const BRAND_PREFIX = 'thien-kim-wine';

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

    public function resolveCanonicalSlug(Image $image, ?string $fallbackBase = null, ?int $index = null): string
    {
        if (! empty($image->canonical_slug) && $this->isCanonicalSlug($image->canonical_slug)) {
            return $image->canonical_slug;
        }

        return $this->buildCanonicalSlug($image, $fallbackBase, $index);
    }

    public function getCanonicalUrl(Image $image): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $semantic = $this->resolveSemanticType($image);
        $key = $this->resolveCanonicalKey($image);
        $slug = $this->resolveCanonicalSlug($image, $semantic);

        return "{$baseUrl}/media/{$semantic}/{$key}/{$slug}";
    }

    public function ensureMetadata(
        Image $image,
        ?string $semanticType = null,
        ?string $slugBase = null,
        bool $persist = false
    ): void {
        $dirty = false;
        $semanticType = MediaSemanticRegistry::normalize($semanticType)
            ?? MediaSemanticRegistry::normalize($image->semantic_type)
            ?? MediaSemanticRegistry::fromModelType($image->model_type)
            ?? MediaSemanticRegistry::SHARED;

        if ($image->semantic_type !== $semanticType) {
            $image->semantic_type = $semanticType;
            $dirty = true;
        }

        if (empty($image->canonical_key)) {
            $image->canonical_key = $this->resolveCanonicalKey($image);
            $dirty = true;
        }

        if (empty($image->canonical_slug) || ! $this->isCanonicalSlug($image->canonical_slug)) {
            $image->canonical_slug = $this->resolveCanonicalSlug($image, $slugBase);
            $dirty = true;
        }

        if ($persist && $dirty) {
            $image->saveQuietly();
        }
    }

    /**
     * @return array<string, string|null>
     */
    public function metadataFor(
        Image $image,
        ?string $semanticType = null,
        ?string $slugBase = null,
        bool $persist = false
    ): array {
        $this->ensureMetadata($image, $semanticType, $slugBase, $persist);

        return [
            'canonical_url' => $this->getCanonicalUrl($image),
            'canonical_key' => $image->canonical_key ?: $this->resolveCanonicalKey($image),
            'canonical_slug' => $image->canonical_slug ?: $this->resolveCanonicalSlug($image, $slugBase),
            'semantic_type' => $image->semantic_type ?: $this->resolveSemanticType($image, $semanticType),
            'storage_disk' => $image->disk,
            'storage_key' => $image->file_path,
        ];
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

    public function buildCanonicalSlug(Image $image, ?string $fallbackBase = null, ?int $index = null): string
    {
        $semantic = $this->resolveSemanticType($image);
        $typeLabel = $this->resolveTypeLabel($semantic);
        $entityBase = $this->resolveEntityBase($image, $fallbackBase);
        $entitySlug = Str::of($entityBase)->slug('-')->toString();

        if ($entitySlug === '') {
            $entitySlug = $typeLabel;
        }

        $position = $index ?? ($image->order ?? 0) + 1;
        if ($position < 1) {
            $position = 1;
        }

        return sprintf(
            '%s-%s-%s-anh-%d',
            self::BRAND_PREFIX,
            $typeLabel,
            $entitySlug,
            $position
        );
    }

    public function makeCanonicalSlug(string $semantic, string $entityBase, int $index): string
    {
        $typeLabel = $this->resolveTypeLabel($semantic);
        $entitySlug = Str::of($entityBase)->slug('-')->toString();

        if ($entitySlug === '') {
            $entitySlug = $typeLabel;
        }

        $position = $index < 1 ? 1 : $index;

        return sprintf(
            '%s-%s-%s-anh-%d',
            self::BRAND_PREFIX,
            $typeLabel,
            $entitySlug,
            $position
        );
    }

    private function resolveTypeLabel(string $semantic): string
    {
        return match ($semantic) {
            MediaSemanticRegistry::PRODUCT => 'san-pham',
            MediaSemanticRegistry::ARTICLE => 'bai-viet',
            MediaSemanticRegistry::SETTINGS_LOGO => 'logo',
            MediaSemanticRegistry::SETTINGS_FAVICON => 'favicon',
            MediaSemanticRegistry::SETTINGS_OG => 'og-image',
            MediaSemanticRegistry::SETTINGS_WATERMARK => 'watermark',
            MediaSemanticRegistry::SOCIAL => 'social',
            MediaSemanticRegistry::HOME => 'home',
            MediaSemanticRegistry::TERM => 'danh-muc',
            MediaSemanticRegistry::CONTENT => 'noi-dung',
            default => 'shared',
        };
    }

    private function resolveEntityBase(Image $image, ?string $fallbackBase = null): string
    {
        if ($image->model_type && $image->model_id) {
            $resolved = $this->resolveEntityFromModel($image);
            if ($resolved) {
                return $resolved;
            }
        }

        return $fallbackBase
            ?? $image->alt
            ?? $this->basename($image->file_path)
            ?? $this->resolveSemanticType($image);
    }

    private function resolveEntityFromModel(Image $image): ?string
    {
        return match ($image->model_type) {
            'App\\Models\\Product' => \App\Models\Product::query()
                ->whereKey($image->model_id)
                ->value('slug') ?? \App\Models\Product::query()->whereKey($image->model_id)->value('name'),
            'App\\Models\\Article' => \App\Models\Article::query()
                ->whereKey($image->model_id)
                ->value('slug') ?? \App\Models\Article::query()->whereKey($image->model_id)->value('title'),
            'App\\Models\\CatalogTerm' => \App\Models\CatalogTerm::query()
                ->whereKey($image->model_id)
                ->value('slug') ?? \App\Models\CatalogTerm::query()->whereKey($image->model_id)->value('name'),
            default => null,
        };
    }

    private function isCanonicalSlug(string $value): bool
    {
        return (bool) preg_match('/^'.self::BRAND_PREFIX.'-[a-z0-9\\-]+-[a-z0-9\\-]+-anh-\\d+$/', $value);
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

<?php

namespace App\Models\Concerns;

use App\Models\Image;
use App\Models\RichEditorMedia;
use App\Services\Media\MediaCanonicalService;
use App\Support\Media\MediaSemanticRegistry;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasRichEditorMedia
{
    /**
     * Lưu snapshot nội dung cũ để so sánh sau khi save.
     *
     * @var array<string, string|null>
     */
    protected array $richEditorOriginalContent = [];

    /**
     * Giới hạn kích thước file (5MB).
     */
    protected int $richEditorMaxImageSize = 5_000_000;

    public static function bootHasRichEditorMedia(): void
    {
        static::updating(function ($model) {
            $model->snapshotRichEditorContent();
        });

        static::saving(function ($model) {
            $model->convertBase64ImagesToFiles();
        });

        static::saved(function ($model) {
            $model->cleanupRemovedRichEditorImages();
            $model->syncRichEditorMedia();
        });

        static::deleted(function ($model) {
            // Bỏ qua soft delete để còn khôi phục; cleanup chỉ chạy khi xoá cứng.
            if (method_exists($model, 'isForceDeleting')) {
                return;
            }

            $model->deleteAllRichEditorImages();
            $model->richEditorMedia()->delete();
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::forceDeleted(function ($model) {
                $model->deleteAllRichEditorImages();
                $model->richEditorMedia()->delete();
            });
        }
    }

    public function richEditorMedia(): MorphMany
    {
        return $this->morphMany(RichEditorMedia::class, 'model');
    }

    protected function syncRichEditorMedia(): void
    {
        $richEditorFields = $this->getRichEditorFields();

        foreach ($richEditorFields as $fieldName) {
            $content = $this->getAttribute($fieldName);

            if (! $content) {
                $this->richEditorMedia()->where('field_name', $fieldName)->delete();

                continue;
            }

            $imagePaths = $this->extractImagePathsFromContent($content);

            $existingMedia = $this->richEditorMedia()
                ->where('field_name', $fieldName)
                ->pluck('file_path')
                ->toArray();

            $mediaToKeep = array_intersect($existingMedia, $imagePaths);
            $mediaToDelete = array_diff($existingMedia, $imagePaths);
            $mediaToAdd = array_diff($imagePaths, $existingMedia);

            $this->richEditorMedia()
                ->where('field_name', $fieldName)
                ->whereIn('file_path', $mediaToDelete)
                ->delete();

            foreach ($mediaToAdd as $imagePath) {
                $this->richEditorMedia()->create([
                    'field_name' => $fieldName,
                    'file_path' => $imagePath,
                    'disk' => 'public',
                ]);
            }
        }
    }

    protected function extractImagePathsFromContent(string $content): array
    {
        $paths = [];

        // 1) HTML img src
        preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches);
        if (! empty($matches[1])) {
            foreach ($matches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        // 2) data-url attr (một số editor lưu thêm)
        preg_match_all('/data-url=[\'"]([^\'"]+)[\'"]/', $content, $dataMatches);
        if (! empty($dataMatches[1])) {
            foreach ($dataMatches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        // 3) JSON "url":"..." (lexical output khi serialize)
        if (preg_match_all('/"url":"([^"]+)"/', $content, $jsonMatches)) {
            foreach ($jsonMatches[1] as $src) {
                $relative = $this->normalizeStoragePath($src);
                if ($relative) {
                    $paths[] = $relative;
                }
            }
        }

        return array_unique($paths);
    }

    protected function getRichEditorFields(): array
    {
        return property_exists($this, 'richEditorFields')
            ? $this->richEditorFields
            : [];
    }

    protected function convertBase64ImagesToFiles(): void
    {
        $richEditorFields = $this->getRichEditorFields();

        foreach ($richEditorFields as $fieldName) {
            $content = $this->getAttribute($fieldName);

            if (! $content) {
                continue;
            }

            $content = $this->convertBase64ToStorage($content);
            $content = $this->rewriteRichEditorUrlsToCanonical($content);
            $this->setAttribute($fieldName, $content);
        }
    }

    /**
     * Chuyển toàn bộ base64 image trong nội dung sang file lưu ở disk public.
     */
    protected function convertBase64ToStorage(string $content): string
    {
        preg_match_all(
            '/data:image\/(png|jpg|jpeg|gif|webp|svg\+xml);base64,([A-Za-z0-9+\/=]+)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            return $content;
        }

        foreach ($matches as $match) {
            $fullBase64 = $match[0];
            $extension = $match[1] === 'svg+xml' ? 'svg' : $match[1];
            $base64Data = $match[2];

            try {
                $filePath = $this->saveBase64AsFile($base64Data, $extension);
                $fileUrl = $this->richEditorPublicUrl($filePath);
                $content = str_replace($fullBase64, $fileUrl, $content);

                Log::info(sprintf(
                    'Converted base64 image for %s:%s -> %s',
                    static::class,
                    $this->getKey() ?? 'new',
                    $filePath
                ));
            } catch (\Exception $e) {
                Log::error('Failed to convert base64 image: '.$e->getMessage());

                continue;
            }
        }

        return $content;
    }

    /**
     * Rewrite legacy storage URLs trong rich editor sang canonical URL.
     */
    protected function rewriteRichEditorUrlsToCanonical(string $content): string
    {
        $rewriteValue = function (string $value): string {
            if ($value === '' || str_starts_with($value, 'data:image')) {
                return $value;
            }

            if ($this->isCanonicalMediaUrl($value)) {
                return $value;
            }

            $relative = $this->normalizeStoragePath($value);
            if (! $relative) {
                return $value;
            }

            $canonicalUrl = $this->resolveCanonicalUrlForStoragePath($relative);

            return $canonicalUrl ?? $value;
        };

        $replaceAttributeValue = function (string $source, string $attribute) use ($rewriteValue): string {
            return preg_replace_callback(
                '/('.$attribute.'=["\'])([^"\']+)(["\'])/i',
                function ($matches) use ($rewriteValue) {
                    $value = $rewriteValue($matches[2]);
                    return $matches[1].$value.$matches[3];
                },
                $source
            );
        };

        $content = $replaceAttributeValue($content, 'src');
        $content = $replaceAttributeValue($content, 'data-url');

        $content = preg_replace_callback(
            '/"url":"([^"]+)"/',
            function ($matches) use ($rewriteValue) {
                $value = $rewriteValue($matches[1]);
                return '"url":"'.$value.'"';
            },
            $content
        );

        return $content;
    }

    /**
     * Lưu base64 thành file và trả về relative path trên disk.
     */
    protected function saveBase64AsFile(string $base64Data, string $extension): string
    {
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            throw new \Exception('Failed to decode base64 data');
        }

        if (strlen($imageData) > $this->richEditorMaxImageSize) {
            throw new \Exception('Image exceeds maximum size of 5MB');
        }

        $baseDir = $this->richEditorContentDirectory();
        $datedDir = $baseDir.'/'.now()->format('Y/m/d');
        $filename = $this->richEditorFilenamePrefix().'-'.time().'-'.Str::random(8).'.'.$extension;
        $path = trim($datedDir, '/').'/'.$filename;

        $disk = Storage::disk($this->richEditorDisk());
        $directory = dirname($path);

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $saved = $disk->put($path, $imageData);

        if (! $saved) {
            throw new \Exception('Failed to save file to storage');
        }

        return $path;
    }

    /**
     * Xóa file và ghi log nhẹ nhàng, không throw.
     */
    protected function deleteImageFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $relativePath = ltrim($relativePath, '/');

        if ($this->isRichEditorImageInUseElsewhere($relativePath)) {
            return;
        }

        if ($this->isReferencedByGalleryImage($relativePath)) {
            return;
        }

        $disk = Storage::disk($this->richEditorDisk());

        try {
            if ($disk->exists($relativePath)) {
                $disk->delete($relativePath);
                Log::info(sprintf('Deleted rich-editor image: %s', $relativePath));
            }

            $this->deleteRichEditorImageRecord($relativePath);
        } catch (\Exception $e) {
            Log::error(sprintf('Error deleting image %s: %s', $relativePath, $e->getMessage()));
        }
    }

    /**
     * Tạo URL public cho ảnh rich editor.
     * Ưu tiên đường dẫn tương đối để không hardcode host (127.x) khi deploy.
     */
    protected function richEditorPublicUrl(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        // Local/public disk: trả về /storage/...
        if ($this->richEditorDisk() === 'public' && config('filesystems.disks.public.driver') === 'local') {
            return '/storage/'.$relativePath;
        }

        // Các disk khác (S3/CDN) dùng URL gốc của disk
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->richEditorDisk());

        return $disk->url($relativePath);
    }

    /**
     * Chuẩn hóa URL => relative path trên disk public.
     */
    protected function normalizeStoragePath(string $url): ?string
    {
        // remove domain
        $url = $this->stripBaseUrl($url);

        // strip query/hash
        $url = preg_replace('/[#?].*$/', '', $url) ?? $url;

        // strip leading slashes
        $url = ltrim($url, '/');

        // canonical media route -> resolve to storage key
        if (str_starts_with($url, 'media/')) {
            $canonicalKey = $this->extractCanonicalKey($url);
            if ($canonicalKey) {
                $image = app(MediaCanonicalService::class)->resolveByKey($canonicalKey);
                if ($image instanceof Image && $image->file_path) {
                    return ltrim($image->file_path, '/');
                }
            }
        }

        // remove "storage/" prefix
        if (str_starts_with($url, 'storage/')) {
            $url = substr($url, strlen('storage/'));
        }

        // chỉ nhận các path thuộc thư mục rich editor
        if (str_starts_with($url, 'uploads/') || str_starts_with($url, 'rich-editor-images')) {
            return $url;
        }

        return null;
    }

    protected function stripBaseUrl(string $url): string
    {
        $url = str_replace(config('app.url'), '', $url);
        $url = str_replace(url('/'), '', $url);

        return $url;
    }

    protected function extractCanonicalKey(string $url): ?string
    {
        $segments = explode('/', $url);

        if (count($segments) < 3 || $segments[0] !== 'media') {
            return null;
        }

        return $segments[2] ?: null;
    }

    protected function isCanonicalMediaUrl(string $url): bool
    {
        $normalized = ltrim($this->stripBaseUrl($url), '/');

        return str_starts_with($normalized, 'media/');
    }

    protected function resolveCanonicalUrlForStoragePath(string $relativePath): ?string
    {
        $relativePath = ltrim($relativePath, '/');
        $image = $this->findPreferredImageForPath($relativePath);

        if (! $image) {
            $disk = Storage::disk($this->richEditorDisk());
            if (! $disk->exists($relativePath)) {
                return null;
            }

            $mime = $disk->mimeType($relativePath) ?: null;

            $image = Image::create([
                'file_path' => $relativePath,
                'disk' => $this->richEditorDisk(),
                'mime' => $mime,
                'order' => 0,
                'active' => true,
                'semantic_type' => MediaSemanticRegistry::CONTENT,
            ]);
        }

        return $image->canonical_url;
    }

    protected function findPreferredImageForPath(string $relativePath): ?Image
    {
        $relativePath = ltrim($relativePath, '/');

        $withModel = Image::query()
            ->where('file_path', $relativePath)
            ->whereNotNull('model_type')
            ->first();

        if ($withModel) {
            return $withModel;
        }

        $contentImage = Image::query()
            ->where('file_path', $relativePath)
            ->where('semantic_type', MediaSemanticRegistry::CONTENT)
            ->first();

        return $contentImage;
    }

    protected function isRichEditorImageInUseElsewhere(string $relativePath): bool
    {
        return RichEditorMedia::query()
            ->where('file_path', $relativePath)
            ->where(function ($query) {
                $query
                    ->where('model_type', '!=', get_class($this))
                    ->orWhere('model_id', '!=', $this->getKey());
            })
            ->exists();
    }

    protected function isReferencedByGalleryImage(string $relativePath): bool
    {
        return Image::query()
            ->where('file_path', $relativePath)
            ->whereNotNull('model_type')
            ->exists();
    }

    protected function deleteRichEditorImageRecord(string $relativePath): void
    {
        Image::query()
            ->where('file_path', $relativePath)
            ->whereNull('model_type')
            ->where('semantic_type', MediaSemanticRegistry::CONTENT)
            ->delete();
    }

    /**
     * Lưu snapshot nội dung cũ trước khi update.
     */
    protected function snapshotRichEditorContent(): void
    {
        $this->richEditorOriginalContent = [];

        foreach ($this->getRichEditorFields() as $fieldName) {
            $this->richEditorOriginalContent[$fieldName] = $this->getOriginal($fieldName);
        }
    }

    /**
     * Xóa ảnh không còn xuất hiện sau khi record đã lưu.
     */
    protected function cleanupRemovedRichEditorImages(): void
    {
        if (empty($this->richEditorOriginalContent)) {
            return;
        }

        foreach ($this->getRichEditorFields() as $fieldName) {
            $oldContent = $this->richEditorOriginalContent[$fieldName] ?? null;
            $newContent = $this->getAttribute($fieldName);

            if (! $oldContent) {
                continue;
            }

            $oldImages = $this->extractImagePathsFromContent($oldContent);
            $newImages = $this->extractImagePathsFromContent($newContent ?? '');

            $imagesToDelete = array_diff($oldImages, $newImages);

            foreach ($imagesToDelete as $relativePath) {
                $this->deleteImageFile($relativePath);
            }
        }

        $this->richEditorOriginalContent = [];
    }

    /**
     * Xóa toàn bộ ảnh liên quan khi xóa model.
     */
    protected function deleteAllRichEditorImages(): void
    {
        foreach ($this->getRichEditorFields() as $fieldName) {
            $content = $this->getAttribute($fieldName);
            if (! $content) {
                continue;
            }

            $images = $this->extractImagePathsFromContent($content);
            foreach ($images as $relativePath) {
                $this->deleteImageFile($relativePath);
            }
        }
    }

    /**
     * Tên file prefix (per-model) để dễ tìm kiếm.
     */
    protected function richEditorFilenamePrefix(): string
    {
        if (method_exists($this, 'mediaPlaceholderKey')) {
            return $this->mediaPlaceholderKey().'-lexical';
        }

        return 'lexical';
    }

    /**
     * Thư mục lưu ảnh theo model, tách riêng để cleanup an toàn.
     */
    protected function richEditorContentDirectory(): string
    {
        if (property_exists($this, 'richEditorContentDirectory')) {
            /** @phpstan-ignore-next-line */
            return trim($this->richEditorContentDirectory, '/');
        }

        if (method_exists($this, 'mediaPlaceholderKey')) {
            return 'uploads/'.Str::plural($this->mediaPlaceholderKey()).'/content';
        }

        return 'uploads/rich-editor/content';
    }

    protected function richEditorDisk(): string
    {
        return property_exists($this, 'richEditorDisk')
            ? (string) $this->richEditorDisk
            : 'public';
    }
}

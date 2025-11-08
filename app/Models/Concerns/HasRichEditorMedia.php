<?php

namespace App\Models\Concerns;

use App\Models\RichEditorMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasRichEditorMedia
{
    public static function bootHasRichEditorMedia(): void
    {
        static::saving(function ($model) {
            $model->convertBase64ImagesToFiles();
        });

        static::saved(function ($model) {
            $model->syncRichEditorMedia();
        });

        static::deleting(function ($model) {
            $model->richEditorMedia()->delete();
        });
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
            
            if (!$content) {
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
        
        if (preg_match_all('/"url":"([^"]+)"/', $content, $matches)) {
            foreach ($matches[1] as $url) {
                $parsedUrl = parse_url($url);
                $path = $parsedUrl['path'] ?? '';
                
                if (str_starts_with($path, '/storage/')) {
                    $paths[] = str_replace('/storage/', '', $path);
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
            
            if (!$content) {
                continue;
            }

            $content = $this->replaceBase64ImagesWithFiles($content);
            $this->setAttribute($fieldName, $content);
        }
    }

    protected function replaceBase64ImagesWithFiles(string $content): string
    {
        $pattern = '/src=["\']data:image\/(png|jpg|jpeg|gif|webp);base64,([^"\']+)["\']/i';
        
        return preg_replace_callback($pattern, function ($matches) {
            $extension = $matches[1];
            $base64Data = $matches[2];
            
            try {
                $imageData = base64_decode($base64Data);
                
                if ($imageData === false) {
                    return $matches[0];
                }
                
                $filename = Str::random(40) . '.' . $extension;
                $directory = 'rich-editor-images';
                $path = $directory . '/' . $filename;
                
                Storage::disk('public')->put($path, $imageData);
                
                $relativePath = '/storage/' . $path;
                
                return 'src="' . $relativePath . '"';
            } catch (\Exception $e) {
                \Log::error('Failed to convert base64 image: ' . $e->getMessage());
                return $matches[0];
            }
        }, $content);
    }
}

<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

class CategoryGridTransformer extends AbstractComponentTransformer
{
    public function transform(HomeComponent $component, HomeComponentResources $resources): ?array
    {
        $config = $this->normalizeConfig($component);
        $itemsConfig = $this->ensureArray($config['categories'] ?? []);

        $categories = [];

        foreach ($itemsConfig as $item) {
            if (! is_array($item)) {
                continue;
            }

            $termId = $this->toPositiveInt($item['term_id'] ?? null);
            $term = $termId ? $resources->term($component, $termId) : null;

            $imageData = null;

            $imageId = $this->toPositiveInt($item['image_id'] ?? null);
            if ($imageId) {
                $image = $resources->image($component, $imageId);
                if ($image) {
                    $imageData = $resources->mapImage($image, $item['title'] ?? $term?->name);
                }
            }

            if (! $imageData && isset($item['image']) && is_array($item['image'])) {
                $imageObj = $item['image'];
                if (! empty($imageObj['url'])) {
                    $imageData = [
                        'id' => (int) ($imageObj['id'] ?? 0),
                        'url' => $imageObj['url'],
                        'alt' => $imageObj['alt'] ?? $item['title'] ?? $term?->name ?? 'Category Image',
                    ];
                }
            }

            if (! $imageData) {
                continue;
            }

            $categories[] = [
                'title' => $item['title'] ?? ($term?->name ?? 'Untitled'),
                'href' => $item['href'] ?? ($term ? "/categories/{$term->slug}" : '#'),
                'image' => $imageData,
            ];
        }

        if (empty($categories)) {
            return null;
        }

        $config['categories'] = $categories;

        return $resources->payload($component, $config);
    }
}

<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\CatalogTerm;
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
            if (!is_array($item)) {
                continue;
            }

            // Get term info
            $termId = $this->toPositiveInt($item['term_id'] ?? null);
            $term = null;
            if ($termId) {
                $term = CatalogTerm::find($termId);
            }

            // Get image (required) - support both formats
            $imageData = null;
            
            // Format 1: image_id reference to Image model
            $imageId = $this->toPositiveInt($item['image_id'] ?? null);
            if ($imageId) {
                $image = $resources->image($component, $imageId);
                if ($image) {
                    $imageData = $resources->mapImage($image, $item['title'] ?? $term?->name);
                }
            }
            
            // Format 2: Direct image object from JSON (e.g., from file upload in admin)
            if (!$imageData && isset($item['image']) && is_array($item['image'])) {
                $imageObj = $item['image'];
                if (!empty($imageObj['url'])) {
                    $imageData = [
                        'id' => (int) ($imageObj['id'] ?? 0),
                        'url' => $imageObj['url'],
                        'alt' => $imageObj['alt'] ?? $item['title'] ?? $term?->name ?? 'Category Image',
                    ];
                }
            }

            // Skip if no valid image found
            if (!$imageData) {
                continue;
            }

            // Build category item
            $categoryItem = [
                'title' => $item['title'] ?? ($term?->name ?? 'Untitled'),
                'href' => $item['href'] ?? ($term ? "/categories/{$term->slug}" : '#'),
                'image' => $imageData,
            ];

            $categories[] = $categoryItem;
        }

        if (empty($categories)) {
            return null;
        }

        $config['categories'] = $categories;

        return $resources->payload($component, $config);
    }
}

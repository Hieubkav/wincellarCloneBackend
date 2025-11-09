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

            // Get image (required)
            $imageId = $this->toPositiveInt($item['image_id'] ?? null);
            if (!$imageId) {
                continue;
            }

            $image = $resources->image($component, $imageId);
            if (!$image) {
                continue;
            }

            // Build category item
            $categoryItem = [
                'title' => $item['title'] ?? ($term?->name ?? 'Untitled'),
                'href' => $item['href'] ?? ($term ? "/categories/{$term->slug}" : '#'),
                'image' => $resources->mapImage($image, $item['title'] ?? $term?->name),
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

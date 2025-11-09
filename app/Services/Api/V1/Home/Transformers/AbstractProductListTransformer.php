<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

abstract class AbstractProductListTransformer extends AbstractComponentTransformer
{
    /**
     * @param array<int, mixed> $itemsConfig
     * @return array<int, array<string, mixed>>
     */
    protected function buildProductEntries(HomeComponent $component, array $itemsConfig, HomeComponentResources $resources): array
    {
        $entries = [];

        foreach ($itemsConfig as $item) {
            $productId = null;
            $badge = null;
            $href = null;

            // Support 2 formats:
            // 1. Simple format from Filament .simple(): ["126", "127"] or [126, 127]
            // 2. Object format: [{"product_id": 126, "badge": "New"}, ...]
            if (is_array($item)) {
                $productId = $this->toPositiveInt($item['product_id'] ?? null);
                $badge = $item['badge'] ?? null;
                $href = $item['href'] ?? null;
            } else {
                // Simple format: just ID as string or int
                $productId = $this->toPositiveInt($item);
            }

            if (!$productId) {
                continue;
            }

            $product = $resources->product($component, $productId);
            if (!$product) {
                continue;
            }

            $entries[] = [
                'product' => $resources->mapProductSummary($product),
                'badge' => $badge,
                'href' => $href ?? $resources->defaultProductHref($product),
            ];
        }

        return $entries;
    }
}

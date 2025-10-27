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
            if (!is_array($item)) {
                continue;
            }

            $productId = $this->toPositiveInt($item['product_id'] ?? null);
            if (!$productId) {
                continue;
            }

            $product = $resources->product($component, $productId);
            if (!$product) {
                continue;
            }

            $entries[] = [
                'product' => $resources->mapProductSummary($product),
                'badge' => $item['badge'] ?? null,
                'href' => $item['href'] ?? $resources->defaultProductHref($product),
            ];
        }

        return $entries;
    }
}

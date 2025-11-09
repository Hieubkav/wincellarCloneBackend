<?php

namespace App\Services\Api\V1\Home\Transformers;

use App\Models\HomeComponent;
use App\Services\Api\V1\Home\HomeComponentResources;

abstract class AbstractArticleListTransformer extends AbstractComponentTransformer
{
    /**
     * @param array<int, mixed> $itemsConfig
     * @return array<int, array<string, mixed>>
     */
    protected function buildArticleEntries(HomeComponent $component, array $itemsConfig, HomeComponentResources $resources): array
    {
        $entries = [];

        foreach ($itemsConfig as $item) {
            $articleId = null;
            $href = null;

            // Support 2 formats:
            // 1. Simple format from Filament .simple(): ["1", "2"] or [1, 2]
            // 2. Object format: [{"article_id": 1, "href": "/article"}, ...]
            if (is_array($item)) {
                $articleId = $this->toPositiveInt($item['article_id'] ?? null);
                $href = $item['href'] ?? null;
            } else {
                // Simple format: just ID as string or int
                $articleId = $this->toPositiveInt($item);
            }

            if (!$articleId) {
                continue;
            }

            $article = $resources->article($component, $articleId);
            if (!$article) {
                continue;
            }

            $entries[] = [
                'article' => $resources->mapArticleSummary($article),
                'href' => $href ?? $resources->defaultArticleHref($article),
            ];
        }

        return $entries;
    }
}

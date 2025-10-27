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
            if (!is_array($item)) {
                continue;
            }

            $articleId = $this->toPositiveInt($item['article_id'] ?? null);
            if (!$articleId) {
                continue;
            }

            $article = $resources->article($component, $articleId);
            if (!$article) {
                continue;
            }

            $entries[] = [
                'article' => $resources->mapArticleSummary($article),
                'href' => $item['href'] ?? $resources->defaultArticleHref($article),
            ];
        }

        return $entries;
    }
}

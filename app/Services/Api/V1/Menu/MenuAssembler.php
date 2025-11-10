<?php

namespace App\Services\Api\V1\Menu;

use App\Models\Menu;
use Illuminate\Support\Collection;

class MenuAssembler
{
    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\Menu> $menus
     * @return array<int, array<string, mixed>>
     */
    public function build(Collection $menus): array
    {
        if ($menus->isEmpty()) {
            return [];
        }

        $payload = [];

        foreach ($menus as $menu) {
            $payload[] = $this->transformMenu($menu);
        }

        return $payload;
    }

    /**
     * Transform Menu model to API response format
     */
    private function transformMenu(Menu $menu): array
    {
        $data = [
            'id' => $menu->id,
            'label' => $menu->title,
            'href' => $this->getMenuHref($menu),
            'type' => $menu->type,
        ];

        // Add children for mega menu type
        if ($menu->type === 'mega' && $menu->blocks->isNotEmpty()) {
            $data['children'] = $this->transformBlocks($menu->blocks);
        }

        return $data;
    }

    /**
     * Get menu href
     */
    private function getMenuHref(Menu $menu): string
    {
        // Priority 1: Manual href
        if ($menu->href) {
            return $menu->href;
        }

        // Priority 2: Auto href from term
        if ($menu->term) {
            $group = $menu->term->group;
            if ($group) {
                return '/san-pham?' . http_build_query([
                    'filter' => [
                        $group->code => $menu->term->slug,
                    ],
                ]);
            }
        }

        return '#';
    }

    /**
     * Transform MenuBlocks collection to API response format
     */
    private function transformBlocks(Collection $blocks): array
    {
        $children = [];

        foreach ($blocks as $block) {
            $children[] = [
                'label' => $block->title,
                'children' => $this->transformBlockItems($block->items),
            ];
        }

        return $children;
    }

    /**
     * Transform MenuBlockItems collection to API response format
     */
    private function transformBlockItems(Collection $items): array
    {
        $children = [];

        foreach ($items as $item) {
            $itemData = [
                'label' => $item->displayLabel(),
                'href' => $item->displayHref() ?? '#',
            ];

            // Add badge if exists
            if ($item->badge) {
                $itemData['isHot'] = $item->badge === 'HOT';
                $itemData['badge'] = $item->badge;
            }

            $children[] = $itemData;
        }

        return $children;
    }
}

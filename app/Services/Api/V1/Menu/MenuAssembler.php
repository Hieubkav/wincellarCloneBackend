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
            'label' => $menu->title ?? '',
            'href' => $menu->href ?? '#',
            'type' => $menu->type,
        ];

        // Add children for mega menu type (only active blocks)
        if ($menu->type === 'mega') {
            $activeBlocks = $menu->blocks->filter(fn ($block) => $block->active);
            if ($activeBlocks->isNotEmpty()) {
                $data['children'] = $this->transformBlocks($activeBlocks);
            }
        }

        return $data;
    }

    /**
     * Transform MenuBlocks collection to API response format
     */
    private function transformBlocks(Collection $blocks): array
    {
        $children = [];

        foreach ($blocks as $block) {
            // Only include active items
            $activeItems = $block->items->filter(fn ($item) => $item->active);
            
            if ($activeItems->isEmpty()) {
                continue;
            }

            $children[] = [
                'label' => $block->title ?? '',
                'children' => $this->transformBlockItems($activeItems),
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
                'label' => $item->label ?? '',
                'href' => $item->href ?? '#',
            ];

            // Add badge if exists
            if ($item->badge) {
                $itemData['isHot'] = strtoupper($item->badge) === 'HOT';
                $itemData['badge'] = $item->badge;
            }

            $children[] = $itemData;
        }

        return $children;
    }
}

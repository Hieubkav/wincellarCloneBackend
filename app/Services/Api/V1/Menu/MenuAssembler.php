<?php

namespace App\Services\Api\V1\Menu;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuAssembler
{
    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Menu>  $menus
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

        if ($menu->relationLoaded('items') && $menu->items->isNotEmpty()) {
            $children = $this->transformTreeItems($menu->items);

            if ($children !== []) {
                $data['children'] = $children;
            }
        } elseif ($menu->type === 'mega') {
            $activeBlocks = $menu->blocks->filter(fn ($block) => $block->active);
            if ($activeBlocks->isNotEmpty()) {
                $data['children'] = $this->transformBlocks($activeBlocks, $menu);
            }
        }

        return $data;
    }

    private function transformTreeItems(Collection $items): array
    {
        $byParent = $items
            ->filter(fn (MenuItem $item) => $item->active)
            ->groupBy(fn (MenuItem $item) => $item->parent_id ?: 0);

        return $this->buildTreeLevel($byParent, 0, 0);
    }

    private function buildTreeLevel(Collection $byParent, int $parentId, int $depth): array
    {
        if ($depth >= 5) {
            return [];
        }

        return ($byParent->get($parentId) ?? collect())
            ->sortBy([['order', 'asc'], ['id', 'asc']])
            ->map(function (MenuItem $item) use ($byParent, $depth) {
                $node = [
                    'id' => $item->id,
                    'label' => $item->label ?? '',
                    'href' => $item->href ?? '#',
                    'type' => $item->depth === 0 ? 'mega' : 'standard',
                ];

                if ($item->badge) {
                    $node['badge'] = $item->badge;
                    $node['isHot'] = strtoupper($item->badge) === 'HOT';
                }

                $children = $this->buildTreeLevel($byParent, $item->id, $depth + 1);
                if ($children !== []) {
                    $node['children'] = $children;
                }

                return $node;
            })
            ->values()
            ->all();
    }

    /**
     * Transform MenuBlocks collection to API response format
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\MenuBlock>  $blocks
     * @param  Menu  $parentMenu  Parent menu for "View All" link
     */
    private function transformBlocks(Collection $blocks, Menu $parentMenu): array
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

<?php

namespace App\Observers;

use App\Models\MenuBlock;

class MenuBlockObserver
{
    public function creating(MenuBlock $menuBlock): void
    {
        if ($menuBlock->order === null) {
            $maxOrder = MenuBlock::where('menu_id', $menuBlock->menu_id)
                ->max('order') ?? -1;
            $menuBlock->order = $maxOrder + 1;
        }
    }
}

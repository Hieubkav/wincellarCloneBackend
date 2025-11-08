<?php

namespace App\Observers;

use App\Models\HomeComponent;

class HomeComponentObserver
{
    public function creating(HomeComponent $homeComponent): void
    {
        if ($homeComponent->order === null) {
            $maxOrder = HomeComponent::max('order') ?? -1;
            $homeComponent->order = $maxOrder + 1;
        }
    }
}

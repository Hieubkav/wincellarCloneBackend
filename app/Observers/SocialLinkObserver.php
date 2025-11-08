<?php

namespace App\Observers;

use App\Models\SocialLink;

class SocialLinkObserver
{
    public function creating(SocialLink $socialLink): void
    {
        if ($socialLink->order === null) {
            $maxOrder = SocialLink::max('order') ?? -1;
            $socialLink->order = $maxOrder + 1;
        }
    }
}

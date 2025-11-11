<?php

namespace App\Observers;

use App\Models\SocialLink;
use Illuminate\Support\Facades\Cache;

class SocialLinkObserver
{
    public function creating(SocialLink $socialLink): void
    {
        if ($socialLink->order === null) {
            $maxOrder = SocialLink::max('order') ?? -1;
            $socialLink->order = $maxOrder + 1;
        }
    }

    /**
     * Invalidate API cache when social link is saved
     */
    public function saved(SocialLink $socialLink): void
    {
        Cache::forget('api:v1:social-links');
    }

    /**
     * Invalidate API cache when social link is deleted
     */
    public function deleted(SocialLink $socialLink): void
    {
        Cache::forget('api:v1:social-links');
    }
}

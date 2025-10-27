<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PruneOrphanImages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public function handle(): void
    {
        $removed = 0;

        Image::query()
            ->whereNull('deleted_at')
            ->chunkById(200, function ($images) use (&$removed): void {
                /** @var \Illuminate\Support\Collection<int, \App\Models\Image> $images */
                foreach ($images as $image) {
                    if ($this->isOrphan($image)) {
                        $image->forceDelete();
                        $removed++;
                    }
                }
            });

        if ($removed > 0) {
            Log::info('PruneOrphanImages removed media files', [
                'count' => $removed,
            ]);
        }
    }

    private function isOrphan(Image $image): bool
    {
        if (!$image->model_type || !$image->model_id) {
            return true;
        }

        if (!class_exists($image->model_type)) {
            return true;
        }

        $modelClass = $image->model_type;

        $instance = new $modelClass();

        return !$instance->newQueryWithoutScopes()
            ->whereKey($image->model_id)
            ->exists();
    }
}

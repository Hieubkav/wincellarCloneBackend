<?php

namespace App\Console\Commands;

use App\Models\Image;
use App\Services\Media\MediaCanonicalService;
use Illuminate\Console\Command;

class BackfillCanonicalMediaMetadata extends Command
{
    protected $signature = 'media:backfill-canonical-metadata {--chunk=500} {--dry-run : Chỉ hiển thị, không ghi DB}';

    protected $description = 'Backfill canonical metadata cho ảnh legacy (semantic_type, canonical_key, canonical_slug)';

    public function handle(MediaCanonicalService $canonicalService): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $total = Image::query()->count();
        $this->info("Bắt đầu backfill {$total} ảnh (chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no').')');

        $updated = 0;
        $skipped = 0;

        Image::query()->orderBy('id')->chunkById($chunkSize, function ($images) use ($canonicalService, $dryRun, &$updated, &$skipped) {
            foreach ($images as $image) {
                $before = [
                    'semantic_type' => $image->semantic_type,
                    'canonical_key' => $image->canonical_key,
                    'canonical_slug' => $image->canonical_slug,
                ];

                $canonicalService->ensureMetadata($image);

                $after = [
                    'semantic_type' => $image->semantic_type,
                    'canonical_key' => $image->canonical_key,
                    'canonical_slug' => $image->canonical_slug,
                ];

                if ($before !== $after) {
                    $updated++;
                    if (! $dryRun) {
                        $image->saveQuietly();
                    }
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Hoàn tất. Updated={$updated}, Skipped={$skipped}.");

        return self::SUCCESS;
    }
}

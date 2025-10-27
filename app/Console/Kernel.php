<?php

namespace App\Console;

use App\Jobs\PruneOrphanImages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new PruneOrphanImages())
            ->weeklyOn(1, '02:00')
            ->name('prune-orphan-images')
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}

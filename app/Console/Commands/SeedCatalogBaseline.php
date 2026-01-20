<?php

namespace App\Console\Commands;

use Database\Seeders\CatalogBaselineSeeder;
use Illuminate\Console\Command;

class SeedCatalogBaseline extends Command
{
    protected $signature = 'catalog:seed-baseline';

    protected $description = 'Khôi phục baseline phân loại và nhóm thuộc tính từ seed JSON.';

    public function handle(): int
    {
        $this->call(CatalogBaselineSeeder::class);
        $this->info('Đã khôi phục baseline catalog attribute groups và product types.');

        return self::SUCCESS;
    }
}

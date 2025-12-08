<?php

namespace App\Console\Commands;

use App\Models\CatalogAttributeGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExtraAttrsLabels extends Command
{
    protected $signature = 'products:sync-extra-attrs-labels';

    protected $description = 'Sync labels trong extra_attrs của products với tên hiện tại của attribute groups';

    public function handle(): int
    {
        $groups = CatalogAttributeGroup::where('filter_type', 'nhap_tay')->get();

        if ($groups->isEmpty()) {
            $this->info('Không có attribute groups loại nhập tay.');
            return self::SUCCESS;
        }

        $totalUpdated = 0;

        foreach ($groups as $group) {
            $code = $group->code;
            $newName = $group->name;
            $jsonPath = '$."' . $code . '".label';

            $affected = DB::table('products')
                ->whereRaw('JSON_EXTRACT(extra_attrs, ?) IS NOT NULL', ['$."' . $code . '"'])
                ->update([
                    'extra_attrs' => DB::raw("JSON_SET(extra_attrs, '{$jsonPath}', " . DB::getPdo()->quote($newName) . ")"),
                ]);

            if ($affected > 0) {
                $this->info("Đã cập nhật {$affected} products cho group [{$code}] -> \"{$newName}\"");
                $totalUpdated += $affected;
            }
        }

        $this->info("Hoàn thành! Tổng cộng {$totalUpdated} products đã được cập nhật.");

        return self::SUCCESS;
    }
}

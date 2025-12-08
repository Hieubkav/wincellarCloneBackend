<?php

namespace App\Console\Commands;

use App\Models\CatalogAttributeGroup;
use App\Models\Product;
use Illuminate\Console\Command;

class CleanOrphanExtraAttrs extends Command
{
    protected $signature = 'products:clean-orphan-extra-attrs';

    protected $description = 'Xóa các extra_attrs không còn attribute group tương ứng';

    public function handle(): int
    {
        $validCodes = CatalogAttributeGroup::where('filter_type', 'nhap_tay')
            ->pluck('code')
            ->toArray();

        $this->info('Valid attribute codes: ' . implode(', ', $validCodes));

        $products = Product::whereNotNull('extra_attrs')->get();
        $totalCleaned = 0;

        foreach ($products as $product) {
            $extraAttrs = $product->extra_attrs ?? [];
            $cleaned = false;

            foreach (array_keys($extraAttrs) as $code) {
                if (!in_array($code, $validCodes, true)) {
                    $this->warn("Product [{$product->id}] {$product->name}: removing orphan code [{$code}]");
                    unset($extraAttrs[$code]);
                    $cleaned = true;
                }
            }

            if ($cleaned) {
                $product->extra_attrs = empty($extraAttrs) ? null : $extraAttrs;
                $product->save();
                $totalCleaned++;
            }
        }

        $this->info("Hoàn thành! Đã clean {$totalCleaned} products.");

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_category_product', function (Blueprint $table) {
            if (! $this->indexExists('product_category_product', 'pcp_product_category_index')) {
                $table->index(['product_id', 'product_category_id'], 'pcp_product_category_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_category_product', function (Blueprint $table) {
            if ($this->indexExists('product_category_product', 'pcp_product_category_index')) {
                $table->dropIndex('pcp_product_category_index');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select(
            'SELECT INDEX_NAME FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = ? 
             AND index_name = ?',
            [$table, $indexName]
        );

        return count($indexes) > 0;
    }
};

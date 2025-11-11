<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = DB::select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = 'products' 
             AND index_name = 'products_name_description_fulltext'"
        );

        if ($indexExists[0]->count == 0) {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['name', 'description'], 'products_name_description_fulltext');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText('products_name_description_fulltext');
        });
    }
};

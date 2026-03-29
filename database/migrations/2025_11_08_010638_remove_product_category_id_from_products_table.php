<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
        });

        if (Schema::hasColumn('products', 'product_category_id')) {
            if (DB::getDriverName() === 'sqlite') {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex('products_type_category_index');
                });
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('product_category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_category_id')->nullable()->after('slug')->constrained('product_categories');
        });
    }
};

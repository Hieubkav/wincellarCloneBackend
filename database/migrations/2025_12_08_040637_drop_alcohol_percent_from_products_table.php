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
        if (Schema::hasColumn('products', 'alcohol_percent')) {
            if (DB::getDriverName() === 'sqlite') {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex('products_alcohol_percent_index');
                });
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('alcohol_percent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('alcohol_percent', 5, 2)->nullable()->after('original_price');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds critical performance indexes to products table to optimize filter page queries:
     * 1. active index - for WHERE active = 1 (used in every query)
     * 2. active + created_at composite - for default sorting (most recent products)
     * 3. type_id + active composite - for filtering by product type
     * 
     * Expected performance improvement: 10-20x faster queries on filter page
     * Note: FULLTEXT index already exists (added in 2025_11_09_999999 migration)
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Index for WHERE active = 1 (used in all queries via ->active() scope)
            $table->index('active', 'products_active_index');
            
            // Composite index for WHERE active = 1 ORDER BY created_at DESC (default sort)
            $table->index(['active', 'created_at'], 'products_active_created_index');
            
            // Composite index for WHERE type_id = X AND active = 1
            $table->index(['type_id', 'active'], 'products_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_index');
            $table->dropIndex('products_active_created_index');
            $table->dropIndex('products_type_active_index');
        });
    }
};

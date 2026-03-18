<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add composite indexes for performance optimization
     * Fixes ROOT CAUSE #4: Missing Database Indexes
     */
    public function up(): void
    {
        // Products table - Composite indexes for common queries
        Schema::table('products', function (Blueprint $table) {
            // Index for: WHERE type_id = ? AND active = ? ORDER BY created_at
            $table->index(['type_id', 'active', 'created_at'], 'idx_products_type_active_created');

            // Index for: WHERE active = ? AND price BETWEEN ? AND ?
            $table->index(['active', 'price'], 'idx_products_active_price');

            // Index for: WHERE type_id = ? AND active = ? AND price BETWEEN ?
            $table->index(['type_id', 'active', 'price'], 'idx_products_type_active_price');
        });

        // Tracking Events - Critical for analytics queries
        Schema::table('tracking_events', function (Blueprint $table) {
            // Index for: WHERE visitor_id = ? ORDER BY occurred_at
            $table->index(['visitor_id', 'occurred_at'], 'idx_tracking_visitor_occurred');

            // Index for: WHERE event_type = ? AND occurred_at >= ?
            $table->index(['event_type', 'occurred_at'], 'idx_tracking_type_occurred');

            // Index for: WHERE event_type = ? AND product_id IS NOT NULL AND occurred_at >= ?
            $table->index(['event_type', 'product_id', 'occurred_at'], 'idx_tracking_type_product_occurred');

            // Index for: WHERE event_type = ? AND article_id IS NOT NULL AND occurred_at >= ?
            $table->index(['event_type', 'article_id', 'occurred_at'], 'idx_tracking_type_article_occurred');
        });

        // Product Categories
        Schema::table('product_categories', function (Blueprint $table) {
            // Index for: WHERE type_id = ? AND active = ? ORDER BY order
            $table->index(['type_id', 'active', 'order'], 'idx_categories_type_active_order');
        });

        // Articles
        Schema::table('articles', function (Blueprint $table) {
            // Index for: WHERE active = ? ORDER BY published_at DESC
            $table->index(['active', 'published_at'], 'idx_articles_active_published');

            // Index for: WHERE active = ? ORDER BY created_at DESC
            $table->index(['active', 'created_at'], 'idx_articles_active_created');
        });

        // Product Term Assignments - For filter queries
        Schema::table('product_term_assignments', function (Blueprint $table) {
            // Index for: WHERE term_id IN (?) - optimizes filter queries
            // Foreign key index should already exist, but ensure composite for JOIN optimization
            $table->index(['term_id', 'product_id'], 'idx_term_product');
        });

        // Images - Polymorphic relationship optimization
        Schema::table('images', function (Blueprint $table) {
            // Index for: WHERE model_type = ? AND model_id = ? ORDER BY order
            $table->index(['model_type', 'model_id', 'order'], 'idx_images_polymorphic_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_type_active_created');
            $table->dropIndex('idx_products_active_price');
            $table->dropIndex('idx_products_type_active_price');
        });

        Schema::table('tracking_events', function (Blueprint $table) {
            $table->dropIndex('idx_tracking_visitor_occurred');
            $table->dropIndex('idx_tracking_type_occurred');
            $table->dropIndex('idx_tracking_type_product_occurred');
            $table->dropIndex('idx_tracking_type_article_occurred');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_type_active_order');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_active_published');
            $table->dropIndex('idx_articles_active_created');
        });

        Schema::table('product_term_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_term_product');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->dropIndex('idx_images_polymorphic_order');
        });
    }
};

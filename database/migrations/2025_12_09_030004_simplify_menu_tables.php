<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đơn giản hóa bảng menu - bỏ liên kết CatalogTerm/AttributeGroup
     * Menu giờ chỉ nhập thủ công label + href cho linh hoạt tối đa
     */
    public function up(): void
    {
        // 1. menus: bỏ term_id, config
        // Phải drop foreign key TRƯỚC, rồi mới drop index và column
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
        });
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_term_type_index');
            $table->dropColumn(['term_id', 'config']);
        });

        // 2. menu_blocks: bỏ attribute_group_id, max_terms, config
        Schema::table('menu_blocks', function (Blueprint $table) {
            $table->dropForeign(['attribute_group_id']);
        });
        Schema::table('menu_blocks', function (Blueprint $table) {
            $table->dropIndex('menu_blocks_group_order_index');
            $table->dropColumn(['attribute_group_id', 'max_terms', 'config']);
        });

        // 3. menu_block_items: bỏ term_id
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
        });
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->dropIndex('menu_block_items_term_index');
            $table->dropColumn('term_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục menu_block_items
        Schema::table('menu_block_items', function (Blueprint $table) {
            $table->foreignId('term_id')
                ->nullable()
                ->after('menu_block_id')
                ->constrained('catalog_terms')
                ->nullOnDelete();
            $table->index(['term_id', 'menu_block_id'], 'menu_block_items_term_index');
        });

        // Khôi phục menu_blocks
        Schema::table('menu_blocks', function (Blueprint $table) {
            $table->foreignId('attribute_group_id')
                ->nullable()
                ->after('title')
                ->constrained('catalog_attribute_groups')
                ->nullOnDelete();
            $table->unsignedInteger('max_terms')->nullable()->after('attribute_group_id');
            $table->json('config')->nullable()->after('max_terms');
            $table->index(['attribute_group_id', 'order'], 'menu_blocks_group_order_index');
        });

        // Khôi phục menus
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('term_id')
                ->nullable()
                ->after('title')
                ->constrained('catalog_terms')
                ->nullOnDelete();
            $table->json('config')->nullable()->after('href');
            $table->index(['term_id', 'type'], 'menus_term_type_index');
        });
    }
};

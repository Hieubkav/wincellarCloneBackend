<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * `catalog_terms` lưu từng term cụ thể trong mỗi group (brand, country, grape, ...).
         * - `parent_id` hỗ trợ cấu trúc cây (ví dụ region -> parent country, phụ kiện -> dòng sản phẩm).
         * - `icon_type` + `icon_value` giúp FE hiển thị icon theo dạng image/lucide/emoji.
         * - `metadata` mở rộng cho mô tả bổ sung (color, tagline, external_ref...).
         */
        Schema::create('catalog_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('catalog_attribute_groups')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('catalog_terms')
                ->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon_type', 20)->nullable(); // image|lucide|emoji|svg
            $table->string('icon_value', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['group_id', 'slug'], 'catalog_terms_group_slug_unique');
            $table->index(['group_id', 'position'], 'catalog_terms_group_position_index');
            $table->index(['parent_id', 'group_id'], 'catalog_terms_parent_group_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_terms');
    }
};


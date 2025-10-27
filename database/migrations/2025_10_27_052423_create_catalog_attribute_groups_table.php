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
         * Mục tiêu: gom toàn bộ bộ lọc động (brand, country, grape, material, ...) thành taxonomy linh hoạt.
         * `catalog_attribute_groups` định nghĩa từng nhóm filter/attribute kèm cấu hình hiển thị trên FE/Admin.
         * - `code`: khóa duy nhất để map với FE/API (vd. brand, country, accessory_type).
         * - `filter_type`: các kiểu xử lý (single|multi|hierarchy|range) để service suy ra UI và query builder.
         * - `display_config`: JSON mở rộng (icon mặc định, màu sắc, template slug...).
         */
        Schema::create('catalog_attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('filter_type', 30)->default('multi'); // single|multi|hierarchy|range
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_primary')->default(false); // nhóm chính như brand/country phục vụ breadcrumb
            $table->unsignedInteger('position')->default(0);
            $table->json('display_config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_attribute_groups');
    }
};


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
         * Mục tiêu: phân loại chi tiết hơn trong từng category (ví dụ: sparkling, dessert, sake...) để hỗ trợ filter nhiều
         * điều kiện đồng thời như tài liệu yêu cầu.
         * - `slug` là khóa cho URL/redirect + mapping với seed dữ liệu lớn.
         * - `description` dùng hiển thị tooltip/mega menu.
         * - `order`, `active` giúp sắp xếp block filter và dễ dàng ẩn tạm qua admin.
         */
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};

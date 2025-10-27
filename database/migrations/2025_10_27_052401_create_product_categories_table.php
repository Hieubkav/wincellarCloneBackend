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
         * Mục tiêu: gom nhóm sản phẩm theo danh mục chính (vang, bia, thịt nguội, quà tặng) để phục vụ filter &
         * điều hướng mega menu. Các trường giúp:
         * - `slug` làm khóa business cho URL & redirect.
         * - `description` để tooltip/SEO nếu cần mở rộng landing page.
         * - `order`, `active` giúp Filament bật/tắt thứ tự mà không cần hardcode trên FE.
         */
        Schema::create('product_categories', function (Blueprint $table) {
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
        Schema::dropIfExists('product_categories');
    }
};

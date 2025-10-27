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
         * Mục tiêu: cấu hình trang chủ linh hoạt thông qua danh sách component (HeroCarousel, FavouriteProducts,...).
         * - `type` định nghĩa preset FE sẽ render.
         * - `config` (JSON) chứa payload động (danh sách product_id/article_id...) thay vì tạo nhiều bảng phụ.
         * - `order`, `active` giúp reorder/ẩn block ngay trong Filament.
         */
        Schema::create('home_components', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->json('config')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_components');
    }
};

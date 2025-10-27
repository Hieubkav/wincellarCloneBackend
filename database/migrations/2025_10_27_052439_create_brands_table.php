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
         * Mục tiêu: quản lý thương hiệu sản phẩm (nhà sản xuất, nhà phân phối).
         * - `slug` để làm canonical URL + redirect khi rename.
         * - `logo_image_id` FK ảnh trực tiếp vì brand thường có 1 logo cố định, tiện cho Filament chọn nhanh.
         * - `description`, `active` dùng cho trang brand showcase & ẩn dòng khi hết hợp tác.
         */
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('logo_image_id')
                ->nullable()
                ->constrained('images')
                ->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

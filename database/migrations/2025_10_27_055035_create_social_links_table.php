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
         * Mục tiêu: quản lý danh sách mạng xã hội để FE render header/footer.
         * - `platform` + `url` là data chính, `icon_image_id` FK trực tiếp giúp hiển thị biểu tượng chuẩn.
         * - `order`, `active` hỗ trợ thay đổi ưu tiên hiển thị/ẩn mà không xóa dữ liệu lịch sử.
         */
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('url', 2048);
            $table->foreignId('icon_image_id')
                ->nullable()
                ->constrained('images')
                ->nullOnDelete();
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
        Schema::dropIfExists('social_links');
    }
};

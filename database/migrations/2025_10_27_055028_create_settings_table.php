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
         * Mục tiêu: bảng singleton chứa cấu hình toàn site (logo, hotline, metadata mặc định).
         * - Hai FK ảnh (logo/favicon) dùng nullOnDelete để nếu xóa media thì UI fallback placeholder.
         * - Trường contact + meta giúp FE render header/footer mà không cần hardcode.
         * - `extra` JSON để mở rộng nhanh mà không đổi schema.
         */
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logo_image_id')
                ->nullable()
                ->constrained('images')
                ->nullOnDelete();
            $table->foreignId('favicon_image_id')
                ->nullable()
                ->constrained('images')
                ->nullOnDelete();
            $table->string('site_name')->nullable();
            $table->string('hotline')->nullable();
            $table->string('address')->nullable();
            $table->string('hours')->nullable();
            $table->string('email')->nullable();
            $table->string('meta_default_title')->nullable();
            $table->text('meta_default_description')->nullable();
            $table->string('meta_default_keywords')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

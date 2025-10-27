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
         * Mục tiêu: lưu metadata cho mọi file ảnh dùng chung để các module (product, article, brand, home component, ...)
         * có thể tái sử dụng gallery một cách thống nhất.
         * Lưu ý: `order=0` tương đương ảnh cover, partial unique ở mức DB giúp Filament tránh tạo 2 cover cho cùng model.
         * Ý nghĩa từng nhóm trường:
         * - `file_path`, `disk`, kích thước: phục vụ render responsive + job tối ưu ảnh.
         * - `model_type/model_id`: khóa polymorphic để truy vết ảnh thuộc resource nào, hỗ trợ clean-up orphan.
         * - `order`, `active`, `extra_attributes`: điều khiển hiển thị FE (cover, gallery, captions tùy biến).
         */
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 2048);
            $table->string('disk', 100)->default('public');
            $table->string('alt')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('mime', 191)->nullable();
            $table->morphs('model');
            $table->unsignedInteger('order')->default(1);
            $table->boolean('active')->default(true);
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_type', 'model_id'], 'images_model_index');
            $table->unique(['model_type', 'model_id', 'order'], 'images_unique_order_per_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};

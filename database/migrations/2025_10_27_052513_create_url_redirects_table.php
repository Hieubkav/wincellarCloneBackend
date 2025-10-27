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
         * Mục tiêu: lưu redirect 301 tự sinh khi slug thay đổi (Product/Article) để đảm bảo SEO.
         * - `from_slug` unique để không thể tạo trùng và đảm bảo middleware có thể resolve nhanh.
         * - `target_type/target_id` giúp flatten chuỗi redirect và kiểm soát quyền (staff không được sửa).
         * - `hit_count`, `last_triggered_at` phục vụ audit + job flatten chain (ưu tiên rule đang được truy cập).
         */
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_slug')->unique();
            $table->string('to_slug');
            $table->enum('target_type', ['Product', 'Article']);
            $table->unsignedBigInteger('target_id');
            $table->boolean('auto_generated')->default(true);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id'], 'url_redirects_target_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};

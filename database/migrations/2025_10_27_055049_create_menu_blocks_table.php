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
         * Mục tiêu: mô tả từng block con trong mega menu (title + danh sách items).
         * - FK `menu_id` cascade delete để khi bỏ menu cha sẽ dọn block con.
         * - `order`, `active` đảm bảo FE render đúng thứ tự cột và dễ dàng ẩn block tạm thời.
         */
        Schema::create('menu_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_blocks');
    }
};

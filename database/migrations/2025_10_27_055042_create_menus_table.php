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
         * Mục tiêu: định nghĩa menu cấp 1 (standard hoặc mega) để build header linh hoạt.
         * - `type` xác định cách FE render (link đơn hay mega-menu).
         * - `href` dùng cho menu đơn, `config` chứa dữ liệu tùy chỉnh (ví dụ mega hero).
         * - `order`, `active` giúp reorder và bật/tắt block trong Filament.
         */
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 20)->default('standard'); // standard | mega
            $table->string('href', 2048)->nullable();
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
        Schema::dropIfExists('menus');
    }
};

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
         * Mục tiêu: lưu vùng sản xuất chi tiết trong từng quốc gia (Bordeaux, Toscana...) để:
         * - FE render breadcrumbs / filters đa cấp.
         * - Tracking có thể thống kê sản phẩm theo terroir.
         * FK `country_id` dùng restrict để tránh orphan khi xóa country.
         */
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index(['country_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};

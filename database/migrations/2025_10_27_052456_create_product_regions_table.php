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
         * Mục tiêu: cho phép mỗi sản phẩm gắn nhiều vùng (ví dụ blend từ nhiều appellation) và xác định vùng chính
         * qua `order=0`. Pivot này hỗ trợ filter `region` đồng thời với country mà vẫn đảm bảo DISTINCT kết quả.
         */
        Schema::create('product_regions', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);

            $table->primary(['product_id', 'region_id']);
            $table->index(['region_id', 'product_id'], 'product_regions_region_product_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_regions');
    }
};

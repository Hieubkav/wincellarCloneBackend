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
         * Mục tiêu: bảng trung tâm cho nghiệp vụ sản phẩm (filter đa điều kiện, trang chi tiết, analytics).
         * Các nhóm trường:
         * - Dimension cố định (`product_category_id`, `type_id`) dùng FK vì xuất hiện thường xuyên trong UI & báo cáo.
         * - Bộ lọc linh hoạt (brand, origin, grape, material, ...) quản lý qua taxonomy nên không còn FK trực tiếp.
         * - Thông tin thương mại (`price`, `original_price`, `badges`) phục vụ CTA "Liên hệ" và tính discount_percent tại BE.
         * - Thông tin cảm quan (`alcohol_percent`, `volume_ml`, `description`) để FE dựng spec sheet / filter range.
         * - Trường SEO (`slug`, meta*) đảm bảo redirect tự sinh + meta fallback theo PLAN.md.
         */
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('type_id')->constrained('product_types')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('original_price')->default(0);
            $table->decimal('alcohol_percent', 5, 2)->nullable();
            $table->unsignedInteger('volume_ml')->nullable();
            $table->json('badges')->nullable();
            $table->boolean('active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->index('price');
            $table->index('alcohol_percent');
            $table->index('volume_ml');
            $table->index(['type_id', 'product_category_id'], 'products_type_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


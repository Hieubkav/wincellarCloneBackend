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
         * Pivot gắn product với bất kỳ term nào (brand, country, material...).
         * `is_primary` dùng để đánh dấu term chính (ví dụ brand chính, country chính) phục vụ breadcrumb hoặc detail.
         * `position` duy trì thứ tự hiển thị khi product thuộc nhiều term cùng group.
         * `extra` cho phép bổ sung text phụ (ví dụ ghi chú pairing, vintage note).
         */
        Schema::create('product_term_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('catalog_terms')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'term_id'], 'product_term_assignments_unique');
            $table->index(['term_id', 'product_id'], 'product_term_assignments_term_product_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_term_assignments');
    }
};


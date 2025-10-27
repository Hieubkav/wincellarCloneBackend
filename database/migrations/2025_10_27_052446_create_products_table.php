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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('type_id')->constrained('product_types')->restrictOnDelete();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
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

            $table->index(
                ['brand_id', 'country_id', 'region_id', 'type_id', 'product_category_id'],
                'products_filter_index'
            );
            $table->index('price');
            $table->index('alcohol_percent');
            $table->index('volume_ml');
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

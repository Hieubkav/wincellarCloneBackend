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
        Schema::create('product_grapes', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grape_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);

            $table->primary(['product_id', 'grape_id']);
            $table->index(['grape_id', 'product_id'], 'product_grapes_grape_product_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_grapes');
    }
};

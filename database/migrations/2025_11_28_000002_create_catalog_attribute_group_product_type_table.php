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
        Schema::create('catalog_attribute_group_product_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                ->constrained('catalog_attribute_groups')
                ->cascadeOnDelete();
            $table->foreignId('type_id')
                ->constrained('product_types')
                ->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['group_id', 'type_id']);
            $table->index(['type_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_attribute_group_product_type');
    }
};

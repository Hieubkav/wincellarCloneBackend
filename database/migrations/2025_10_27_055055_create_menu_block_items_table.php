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
        Schema::create('menu_block_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_block_id')->constrained('menu_blocks')->cascadeOnDelete();
            $table->string('label');
            $table->string('href', 2048);
            $table->string('badge', 50)->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['menu_block_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_block_items');
    }
};

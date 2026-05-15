<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_filter_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route_prefix')->default('san-pham')->index();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_filter_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('product_filter_groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('filter_payload')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['group_id', 'slug'], 'product_filter_presets_group_slug_unique');
            $table->index(['active', 'position'], 'product_filter_presets_active_position_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filter_presets');
        Schema::dropIfExists('product_filter_groups');
    }
};

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
         * Mục tiêu: quản lý nội dung bài viết (editorial) cho landing, SEO, component EditorialSpotlight.
         * - `slug` làm URL chính + trigger redirect khi rename.
         * - `author_id` FK user để audit trách nhiệm biên tập.
         * - `active`, trường meta giúp ẩn bài mà không xóa và tối ưu hiển thị trên social.
         */
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

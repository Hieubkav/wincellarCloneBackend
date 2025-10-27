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
         * Mục tiêu: thu thập dữ liệu hành vi (visitor/session/event) và tổng hợp daily để phục vụ dashboard 7/30/90 ngày.
         * Thiết kế tách raw -> aggregate nhằm đảm bảo có thể purge raw >90 ngày nhưng vẫn giữ số liệu tổng.
         */
        // Lưu thông tin định danh ẩn danh để gộp nhiều session của cùng người dùng (cookie `anon_id`).
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('anon_id', 64)->unique();
            $table->string('ip_hash', 128);
            $table->string('user_agent')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('last_seen_at');
        });

        // Một visitor có thể có nhiều session để tính thời lượng và phân tích funnel.
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['visitor_id', 'started_at'], 'visitor_sessions_visitor_started_index');
        });

        // Event raw: product_view/article_view/cta_contact, làm nguồn dữ liệu cho dashboard & redirect CTA.
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('visitor_sessions')->cascadeOnDelete();
            $table->enum('event_type', ['product_view', 'article_view', 'cta_contact']);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at'], 'tracking_events_type_time_index');
            $table->index(['product_id', 'event_type'], 'tracking_events_product_index');
            $table->index(['article_id', 'event_type'], 'tracking_events_article_index');
        });

        // Bảng tổng hợp daily giúp truy vấn 7/30/90/all-time nhanh, không phải scan hàng triệu event raw.
        Schema::create('tracking_event_aggregates_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('event_type', ['product_view', 'article_view', 'cta_contact']);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(
                ['date', 'event_type', 'product_id', 'article_id'],
                'tracking_event_daily_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_event_aggregates_daily');
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('visitor_sessions');
        Schema::dropIfExists('visitors');
    }
};

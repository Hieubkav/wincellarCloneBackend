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

        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['visitor_id', 'started_at'], 'visitor_sessions_visitor_started_index');
        });

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

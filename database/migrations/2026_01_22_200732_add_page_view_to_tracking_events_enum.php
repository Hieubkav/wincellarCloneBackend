<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE tracking_events MODIFY COLUMN event_type ENUM('product_view', 'article_view', 'cta_contact', 'page_view')");
        DB::statement("ALTER TABLE tracking_event_aggregates_daily MODIFY COLUMN event_type ENUM('product_view', 'article_view', 'cta_contact', 'page_view')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("DELETE FROM tracking_events WHERE event_type = 'page_view'");
        DB::statement("DELETE FROM tracking_event_aggregates_daily WHERE event_type = 'page_view'");
        DB::statement("ALTER TABLE tracking_events MODIFY COLUMN event_type ENUM('product_view', 'article_view', 'cta_contact')");
        DB::statement("ALTER TABLE tracking_event_aggregates_daily MODIFY COLUMN event_type ENUM('product_view', 'article_view', 'cta_contact')");
    }
};

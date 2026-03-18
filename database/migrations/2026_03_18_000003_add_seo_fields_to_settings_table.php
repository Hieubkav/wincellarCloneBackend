<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('site_tagline')->nullable()->after('meta_default_keywords');
            $table->string('organization_legal_name')->nullable()->after('site_tagline');
            $table->string('organization_short_name')->nullable()->after('organization_legal_name');
            $table->string('primary_phone')->nullable()->after('organization_short_name');
            $table->string('primary_email')->nullable()->after('primary_phone');
            $table->string('price_range')->nullable()->after('primary_email');
            $table->json('social_links_schema')->nullable()->after('price_range');
            $table->string('default_meta_title_template')->nullable()->after('social_links_schema');
            $table->string('default_og_title')->nullable()->after('default_meta_title_template');
            $table->string('default_og_description', 500)->nullable()->after('default_og_title');
            $table->boolean('indexing_enabled')->default(true)->after('default_og_description');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_tagline',
                'organization_legal_name',
                'organization_short_name',
                'primary_phone',
                'primary_email',
                'price_range',
                'social_links_schema',
                'default_meta_title_template',
                'default_og_title',
                'default_og_description',
                'indexing_enabled',
            ]);
        });
    }
};

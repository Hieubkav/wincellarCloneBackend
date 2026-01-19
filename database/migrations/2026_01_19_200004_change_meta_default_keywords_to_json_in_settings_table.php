<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('meta_default_keywords_new')->nullable();
        });

        DB::table('settings')->get()->each(function ($setting) {
            $keywords = $setting->meta_default_keywords;
            $keywordsArray = [];
            
            if (!empty($keywords)) {
                $keywordsArray = array_map('trim', explode(',', $keywords));
                $keywordsArray = array_filter($keywordsArray);
                $keywordsArray = array_values($keywordsArray);
            }
            
            DB::table('settings')
                ->where('id', $setting->id)
                ->update(['meta_default_keywords_new' => json_encode($keywordsArray)]);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('meta_default_keywords');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('meta_default_keywords_new', 'meta_default_keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('meta_default_keywords_new')->nullable();
        });

        DB::table('settings')->get()->each(function ($setting) {
            $keywords = json_decode($setting->meta_default_keywords, true) ?? [];
            $keywordsString = implode(', ', $keywords);
            
            DB::table('settings')
                ->where('id', $setting->id)
                ->update(['meta_default_keywords_new' => $keywordsString]);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('meta_default_keywords');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('meta_default_keywords_new', 'meta_default_keywords');
        });
    }
};

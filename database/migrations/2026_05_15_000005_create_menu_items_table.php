<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('label');
            $table->string('href')->nullable();
            $table->string('semantic_type', 50)->nullable();
            $table->json('route_payload')->nullable();
            $table->string('badge', 50)->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'order']);
            $table->index(['menu_id', 'depth', 'order']);
            $table->index(['active', 'order']);
        });

        $this->backfillFromLegacyTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }

    private function backfillFromLegacyTables(): void
    {
        DB::transaction(function (): void {
            DB::table('menus')
                ->orderBy('order')
                ->orderBy('id')
                ->get()
                ->each(function ($menu): void {
                    $blocks = DB::table('menu_blocks')
                        ->where('menu_id', $menu->id)
                        ->orderBy('order')
                        ->orderBy('id')
                        ->get();

                    foreach ($blocks as $block) {
                        $blockNodeId = DB::table('menu_items')->insertGetId([
                            'menu_id' => $menu->id,
                            'parent_id' => null,
                            'label' => $block->title,
                            'href' => null,
                            'depth' => 0,
                            'order' => (int) $block->order,
                            'active' => (bool) $block->active,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        DB::table('menu_block_items')
                            ->where('menu_block_id', $block->id)
                            ->orderBy('order')
                            ->orderBy('id')
                            ->get()
                            ->each(function ($item) use ($menu, $blockNodeId): void {
                                DB::table('menu_items')->insert([
                                    'menu_id' => $menu->id,
                                    'parent_id' => $blockNodeId,
                                    'label' => $item->label,
                                    'href' => $item->href,
                                    'semantic_type' => $item->semantic_type ?? null,
                                    'route_payload' => $item->route_payload ?? null,
                                    'badge' => $item->badge,
                                    'depth' => 1,
                                    'order' => (int) $item->order,
                                    'active' => (bool) $item->active,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            });
                    }
                });
        });
    }
};

<?php

namespace App\Console\Commands;

use App\Models\CatalogAttributeGroup;
use App\Models\ProductType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportCatalogBaseline extends Command
{
    protected $signature = 'catalog:export-baseline';

    protected $description = 'Export current catalog data to baseline JSON files';

    public function handle(): int
    {
        $this->info('Exporting catalog baseline...');

        $dataPath = database_path('seeders/data');
        if (! File::isDirectory($dataPath)) {
            File::makeDirectory($dataPath, 0755, true);
        }

        // Export Catalog Attribute Groups with terms
        $groups = CatalogAttributeGroup::with('terms')
            ->orderBy('position')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'filter_type' => $group->filter_type,
                    'input_type' => $group->input_type,
                    'is_filterable' => $group->is_filterable,
                    'position' => $group->position,
                    'display_config' => $group->display_config,
                    'icon_path' => $group->icon_path,
                    'created_at' => $group->created_at?->toISOString(),
                    'updated_at' => $group->updated_at?->toISOString(),
                    'terms' => $group->terms->map(function ($term) {
                        return [
                            'id' => $term->id,
                            'slug' => $term->slug,
                            'name' => $term->name,
                            'description' => $term->description,
                            'icon_type' => $term->icon_type,
                            'icon_value' => $term->icon_value,
                            'metadata' => $term->metadata,
                            'is_active' => $term->is_active,
                            'position' => $term->position,
                        ];
                    })->toArray(),
                ];
            });

        File::put(
            $dataPath.'/catalog-attribute-groups.json',
            json_encode($groups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info('✓ Exported '.count($groups).' attribute groups');

        // Export Product Types with attribute groups
        $types = ProductType::with('attributeGroups')
            ->orderBy('order')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'description' => $type->description,
                    'order' => $type->order,
                    'active' => $type->active,
                    'created_at' => $type->created_at?->toISOString(),
                    'updated_at' => $type->updated_at?->toISOString(),
                    'attribute_groups' => $type->attributeGroups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'code' => $group->code,
                            'name' => $group->name,
                            'filter_type' => $group->filter_type,
                            'input_type' => $group->input_type,
                            'is_filterable' => $group->is_filterable,
                            'position' => $group->position,
                            'display_config' => $group->display_config,
                            'icon_path' => $group->icon_path,
                            'created_at' => $group->created_at?->toISOString(),
                            'updated_at' => $group->updated_at?->toISOString(),
                            'pivot' => [
                                'type_id' => $group->pivot->type_id,
                                'group_id' => $group->pivot->group_id,
                                'position' => $group->pivot->position,
                            ],
                        ];
                    })->toArray(),
                ];
            });

        File::put(
            $dataPath.'/product-types.json',
            json_encode($types, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->info('✓ Exported '.count($types).' product types');
        $this->info('✓ Baseline exported successfully to database/seeders/data/');

        return self::SUCCESS;
    }
}

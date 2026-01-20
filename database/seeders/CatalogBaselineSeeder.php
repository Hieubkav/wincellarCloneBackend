<?php

namespace Database\Seeders;

use App\Models\CatalogAttributeGroup;
use App\Models\CatalogTerm;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CatalogBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = database_path('seeders/data');
        $groupsFile = $dataPath.'/catalog-attribute-groups.json';
        $typesFile = $dataPath.'/product-types.json';

        if (!file_exists($groupsFile) || !file_exists($typesFile)) {
            throw new RuntimeException('Thiếu file baseline JSON trong database/seeders/data.');
        }

        $groups = json_decode(file_get_contents($groupsFile), true);
        $types = json_decode(file_get_contents($typesFile), true);

        if (!is_array($groups) || !is_array($types)) {
            throw new RuntimeException('File baseline JSON không hợp lệ.');
        }

        DB::transaction(function () use ($groups, $types) {
            $groupMap = [];

            foreach ($groups as $group) {
                $code = $group['code'] ?? null;
                if (!$code) {
                    throw new RuntimeException('Thiếu code cho catalog attribute group.');
                }

                $groupModel = CatalogAttributeGroup::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $group['name'] ?? $code,
                        'filter_type' => $group['filter_type'] ?? 'chon_don',
                        'input_type' => $group['input_type'] ?? null,
                        'is_filterable' => (bool) ($group['is_filterable'] ?? false),
                        'position' => (int) ($group['position'] ?? 0),
                        'display_config' => $group['display_config'] ?? null,
                        'icon_path' => $group['icon_path'] ?? null,
                    ]
                );

                $groupMap[$code] = $groupModel;

                foreach (($group['terms'] ?? []) as $term) {
                    $slug = $term['slug'] ?? null;
                    if (!$slug) {
                        throw new RuntimeException("Thiếu slug cho term trong group {$code}.");
                    }

                    CatalogTerm::updateOrCreate(
                        [
                            'group_id' => $groupModel->id,
                            'slug' => $slug,
                        ],
                        [
                            'name' => $term['name'] ?? $slug,
                            'description' => $term['description'] ?? null,
                            'icon_type' => $term['icon_type'] ?? null,
                            'icon_value' => $term['icon_value'] ?? null,
                            'metadata' => $term['metadata'] ?? null,
                            'is_active' => (bool) ($term['is_active'] ?? true),
                            'position' => (int) ($term['position'] ?? 0),
                        ]
                    );
                }
            }

            foreach ($types as $type) {
                $slug = $type['slug'] ?? null;
                if (!$slug) {
                    throw new RuntimeException('Thiếu slug cho product type.');
                }

                $typeModel = ProductType::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $type['name'] ?? $slug,
                        'order' => (int) ($type['order'] ?? 0),
                        'active' => (bool) ($type['active'] ?? true),
                    ]
                );

                $syncData = [];
                foreach (($type['attribute_groups'] ?? []) as $group) {
                    $groupCode = $group['code'] ?? null;
                    if (!$groupCode || !isset($groupMap[$groupCode])) {
                        continue;
                    }

                    $position = $group['pivot']['position'] ?? $group['position'] ?? 0;
                    $syncData[$groupMap[$groupCode]->id] = ['position' => (int) $position];
                }

                if (!empty($syncData)) {
                    $typeModel->attributeGroups()->sync($syncData);
                }
            }
        });
    }
}

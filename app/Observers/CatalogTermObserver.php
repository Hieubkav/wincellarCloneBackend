<?php

namespace App\Observers;

use App\Models\CatalogTerm;
use Illuminate\Support\Str;

class CatalogTermObserver
{
    public function creating(CatalogTerm $catalogTerm): void
    {
        if (empty($catalogTerm->slug)) {
            $catalogTerm->slug = $this->generateUniqueSlug($catalogTerm->name, $catalogTerm->group_id);
        }

        if ($catalogTerm->position === null) {
            $catalogTerm->position = CatalogTerm::where('group_id', $catalogTerm->group_id)->max('position') + 1;
        }
    }

    public function updating(CatalogTerm $catalogTerm): void
    {
        if ($catalogTerm->isDirty('name')) {
            $catalogTerm->slug = $this->generateUniqueSlug($catalogTerm->name, $catalogTerm->group_id, $catalogTerm->id);
        }
    }

    private function generateUniqueSlug(string $name, int $groupId, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $groupId, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, int $groupId, ?int $ignoreId = null): bool
    {
        $query = CatalogTerm::where('group_id', $groupId)->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}

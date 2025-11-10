# Filament 4.x Standards - Comprehensive Guide

Complete Filament 4.x coding standards for Laravel 12 project with custom Schema namespace, Vietnamese UI, and Observer patterns.

## Full details available in current SKILL.md

This file serves as extended documentation. For comprehensive patterns, examples, and implementation details, refer to the complete content in SKILL.md which already contains:

- Complete namespace structure (Schemas vs Forms)
- Vietnamese UI standards
- Observer patterns for SEO and images
- RelationManagers implementation
- Settings pages setup
- ImagesRelationManager integration
- Complete code examples
- Troubleshooting guides

## Additional Advanced Topics

### Performance Optimization

**Eager Loading:**
```php
->modifyQueryUsing(fn($query) => $query->with(['category', 'tags', 'coverImage']))
```

**Caching Navigation Badge:**
```php
public static function getNavigationBadge(): ?string
{
    return Cache::remember('product.active.count', 600, function() {
        return (string) static::getModel()::where('active', true)->count();
    });
}
```

### Advanced Patterns

**Conditional Field Visibility:**
```php
Select::make('type')->live(),

Group::make([
    // Fields only visible when type = 'variable'
])->visible(fn(Get $get) => $get('type') === 'variable'),
```

**Dynamic Options:**
```php
Select::make('category_id')
    ->options(fn() => Category::pluck('name', 'id'))
    ->reactive()
    ->afterStateUpdated(fn($state, callable $set) => 
        $set('subcategory_id', null)
    ),
```

For complete implementation guide, see SKILL.md.

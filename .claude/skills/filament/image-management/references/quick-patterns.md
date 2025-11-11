## Quick Patterns

### 1. Multiple Images (Gallery)

**Model:**
```php
public function images(): MorphMany
{
    return $this->morphMany(Image::class, 'model')
        ->orderBy('order');
}

public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')
        ->where('order', 0);
}
```

**Resource:**
```php
public static function getRelations(): array
{
    return [ImagesRelationManager::class];
}
```

### 2. Single Image (BelongsTo)

**Model:**
```php
public function logoImage(): BelongsTo
{
    return $this->belongsTo(Image::class, 'logo_image_id');
}
```

**Form:**
```php
Select::make('logo_image_id')
    ->label('Logo')
    ->relationship('logoImage', 'file_path')
    ->searchable();
```

### 3. CheckboxList Picker

```php
Action::make('selectFromLibrary')
    ->label('Chọn từ thư viện')
    ->form(function() {
        $images = Image::where('active', true)
            ->limit(100)
            ->get();
        
        return [
            CheckboxList::make('selected_images')
                ->options($images->mapWithKeys(fn($img) => [
                    $img->id => view('filament.image-option', [
                        'image' => $img
                    ])->render()
                ]))
                ->columns(4),
        ];
    })
    ->action(function($data, $record) {
        foreach ($data['selected_images'] as $imageId) {
            Image::find($imageId)->update([
                'model_type' => get_class($record),
                'model_id' => $record->id,
            ]);
        }
    });
```


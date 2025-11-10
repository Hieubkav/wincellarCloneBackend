# Image Management System - Comprehensive Guide

Complete guide to the centralized polymorphic image management system with CheckboxList picker, WebP auto-conversion, and Observer patterns.

## System Architecture

### Database Schema

```sql
CREATE TABLE images (
    id BIGINT UNSIGNED PRIMARY KEY,
    model_type VARCHAR(255) NOT NULL,  -- Polymorphic
    model_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(50),
    size BIGINT UNSIGNED,
    alt_text VARCHAR(255),
    title VARCHAR(255),
    `order` INT UNSIGNED DEFAULT 0,  -- 0 = cover/primary
    active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX(model_type, model_id),
    INDEX(`order`),
    INDEX(active)
);
```

### Complete Implementation Details

For full implementation details, code examples, and patterns, refer to the current SKILL.md which already includes:

- Complete model relationships (morphMany, morphOne, belongsTo)
- ImagesRelationManager full implementation
- CheckboxList picker (v1.2.0 with native Filament)
- WebP auto-conversion logic
- ImageObserver for auto alt-text and order
- Soft delete with reference cleanup
- Gallery reordering
- Cover image selection
- Complete troubleshooting guide

## Advanced Topics

### Batch Image Operations

```php
// Batch update order
Image::whereIn('id', $imageIds)
    ->each(function($image, $index) {
        $image->update(['order' => $index + 1]);
    });

// Batch update alt text
Image::where('model_type', Product::class)
    ->whereNull('alt_text')
    ->each(function($image) {
        $model = $image->model;
        $image->update(['alt_text' => $model->name ?? 'Image']);
    });
```

### Image Optimization

```php
// Optimize existing images
Image::whereDoesntHave('webpVersion')
    ->chunk(100, function($images) {
        foreach ($images as $image) {
            $this->convertToWebP($image);
        }
    });
```

For complete implementation, see SKILL.md.

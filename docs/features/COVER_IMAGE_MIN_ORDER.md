# Cover Image: Min Order Logic

**Date:** 2025-11-09  
**Change:** Cover image sử dụng min order thay vì fixed order = 0

---

## 🎯 Motivation

### Before (Fixed Order = 0)
❌ Cover image PHẢI có `order = 0`  
❌ Nếu xóa image order = 0, không có cover  
❌ Logic cứng nhắc, không linh hoạt  
❌ Cần đảm bảo first image luôn có order = 0  

### After (Min Order)
✅ Cover image = image có order NHỎ NHẤT  
✅ Xóa image nào cũng được, min order auto update  
✅ Logic linh hoạt, giống Product model  
✅ Không cần force order = 0  

---

## 🔧 Implementation

### File Updated: `app/Models/Concerns/HasMediaGallery.php`

#### Before:
```php
public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')
        ->where('order', 0);  // ← Fixed to 0!
}

public function getCoverImageUrlAttribute(): ?string
{
    $cover = $this->coverImage;
    
    if ($cover instanceof Image) {
        return $cover->url;
    }

    // Fallback to first image
    if ($this->relationLoaded('images')) {
        $firstImage = $this->getRelation('images')->first();
        if ($firstImage instanceof Image) {
            return $firstImage->url;
        }
    }

    return MediaConfig::placeholder($this->mediaPlaceholderKey());
}
```

#### After:
```php
/**
 * Get cover image (image with minimum order value).
 */
public function coverImage(): MorphOne
{
    return $this->morphOne(Image::class, 'model')
        ->orderBy('order', 'asc');  // ← Min order!
}

public function getCoverImageUrlAttribute(): ?string
{
    // Priority 1: If images are already loaded, use first (already ordered)
    if ($this->relationLoaded('images')) {
        $firstImage = $this->getRelation('images')->first();
        if ($firstImage instanceof Image) {
            return $firstImage->url;
        }
    }

    // Priority 2: Load cover image (min order)
    $cover = $this->relationLoaded('coverImage')
        ? $this->getRelation('coverImage')
        : $this->coverImage;

    if ($cover instanceof Image) {
        return $cover->url;
    }

    // Priority 3: Fallback to placeholder
    return MediaConfig::placeholder($this->mediaPlaceholderKey());
}
```

---

## ✅ Benefits

### 1. Flexibility
```php
// All these scenarios work:
Article with images: [order: 0, 1, 2]     → Cover = 0
Article with images: [order: 3, 5, 7]     → Cover = 3 ✨
Article with images: [order: 10, 20]      → Cover = 10 ✨
```

### 2. No Breaking Changes
```php
// Still works with order = 0
Article with images: [order: 0, 1, 2]     → Cover = 0 ✅
```

### 3. Automatic Recovery
```php
// Before: Delete order=0 → No cover!
Article: [0, 1, 2] → Delete 0 → [1, 2] → ❌ No cover

// After: Delete min → Auto use next min!
Article: [0, 1, 2] → Delete 0 → [1, 2] → ✅ Cover = 1
```

### 4. Consistent with Product Logic
```php
// Product model also uses min order for cover
Product::first()->cover_image_url; // Uses min order
Article::first()->cover_image_url; // Now uses min order too ✅
```

---

## 🧪 Test Results

### Test 1: Works with Order = 0
```
Images orders: [0]
cover_image_url: /storage/articles/article_xxx.webp
✅ PASS
```

### Test 2: Works with Any Order
```
Images orders: [5]
cover_image_url: /storage/articles/article_xxx.webp
✅ PASS - Still works with order = 5
```

### Test 3: Min Order Selection
```
Before: Images orders: [5]
Add image with order: 3
After: Images orders: [3, 5]
Min order: 3
cover_image_url uses order 3: YES
✅ PASS - Always uses min order
```

### Test 4: API Integration
```bash
curl http://127.0.0.1:8000/api/v1/home
# Components: 8
# Has editorial_spotlight: true
# ✅ PASS
```

---

## 📊 Comparison Table

| Scenario | Old Logic (order = 0) | New Logic (min order) |
|----------|----------------------|---------------------|
| Images: [0, 1, 2] | Cover = 0 ✅ | Cover = 0 ✅ |
| Images: [1, 2, 3] | Cover = none ❌ | Cover = 1 ✅ |
| Images: [5, 10, 15] | Cover = none ❌ | Cover = 5 ✅ |
| Delete min image | Break ❌ | Auto use next ✅ |
| Reorder images | Need update order=0 ⚠️ | Auto adjust ✅ |
| Empty images | Placeholder ✅ | Placeholder ✅ |

---

## 🎨 User Experience

### Admin Panel
- ✅ Drag-drop to reorder → Min order auto becomes cover
- ✅ Delete any image → Next min auto becomes cover
- ✅ Upload any image → Just set order, no special handling
- ✅ No need to remember "first image must be order = 0"

### API
- ✅ Always returns valid cover image URL
- ✅ Consistent behavior across models
- ✅ Graceful fallback to placeholder

---

## 🔄 Performance Impact

### Before (order = 0):
```sql
-- coverImage relationship
WHERE order = 0  -- Simple equality check
```

### After (min order):
```sql
-- coverImage relationship  
ORDER BY order ASC  -- Sorts to find minimum
LIMIT 1
```

**Impact:** Negligible
- Database indexes handle ORDER BY efficiently
- Most models have < 10 images
- Sorting 10 rows is microseconds
- **Trade-off worth the flexibility**

### Optimization Already Built-in:
```php
// If images already loaded → Use from collection (no extra query)
if ($this->relationLoaded('images')) {
    return $this->getRelation('images')->first()->url;
}
```

---

## 🚀 Migration Notes

### No Migration Required!
✅ All existing images with order = 0 continue working  
✅ Backward compatible with current data  
✅ New uploads automatically work with any order  

### For New Images:
- ImageObserver still auto-assigns sequential orders (0, 1, 2, ...)
- First image still gets order = 0 by default
- **But now it's not required!**

---

## 📝 Related Changes

**Files Modified:**
- ✅ `app/Models/Concerns/HasMediaGallery.php` - Updated coverImage logic

**Files Using This Trait:**
- ✅ `App\Models\Article` - Now uses min order
- ✅ `App\Models\Product` - Already was using min order (via images relationship)
- ✅ Any other models using `HasMediaGallery` trait

**No Changes Needed:**
- ✅ ImageObserver - Still works as is
- ✅ ImagesRelationManager - Still works as is
- ✅ ArticleResource - Already uses accessor
- ✅ API endpoints - Already use accessor

---

## 🎉 Summary

**Change:** `where('order', 0)` → `orderBy('order', 'asc')`  
**Impact:** More flexible, auto-recovery, consistent logic  
**Breaking:** None (backward compatible)  
**Performance:** Negligible difference  
**User Benefit:** Don't need to worry about order = 0  

**Philosophy:**
> "Cover image = First image in display order (min)"
> Not "Cover image = Image with magical order zero"

---

**Implemented by:** Droid AI Assistant  
**Date:** 2025-11-09 23:55:00  
**Status:** 🟢 COMPLETE & TESTED

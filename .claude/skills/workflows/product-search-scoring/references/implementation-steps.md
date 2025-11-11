## Implementation Steps

### Step 1: Implement Text Normalization

```php
public static function removeVietnameseAccents(string $string): string
{
    $trans = [
        'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        // ... (full mapping in CLAUDE.md)
        'đ' => 'd', 'Đ' => 'D'
    ];
    return strtr($string, $trans);
}

public static function normalizeForSearch(string $string): string
{
    $string = self::removeVietnameseAccents($string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s]/', ' ', $string);
    $string = preg_replace('/\s+/', ' ', $string);
    return trim($string);
}
```

### Step 2: Implement Keyword Splitting

```php
public static function splitSearchTerms(string $string): array
{
    $normalized = self::normalizeForSearch($string);
    $words = explode(' ', $normalized);
    $stopWords = ['va', 'la', 'cua', 'voi', 'theo', 'tu'];
    
    return array_filter($words, function ($word) use ($stopWords) {
        return strlen($word) > 0 && !in_array($word, $stopWords);
    });
}
```

### Step 3: Build Search Query

```php
// In ProductFilter Livewire component
if ($filters['search'] !== '') {
    $searchTerms = StringHelper::splitSearchTerms($filters['search']);
    
    if (!empty($searchTerms)) {
        $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere('search_index', 'like', '%' . $term . '%');
            }
        });
    }
}
```

### Step 4: Apply Additional Filters

**Category Filters:**
```php
if ($filters['giay']) {
    $query->where('name', 'like', '%giày%');
}

if ($filters['tatvo']) {
    $query->where(function ($subQuery) {
        $subQuery->where('name', 'like', '%tất%')
            ->orWhere('name', 'like', '%vớ%')
            ->orWhere('name', 'like', '%dép%');
    });
}
```

**Brand/Type/Tag Filters:**
```php
if (!empty($filters['typeSelected'])) {
    $query->whereIn('type', $filters['typeSelected']);
}

if (!empty($filters['brandSelected'])) {
    $query->whereIn('brand', $filters['brandSelected']);
}

if (!empty($filters['tagSelected'])) {
    $query->whereHas('tags', function ($tagQuery) use ($filters) {
        $tagQuery->whereIn('name', $filters['tagSelected']);
    });
}
```

### Step 5: Implement Sorting

```php
protected function applySort(Builder $query, string $sort): void
{
    $priceExpression = '(SELECT MIN(v.price) FROM variants v 
                       WHERE v.product_id = products.id AND v.stock > 0)';

    switch ($sort) {
        case 'price_asc':
            $query->orderByRaw('COALESCE(' . $priceExpression . ', 999999999) asc')
                ->orderBy('products.id', 'asc');
            break;
        case 'price_desc':
            $query->orderByRaw('COALESCE(' . $priceExpression . ', 0) desc')
                ->orderBy('products.id', 'desc');
            break;
        default: // latest
            $query->orderBy('products.updated_at', 'desc')
                ->orderBy('products.id', 'desc');
    }
}
```

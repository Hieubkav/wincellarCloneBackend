---
name: api-cache-invalidation
description: Automatic cache invalidation system với Laravel Observers và Next.js On-Demand Revalidation. Tự động sync data real-time giữa backend và frontend khi admin update. USE WHEN cần setup cache management, sync frontend-backend, API cache strategy, hoặc user phàn nàn "phải Ctrl+F5 mới thấy data mới".
---

# API Cache Invalidation System

Hệ thống tự động invalidate cache khi có data update, kết hợp **Laravel Observers** (backend) và **Next.js ISR + On-Demand Revalidation** (frontend).

## When to Activate This Skill

- User nói "cache không update"
- User nói "phải Ctrl+F5 mới thấy data mới"
- User muốn "sync data real-time"
- Cần setup cache strategy cho API
- Frontend không reflect backend changes
- User mentions "cache invalidation" or "revalidation"

## Core Components

### 1. Backend: Laravel Observers + Cache Version
- Observer detect model changes (create/update/delete)
- Auto-increment cache version
- Trigger Next.js on-demand revalidation
- Return cache version trong API response

### 2. Frontend: Next.js ISR + On-Demand Revalidation
- Time-based: Revalidate mỗi 10s (fallback)
- On-demand: Instant revalidation khi backend trigger
- Cache version tracking
- Revalidation API endpoint

## Quick Setup Workflow

### Phase 1: Backend Setup (Laravel)

**Step 1: Create RevalidationService**
```php
// app/Services/RevalidationService.php
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RevalidationService
{
    public function triggerRevalidation(array $paths = []): bool
    {
        $url = config('services.nextjs.revalidate_url');
        $secret = config('services.nextjs.revalidate_secret');
        
        return Http::timeout(5)->post($url, [
            'secret' => $secret,
            'paths' => $paths,
        ])->successful();
    }
    
    public function revalidateAll(): bool
    {
        return $this->triggerRevalidation(['/', '/products', '/filter']);
    }
}
```

**Step 2: Update Observers**
```php
// app/Observers/ProductObserver.php
private function incrementCacheVersion(): void
{
    $version = (int) Cache::get('api_cache_version', 0);
    Cache::put('api_cache_version', $version + 1);
    
    // Trigger Next.js revalidation
    try {
        app(\App\Services\RevalidationService::class)->revalidateAll();
    } catch (\Throwable $e) {
        \Log::warning('Revalidation failed', ['error' => $e->getMessage()]);
    }
}

public function created(Product $product): void { $this->incrementCacheVersion(); }
public function updated(Product $product): void { $this->incrementCacheVersion(); }
public function deleted(Product $product): void { $this->incrementCacheVersion(); }
```

**Step 3: Include Cache Version in API**
```php
// app/Http/Controllers/Api/V1/MenuController.php
use Illuminate\Support\Facades\Cache;

public function __invoke(): JsonResponse
{
    $data = Menu::active()->get();
    $cacheVersion = (int) Cache::get('api_cache_version', 0);
    
    return response()->json([
        'data' => $data,
        'meta' => ['cache_version' => $cacheVersion],
    ]);
}
```

**Step 4: Config Setup**
```php
// config/services.php
'nextjs' => [
    'revalidate_url' => env('NEXT_REVALIDATE_URL'),
    'revalidate_secret' => env('NEXT_REVALIDATE_SECRET'),
],
```

```bash
# .env
NEXT_REVALIDATE_URL=http://localhost:3000/api/revalidate
NEXT_REVALIDATE_SECRET=your-secret-token-change-in-production
```

### Phase 2: Frontend Setup (Next.js)

**Step 1: Create Revalidation Endpoint**
```typescript
// app/api/revalidate/route.ts
import { revalidatePath } from 'next/cache';
import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  const { secret, paths } = await request.json();
  
  if (secret !== process.env.REVALIDATE_SECRET) {
    return NextResponse.json({ success: false }, { status: 401 });
  }
  
  for (const path of paths || []) {
    revalidatePath(path, 'page');
  }
  
  return NextResponse.json({ 
    success: true, 
    revalidated: { paths, timestamp: new Date().toISOString() }
  });
}
```

**Step 2: Update API Client**
```typescript
// lib/api/menus.ts
export interface MenusResponse {
  data: MenuItem[];
  meta: { cache_version: number };
}

export async function fetchMenus(): Promise<MenuItem[]> {
  const response = await apiFetch<MenusResponse>("v1/menus", {
    next: { revalidate: 10 }, // Fallback: revalidate mỗi 10s
  });
  return response.data;
}
```

**Step 3: Environment Setup**
```bash
# .env.local
REVALIDATE_SECRET=your-secret-token-change-in-production
```

## Models to Observe

Apply cache invalidation cho TẤT CẢ models quan trọng:

```php
// Menu system
- Menu
- MenuBlock  
- MenuBlockItem

// Content
- HomeComponent
- Product
- Article
- Image

// Taxonomy
- CatalogTerm (optional)
- CatalogAttributeGroup (optional)
```

## Cache Strategy Comparison

| Strategy | Update Time | Server Load | Use Case |
|----------|------------|-------------|----------|
| **No cache** | Real-time | 🔥 Very high | Dev only |
| **Time-based only (10s)** | 10 seconds | ✅ Low | Simple sites |
| **On-demand only** | 1-2 seconds | ⚠️ Medium | Medium traffic |
| **Hybrid (10s + On-demand)** | 1-2s with fallback | ✅ Optimal | **RECOMMENDED** |

## Testing the System

### Test 1: Check Cache Version
```bash
curl http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'
# Output: 4
```

### Test 2: Update Data
```bash
# Update menu trong admin panel hoặc:
php artisan tinker
> $menu = App\Models\Menu::first();
> $menu->touch();
```

### Test 3: Verify Version Increment
```bash
curl http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'
# Output: 5 (đã tăng!)
```

### Test 4: Check Frontend Update
```bash
# F5 trình duyệt trong 1-2 giây → Thấy data mới!
```

## Common Issues & Solutions

### Issue 1: "Revalidation not working"
**Check:**
- Next.js server đang chạy?
- NEXT_REVALIDATE_URL đúng?
- NEXT_REVALIDATE_SECRET khớp giữa backend và frontend?

**Debug:**
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "revalidation"

# Test endpoint
curl -X POST http://localhost:3000/api/revalidate \
  -H "Content-Type: application/json" \
  -d '{"secret":"your-secret","paths":["/"]}'
```

### Issue 2: "Cache version not incrementing"
**Check:**
- Observer đã được register? (Model có #[ObservedBy] attribute?)
- incrementCacheVersion() được gọi trong created/updated/deleted?

**Debug:**
```bash
php artisan tinker
> Cache::get('api_cache_version')
> Cache::put('api_cache_version', 0) # Reset for testing
```

### Issue 3: "Frontend still shows old data"
**Check:**
- Browser cache? (Hard refresh: Ctrl+Shift+R)
- Next.js build cache? (Delete .next folder và rebuild)
- API response có meta.cache_version?

## Performance Considerations

### Optimal Settings
- **Revalidate time:** 10 seconds (balance between freshness và load)
- **HTTP timeout:** 5 seconds (avoid blocking)
- **Fail silently:** Log warning nhưng không crash

### Load Testing
```bash
# Simulate 100 requests
ab -n 100 -c 10 http://127.0.0.1:8000/api/v1/menus

# Check response time
curl -w "@curl-format.txt" -o /dev/null -s http://127.0.0.1:8000/api/v1/menus
```

## Key Principles

1. **Dual-layer protection:** Time-based (10s) + On-demand (instant)
2. **Fail gracefully:** On-demand fail → Time-based fallback
3. **Cache version:** Track changes, useful for debugging
4. **Observer pattern:** DRY, centralized cache logic
5. **Secure endpoint:** Always validate secret token
6. **Log everything:** Essential for debugging production issues

## Security Checklist

- [ ] ✅ Secret token đủ mạnh (min 32 chars)
- [ ] ✅ Secret khác nhau giữa dev và production
- [ ] ✅ Endpoint không expose trong public docs
- [ ] ✅ Timeout để prevent DoS
- [ ] ✅ Rate limiting (optional)

## Supplementary Resources

For comprehensive guide: `read .claude/skills/api-cache-invalidation/CLAUDE.md`

For related skills:
- `read .claude/skills/api-design-principles/SKILL.md`
- `read .claude/skills/filament-rules/SKILL.md`

## Quick Commands

```bash
# Backend: Increment version manually
php artisan tinker --execute="Cache::increment('api_cache_version');"

# Backend: Trigger revalidation
php artisan tinker --execute="app(\App\Services\RevalidationService::class)->revalidateAll();"

# Frontend: Check endpoint health
curl http://localhost:3000/api/revalidate

# Frontend: Rebuild with new cache
rm -rf .next && npm run build
```

## Success Metrics

✅ **User không phàn nàn "phải Ctrl+F5"**  
✅ **Admin update → User thấy mới trong 1-2s**  
✅ **Server load không tăng đáng kể**  
✅ **Zero downtime khi deploy**  
✅ **API response time < 500ms**

Khi đạt được tất cả metrics trên → Hệ thống hoạt động tốt! 🎉

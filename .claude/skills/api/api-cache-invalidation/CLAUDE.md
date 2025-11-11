# API Cache Invalidation System - Comprehensive Guide

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Backend Implementation](#backend-implementation)
3. [Frontend Implementation](#frontend-implementation)
4. [Testing & Debugging](#testing--debugging)
5. [Production Deployment](#production-deployment)
6. [Advanced Patterns](#advanced-patterns)

---

## Architecture Overview

### Problem Statement
**Vấn đề:** User phải Ctrl+F5 để thấy data mới sau khi admin update.

**Nguyên nhân:** 
- Frontend cache data với ISR (Incremental Static Regeneration)
- Backend update không notify frontend
- Static pages serve stale data

**Giải pháp:** Hybrid cache invalidation system

### Solution Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     ADMIN UPDATE DATA                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              LARAVEL OBSERVER PATTERN                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  Menu    │  │ Product  │  │ Article  │  │  Image   │   │
│  │ Observer │  │ Observer │  │ Observer │  │ Observer │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
└───────┼────────────┼─────────────┼─────────────┼───────────┘
        │            │             │             │
        └────────────┴─────────────┴─────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │  incrementCacheVersion │
        │  Cache::put('api_cache_│
        │  version', version + 1)│
        └────────┬───────────────┘
                 │
        ┌────────┴────────┐
        │                 │
        ▼                 ▼
┌───────────────┐  ┌──────────────────────┐
│ API Response  │  │ RevalidationService  │
│ meta: {       │  │ →POST /api/revalidate│
│   cache_      │  │ paths: ['/', ...]    │
│   version: 5  │  └──────┬───────────────┘
│ }             │         │
└───────────────┘         │
        │                 │
        └────────┬────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│               NEXT.JS REVALIDATION                          │
│                                                              │
│  ┌──────────────────┐        ┌──────────────────┐          │
│  │ Time-based (10s) │   OR   │ On-Demand (1-2s) │          │
│  │ revalidate: 10   │        │ revalidatePath() │          │
│  └──────────────────┘        └──────────────────┘          │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              USER SEES FRESH DATA                            │
│              F5 trong 1-2 giây!                              │
└─────────────────────────────────────────────────────────────┘
```

### Key Components

**Backend:**
1. **Observers** - Detect model changes
2. **Cache Version** - Track update state
3. **RevalidationService** - Trigger Next.js
4. **API Meta** - Include version in response

**Frontend:**
1. **Revalidation Endpoint** - Receive webhooks
2. **ISR Config** - Time-based fallback
3. **API Client** - Parse cache version

---

## Backend Implementation

### Step 1: Create RevalidationService

**File:** `app/Services/RevalidationService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevalidationService
{
    /**
     * Trigger Next.js on-demand revalidation
     * 
     * @param array<string> $paths Pages to revalidate (e.g., ["/", "/products"])
     * @return bool Success status
     */
    public function triggerRevalidation(array $paths = []): bool
    {
        $url = config('services.nextjs.revalidate_url');
        $secret = config('services.nextjs.revalidate_secret');

        if (!$url || !$secret) {
            Log::warning('Next.js revalidation not configured', [
                'url' => $url,
                'has_secret' => !empty($secret),
            ]);
            return false;
        }

        try {
            $response = Http::timeout(5)->post($url, [
                'secret' => $secret,
                'paths' => $paths,
            ]);

            if ($response->successful()) {
                Log::info('Next.js revalidation triggered', [
                    'paths' => $paths,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::warning('Next.js revalidation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Next.js revalidation error', [
                'error' => $e->getMessage(),
                'paths' => $paths,
            ]);
            return false;
        }
    }

    /**
     * Revalidate home page
     */
    public function revalidateHome(): bool
    {
        return $this->triggerRevalidation(['/']);
    }

    /**
     * Revalidate all common pages
     */
    public function revalidateAll(): bool
    {
        return $this->triggerRevalidation(
            paths: ['/', '/products', '/filter']
        );
    }
    
    /**
     * Revalidate specific product pages
     */
    public function revalidateProduct(string $slug): bool
    {
        return $this->triggerRevalidation([
            '/',
            '/products',
            "/products/{$slug}"
        ]);
    }
}
```

### Step 2: Update All Observers

**Pattern:** Add incrementCacheVersion() to tất cả observers

**Example 1: MenuObserver**
```php
<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Increment cache version AND trigger revalidation
     */
    private function incrementCacheVersion(): void
    {
        $version = (int) Cache::get('api_cache_version', 0);
        Cache::put('api_cache_version', $version + 1);
        Cache::put('last_cache_clear', now()->toIso8601String());
        
        // Trigger Next.js revalidation
        try {
            app(\App\Services\RevalidationService::class)->revalidateAll();
        } catch (\Throwable $e) {
            \Log::warning('Failed to trigger revalidation', [
                'model' => 'Menu',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function created(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function updated(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function deleted(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function restored(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }

    public function forceDeleted(Menu $menu): void
    {
        $this->incrementCacheVersion();
    }
}
```

**Example 2: ProductObserver (with specific revalidation)**
```php
private function incrementCacheVersion(): void
{
    $version = (int) Cache::get('api_cache_version', 0);
    Cache::put('api_cache_version', $version + 1);
    
    try {
        app(\App\Services\RevalidationService::class)->revalidateAll();
    } catch (\Throwable $e) {
        \Log::warning('Revalidation failed', ['error' => $e->getMessage()]);
    }
}

public function updated(Product $product): void
{
    // Specific revalidation for product pages
    if ($product->isDirty('slug') || $product->isDirty('name')) {
        try {
            app(\App\Services\RevalidationService::class)
                ->revalidateProduct($product->slug);
        } catch (\Throwable $e) {
            \Log::warning('Product revalidation failed');
        }
    }
    
    $this->incrementCacheVersion();
}
```

**Models to Update:**
- ✅ MenuObserver
- ✅ MenuBlockObserver
- ✅ MenuBlockItemObserver
- ✅ HomeComponentObserver
- ✅ ProductObserver
- ✅ ArticleObserver
- ✅ ImageObserver
- ✅ CatalogTermObserver (optional)

### Step 3: Update API Controllers

**Pattern:** Include cache version trong response meta

**Example: MenuController**
```php
<?php

namespace App\Http\Controllers\Api\V1\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $menus = Menu::query()
            ->with(['blocks.items'])
            ->active()
            ->orderBy('order')
            ->get();

        $payload = $this->transformMenus($menus);
        
        // IMPORTANT: Include cache version
        $cacheVersion = (int) Cache::get('api_cache_version', 0);

        return response()->json([
            'data' => $payload,
            'meta' => [
                'cache_version' => $cacheVersion,
                'updated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
```

**APIs to Update:**
- ✅ `/api/v1/menus`
- ✅ `/api/v1/home`
- ✅ `/api/v1/products` (optional)
- ✅ `/api/v1/articles` (optional)

### Step 4: Configuration

**File:** `config/services.php`
```php
return [
    // ... existing services

    'nextjs' => [
        'revalidate_url' => env('NEXT_REVALIDATE_URL'),
        'revalidate_secret' => env('NEXT_REVALIDATE_SECRET'),
    ],
];
```

**File:** `.env`
```bash
# Next.js On-Demand Revalidation
NEXT_REVALIDATE_URL=http://localhost:3000/api/revalidate
NEXT_REVALIDATE_SECRET=wincellar-secret-2025-change-in-production
```

**Production `.env`:**
```bash
NEXT_REVALIDATE_URL=https://yourdomain.com/api/revalidate
NEXT_REVALIDATE_SECRET=use-strong-random-64-char-string-here
```

---

## Frontend Implementation

### Step 1: Create Revalidation Endpoint

**File:** `app/api/revalidate/route.ts`

```typescript
import { revalidatePath } from 'next/cache';
import { NextRequest, NextResponse } from 'next/server';

/**
 * On-Demand Revalidation API
 * Backend calls this endpoint when data changes
 * 
 * POST /api/revalidate
 * Body: { 
 *   secret: "your-secret-token",
 *   paths: ["/", "/products"]
 * }
 */
export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { secret, paths } = body;

    // Security: Verify secret token
    const revalidateSecret = process.env.REVALIDATE_SECRET;
    if (!revalidateSecret || secret !== revalidateSecret) {
      console.error('Invalid revalidation secret');
      return NextResponse.json(
        { success: false, message: 'Invalid secret' },
        { status: 401 }
      );
    }

    // Revalidate requested paths
    if (paths && Array.isArray(paths)) {
      for (const path of paths) {
        console.log(`Revalidating path: ${path}`);
        revalidatePath(path, 'page');
      }
    }

    return NextResponse.json({
      success: true,
      message: 'Revalidated successfully',
      revalidated: {
        paths: paths || [],
        timestamp: new Date().toISOString(),
      },
    });
  } catch (error) {
    console.error('Revalidation error:', error);
    return NextResponse.json(
      { 
        success: false, 
        message: 'Revalidation failed',
        error: error instanceof Error ? error.message : 'Unknown error'
      },
      { status: 500 }
    );
  }
}

/**
 * Health check endpoint
 * GET /api/revalidate
 */
export async function GET() {
  return NextResponse.json({
    status: 'ok',
    message: 'On-demand revalidation endpoint is ready',
    usage: 'POST with { secret, paths: ["/"] }',
  });
}
```

### Step 2: Update API Client

**File:** `lib/api/menus.ts`

```typescript
import { apiFetch } from "./client";

export interface MenuItem {
  id: number;
  label: string;
  href: string;
  type: "standard" | "mega";
  children?: MenuBlock[];
}

export interface MenusResponse {
  data: MenuItem[];
  meta: {
    cache_version: number;
    updated_at?: string;
  };
}

export async function fetchMenus(): Promise<MenuItem[]> {
  const response = await apiFetch<MenusResponse>("v1/menus", {
    // Time-based revalidation (fallback)
    // 10s = balance between freshness and performance
    next: { revalidate: 10 },
  });
  
  // Optional: Log cache version for debugging
  if (process.env.NODE_ENV === 'development') {
    console.log(`Menu cache version: ${response.meta.cache_version}`);
  }
  
  return response.data;
}
```

**File:** `lib/api/home.ts`

```typescript
export interface HomeComponentsResponse {
  data: HomeComponent[];
  meta: {
    cache_version: number;
  };
}

export async function fetchHomeComponents(): Promise<HomeComponent[]> {
  const response = await apiFetch<HomeComponentsResponse>("v1/home", {
    next: { revalidate: 10 },
  });
  return response.data;
}
```

### Step 3: Environment Setup

**File:** `.env.local` (development)
```bash
# API Backend
NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api

# Revalidation Secret (MUST match backend)
REVALIDATE_SECRET=wincellar-secret-2025-change-in-production
```

**File:** `.env.production` (production)
```bash
NEXT_PUBLIC_API_BASE_URL=https://api.yourdomain.com/api
REVALIDATE_SECRET=use-strong-random-64-char-string-here
```

---

## Testing & Debugging

### Test Scenario 1: Manual Cache Version

```bash
# 1. Check current version
curl http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'
# Output: 4

# 2. Increment manually
php artisan tinker
> Cache::increment('api_cache_version');
> exit

# 3. Verify increment
curl http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'
# Output: 5
```

### Test Scenario 2: Observer Trigger

```bash
# 1. Current version
curl -s http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'

# 2. Update menu
php artisan tinker
> $menu = App\Models\Menu::first();
> $menu->touch();
> exit

# 3. Check version (should increment)
curl -s http://127.0.0.1:8000/api/v1/menus | jq '.meta.cache_version'

# 4. Check logs
tail -f storage/logs/laravel.log | grep "revalidation"
```

### Test Scenario 3: On-Demand Revalidation

```bash
# 1. Test endpoint directly
curl -X POST http://localhost:3000/api/revalidate \
  -H "Content-Type: application/json" \
  -d '{"secret":"wincellar-secret-2025-change-in-production","paths":["/"]}'

# Expected output:
# {"success":true,"message":"Revalidated successfully",...}

# 2. Test from backend
php artisan tinker
> app(\App\Services\RevalidationService::class)->revalidateAll();
> exit

# 3. Verify frontend (F5 browser)
# Data should update immediately
```

### Debugging Checklist

**Backend Issues:**
- [ ] Observer registered? Check model has `#[ObservedBy]`
- [ ] Cache driver working? Try `Cache::put('test', 1)`
- [ ] HTTP client working? Check network connectivity
- [ ] Logs showing errors? `tail -f storage/logs/laravel.log`

**Frontend Issues:**
- [ ] Next.js server running? Check `http://localhost:3000`
- [ ] Environment variables loaded? Check `process.env.REVALIDATE_SECRET`
- [ ] Endpoint accessible? `curl http://localhost:3000/api/revalidate`
- [ ] Browser cache cleared? Try incognito mode

**Common Errors:**
```bash
# Error 1: "Connection refused"
# Fix: Make sure Next.js is running on correct port

# Error 2: "Invalid secret"
# Fix: Verify REVALIDATE_SECRET matches in both .env files

# Error 3: "revalidatePath is not a function"
# Fix: Update to Next.js 13+ with App Router

# Error 4: "Cache version not found"
# Fix: Initialize cache: Cache::put('api_cache_version', 0)
```

---

## Production Deployment

### Checklist

**Security:**
- [ ] ✅ Strong secret token (64+ random characters)
- [ ] ✅ Different secrets for dev/staging/production
- [ ] ✅ HTTPS only in production
- [ ] ✅ Rate limiting on revalidation endpoint (optional)
- [ ] ✅ IP whitelist (optional, for extra security)

**Performance:**
- [ ] ✅ HTTP timeout set (5s recommended)
- [ ] ✅ Fail silently (don't block main flow)
- [ ] ✅ Async revalidation (non-blocking)
- [ ] ✅ Monitor response times

**Monitoring:**
- [ ] ✅ Log all revalidation attempts
- [ ] ✅ Alert on repeated failures
- [ ] ✅ Track cache hit/miss rate
- [ ] ✅ Monitor API response times

### Production Environment Variables

**Backend (.env):**
```bash
NEXT_REVALIDATE_URL=https://yourdomain.com/api/revalidate
NEXT_REVALIDATE_SECRET=<GENERATE_RANDOM_64_CHAR_STRING>
```

**Frontend (.env.production):**
```bash
NEXT_PUBLIC_API_BASE_URL=https://api.yourdomain.com/api
REVALIDATE_SECRET=<SAME_AS_BACKEND_SECRET>
```

### Generate Strong Secret

```bash
# Method 1: OpenSSL
openssl rand -base64 48

# Method 2: PHP
php -r "echo bin2hex(random_bytes(32));"

# Method 3: Node.js
node -e "console.log(require('crypto').randomBytes(32).toString('base64'))"
```

---

## Advanced Patterns

### Pattern 1: Selective Revalidation

Chỉ revalidate pages liên quan:

```php
class ProductObserver
{
    public function updated(Product $product): void
    {
        $paths = ['/'];
        
        // Always revalidate home
        $paths[] = '/products';
        
        // Revalidate specific product page
        if ($product->slug) {
            $paths[] = "/products/{$product->slug}";
        }
        
        // Revalidate category pages
        if ($product->category) {
            $paths[] = "/category/{$product->category->slug}";
        }
        
        app(\App\Services\RevalidationService::class)
            ->triggerRevalidation($paths);
            
        $this->incrementCacheVersion();
    }
}
```

### Pattern 2: Batch Revalidation

Avoid spam khi import bulk data:

```php
use Illuminate\Support\Facades\DB;

// Disable observers during bulk import
Product::withoutEvents(function () {
    // Import 1000 products
    foreach ($products as $product) {
        Product::create($product);
    }
});

// Single revalidation after done
Cache::increment('api_cache_version');
app(\App\Services\RevalidationService::class)->revalidateAll();
```

### Pattern 3: Conditional Revalidation

Chỉ revalidate khi cần:

```php
public function updated(Product $product): void
{
    // Only revalidate if public-facing fields changed
    if ($product->isDirty(['name', 'price', 'description', 'active'])) {
        $this->incrementCacheVersion();
    }
    
    // Don't revalidate for internal fields
    // (e.g., admin_notes, internal_sku)
}
```

### Pattern 4: Priority Queues

Use queues cho revalidation:

```php
// app/Jobs/RevalidateNextJs.php
class RevalidateNextJs implements ShouldQueue
{
    public function __construct(
        public array $paths
    ) {}
    
    public function handle(): void
    {
        app(\App\Services\RevalidationService::class)
            ->triggerRevalidation($this->paths);
    }
}

// In Observer
public function updated(Product $product): void
{
    RevalidateNextJs::dispatch(['/products', '/']);
    $this->incrementCacheVersion();
}
```

---

## Maintenance

### Reset Cache Version

```bash
# Set to 0
php artisan tinker
> Cache::put('api_cache_version', 0);
> Cache::put('last_cache_clear', now()->toIso8601String());
```

### Clear All Caches

```bash
# Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Next.js
rm -rf .next
npm run build
```

### Monitor Cache Performance

```php
// Add to your monitoring service
$cacheVersion = Cache::get('api_cache_version', 0);
$lastClear = Cache::get('last_cache_clear');

Log::info('Cache metrics', [
    'version' => $cacheVersion,
    'last_clear' => $lastClear,
    'updates_per_hour' => $this->calculateUpdateRate(),
]);
```

---

## Conclusion

**Key Takeaways:**

1. ✅ **Dual-layer protection:** Time-based + On-demand
2. ✅ **Fail gracefully:** On-demand fail → fallback to time-based
3. ✅ **Observer pattern:** DRY, centralized logic
4. ✅ **Cache version:** Essential for tracking and debugging
5. ✅ **Security first:** Always validate secret tokens
6. ✅ **Monitor everything:** Logs are your best friend

**Success Metrics:**

- User không phàn nàn cache issues
- Admin update → User thấy trong 1-2s
- Server load không tăng
- Zero downtime khi deploy
- API response time < 500ms

Khi đạt tất cả metrics → Hệ thống production-ready! 🚀

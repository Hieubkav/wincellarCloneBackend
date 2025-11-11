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

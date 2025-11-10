# Frontend Cache Busting Strategy

## Problem
Khi data được update trong admin (Filament), frontend vẫn hiển thị data cũ do browser cache hoặc CDN cache.

## Solution: Cache Version API

### Backend Implementation

**4 endpoints được tạo:**

1. **GET /api/v1/cache/version** - Lấy version hiện tại
2. **POST /api/v1/cache/version/increment** - Tăng version (manual)
3. **POST /api/v1/cache/clear** - Clear all Laravel caches
4. **GET /api/v1/cache/status** - Check cache status

**Auto-increment khi data thay đổi:**
- HomeComponentObserver tự động increment version khi create/update/delete HomeComponent
- Có thể thêm observer cho Product, Article, Category models

### Frontend Implementation

#### Option 1: Query String Cache Busting (Recommended)

```javascript
// React/Next.js example
import { useEffect, useState } from 'react';

const useCacheVersion = () => {
  const [version, setVersion] = useState(0);
  
  useEffect(() => {
    // Fetch cache version on mount
    fetch('http://127.0.0.1:8000/api/v1/cache/version')
      .then(res => res.json())
      .then(data => setVersion(data.data.version));
      
    // Poll every 30 seconds to check for updates
    const interval = setInterval(() => {
      fetch('http://127.0.0.1:8000/api/v1/cache/version')
        .then(res => res.json())
        .then(data => {
          if (data.data.version !== version) {
            // Version changed - reload page or refetch data
            window.location.reload();
          }
        });
    }, 30000); // Check every 30s
    
    return () => clearInterval(interval);
  }, [version]);
  
  return version;
};

// Use in API calls
const fetchData = async (url) => {
  const version = await getCacheVersion();
  const response = await fetch(`${url}?v=${version}`);
  return response.json();
};

// Example: Fetch home components
const getHomeComponents = () => {
  return fetchData('http://127.0.0.1:8000/api/v1/home');
};
```

#### Option 2: ETag / Last-Modified Headers (Advanced)

```javascript
// Add to backend responses
// In HomeController or middleware:
return response()->json($data)
    ->header('ETag', md5(json_encode($data)))
    ->header('Last-Modified', now()->toRfc7231String())
    ->header('Cache-Control', 'public, max-age=300, must-revalidate');

// Frontend check
const fetchWithETag = async (url, etag) => {
  const response = await fetch(url, {
    headers: {
      'If-None-Match': etag
    }
  });
  
  if (response.status === 304) {
    // Use cached version
    return cachedData;
  }
  
  // Save new ETag and data
  const newETag = response.headers.get('ETag');
  const data = await response.json();
  saveToCache(url, data, newETag);
  return data;
};
```

#### Option 3: Manual Clear Button (Simple)

```javascript
// Add button in admin panel or frontend
const clearCache = async () => {
  const response = await fetch('http://127.0.0.1:8000/api/v1/cache/clear', {
    method: 'POST'
  });
  
  const result = await response.json();
  
  if (result.success) {
    alert('Cache cleared! Refreshing page...');
    window.location.reload();
  }
};

// Button component
<button onClick={clearCache}>
  Clear Cache & Refresh
</button>
```

#### Option 4: Service Worker Cache Busting

```javascript
// service-worker.js
const CACHE_VERSION = 'v1';

self.addEventListener('message', (event) => {
  if (event.data.action === 'CHECK_VERSION') {
    fetch('http://127.0.0.1:8000/api/v1/cache/version')
      .then(res => res.json())
      .then(data => {
        if (data.data.version !== currentVersion) {
          // Clear caches
          caches.keys().then(keys => {
            return Promise.all(keys.map(key => caches.delete(key)));
          });
          
          // Notify clients
          self.clients.matchAll().then(clients => {
            clients.forEach(client => {
              client.postMessage({
                type: 'CACHE_UPDATED',
                version: data.data.version
              });
            });
          });
        }
      });
  }
});
```

### Testing

```bash
# Test cache version
curl http://127.0.0.1:8000/api/v1/cache/version

# Increment version
curl -X POST http://127.0.0.1:8000/api/v1/cache/version/increment

# Check new version
curl http://127.0.0.1:8000/api/v1/cache/version

# Clear all caches
curl -X POST http://127.0.0.1:8000/api/v1/cache/clear
```

### Recommended Strategy

**For Production:**
1. Use Option 1 (Query String) for simple implementation
2. Poll cache version every 30-60 seconds
3. When version changes:
   - Show notification to user
   - Auto-reload page or refetch data
   - Or show "New content available" button

**For Development:**
1. Add "Clear Cache" button in admin panel
2. Call `/api/v1/cache/clear` after saving changes
3. Auto-reload frontend page

### Example: Complete Integration

```javascript
// hooks/useCachedApi.js
import { useEffect, useState } from 'react';

export const useCachedApi = (url) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [version, setVersion] = useState(0);

  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Get current cache version
      const versionRes = await fetch('http://127.0.0.1:8000/api/v1/cache/version');
      const versionData = await versionRes.json();
      const currentVersion = versionData.data.version;
      
      // Fetch data with version in query string
      const dataRes = await fetch(`${url}?v=${currentVersion}`);
      const responseData = await dataRes.json();
      
      setData(responseData);
      setVersion(currentVersion);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
    
    // Check for version updates every 30 seconds
    const interval = setInterval(async () => {
      const versionRes = await fetch('http://127.0.0.1:8000/api/v1/cache/version');
      const versionData = await versionRes.json();
      
      if (versionData.data.version !== version) {
        console.log('New version detected, refreshing data...');
        fetchData();
      }
    }, 30000);
    
    return () => clearInterval(interval);
  }, [url]);

  return { data, loading, error, refresh: fetchData };
};

// Usage in component
const HomePage = () => {
  const { data, loading, error, refresh } = useCachedApi('http://127.0.0.1:8000/api/v1/home');
  
  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  
  return (
    <div>
      <button onClick={refresh}>Refresh</button>
      {/* Render data */}
    </div>
  );
};
```

## Benefits

✅ **Automatic cache invalidation** khi data update  
✅ **No manual cache clearing needed** từ users  
✅ **Works with any frontend framework** (React, Vue, Angular, etc.)  
✅ **CDN-friendly** - Query string bypasses CDN cache  
✅ **Graceful updates** - Users can be notified instead of forced refresh  

## Monitoring

```bash
# Check cache status
curl http://127.0.0.1:8000/api/v1/cache/status

# Response:
{
  "data": {
    "last_clear": "2025-11-09T23:26:53+00:00",
    "cache_driver": "redis",
    "timestamp": "2025-11-09T23:30:00+00:00"
  }
}
```

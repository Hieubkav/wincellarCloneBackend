# API Endpoints - Wincellar Clone

**Base URL:** `http://localhost:8000/api/v1`  
**Version:** v1  
**Last Updated:** 2025-12-07

---

## 📋 Danh Sách Tất Cả API Endpoints

### 🏥 Health & System

#### Health Check
```
GET /api/v1/health
```
**Mô tả:** Kiểm tra tình trạng hệ thống (database, cache, storage)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "status": "healthy",
  "services": {
    "database": {"status": "healthy", "response_time_ms": 2.34},
    "cache": {"status": "healthy", "response_time_ms": 1.23},
    "storage": {"status": "healthy", "response_time_ms": 0.45}
  },
  "performance": {
    "response_time_ms": 15.67,
    "memory_usage_mb": 12.5
  }
}
```

---

### 🔄 Cache Management

#### Get Cache Version
```
GET /api/v1/cache/version
```
**Mô tả:** Lấy phiên bản cache hiện tại (dùng cho cache busting ở frontend)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "data": {
    "version": 15,
    "timestamp": "2025-11-13T10:30:00Z"
  }
}
```

**Usage:**
- Frontend lưu version vào local storage
- Mỗi lần app load, check version mới
- Nếu version thay đổi → clear cache & reload data
- Version tự động tăng khi admin update data

---

#### Increment Cache Version
```
POST /api/v1/cache/version/increment
```
**Mô tả:** Tăng phiên bản cache (gọi khi admin update data để invalidate frontend cache)  
**Auth:** No (nên thêm auth trong production)  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "success": true,
  "data": {
    "old_version": 14,
    "new_version": 15,
    "timestamp": "2025-11-13T10:30:00Z"
  }
}
```

---

#### Get Cache Status
```
GET /api/v1/cache/status
```
**Mô tả:** Kiểm tra trạng thái cache và thời gian clear gần nhất  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "data": {
    "last_clear": "2025-11-13T09:15:00Z",
    "cache_driver": "redis",
    "timestamp": "2025-11-13T10:30:00Z"
  }
}
```

---

#### Clear Cache
```
POST /api/v1/cache/clear
```
**Mô tả:** Xóa toàn bộ cache của ứng dụng (Laravel cache, config, routes, views)  
**Auth:** No (nên thêm auth trong production)  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "success": true,
  "message": "Cache cleared successfully",
  "timestamp": "2025-11-13T10:30:00Z"
}
```

**Response 500:**
```json
{
  "success": false,
  "message": "Failed to clear cache",
  "error": "Connection refused"
}
```

**⚠️ Warning:** Endpoint này clear toàn bộ cache! Trong production nên:
- Yêu cầu authentication (admin only)
- Log tất cả clear cache actions
- Rate limit thấp hơn

---

### 🏠 Home

#### Get Home Data
```
GET /api/v1/home
```
**Mô tả:** Lấy dữ liệu trang chủ (components, featured products, banners)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "data": {
    "components": [...]
  }
}
```

---

### 🍷 Products (Sản Phẩm)

#### List Products
```
GET /api/v1/san-pham
```
**Mô tả:** Danh sách sản phẩm với filter, sort, pagination  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | integer | Trang hiện tại | `1` |
| `per_page` | integer | Số items per page (max: 60) | `24` |
| `sort` | string | Sắp xếp | `-created_at`, `price`, `-price` |
| `q` | string | Tìm kiếm theo tên | `rượu vang` |
| `terms[brand][]` | integer[] | Filter theo thương hiệu | `[1,2,3]` |
| `terms[origin.country][]` | integer[] | Filter theo quốc gia | `[1,2]` |
| `terms[origin.region][]` | integer[] | Filter theo vùng | `[1,2]` |
| `terms[grape][]` | integer[] | Filter theo giống nho | `[1,2,3]` |
| `type[]` | integer[] | Filter theo loại | `[1,2]` |
| `category[]` | integer[] | Filter theo danh mục | `[1]` |
| `price_min` | integer | Giá tối thiểu | `100000` |
| `price_max` | integer | Giá tối đa | `5000000` |

**Example Request:**
```bash
GET /api/v1/san-pham?page=1&per_page=24&sort=-created_at&price_min=100000&price_max=500000
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Rượu Vang Đỏ Bordeaux",
      "slug": "ruou-vang-do-bordeaux",
      "price": 500000,
      "original_price": 600000,
      "discount_percent": 17,
      "main_image_url": "/storage/...",
      "brand_term": {"id": 1, "name": "Brand X"},
      "country_term": {"id": 2, "name": "Pháp"},
      "_links": {
        "self": {"href": "...", "method": "GET"},
        "category": {"href": "...", "method": "GET"}
      }
    }
  ],
  "meta": {
    "pagination": {
      "page": 1,
      "per_page": 24,
      "total": 100,
      "last_page": 5,
      "has_more": true
    },
    "api_version": "v1"
  },
  "_links": {
    "self": {"href": "..."},
    "next": {"href": "..."},
    "last": {"href": "..."}
  }
}
```

---

#### Get Product Detail
```
GET /api/v1/san-pham/{slug}
```
**Mô tả:** Chi tiết sản phẩm theo slug  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | Product slug (e.g., `ruou-vang-do-bordeaux`) |

**Example Request:**
```bash
GET /api/v1/san-pham/ruou-vang-do-bordeaux
```

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "name": "Rượu Vang Đỏ Bordeaux",
    "slug": "ruou-vang-do-bordeaux",
    "description": "Mô tả chi tiết...",
    "price": 500000,
    "discount_percent": 17,
    "gallery": [...],
    "grape_terms": [
      {"id": 1, "name": "Cabernet Sauvignon"},
      {"id": 2, "name": "Merlot"}
    ],
    "breadcrumbs": [
      {"label": "Rượu Vang", "href": "..."},
      {"label": "Brand X", "href": "..."}
    ],
    "meta": {
      "title": "SEO Title",
      "description": "SEO Description"
    },
    "_links": {
      "self": {...},
      "related": {...}
    }
  },
  "meta": {
    "api_version": "v1"
  }
}
```

**Response 404:**
```json
{
  "error": "NotFound",
  "message": "Product not found",
  "timestamp": "2025-11-09T15:30:00Z",
  "correlation_id": "uuid"
}
```

---

#### Get Filter Options
```
GET /api/v1/san-pham/filters/options
```
**Mô tả:** Lấy danh sách options cho filters (brands, countries, types, etc.)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "data": {
    "brands": [...],
    "countries": [...],
    "types": [...],
    "categories": [...]
  }
}
```

---

#### Search Products
```
GET /api/v1/san-pham/search
```
**Mô tả:** Tìm kiếm sản phẩm (full-text search)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query (required) |
| `page` | integer | Trang hiện tại |
| `per_page` | integer | Items per page |

**Example Request:**
```bash
GET /api/v1/san-pham/search?q=bordeaux&page=1
```

---

#### Search Suggestions
```
GET /api/v1/san-pham/search/suggest
```
**Mô tả:** Gợi ý tìm kiếm (autocomplete)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query (required) |
| `limit` | integer | Max suggestions (default: 10) |

**Example Request:**
```bash
GET /api/v1/san-pham/search/suggest?q=bor&limit=5
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Rượu Vang Bordeaux",
      "slug": "ruou-vang-bordeaux"
    }
  ]
}
```

---

### 📰 Articles (Bài Viết)

#### List Articles
```
GET /api/v1/bai-viet
```
**Mô tả:** Danh sách bài viết  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Trang hiện tại |
| `per_page` | integer | Items per page (default: 12) |
| `sort` | string | Sắp xếp | `-created_at`, `title` |

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Cách Chọn Rượu Vang Phù Hợp",
      "slug": "cach-chon-ruou-vang-phu-hop",
      "excerpt": "Hướng dẫn chi tiết...",
      "cover_image_url": "/storage/...",
      "published_at": "2025-11-09T15:30:00Z",
      "_links": {
        "self": {...}
      }
    }
  ],
  "meta": {
    "pagination": {...}
  }
}
```

---

#### Get Article Detail
```
GET /api/v1/bai-viet/{slug}
```
**Mô tả:** Chi tiết bài viết theo slug  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `slug` | string | Article slug |

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "title": "Cách Chọn Rượu Vang Phù Hợp",
    "slug": "cach-chon-ruou-vang-phu-hop",
    "excerpt": "...",
    "content": "Full content...",
    "gallery": [...],
    "author": {
      "id": 1,
      "name": "Admin"
    },
    "meta": {
      "title": "SEO Title",
      "description": "SEO Description"
    }
  }
}
```

---

### ⚙️ Settings (Cài Đặt)

#### Get Application Settings
```
GET /api/v1/settings
```
**Mô tả:** Lấy thông tin cài đặt ứng dụng (logo, favicon, watermark sản phẩm, thông tin liên hệ, meta defaults)  
**Auth:** No  
**Rate Limit:** 60 requests/minute  
**Cache:** 1 hour (auto-invalidate khi admin update)

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "site_name": "Wincellar Clone",
    "hotline": "0123 456 789",
    "address": "123 Đường ABC, Quận 1, TP.HCM",
    "hours": "8:00 - 22:00 hàng ngày",
    "email": "contact@wincellar.com",
    "google_map_embed": "<iframe src=\"https://www.google.com/maps/embed?...\" width=\"600\" height=\"450\"></iframe>",
    "logo_url": "/storage/images/logo.png",
    "favicon_url": "/storage/images/favicon.ico",
    "product_watermark_url": "/storage/images/watermark.png",
    "product_watermark_position": "none",
    "product_watermark_size": "128x128",
    "meta_defaults": {
      "title": "Wincellar - Cửa hàng rượu vang uy tín",
      "description": "Chuyên cung cấp rượu vang nhập khẩu chính hãng",
      "keywords": "rượu vang, wine, bordeaux"
    },
    "extra": {
      "facebook": "https://facebook.com/wincellar",
      "instagram": "https://instagram.com/wincellar"
    },
    "_links": {
      "self": {
        "href": "http://localhost:8000/api/v1/settings",
        "method": "GET"
      }
    }
  },
  "meta": {
    "api_version": "v1",
    "timestamp": "2025-12-07T10:30:00Z"
  }
}
```

**Usage Notes:**
- Settings được cache 1 giờ để tối ưu performance
- Cache tự động invalidate khi admin update settings trong Filament
- Nếu chưa có settings, API trả về default values
- Trường `product_watermark_url` cho biết ảnh watermark overlay sản phẩm (nếu đã cấu hình)
- `product_watermark_position` (none/top_left/top_right/bottom_left/bottom_right) + `product_watermark_size` (64x64..192x192) cho FE render thống nhất
- Không trả về sensitive data (email passwords, API keys, etc.)
- Frontend nên call endpoint này 1 lần khi app init và lưu vào global state

**Example Request:**
```bash
curl http://localhost:8000/api/v1/settings
```

---

### 🍔 Menus

#### Get Menus
```
GET /api/v1/menus
```
**Mô tả:** Lấy cấu trúc menu navigation với blocks và items  
**Auth:** No  
**Rate Limit:** 60 requests/minute  
**Cache:** Kèm cache_version để frontend biết khi nào update

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Main Navigation",
      "slug": "main-nav",
      "is_active": true,
      "blocks": [
        {
          "id": 1,
          "title": "Sản phẩm",
          "order": 1,
          "items": [
            {
              "id": 1,
              "label": "Rượu Vang Đỏ",
              "url": "/san-pham?type=red-wine",
              "order": 1,
              "term": {
                "id": 5,
                "name": "Rượu Vang Đỏ",
                "slug": "red-wine"
              }
            }
          ]
        }
      ]
    }
  ],
  "meta": {
    "cache_version": 15
  }
}
```

**Cấu trúc:**
- **Menu:** Container chính (Main Nav, Footer Nav...)
- **Block:** Nhóm items (Sản phẩm, Bài viết...)
- **Item:** Link đơn lẻ với label + URL
- **Term:** Taxonomy term (brand, category...) nếu có

---

### 📱 Social Links

#### Get Social Links
```
GET /api/v1/social-links
```
**Mô tả:** Lấy danh sách các social media links (Facebook, Instagram, YouTube...)  
**Auth:** No  
**Rate Limit:** 60 requests/minute  
**Cache:** 5 phút (auto-invalidate khi admin update)

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "platform": "facebook",
      "url": "https://facebook.com/wincellar",
      "icon_url": "/storage/icons/facebook.svg",
      "order": 1
    },
    {
      "id": 2,
      "platform": "instagram",
      "url": "https://instagram.com/wincellar",
      "icon_url": "/storage/icons/instagram.svg",
      "order": 2
    },
    {
      "id": 3,
      "platform": "youtube",
      "url": "https://youtube.com/@wincellar",
      "icon_url": "/storage/icons/youtube.svg",
      "order": 3
    }
  ],
  "meta": {
    "api_version": "v1",
    "timestamp": "2025-11-13T10:30:00Z"
  }
}
```

**Notes:**
- Chỉ trả về links đang active (is_active = true)
- Sorted theo field `order` (ascending)
- Dùng ở Footer, Contact page, Share buttons
- icon_url có thể là SVG hoặc PNG

---

### 📊 Tracking & Analytics

API để tracking visitor behavior (product views, article views, CTA interactions)

#### Generate Anonymous ID
```
GET /api/v1/track/generate-id
```
**Mô tả:** Generate UUID mới cho anonymous tracking (call 1 lần khi user lần đầu vào site)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Response 200:**
```json
{
  "success": true,
  "data": {
    "anon_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

**Workflow:**
1. Frontend check localStorage cho `anon_id`
2. Nếu chưa có → call endpoint này → lưu vào localStorage
3. Dùng `anon_id` này cho tất cả tracking requests
4. KHÔNG reset anon_id (trừ khi user clear cookies/storage)

---

#### Track Visitor
```
POST /api/v1/track/visitor
```
**Mô tả:** Track visitor và tạo session (call khi app init hoặc tab focus)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Request Body:**
```json
{
  "anon_id": "550e8400-e29b-41d4-a716-446655440000",
  "user_agent": "Mozilla/5.0 ..."
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `anon_id` | string (UUID) | ✅ | Anonymous ID từ localStorage |
| `user_agent` | string | ❌ | Browser user agent (auto-detect nếu không gửi) |

**Response 200:**
```json
{
  "success": true,
  "data": {
    "visitor_id": 123,
    "session_id": 456
  }
}
```

**Response 422:**
```json
{
  "success": false,
  "errors": {
    "anon_id": ["The anon id field is required."]
  }
}
```

---

#### Track Event
```
POST /api/v1/track/event
```
**Mô tả:** Track user events (product view, article view, CTA contact clicks)  
**Auth:** No  
**Rate Limit:** 60 requests/minute

**Request Body:**
```json
{
  "anon_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "product_view",
  "product_id": 123,
  "metadata": {
    "referrer": "/san-pham",
    "page_url": "/san-pham/ruou-vang-do"
  }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `anon_id` | string (UUID) | ✅ | Anonymous ID từ localStorage |
| `event_type` | string | ✅ | Loại event: `product_view`, `article_view`, `cta_contact` |
| `product_id` | integer | ❌ | Product ID (bắt buộc nếu event_type = product_view) |
| `article_id` | integer | ❌ | Article ID (bắt buộc nếu event_type = article_view) |
| `metadata` | object | ❌ | Additional data (referrer, page_url, etc.) |

**Example Request (Product View):**
```json
{
  "anon_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "product_view",
  "product_id": 123,
  "metadata": {
    "referrer": "/san-pham",
    "page_url": "/san-pham/ruou-vang-do"
  }
}
```

**Example Request (CTA Contact):**
```json
{
  "anon_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "cta_contact",
  "metadata": {
    "button_location": "product_detail",
    "button_text": "Liên hệ tư vấn"
  }
}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "event_id": 789,
    "event_type": "product_view",
    "occurred_at": "2025-11-13T10:30:00Z"
  }
}
```

**Response 422:**
```json
{
  "success": false,
  "errors": {
    "event_type": ["The selected event type is invalid."]
  }
}
```

**Event Types:**
- **product_view:** User xem chi tiết sản phẩm
- **article_view:** User xem chi tiết bài viết
- **cta_contact:** User click nút "Liên hệ", "Hotline", "Zalo"

**Best Practices:**
- Track product_view khi component mount (không khi scroll qua)
- Track cta_contact khi user click (không khi hover)
- Debounce tracking calls để tránh spam
- Dùng metadata để lưu context (referrer, search query...)

---

### 📚 Documentation

#### Swagger UI
```
GET /api/documentation
```
**Mô tả:** Interactive API documentation (Swagger UI)  
**Auth:** No

**Access:** `http://localhost:8000/api/documentation`

#### OpenAPI Spec (JSON)
```
GET /docs/api-docs.json
```
**Mô tả:** OpenAPI specification in JSON format  
**Auth:** No

---

## 🔧 Common Headers

### Request Headers
```
X-Correlation-ID: <uuid>    # Optional - For request tracking
Accept: application/json      # Required
```

### Response Headers
```
X-Correlation-ID: <uuid>           # Request tracking
X-Execution-Time: <ms>             # Performance metric
X-Memory-Usage: <MB>               # Memory usage
X-Memory-Peak: <MB>                # Peak memory
X-RateLimit-Limit: 60              # Rate limit max
X-RateLimit-Remaining: 59          # Remaining requests
X-RateLimit-Reset: <timestamp>     # Reset timestamp
Content-Type: application/json
```

---

## ⚠️ Error Responses

### Validation Error (422)
```json
{
  "error": "ValidationError",
  "message": "Request validation failed",
  "timestamp": "2025-11-09T15:30:00Z",
  "path": "api/v1/san-pham",
  "correlation_id": "uuid",
  "details": {
    "errors": [
      {
        "field": "price_min",
        "message": "The price min must be an integer.",
        "value": "abc"
      }
    ]
  }
}
```

### Not Found (404)
```json
{
  "error": "NotFound",
  "message": "Product not found",
  "timestamp": "2025-11-09T15:30:00Z",
  "correlation_id": "uuid",
  "details": {
    "identifier": "non-existent-slug"
  }
}
```

### Bad Request (400)
```json
{
  "error": "BadRequest",
  "message": "Invalid price range",
  "timestamp": "2025-11-09T15:30:00Z",
  "correlation_id": "uuid",
  "details": {
    "price_min": 5000000,
    "price_max": 1000000,
    "constraint": "price_min must be less than or equal to price_max"
  }
}
```

### Rate Limit Exceeded (429)
```json
{
  "error": "RateLimitExceeded",
  "message": "Too many requests. Please slow down.",
  "timestamp": "2025-11-09T15:30:00Z",
  "correlation_id": "uuid",
  "details": {
    "retry_after": 60
  }
}
```

---

## 🧪 Testing with cURL

### Health Check
```bash
curl http://localhost:8000/api/v1/health
```

### List Products
```bash
curl "http://localhost:8000/api/v1/san-pham?page=1&per_page=10"
```

### Get Product Detail
```bash
curl http://localhost:8000/api/v1/san-pham/ruou-vang-do
```

### Search Products
```bash
curl "http://localhost:8000/api/v1/san-pham/search?q=bordeaux"
```

### With Correlation ID
```bash
curl -H "X-Correlation-ID: test-123" http://localhost:8000/api/v1/health
```

---

## 📊 Rate Limiting

- **Limit:** 60 requests per minute per IP address
- **Headers:** 
  - `X-RateLimit-Limit`: Max requests
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Reset timestamp
- **Response:** 429 when exceeded

---

## 🔗 Quick Links

- **Interactive Docs:** http://localhost:8000/api/documentation
- **OpenAPI Spec:** http://localhost:8000/docs/api-docs.json
- **Health Check:** http://localhost:8000/api/v1/health

---

## 📝 Notes

1. Tất cả timestamps theo format ISO 8601 (UTC)
2. Tất cả responses include `api_version` trong meta
3. HATEOAS links (`_links`) để navigate giữa các resources
4. Correlation ID để tracking requests across system
5. Performance headers để monitor API performance

---

**Last Updated:** 2025-12-07  
**API Version:** v1  
**Total Endpoints:** 19

## 📋 Endpoint Summary

| Category | Endpoints | Description |
|----------|-----------|-------------|
| 🏥 Health | 1 | System health check |
| 🔄 Cache | 4 | Cache management & version control |
| 🏠 Home | 1 | Homepage data |
| 🍷 Products | 5 | Product listing, detail, search, filters |
| 📰 Articles | 2 | Article listing & detail |
| 🍔 Menus | 1 | Navigation menu structure |
| 📱 Social Links | 1 | Social media links |
| 📊 Tracking | 3 | Visitor & event tracking |
| ⚙️ Settings | 1 | Application settings |

# API Endpoints - Wincellar Clone

**Base URL:** `http://localhost:8000/api/v1`  
**Version:** v1  
**Last Updated:** 2025-11-09

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
| `alcohol_min` | float | Độ cồn tối thiểu | `12.5` |
| `alcohol_max` | float | Độ cồn tối đa | `15.0` |

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

**Last Updated:** 2025-11-09  
**API Version:** v1

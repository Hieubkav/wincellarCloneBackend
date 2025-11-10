# REST API Best Practices

Comprehensive guide to REST API design patterns, conventions, and best practices.

## Resource Naming Conventions

### Use Nouns, Not Verbs

```
✅ Good:
GET    /api/users
POST   /api/users
GET    /api/users/123
DELETE /api/users/123

❌ Bad:
GET    /api/getUsers
POST   /api/createUser
GET    /api/getUserById/123
POST   /api/deleteUser/123
```

### Plural vs Singular

**Always use plural for collections:**

```
✅ /api/users      (collection)
✅ /api/users/123  (single resource)

❌ /api/user       (confusing)
❌ /api/user/123   (inconsistent)
```

### Nested Resources

```
✅ Good hierarchy:
/api/users/123/orders          # User's orders
/api/users/123/orders/456      # Specific order
/api/orders/456/items          # Order items
/api/categories/789/products   # Products in category

❌ Avoid deep nesting (max 2 levels):
/api/users/123/orders/456/items/789/reviews  # Too deep!
```

**Alternative for deep relationships:**

```
# Instead of deep nesting:
GET /api/users/123/orders/456/items/789

# Use direct resource access:
GET /api/order-items/789
# or
GET /api/order-items?order_id=456
```

---

## HTTP Methods & Semantics

### Method Overview

| Method | Purpose | Idempotent? | Safe? | Request Body? | Response Body? |
|--------|---------|-------------|-------|---------------|----------------|
| GET | Retrieve resources | Yes | Yes | No | Yes |
| POST | Create resources | No | No | Yes | Yes |
| PUT | Replace resource | Yes | No | Yes | Yes |
| PATCH | Update partial | No* | No | Yes | Yes |
| DELETE | Remove resource | Yes | No | Optional | Optional |
| HEAD | Get headers only | Yes | Yes | No | No |
| OPTIONS | Get capabilities | Yes | Yes | No | Yes |

\* PATCH can be designed to be idempotent

### GET - Retrieve Resources

```
GET /api/users
Response: 200 OK
[
  {"id": 1, "name": "Alice"},
  {"id": 2, "name": "Bob"}
]

GET /api/users/1
Response: 200 OK
{"id": 1, "name": "Alice", "email": "alice@example.com"}

GET /api/users/999
Response: 404 Not Found
{"error": "User not found"}
```

**Query parameters for filtering:**

```
GET /api/users?status=active
GET /api/users?role=admin&sort=name
GET /api/products?min_price=10&max_price=100&category=electronics
```

### POST - Create Resources

```
POST /api/users
Content-Type: application/json

{
  "name": "Charlie",
  "email": "charlie@example.com"
}

Response: 201 Created
Location: /api/users/3
{
  "id": 3,
  "name": "Charlie",
  "email": "charlie@example.com",
  "created_at": "2024-01-15T10:30:00Z"
}
```

### PUT - Replace Entire Resource

```
PUT /api/users/3
Content-Type: application/json

{
  "name": "Charles Updated",
  "email": "charles@example.com",
  "role": "admin"
}

Response: 200 OK
{
  "id": 3,
  "name": "Charles Updated",
  "email": "charles@example.com",
  "role": "admin",
  "updated_at": "2024-01-15T11:00:00Z"
}
```

**Important:** PUT replaces the entire resource. Missing fields are removed/reset.

### PATCH - Partial Update

```
PATCH /api/users/3
Content-Type: application/json

{
  "name": "Charles"
}

Response: 200 OK
{
  "id": 3,
  "name": "Charles",           # Updated
  "email": "charles@example.com",  # Unchanged
  "role": "admin",             # Unchanged
  "updated_at": "2024-01-15T11:30:00Z"
}
```

### DELETE - Remove Resource

```
DELETE /api/users/3

Response: 204 No Content
(empty body)

# or with confirmation:
Response: 200 OK
{
  "message": "User deleted successfully",
  "id": 3
}
```

---

## Status Codes

### 2xx Success

| Code | Meaning | When to Use |
|------|---------|-------------|
| 200 OK | Success with body | GET, PATCH, PUT with response |
| 201 Created | Resource created | POST successful creation |
| 202 Accepted | Async processing started | Long-running operations |
| 204 No Content | Success, no body | DELETE, PUT without response |

### 4xx Client Errors

| Code | Meaning | When to Use |
|------|---------|-------------|
| 400 Bad Request | Invalid request format | Malformed JSON, invalid data |
| 401 Unauthorized | Authentication required | Missing/invalid auth token |
| 403 Forbidden | No permission | Valid auth, but insufficient rights |
| 404 Not Found | Resource doesn't exist | Invalid ID, deleted resource |
| 405 Method Not Allowed | HTTP method not supported | POST to read-only resource |
| 409 Conflict | Resource state conflict | Duplicate email, version conflict |
| 422 Unprocessable Entity | Validation failed | Valid JSON, but business rules violated |
| 429 Too Many Requests | Rate limit exceeded | Too many API calls |

### 5xx Server Errors

| Code | Meaning | When to Use |
|------|---------|-------------|
| 500 Internal Server Error | Unexpected error | Uncaught exceptions |
| 502 Bad Gateway | Upstream service error | Proxy/gateway issues |
| 503 Service Unavailable | Temporarily down | Maintenance, overload |
| 504 Gateway Timeout | Upstream timeout | Slow backend service |

---

## Pagination Strategies

### Offset-Based Pagination

**Simple and intuitive:**

```
GET /api/users?page=2&page_size=20

Response:
{
  "data": [...],
  "meta": {
    "page": 2,
    "page_size": 20,
    "total": 150,
    "pages": 8
  },
  "links": {
    "first": "/api/users?page=1&page_size=20",
    "prev": "/api/users?page=1&page_size=20",
    "self": "/api/users?page=2&page_size=20",
    "next": "/api/users?page=3&page_size=20",
    "last": "/api/users?page=8&page_size=20"
  }
}
```

**Pros:**
- Easy to implement
- Jump to any page
- Shows total count

**Cons:**
- Performance degrades with large offsets
- Inconsistent results if data changes during pagination

### Cursor-Based Pagination (Recommended for Large Datasets)

```
GET /api/users?limit=20&cursor=eyJpZCI6MTIzfQ==

Response:
{
  "data": [...],
  "pagination": {
    "next_cursor": "eyJpZCI6MTQzfQ==",
    "has_more": true
  }
}
```

**Pros:**
- Consistent performance
- No duplicate/missing items during pagination
- Works well with real-time data

**Cons:**
- Can't jump to specific page
- No total count (expensive to compute)

---

## Filtering and Sorting

### Query Parameters for Filtering

```
# Single filter
GET /api/products?category=electronics

# Multiple filters
GET /api/products?category=electronics&status=active&min_price=100

# Range filters
GET /api/orders?created_after=2024-01-01&created_before=2024-01-31

# Search
GET /api/users?search=john
```

### Sorting

```
# Single field
GET /api/users?sort=name

# Descending
GET /api/users?sort=-created_at

# Multiple fields
GET /api/users?sort=status,-created_at
```

### Field Selection (Sparse Fieldsets)

```
# Return only specific fields
GET /api/users?fields=id,name,email

Response:
[
  {"id": 1, "name": "Alice", "email": "alice@example.com"},
  {"id": 2, "name": "Bob", "email": "bob@example.com"}
]
```

---

## Error Response Format

### Standard Error Structure

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Request validation failed",
    "details": [
      {
        "field": "email",
        "message": "Invalid email format",
        "value": "invalid-email"
      },
      {
        "field": "password",
        "message": "Password must be at least 8 characters"
      }
    ],
    "timestamp": "2024-01-15T10:30:00Z",
    "path": "/api/users",
    "trace_id": "abc-123-def"
  }
}
```

### Error Types Examples

**Validation Error (422):**
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": [...]
  }
}
```

**Not Found (404):**
```json
{
  "error": {
    "code": "RESOURCE_NOT_FOUND",
    "message": "User with ID 123 not found",
    "resource": "User",
    "identifier": "123"
  }
}
```

**Conflict (409):**
```json
{
  "error": {
    "code": "DUPLICATE_RESOURCE",
    "message": "User with email already exists",
    "field": "email",
    "value": "user@example.com"
  }
}
```

---

## Content Negotiation

### Request Headers

```
# JSON (default)
Accept: application/json

# XML
Accept: application/xml

# CSV
Accept: text/csv

# Version via header
Accept: application/vnd.api.v2+json
```

### Response Headers

```
Content-Type: application/json; charset=utf-8
Content-Length: 1234
ETag: "33a64df551425fcc55e4d42a148795d9f25f89d4"
Last-Modified: Mon, 15 Jan 2024 10:30:00 GMT
Cache-Control: max-age=3600, public
```

---

## Rate Limiting

### Response Headers

```
X-RateLimit-Limit: 1000        # Requests allowed per window
X-RateLimit-Remaining: 950     # Requests remaining
X-RateLimit-Reset: 1642251600  # Unix timestamp when limit resets
Retry-After: 3600              # Seconds until retry (when rate limited)
```

### Rate Limit Exceeded Response (429)

```json
{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Rate limit exceeded. Try again in 3600 seconds",
    "retry_after": 3600,
    "limit": 1000,
    "window": "1 hour"
  }
}
```

---

## HATEOAS (Hypermedia)

### Adding Links to Responses

```json
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "_links": {
    "self": {
      "href": "/api/users/123"
    },
    "orders": {
      "href": "/api/users/123/orders"
    },
    "update": {
      "href": "/api/users/123",
      "method": "PATCH"
    },
    "delete": {
      "href": "/api/users/123",
      "method": "DELETE"
    }
  }
}
```

---

## Security Best Practices

### 1. Use HTTPS Always

```
✅ https://api.example.com
❌ http://api.example.com
```

### 2. Authentication

```
# JWT in Authorization header
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...

# API Key
X-API-Key: your-api-key-here
```

### 3. Input Validation

- Validate all inputs
- Use type checking
- Sanitize user data
- Limit request size

### 4. Rate Limiting

- Per user/IP limits
- Different limits for authenticated vs anonymous
- Exponential backoff for abuse

### 5. CORS Configuration

```python
# FastAPI example
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://app.example.com"],  # Specific origins
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "DELETE"],
    allow_headers=["*"],
)
```

---

## Documentation with OpenAPI

### Swagger/OpenAPI Spec Example

```yaml
openapi: 3.0.0
info:
  title: User Management API
  version: 1.0.0
  description: API for managing users

paths:
  /api/users:
    get:
      summary: List users
      parameters:
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: page_size
          in: query
          schema:
            type: integer
            default: 20
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/User'
                  meta:
                    $ref: '#/components/schemas/PaginationMeta'

components:
  schemas:
    User:
      type: object
      required:
        - id
        - email
        - name
      properties:
        id:
          type: integer
        email:
          type: string
          format: email
        name:
          type: string
```

---

## Testing REST APIs

### Unit Testing

```python
import pytest
from httpx import AsyncClient

@pytest.mark.asyncio
async def test_create_user():
    async with AsyncClient(app=app, base_url="http://test") as client:
        response = await client.post(
            "/api/users",
            json={"name": "Test User", "email": "test@example.com"}
        )
    
    assert response.status_code == 201
    data = response.json()
    assert data["name"] == "Test User"
    assert data["email"] == "test@example.com"
    assert "id" in data

@pytest.mark.asyncio
async def test_get_user_not_found():
    async with AsyncClient(app=app, base_url="http://test") as client:
        response = await client.get("/api/users/99999")
    
    assert response.status_code == 404
    assert "error" in response.json()
```

---

For related topics:
- GraphQL design: `read .claude/skills/api-design-principles/references/graphql-schema-design.md`
- Versioning strategies: `read .claude/skills/api-design-principles/references/api-versioning-strategies.md`

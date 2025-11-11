---
name: api-design-patterns
description: Comprehensive REST and GraphQL API design patterns, best practices, OpenAPI specifications, versioning, authentication, error handling, pagination, rate limiting, and security. Use when designing APIs, creating endpoints, reviewing specifications, implementing authentication, or building scalable backend services.
---

# API Design Patterns & Best Practices

Master REST and GraphQL API design to build intuitive, scalable, secure, and maintainable APIs that delight developers.

## When to Use This Skill

- Designing new REST or GraphQL APIs
- Refactoring existing APIs for better usability
- Establishing API design standards and conventions
- Reviewing API specifications before implementation
- Implementing authentication and authorization
- Creating API documentation (OpenAPI/Swagger)
- Troubleshooting API issues
- Planning API versioning strategy
- Implementing rate limiting and security
- Optimizing API performance

---

## Core Design Principles

### REST API Essentials

**Resource-Oriented Design:**
- Use nouns, not verbs: `/users`, `/orders` (not `/getUsers`)
- HTTP methods define actions: GET, POST, PUT, PATCH, DELETE
- Hierarchical URLs: `/users/{id}/orders`
- Plural for collections: `/users` not `/user`

**Good Examples**:
```http
GET    /api/v1/users          # List users
POST   /api/v1/users          # Create user
GET    /api/v1/users/{id}     # Get specific user
PUT    /api/v1/users/{id}     # Replace user
PATCH  /api/v1/users/{id}     # Update user
DELETE /api/v1/users/{id}     # Delete user
```

**Bad Examples**:
```http
GET    /api/v1/getUsers       # Don't use verbs
POST   /api/v1/createUser     # HTTP method already indicates action
POST   /api/v1/updateUser     # Use PUT/PATCH instead
POST   /api/v1/deleteUser     # Use DELETE instead
```

### HTTP Methods and Semantics

- **GET**: Retrieve a resource (safe, idempotent, cacheable)
- **POST**: Create a new resource (not idempotent)
- **PUT**: Replace entire resource (idempotent)
- **PATCH**: Partial update (not necessarily idempotent)
- **DELETE**: Remove a resource (idempotent)

### HTTP Status Codes

**Success (2xx)**:
- `200 OK`: Successful GET, PUT, PATCH, DELETE
- `201 Created`: Successful POST with resource creation
- `202 Accepted`: Request accepted for async processing
- `204 No Content`: Successful DELETE or update with no response body

**Client Errors (4xx)**:
- `400 Bad Request`: Malformed request, validation error
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Authenticated but not authorized
- `404 Not Found`: Resource doesn't exist
- `409 Conflict`: Resource conflict (duplicate, version mismatch)
- `422 Unprocessable Entity`: Valid syntax but semantic errors
- `429 Too Many Requests`: Rate limit exceeded

**Server Errors (5xx)**:
- `500 Internal Server Error`: Unexpected server error
- `502 Bad Gateway`: Upstream service failure
- `503 Service Unavailable`: Temporary overload or maintenance
- `504 Gateway Timeout`: Upstream timeout

---

## GraphQL Essentials

### Schema-First Design

**Core Concepts:**
- **Types** define your domain model
- **Queries** for reading data
- **Mutations** for modifying data
- **Subscriptions** for real-time updates

**Key Benefits:**
- Clients request exactly what they need (no over-fetching)
- Single endpoint, multiple operations
- Strongly typed schema with built-in validation
- Built-in introspection and documentation

**Example Schema:**
```graphql
type User {
  id: ID!
  email: String!
  name: String!
  posts: [Post!]!
}

type Query {
  user(id: ID!): User
  users(limit: Int = 10, offset: Int = 0): [User!]!
}

type Mutation {
  createUser(input: CreateUserInput!): User!
  updateUser(id: ID!, input: UpdateUserInput!): User!
}

input CreateUserInput {
  email: String!
  name: String!
  password: String!
}
```

### GraphQL Best Practices

1. **Schema first**: Design schema before implementing resolvers
2. **Avoid N+1**: Use DataLoaders for efficient batch fetching
3. **Input validation**: Validate at both schema and resolver levels
4. **Error handling**: Return structured errors in payloads (not throw)
5. **Pagination**: Use cursor-based pagination (Relay spec)
6. **Deprecation**: Use `@deprecated` directive for gradual migration
7. **Monitoring**: Track query complexity and execution time

**Schema Evolution:**
```graphql
type User {
  # Old field (deprecated)
  fullName: String @deprecated(reason: "Use firstName and lastName instead")
  
  # New fields
  firstName: String!
  lastName: String!
}
```

---

## API Versioning

### URL Versioning (Recommended)
```http
GET /api/v1/users
GET /api/v2/users
```

**Pros**: Clear, easy to route, visible in logs, SEO-friendly  
**Cons**: Can lead to code duplication across versions

### Header Versioning
```http
GET /api/users
Accept: application/vnd.myapi.v1+json
API-Version: 2
```

**Pros**: Clean URLs, flexible  
**Cons**: Harder to test, less visible in logs

### Query Parameter Versioning
```http
GET /api/users?version=2
```

**Pros**: Simple to implement  
**Cons**: Can be overridden, not RESTful

### GraphQL Schema Evolution
```graphql
# Instead of versions, use deprecation
field: String @deprecated(reason: "Use newField instead")
```

**Pros**: No breaking changes, gradual migration  
**Cons**: Schema can grow large over time

### Version Management Rules
1. Never break backwards compatibility within same version
2. Deprecate old versions with advance notice (6-12 months)
3. Document clear migration guides between versions
4. Support at least 2 major versions simultaneously
5. Monitor usage of deprecated endpoints

---

## Request/Response Patterns

### Standard Request Format

**JSON Request Body**:
```json
{
  "email": "user@example.com",
  "name": "John Doe",
  "preferences": {
    "newsletter": true,
    "notifications": false
  }
}
```

**Query Parameters** (for filtering, pagination, sorting):
```http
GET /api/v1/users?role=admin&status=active&page=2&limit=20&sort=-created_at
```

### Standard Response Format

**Success Response**:
```json
{
  "data": {
    "id": "user_123",
    "email": "user@example.com",
    "name": "John Doe",
    "createdAt": "2025-10-16T10:30:00Z"
  }
}
```

**Error Response**:
```json
{
  "error": {
    "code": "INVALID_EMAIL",
    "message": "Email address is invalid",
    "field": "email",
    "details": "Email must contain @ symbol"
  }
}
```

**Collection Response with Pagination**:
```json
{
  "data": [
    { "id": 1, "name": "User 1" },
    { "id": 2, "name": "User 2" }
  ],
  "pagination": {
    "page": 2,
    "limit": 20,
    "total": 156,
    "totalPages": 8,
    "hasNext": true,
    "hasPrev": true
  },
  "links": {
    "self": "/api/v1/users?page=2",
    "next": "/api/v1/users?page=3",
    "prev": "/api/v1/users?page=1",
    "first": "/api/v1/users?page=1",
    "last": "/api/v1/users?page=8"
  }
}
```

---

## Authentication Patterns

### JWT (JSON Web Tokens)

**Login Flow**:
```http
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "SecurePassword123"
}

Response (200):
{
  "accessToken": "eyJhbGc...",
  "refreshToken": "eyJhbGc...",
  "expiresIn": 900
}
```

**Using Access Token**:
```http
GET /api/v1/users/me
Authorization: Bearer eyJhbGc...
```

**Token Refresh**:
```http
POST /api/v1/auth/refresh
{
  "refreshToken": "eyJhbGc..."
}

Response (200):
{
  "accessToken": "eyJhbGc...",
  "expiresIn": 900
}
```

### API Keys

**Header-based** (recommended):
```http
GET /api/v1/data
X-API-Key: sk_live_abc123xyz
```

**Query parameter** (less secure, use only for public data):
```http
GET /api/v1/public-data?api_key=sk_live_abc123xyz
```

### OAuth 2.0 Flows

**Authorization Code Flow** (for web apps):
1. Redirect user to `/oauth/authorize`
2. User grants permission
3. Receive authorization code
4. Exchange code for access token at `/oauth/token`
5. Use access token for API requests

**Client Credentials Flow** (for server-to-server):
```http
POST /oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials&client_id=abc&client_secret=xyz
```

---

## Error Handling

### Validation Errors

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Request validation failed",
    "errors": [
      {
        "field": "email",
        "message": "Email is required",
        "code": "REQUIRED_FIELD"
      },
      {
        "field": "age",
        "message": "Age must be at least 18",
        "code": "INVALID_VALUE"
      }
    ]
  }
}
```

### Business Logic Errors

```json
{
  "error": {
    "code": "INSUFFICIENT_FUNDS",
    "message": "Account balance too low for this transaction",
    "details": {
      "balance": 50.00,
      "required": 100.00,
      "currency": "USD"
    }
  }
}
```

### Rate Limiting Errors

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1634400000
Retry-After: 3600

{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "API rate limit exceeded",
    "retryAfter": 3600
  }
}
```

---

## Pagination Strategies

### Offset Pagination (Simple)
```http
GET /api/v1/users?offset=40&limit=20
```

**Pros**: Simple, allows jumping to any page  
**Cons**: Performance degrades with large offsets, inconsistent if data changes

### Cursor Pagination (Recommended)
```http
GET /api/v1/users?cursor=eyJpZCI6MTIzfQ&limit=20

Response:
{
  "data": [...],
  "pagination": {
    "nextCursor": "eyJpZCI6MTQzfQ",
    "hasMore": true
  }
}
```

**Pros**: Consistent results, performant at any scale  
**Cons**: Can't jump to specific page

### Page-Number Pagination (User-friendly)
```http
GET /api/v1/users?page=3&limit=20
```

**Pros**: User-friendly, easy to understand  
**Cons**: Same issues as offset pagination

---

## Rate Limiting

### Implementation Pattern

**Headers to include**:
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1634400000
```

**Tiered Limits**:
- Anonymous: 100 requests/hour
- Basic tier: 1,000 requests/hour
- Pro tier: 10,000 requests/hour
- Enterprise: Custom limits

### Rate Limiting Algorithms

**Token Bucket** (recommended):
- Allows bursts of traffic
- Smooth long-term rate
- Most flexible

**Fixed Window**:
- Simple to implement
- Can allow double limit at window boundaries

**Sliding Window**:
- More accurate than fixed window
- More complex to implement
- Better user experience

---

## API Security Best Practices

### 1. Always Use HTTPS
Never send sensitive data over HTTP. Enforce HTTPS at load balancer level.

### 2. Validate All Inputs
```python
from pydantic import BaseModel, EmailStr, constr

class UserCreate(BaseModel):
    email: EmailStr
    password: constr(min_length=8, max_length=100)
    name: constr(min_length=1, max_length=100)
```

### 3. Sanitize Outputs
```python
import html
safe_output = html.escape(user_input)
```

### 4. Use Parameterized Queries
```python
# ✅ SAFE - Parameterized
cursor.execute("SELECT * FROM users WHERE email = ?", (email,))

# ❌ UNSAFE - String concatenation
cursor.execute(f"SELECT * FROM users WHERE email = '{email}'")
```

### 5. Implement CORS Properly
```python
# Be specific with origins
CORS(app, origins=["https://myapp.com", "https://app.myapp.com"])

# ❌ NEVER use wildcard in production
# CORS(app, origins=["*"])  # DANGEROUS
```

### 6. Log Security Events
```python
logger.warning(f"Failed login attempt for {email} from {ip_address}")
logger.critical(f"Privilege escalation attempt by user {user_id}")
```

### 7. Rate Limit Authentication Endpoints
Prevent brute force attacks:
- `/auth/login`: 5 attempts per 15 minutes per IP
- `/auth/register`: 3 attempts per hour per IP
- `/auth/reset-password`: 3 attempts per hour per email

---

## Request Validation

### Input Validation Pattern

```python
from pydantic import BaseModel, EmailStr, Field, validator

class UserCreate(BaseModel):
    email: EmailStr
    password: str = Field(min_length=8, max_length=100)
    name: str = Field(min_length=1, max_length=100)
    age: int = Field(ge=18, le=120)

    @validator('password')
    def password_strength(cls, v):
        if not any(c.isupper() for c in v):
            raise ValueError('Password must contain uppercase letter')
        if not any(c.isdigit() for c in v):
            raise ValueError('Password must contain digit')
        return v
```

---

## Filtering, Sorting, Searching

### Filtering
```http
# Single filter
GET /api/v1/posts?status=published

# Multiple filters (AND)
GET /api/v1/posts?status=published&author=john

# Multiple values (OR)
GET /api/v1/posts?tags=tech,ai,ml

# Range filters
GET /api/v1/posts?created_after=2025-01-01&created_before=2025-12-31
```

### Sorting
```http
# Single field ascending
GET /api/v1/posts?sort=created_at

# Single field descending
GET /api/v1/posts?sort=-created_at

# Multiple fields
GET /api/v1/posts?sort=-priority,created_at
```

### Searching
```http
# Full-text search
GET /api/v1/posts?q=machine+learning

# Field-specific search
GET /api/v1/posts?title=contains:machine&author=starts_with:john
```

---

## Idempotency

### Idempotent Operations
- GET, PUT, DELETE: Always idempotent
- POST: Not idempotent by default

### Idempotency Keys for POST

```http
POST /api/v1/payments
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000

{
  "amount": 100.00,
  "currency": "USD"
}
```

**Server behavior**:
- First request: Process and return 201
- Duplicate requests with same key: Return cached response
- Different request with same key: Return 409 Conflict

---

## Async Operations

### Long-Running Tasks

```http
POST /api/v1/reports/generate
{
  "type": "annual_summary",
  "year": 2025
}

Response (202 Accepted):
{
  "id": "job_abc123",
  "status": "processing",
  "statusUrl": "/api/v1/jobs/job_abc123"
}
```

### Check Status

```http
GET /api/v1/jobs/job_abc123

Response:
{
  "id": "job_abc123",
  "status": "completed",
  "result": {
    "reportUrl": "/api/v1/reports/annual_summary_2025.pdf"
  },
  "createdAt": "2025-10-16T10:00:00Z",
  "completedAt": "2025-10-16T10:05:00Z"
}
```

**Status values**: `queued`, `processing`, `completed`, `failed`

---

## Webhooks

### Webhook Payload

```json
{
  "event": "user.created",
  "timestamp": "2025-10-16T10:30:00Z",
  "id": "evt_abc123",
  "data": {
    "id": "user_123",
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

### Webhook Security (HMAC Signature)

```python
import hmac
import hashlib

def verify_webhook(payload, signature, secret):
    expected = hmac.new(
        secret.encode(),
        payload.encode(),
        hashlib.sha256
    ).hexdigest()
    return hmac.compare_digest(f"sha256={expected}", signature)
```

---

## Performance Best Practices

### 1. Use ETags for Caching

```http
GET /api/v1/users/123
ETag: "33a64df551425fcc55e4d42a148795d9f25f89d4"

# Subsequent requests
GET /api/v1/users/123
If-None-Match: "33a64df551425fcc55e4d42a148795d9f25f89d4"

Response: 304 Not Modified (if unchanged)
```

### 2. Implement Field Selection

```http
GET /api/v1/users/123?fields=id,email,name

Response:
{
  "id": "user_123",
  "email": "user@example.com",
  "name": "John Doe"
}
```

### 3. Use Compression

```http
Accept-Encoding: gzip, deflate
```

### 4. Batch Operations

```http
# Instead of N requests
GET /api/v1/users/1
GET /api/v1/users/2
GET /api/v1/users/3

# Use batch endpoint
GET /api/v1/users?ids=1,2,3
```

---

## OpenAPI/Swagger Documentation

### Basic OpenAPI 3.0 Example

```yaml
openapi: 3.0.0
info:
  title: My API
  version: 1.0.0
  description: API for managing users

servers:
  - url: https://api.example.com/v1
    description: Production server

paths:
  /users:
    get:
      summary: List users
      parameters:
        - name: page
          in: query
          schema:
            type: integer
            default: 1
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/User'

components:
  schemas:
    User:
      type: object
      required:
        - id
        - email
      properties:
        id:
          type: string
        email:
          type: string
          format: email
        name:
          type: string

  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

security:
  - BearerAuth: []
```

---

## Common Pitfalls to Avoid

### REST
- ❌ Action-oriented endpoints (`/createUser`)
- ❌ Inconsistent status codes
- ❌ Missing pagination
- ❌ Tight coupling to database structure
- ❌ Using GET for state changes
- ❌ Returning sensitive data (passwords, tokens)
- ❌ No versioning strategy
- ❌ Poor error messages

### GraphQL
- ❌ N+1 query problems (missing DataLoaders)
- ❌ No depth/complexity limiting
- ❌ Generic error handling
- ❌ Over-fetching by providing too many fields
- ❌ No caching strategy
- ❌ Exposing all database fields

### Both
- ❌ Breaking changes without versioning
- ❌ Missing rate limits
- ❌ No API documentation
- ❌ Ignoring CORS configuration
- ❌ No monitoring/logging
- ❌ Inconsistent naming conventions

---

## Best Practices Summary

### REST
1. **Consistent naming**: Plural nouns, lowercase
2. **Stateless**: Each request self-contained
3. **Correct status codes**: 2xx success, 4xx client error, 5xx server error
4. **Version from day one**: Plan for breaking changes
5. **Always paginate**: Large collections need pagination
6. **Rate limiting**: Protect API with limits
7. **Documentation**: Use OpenAPI/Swagger

### GraphQL
1. **Schema first**: Design before implementing
2. **Avoid N+1**: Use DataLoaders for efficient fetching
3. **Input validation**: Validate at schema + resolver levels
4. **Error handling**: Return structured errors
5. **Pagination**: Use cursor-based (Relay spec)
6. **Deprecation**: Use `@deprecated` for migration
7. **Monitoring**: Track query complexity

---

## When to Choose REST vs GraphQL

### Choose REST when:
- Simple CRUD operations
- Need caching (HTTP caching works great)
- Working with files/binary data
- Team familiar with REST
- Mobile apps with strict bandwidth limits

### Choose GraphQL when:
- Complex data requirements
- Multiple client types (web, mobile, desktop)
- Need flexible queries
- Rapid frontend development
- Real-time updates (subscriptions)

---

**Remember**: A well-designed API is intuitive, secure, performant, and well-documented. Follow these patterns to create APIs that developers love to use.

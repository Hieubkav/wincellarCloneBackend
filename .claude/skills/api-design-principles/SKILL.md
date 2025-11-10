---
name: api-design-principles
description: Master REST and GraphQL API design principles to build intuitive, scalable, and maintainable APIs that delight developers. USE WHEN designing new APIs, reviewing API specifications, establishing API design standards, implementing RESTful endpoints, or working with GraphQL schemas.
---

# API Design Principles - Quick Reference

Design intuitive, scalable, and maintainable APIs using proven REST and GraphQL patterns.

## When to Use This Skill

- Designing new REST or GraphQL APIs
- Refactoring existing APIs for better usability
- Establishing API design standards
- Reviewing API specifications before implementation
- Migrating between API paradigms
- Optimizing APIs for specific use cases

## Core Concepts

### REST API Essentials

**Resource-Oriented Design:**
- Use nouns, not verbs: `/users`, `/orders` (not `/getUsers`)
- HTTP methods define actions: GET, POST, PUT, PATCH, DELETE
- Hierarchical URLs: `/users/{id}/orders`
- Plural for collections: `/users` not `/user`

**HTTP Methods:**
- `GET` - Retrieve (idempotent, safe)
- `POST` - Create
- `PUT` - Replace entire resource (idempotent)
- `PATCH` - Partial update
- `DELETE` - Remove (idempotent)

**Status Codes:**
- 2xx = Success (200 OK, 201 Created, 204 No Content)
- 4xx = Client error (400 Bad Request, 404 Not Found, 422 Validation)
- 5xx = Server error (500 Internal Error)

### GraphQL Essentials

**Schema-First:**
- Types define domain model
- Queries for reading
- Mutations for modifying
- Subscriptions for real-time

**Key Benefits:**
- Clients request exactly what they need
- Single endpoint, multiple operations
- Strongly typed schema
- Built-in introspection

### Versioning Strategies

**URL Versioning** (most common):
```
/api/v1/users  →  /api/v2/users
```

**Header Versioning** (cleaner):
```
API-Version: 2
Accept: application/vnd.api.v2+json
```

**GraphQL** (schema evolution):
```graphql
field: String @deprecated(reason: "Use newField")
```

---

## Quick Patterns

### REST: Resource Collections

```
GET    /api/users              List with pagination
POST   /api/users              Create
GET    /api/users/{id}         Get single
PATCH  /api/users/{id}         Partial update
DELETE /api/users/{id}         Remove

# Nested resources
GET    /api/users/{id}/orders  User's orders
```

### REST: Pagination

**Offset-based** (simple):
```
GET /api/users?page=2&page_size=20
```

**Cursor-based** (recommended for large datasets):
```
GET /api/users?limit=20&cursor=abc123
```

### REST: Filtering & Sorting

```
# Filter
GET /api/products?category=electronics&status=active

# Sort
GET /api/users?sort=-created_at  # Descending

# Search
GET /api/users?search=john
```

### REST: Error Responses

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Request validation failed",
    "details": [
      {"field": "email", "message": "Invalid format"}
    ],
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### GraphQL: Schema Structure

```graphql
# Types
type User {
  id: ID!
  email: String!
  orders(first: Int = 20, after: String): OrderConnection!
}

# Pagination (Relay spec)
type OrderConnection {
  edges: [OrderEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

# Mutations with errors
type CreateUserPayload {
  user: User
  errors: [UserError!]
}
```

### GraphQL: Resolver Patterns

**DataLoader (prevents N+1):**
```python
class OrdersByUserLoader(DataLoader):
    async def batch_load_fn(self, user_ids):
        # Load orders for all users in ONE query
        return await db.orders.find_by_user_ids(user_ids)
```

**Field-level auth:**
```python
@require_role("ADMIN")
async def resolve_admin_users(obj, info):
    return await db.users.find(role="ADMIN")
```

---

## Best Practices

### REST
1. **Consistent naming**: Plural nouns (`/users`), lowercase
2. **Stateless**: Each request self-contained
3. **Use correct status codes**: 2xx success, 4xx client error, 5xx server error
4. **Version from day one**: Plan for breaking changes
5. **Pagination**: Always paginate large collections
6. **Rate limiting**: Protect API with limits
7. **Documentation**: Use OpenAPI/Swagger

### GraphQL
1. **Schema first**: Design before implementing
2. **Avoid N+1**: Use DataLoaders for efficient fetching
3. **Input validation**: Validate at schema + resolver levels
4. **Error handling**: Return structured errors in payloads
5. **Pagination**: Use cursor-based (Relay spec)
6. **Deprecation**: Use `@deprecated` for gradual migration
7. **Monitoring**: Track query complexity and execution time

---

## Common Pitfalls

**REST:**
- ❌ Action-oriented endpoints (`/createUser`)
- ❌ Inconsistent status codes
- ❌ Missing pagination
- ❌ Tight coupling to database structure

**GraphQL:**
- ❌ N+1 query problems (missing DataLoaders)
- ❌ No depth/complexity limiting
- ❌ Generic error handling (throwing exceptions)
- ❌ Over-fetching by providing too many fields

**Both:**
- ❌ Breaking changes without versioning
- ❌ Poor error messages
- ❌ Missing rate limits
- ❌ Undocumented APIs

---

## Comprehensive Resources

**For detailed implementations, code examples, and advanced patterns:**

`read .claude/skills/api-design-principles/CLAUDE.md`

**Specialized topics:**
- REST best practices: `read .claude/skills/api-design-principles/references/rest-best-practices.md`
- GraphQL schema design: `read .claude/skills/api-design-principles/references/graphql-schema-design.md`
- API versioning: `read .claude/skills/api-design-principles/references/api-versioning-strategies.md`

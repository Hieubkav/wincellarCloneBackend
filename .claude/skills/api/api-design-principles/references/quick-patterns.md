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


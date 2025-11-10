# GraphQL Schema Design Patterns

Best practices and anti-patterns for GraphQL schema design, resolvers, and performance optimization.

## Schema Design Principles

### 1. Schema-First Development

**Always design schema before implementation:**

```graphql
# schema.graphql - Define types first

type User {
  id: ID!
  email: String!
  name: String!
}

type Query {
  user(id: ID!): User
  users: [User!]!
}
```

**Benefits:**
- Clear contract between frontend/backend
- Documentation built-in
- Type safety from start
- Easier to refactor

### 2. Nullable vs Non-Nullable Fields

```graphql
# Guidelines:
type User {
  # IDs are always non-null
  id: ID!
  
  # Required business fields - non-null
  email: String!
  name: String!
  
  # Optional fields - nullable
  bio: String
  phoneNumber: String
  
  # Relationships can be nullable (might not exist)
  profile: UserProfile
  
  # Lists: prefer non-null list with non-null items
  orders: [Order!]!  # Never null, never contains nulls
  # vs
  orders: [Order]    # Can be null, can contain nulls (avoid!)
}
```

**Rule of thumb:** Make fields non-nullable by default, nullable only when necessary.

---

## Type Design Patterns

### Pattern 1: Interface for Polymorphism

```graphql
interface Node {
  id: ID!
  createdAt: DateTime!
  updatedAt: DateTime!
}

type User implements Node {
  id: ID!
  createdAt: DateTime!
  updatedAt: DateTime!
  email: String!
  name: String!
}

type Product implements Node {
  id: ID!
  createdAt: DateTime!
  updatedAt: DateTime!
  name: String!
  price: Money!
}

type Query {
  # Can query by interface
  node(id: ID!): Node
  
  # Returns different types
  search(query: String!): [Node!]!
}
```

**Client usage:**
```graphql
query {
  node(id: "123") {
    id
    ... on User {
      email
      name
    }
    ... on Product {
      name
      price
    }
  }
}
```

### Pattern 2: Union Types for Heterogeneous Lists

```graphql
union SearchResult = User | Product | Article

type Query {
  search(query: String!): [SearchResult!]!
}
```

**When to use:**
- Results can be different types
- Types don't share common fields (use Interface instead if they do)

### Pattern 3: Enum for Constants

```graphql
enum UserRole {
  ADMIN
  MANAGER
  USER
  GUEST
}

enum OrderStatus {
  PENDING
  CONFIRMED
  SHIPPED
  DELIVERED
  CANCELLED
}

type User {
  role: UserRole!
}

type Order {
  status: OrderStatus!
}
```

**Benefits:**
- Type safety
- Auto-complete in GraphiQL
- Validation built-in
- Self-documenting

---

## Pagination Patterns

### Pattern 1: Relay Cursor Connection (Recommended)

```graphql
type User {
  id: ID!
  name: String!
}

type UserEdge {
  node: User!
  cursor: String!
}

type UserConnection {
  edges: [UserEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

type PageInfo {
  hasNextPage: Boolean!
  hasPreviousPage: Boolean!
  startCursor: String
  endCursor: String
}

type Query {
  users(
    first: Int
    after: String
    last: Int
    before: String
  ): UserConnection!
}
```

**Usage:**
```graphql
query {
  users(first: 10, after: "cursor123") {
    edges {
      node {
        id
        name
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
    totalCount
  }
}
```

**Benefits:**
- Standard pattern (widely adopted)
- Forward/backward pagination
- Cursor-based (consistent with data changes)

### Pattern 2: Simple Offset Pagination

```graphql
type UserPage {
  items: [User!]!
  total: Int!
  page: Int!
  pageSize: Int!
  hasNext: Boolean!
}

type Query {
  users(page: Int = 1, pageSize: Int = 20): UserPage!
}
```

**Simpler but less flexible than Relay pattern.**

---

## Input Design Patterns

### Pattern 1: Input Types for Mutations

```graphql
# Separate input types from output types
input CreateUserInput {
  email: String!
  name: String!
  password: String!
  role: UserRole = USER
}

input UpdateUserInput {
  name: String
  role: UserRole
  isActive: Boolean
}

# Separate input for nested objects
input AddressInput {
  street: String!
  city: String!
  zipCode: String!
}

type Mutation {
  createUser(input: CreateUserInput!): CreateUserPayload!
  updateUser(id: ID!, input: UpdateUserInput!): UpdateUserPayload!
}
```

**Why separate inputs?**
- Different validation rules
- Output has computed fields (id, timestamps)
- Input might have fields not in output (password)

### Pattern 2: Payload Types for Mutations

```graphql
# Always return payload with user + errors
type CreateUserPayload {
  user: User
  errors: [UserError!]
}

type UserError {
  field: String
  message: String!
  code: UserErrorCode!
}

enum UserErrorCode {
  EMAIL_ALREADY_EXISTS
  WEAK_PASSWORD
  INVALID_ROLE
}
```

**Benefits:**
- Return success data OR errors
- Errors are typed and structured
- No exceptions for business logic errors

**Usage:**
```graphql
mutation {
  createUser(input: {
    email: "test@example.com"
    name: "Test User"
    password: "weak"
  }) {
    user {
      id
      name
    }
    errors {
      field
      message
      code
    }
  }
}

# Response:
{
  "data": {
    "createUser": {
      "user": null,
      "errors": [
        {
          "field": "password",
          "message": "Password must be at least 8 characters",
          "code": "WEAK_PASSWORD"
        }
      ]
    }
  }
}
```

---

## Resolver Patterns

### Pattern 1: DataLoader for N+1 Prevention

**Problem: N+1 Query**

```graphql
query {
  users {        # 1 query
    id
    name
    orders {     # N queries (one per user!)
      id
      total
    }
  }
}
```

**Solution: DataLoader**

```python
from aiodataloader import DataLoader

class OrdersByUserLoader(DataLoader):
    async def batch_load_fn(self, user_ids: List[str]) -> List[List[Order]]:
        """Load orders for multiple users in ONE query."""
        
        # Single database query for all user IDs
        orders = await db.orders.find_by_user_ids(user_ids)
        
        # Group by user_id
        orders_by_user = {}
        for order in orders:
            uid = order.user_id
            if uid not in orders_by_user:
                orders_by_user[uid] = []
            orders_by_user[uid].append(order)
        
        # Return in same order as input
        return [orders_by_user.get(uid, []) for uid in user_ids]

# Resolver
@user_type.field("orders")
async def resolve_orders(user, info):
    """Use DataLoader to batch load orders."""
    loader = info.context["loaders"]["orders_by_user"]
    return await loader.load(user["id"])
```

### Pattern 2: Field-Level Authorization

```python
from functools import wraps
from graphql import GraphQLError

def require_role(*allowed_roles):
    """Decorator for field-level auth."""
    def decorator(resolver):
        @wraps(resolver)
        async def wrapper(obj, info, **kwargs):
            # Get current user from context
            user = info.context.get("user")
            
            if not user:
                raise GraphQLError("Authentication required")
            
            if user.role not in allowed_roles:
                raise GraphQLError(
                    f"Insufficient permissions. Required: {allowed_roles}"
                )
            
            return await resolver(obj, info, **kwargs)
        return wrapper
    return decorator

# Usage
@query.field("adminUsers")
@require_role("ADMIN")
async def resolve_admin_users(obj, info):
    return await db.users.find(role="ADMIN")

@mutation.field("deleteUser")
@require_role("ADMIN", "MANAGER")
async def resolve_delete_user(obj, info, id: str):
    return await db.users.delete(id)
```

### Pattern 3: Computed Fields

```graphql
type User {
  id: ID!
  name: String!
  
  # Computed fields
  fullName: String!           # Computed from firstName + lastName
  age: Int                    # Computed from birthDate
  totalOrders: Int!           # Count from database
  totalSpent: Money!          # Sum from orders
}
```

```python
@user_type.field("fullName")
def resolve_full_name(user, info):
    """Simple computed field."""
    return f"{user['first_name']} {user['last_name']}"

@user_type.field("age")
def resolve_age(user, info):
    """Computed from birthDate."""
    if not user.get("birth_date"):
        return None
    
    today = date.today()
    birth = user["birth_date"]
    return today.year - birth.year - (
        (today.month, today.day) < (birth.month, birth.day)
    )

@user_type.field("totalOrders")
async def resolve_total_orders(user, info):
    """Expensive: count from database."""
    # Use DataLoader or caching!
    return await db.orders.count_by_user(user["id"])
```

---

## Query Complexity & Depth Limiting

### Pattern 1: Depth Limiting

```python
from graphql import GraphQLError

def validate_query_depth(query_ast, max_depth: int = 5):
    """Prevent deeply nested queries."""
    
    def get_depth(node, current_depth=0):
        if not hasattr(node, "selection_set") or not node.selection_set:
            return current_depth
        
        max_nested_depth = current_depth
        for selection in node.selection_set.selections:
            nested_depth = get_depth(selection, current_depth + 1)
            max_nested_depth = max(max_nested_depth, nested_depth)
        
        return max_nested_depth
    
    depth = get_depth(query_ast)
    
    if depth > max_depth:
        raise GraphQLError(
            f"Query too deep: {depth} levels (max {max_depth})"
        )
```

**Prevents attacks like:**
```graphql
query {
  user {
    friends {
      friends {
        friends {
          friends {
            # ... 100 levels deep
          }
        }
      }
    }
  }
}
```

### Pattern 2: Complexity Analysis

```python
class ComplexityAnalyzer:
    """Analyze and limit query complexity."""
    
    FIELD_COMPLEXITY = {
        "User": 1,
        "User.orders": 10,     # Expensive relationship
        "Order": 1,
        "Order.items": 5,
    }
    
    def calculate_complexity(self, query_ast, variables: dict) -> int:
        """Calculate total query cost."""
        return self._calculate_field_complexity(query_ast, variables)
    
    def _calculate_field_complexity(self, field, variables: dict) -> int:
        # Get base complexity
        type_name = field.parent_type.name if hasattr(field, "parent_type") else "Query"
        field_name = field.name.value if hasattr(field, "name") else "unknown"
        base_complexity = self.FIELD_COMPLEXITY.get(
            f"{type_name}.{field_name}",
            self.FIELD_COMPLEXITY.get(type_name, 1)
        )
        
        # For lists, multiply by limit
        if hasattr(field, "selection_set") and field.selection_set:
            limit = self._get_limit(field, variables)
            nested_complexity = sum(
                self._calculate_field_complexity(f, variables)
                for f in field.selection_set.selections
            )
            return base_complexity + (nested_complexity * limit)
        
        return base_complexity
```

---

## Subscription Patterns

### Pattern 1: Event-Based Subscriptions

```graphql
type Subscription {
  # Subscribe to order updates for current user
  orderUpdated: Order!
  
  # Subscribe to specific order
  orderStatusChanged(orderId: ID!): OrderStatus!
  
  # Subscribe to new messages in chat
  messageAdded(chatId: ID!): Message!
}
```

```python
@subscription.field("orderUpdated")
async def subscribe_order_updated(obj, info):
    """Subscribe to order updates."""
    
    # Get current user
    user = info.context["user"]
    if not user:
        raise GraphQLError("Authentication required")
    
    # Create async generator
    async def order_stream():
        queue = asyncio.Queue()
        
        # Subscribe to Redis/PubSub channel
        channel = f"user:{user.id}:orders"
        subscription_id = await pubsub.subscribe(
            channel,
            callback=lambda order: queue.put_nowait(order)
        )
        
        try:
            while True:
                order = await queue.get()
                yield order
        finally:
            await pubsub.unsubscribe(subscription_id)
    
    return order_stream()
```

---

## Schema Stitching & Federation

### Pattern 1: Schema Federation (Microservices)

```graphql
# User service
type User @key(fields: "id") {
  id: ID!
  email: String!
  name: String!
}

# Order service
extend type User @key(fields: "id") {
  id: ID! @external
  orders: [Order!]!
}

type Order {
  id: ID!
  userId: ID!
  total: Money!
}
```

**Benefits:**
- Separate teams, separate schemas
- Each service owns its domain
- Unified GraphQL gateway

---

## Anti-Patterns to Avoid

### ❌ Anti-Pattern 1: No Pagination

```graphql
# Bad: Returns ALL users
type Query {
  users: [User!]!
}

# Good: Paginated
type Query {
  users(first: Int!, after: String): UserConnection!
}
```

### ❌ Anti-Pattern 2: Exposing Database Structure

```graphql
# Bad: Mirrors database
type user_table {
  user_id: Int!
  user_email: String!
  user_created_timestamp: BigInt!
}

# Good: Business domain model
type User {
  id: ID!
  email: String!
  createdAt: DateTime!
}
```

### ❌ Anti-Pattern 3: Generic Types

```graphql
# Bad: No type safety
type Query {
  getData(type: String!, id: String!): JSON
}

# Good: Specific types
type Query {
  user(id: ID!): User
  product(id: ID!): Product
}
```

### ❌ Anti-Pattern 4: No Error Handling

```graphql
# Bad: Throws exceptions
type Mutation {
  createUser(input: CreateUserInput!): User!
}

# Good: Returns errors in payload
type Mutation {
  createUser(input: CreateUserInput!): CreateUserPayload!
}

type CreateUserPayload {
  user: User
  errors: [UserError!]
}
```

---

## Testing GraphQL APIs

```python
import pytest
from graphql import graphql

@pytest.mark.asyncio
async def test_user_query():
    query = """
        query {
            user(id: "123") {
                id
                name
                email
            }
        }
    """
    
    result = await graphql(schema, query, context_value={"user": admin_user})
    
    assert not result.errors
    assert result.data["user"]["id"] == "123"
    assert result.data["user"]["name"] == "Test User"

@pytest.mark.asyncio
async def test_create_user_mutation():
    mutation = """
        mutation($input: CreateUserInput!) {
            createUser(input: $input) {
                user {
                    id
                    name
                }
                errors {
                    field
                    message
                    code
                }
            }
        }
    """
    
    variables = {
        "input": {
            "email": "test@example.com",
            "name": "Test User",
            "password": "strongpassword123"
        }
    }
    
    result = await graphql(
        schema,
        mutation,
        variable_values=variables,
        context_value={"user": admin_user}
    )
    
    assert not result.errors
    assert result.data["createUser"]["user"] is not None
    assert len(result.data["createUser"]["errors"]) == 0
```

---

For related topics:
- REST API design: `read .claude/skills/api-design-principles/references/rest-best-practices.md`
- API versioning: `read .claude/skills/api-design-principles/references/api-versioning-strategies.md`

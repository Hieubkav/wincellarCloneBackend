# API Design Principles - Comprehensive Guide

This document provides detailed patterns, code examples, and best practices for REST and GraphQL API design.

## Table of Contents

1. [REST API Patterns](#rest-api-patterns)
2. [GraphQL Patterns](#graphql-patterns)
3. [Versioning Strategies](#versioning-strategies)
4. [Authentication & Security](#authentication--security)
5. [Performance Optimization](#performance-optimization)
6. [Testing Strategies](#testing-strategies)

---

## REST API Patterns

### Pattern 1: Resource Collection Design

**Complete endpoint structure:**

```python
# Good: Resource-oriented endpoints
GET    /api/users              # List users (with pagination)
POST   /api/users              # Create user
GET    /api/users/{id}         # Get specific user
PUT    /api/users/{id}         # Replace user (full update)
PATCH  /api/users/{id}         # Update user fields (partial)
DELETE /api/users/{id}         # Delete user

# Nested resources
GET    /api/users/{id}/orders       # Get user's orders
POST   /api/users/{id}/orders       # Create order for user
GET    /api/users/{id}/orders/{oid} # Get specific order

# Bad: Action-oriented endpoints (AVOID!)
POST   /api/createUser
POST   /api/getUserById
POST   /api/deleteUser
GET    /api/user/list
```

**FastAPI Implementation:**

```python
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel, EmailStr, Field
from typing import Optional, List
from datetime import datetime

app = FastAPI(title="User Management API", version="1.0.0")

# Models
class UserBase(BaseModel):
    email: EmailStr
    name: str = Field(..., min_length=1, max_length=100)
    
class UserCreate(UserBase):
    password: str = Field(..., min_length=8)

class UserUpdate(BaseModel):
    email: Optional[EmailStr] = None
    name: Optional[str] = Field(None, min_length=1, max_length=100)

class UserResponse(UserBase):
    id: str
    created_at: datetime
    updated_at: datetime
    
    class Config:
        from_attributes = True

# Endpoints
@app.get("/api/users", response_model=List[UserResponse])
async def list_users(
    skip: int = 0,
    limit: int = 20,
    search: Optional[str] = None
):
    """List all users with pagination and optional search."""
    users = await db.users.find(search=search, skip=skip, limit=limit)
    return users

@app.post("/api/users", response_model=UserResponse, status_code=status.HTTP_201_CREATED)
async def create_user(user: UserCreate):
    """Create a new user."""
    # Check if user exists
    existing = await db.users.find_by_email(user.email)
    if existing:
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail="User with this email already exists"
        )
    
    # Hash password
    hashed_password = hash_password(user.password)
    
    # Create user
    new_user = await db.users.create(
        email=user.email,
        name=user.name,
        password=hashed_password
    )
    return new_user

@app.get("/api/users/{user_id}", response_model=UserResponse)
async def get_user(user_id: str):
    """Get user by ID."""
    user = await db.users.find_by_id(user_id)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"User {user_id} not found"
        )
    return user

@app.patch("/api/users/{user_id}", response_model=UserResponse)
async def update_user(user_id: str, update: UserUpdate):
    """Partially update user."""
    user = await db.users.find_by_id(user_id)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"User {user_id} not found"
        )
    
    # Update only provided fields
    update_data = update.dict(exclude_unset=True)
    updated_user = await db.users.update(user_id, update_data)
    return updated_user

@app.delete("/api/users/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_user(user_id: str):
    """Delete user."""
    result = await db.users.delete(user_id)
    if not result:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"User {user_id} not found"
        )
    return None
```

### Pattern 2: Advanced Pagination and Filtering

**Cursor-based pagination (recommended for large datasets):**

```python
from typing import Optional, Generic, TypeVar, List
from pydantic import BaseModel
from base64 import b64encode, b64decode
import json

T = TypeVar('T')

class CursorPaginationParams(BaseModel):
    limit: int = Field(20, ge=1, le=100)
    cursor: Optional[str] = None

class PageInfo(BaseModel):
    has_next_page: bool
    has_previous_page: bool
    start_cursor: Optional[str]
    end_cursor: Optional[str]

class Edge(BaseModel, Generic[T]):
    node: T
    cursor: str

class Connection(BaseModel, Generic[T]):
    edges: List[Edge[T]]
    page_info: PageInfo
    total_count: int

def encode_cursor(data: dict) -> str:
    """Encode cursor from dict."""
    json_str = json.dumps(data, default=str)
    return b64encode(json_str.encode()).decode()

def decode_cursor(cursor: str) -> dict:
    """Decode cursor to dict."""
    json_str = b64decode(cursor.encode()).decode()
    return json.loads(json_str)

@app.get("/api/users/cursor", response_model=Connection[UserResponse])
async def list_users_cursor(
    limit: int = Query(20, ge=1, le=100),
    cursor: Optional[str] = Query(None),
    order_by: str = Query("created_at", regex="^(created_at|name|email)$"),
    order: str = Query("desc", regex="^(asc|desc)$")
):
    """List users with cursor-based pagination."""
    
    # Decode cursor
    after = decode_cursor(cursor) if cursor else None
    
    # Build query
    query = db.users.query()
    if after:
        # Resume from cursor position
        if order == "desc":
            query = query.where(order_by, "<", after[order_by])
        else:
            query = query.where(order_by, ">", after[order_by])
    
    # Fetch limit + 1 to check if there's next page
    query = query.order_by(order_by, order).limit(limit + 1)
    users = await query.fetch()
    
    # Check pagination
    has_next = len(users) > limit
    if has_next:
        users = users[:limit]
    
    # Build edges with cursors
    edges = [
        Edge(
            node=user,
            cursor=encode_cursor({order_by: getattr(user, order_by), "id": user.id})
        )
        for user in users
    ]
    
    # Total count (expensive, cache this!)
    total_count = await db.users.count()
    
    return Connection(
        edges=edges,
        page_info=PageInfo(
            has_next_page=has_next,
            has_previous_page=cursor is not None,
            start_cursor=edges[0].cursor if edges else None,
            end_cursor=edges[-1].cursor if edges else None
        ),
        total_count=total_count
    )
```

**Advanced filtering:**

```python
from enum import Enum
from datetime import datetime

class UserStatus(str, Enum):
    ACTIVE = "active"
    INACTIVE = "inactive"
    SUSPENDED = "suspended"

class UserFilterParams(BaseModel):
    status: Optional[UserStatus] = None
    created_after: Optional[datetime] = None
    created_before: Optional[datetime] = None
    email_domain: Optional[str] = None
    search: Optional[str] = None  # Full-text search
    
@app.get("/api/users/search", response_model=List[UserResponse])
async def search_users(
    filters: UserFilterParams = Depends(),
    sort_by: str = Query("created_at"),
    order: str = Query("desc", regex="^(asc|desc)$"),
    limit: int = Query(20, ge=1, le=100),
    offset: int = Query(0, ge=0)
):
    """Advanced user search with multiple filters."""
    
    query = db.users.query()
    
    # Apply filters
    if filters.status:
        query = query.where("status", "=", filters.status)
    
    if filters.created_after:
        query = query.where("created_at", ">=", filters.created_after)
    
    if filters.created_before:
        query = query.where("created_at", "<=", filters.created_before)
    
    if filters.email_domain:
        query = query.where("email", "LIKE", f"%@{filters.email_domain}")
    
    if filters.search:
        # Full-text search (implementation depends on DB)
        query = query.search(filters.search, columns=["name", "email"])
    
    # Sort and paginate
    query = query.order_by(sort_by, order).limit(limit).offset(offset)
    
    users = await query.fetch()
    return users
```

### Pattern 3: Comprehensive Error Handling

```python
from fastapi import Request
from fastapi.responses import JSONResponse
from datetime import datetime
from typing import Any, Dict, Optional

class APIError(BaseModel):
    """Standard error response format."""
    error: str
    message: str
    details: Optional[Dict[str, Any]] = None
    timestamp: datetime
    path: str
    trace_id: Optional[str] = None

class ValidationErrorDetail(BaseModel):
    field: str
    message: str
    type: str
    value: Any = None

# Custom exceptions
class ResourceNotFoundError(Exception):
    def __init__(self, resource: str, identifier: str):
        self.resource = resource
        self.identifier = identifier
        super().__init__(f"{resource} {identifier} not found")

class ResourceConflictError(Exception):
    def __init__(self, resource: str, field: str, value: str):
        self.resource = resource
        self.field = field
        self.value = value
        super().__init__(f"{resource} with {field}='{value}' already exists")

class BusinessRuleViolationError(Exception):
    def __init__(self, rule: str, details: dict = None):
        self.rule = rule
        self.details = details or {}
        super().__init__(f"Business rule violation: {rule}")

# Exception handlers
@app.exception_handler(ResourceNotFoundError)
async def not_found_handler(request: Request, exc: ResourceNotFoundError):
    return JSONResponse(
        status_code=status.HTTP_404_NOT_FOUND,
        content=APIError(
            error="ResourceNotFound",
            message=str(exc),
            details={
                "resource": exc.resource,
                "identifier": exc.identifier
            },
            timestamp=datetime.utcnow(),
            path=str(request.url.path)
        ).dict()
    )

@app.exception_handler(ResourceConflictError)
async def conflict_handler(request: Request, exc: ResourceConflictError):
    return JSONResponse(
        status_code=status.HTTP_409_CONFLICT,
        content=APIError(
            error="ResourceConflict",
            message=str(exc),
            details={
                "resource": exc.resource,
                "field": exc.field,
                "value": exc.value
            },
            timestamp=datetime.utcnow(),
            path=str(request.url.path)
        ).dict()
    )

@app.exception_handler(RequestValidationError)
async def validation_handler(request: Request, exc: RequestValidationError):
    errors = [
        ValidationErrorDetail(
            field=".".join(str(loc) for loc in error["loc"]),
            message=error["msg"],
            type=error["type"],
            value=error.get("input")
        )
        for error in exc.errors()
    ]
    
    return JSONResponse(
        status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
        content=APIError(
            error="ValidationError",
            message="Request validation failed",
            details={"errors": [e.dict() for e in errors]},
            timestamp=datetime.utcnow(),
            path=str(request.url.path)
        ).dict()
    )

# Usage in endpoints
@app.post("/api/users", response_model=UserResponse)
async def create_user(user: UserCreate):
    # Check conflict
    existing = await db.users.find_by_email(user.email)
    if existing:
        raise ResourceConflictError("User", "email", user.email)
    
    # Business logic validation
    if not is_valid_domain(user.email):
        raise BusinessRuleViolationError(
            "InvalidEmailDomain",
            {"allowed_domains": ["example.com", "company.com"]}
        )
    
    return await db.users.create(user)
```

### Pattern 4: HATEOAS (Hypermedia)

```python
from typing import Dict, List

class Link(BaseModel):
    href: str
    method: str = "GET"
    rel: str
    type: Optional[str] = None

class HATEOASResponse(BaseModel):
    """Base class for HATEOAS responses."""
    _links: Dict[str, Link]
    
    @classmethod
    def add_link(cls, links: dict, rel: str, href: str, method: str = "GET"):
        links[rel] = Link(href=href, method=method, rel=rel)
        return links

class UserHATEOASResponse(UserResponse):
    _links: Dict[str, Link]
    
    @classmethod
    def from_user(cls, user: dict, base_url: str):
        user_id = user["id"]
        
        links = {}
        HATEOASResponse.add_link(links, "self", f"{base_url}/api/users/{user_id}")
        HATEOASResponse.add_link(links, "orders", f"{base_url}/api/users/{user_id}/orders")
        HATEOASResponse.add_link(links, "update", f"{base_url}/api/users/{user_id}", "PATCH")
        HATEOASResponse.add_link(links, "delete", f"{base_url}/api/users/{user_id}", "DELETE")
        
        return cls(**user, _links=links)

@app.get("/api/users/{user_id}", response_model=UserHATEOASResponse)
async def get_user_hateoas(user_id: str, request: Request):
    user = await db.users.find_by_id(user_id)
    if not user:
        raise ResourceNotFoundError("User", user_id)
    
    base_url = str(request.base_url).rstrip("/")
    return UserHATEOASResponse.from_user(user, base_url)
```

---

## GraphQL Patterns

### Pattern 1: Complete Schema Design

```graphql
# schema.graphql

# Scalar types
scalar DateTime
scalar Email
scalar Money
scalar JSON

# Enums for type safety
enum UserRole {
  ADMIN
  MANAGER
  USER
  GUEST
}

enum OrderStatus {
  PENDING
  CONFIRMED
  PROCESSING
  SHIPPED
  DELIVERED
  CANCELLED
  REFUNDED
}

# Object types
type User {
  id: ID!
  email: Email!
  name: String!
  role: UserRole!
  isActive: Boolean!
  createdAt: DateTime!
  updatedAt: DateTime!
  
  # Relationships
  profile: UserProfile
  orders(
    first: Int = 20
    after: String
    status: OrderStatus
    sortBy: OrderSortField = CREATED_AT
    sortOrder: SortOrder = DESC
  ): OrderConnection!
  
  # Computed fields
  totalOrders: Int!
  totalSpent: Money!
}

type UserProfile {
  bio: String
  avatar: String
  phone: String
  address: Address
}

type Address {
  street: String!
  city: String!
  state: String!
  zipCode: String!
  country: String!
}

type Order {
  id: ID!
  orderNumber: String!
  status: OrderStatus!
  total: Money!
  subtotal: Money!
  tax: Money!
  shipping: Money!
  items: [OrderItem!]!
  shippingAddress: Address!
  createdAt: DateTime!
  updatedAt: DateTime!
  
  # Relationships
  user: User!
}

type OrderItem {
  id: ID!
  product: Product!
  quantity: Int!
  price: Money!
  total: Money!
}

type Product {
  id: ID!
  name: String!
  description: String
  price: Money!
  stock: Int!
  category: Category!
  images: [String!]!
}

type Category {
  id: ID!
  name: String!
  slug: String!
  products(first: Int = 20, after: String): ProductConnection!
}

# Connection types (Relay spec)
type UserConnection {
  edges: [UserEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

type UserEdge {
  node: User!
  cursor: String!
}

type OrderConnection {
  edges: [OrderEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

type OrderEdge {
  node: Order!
  cursor: String!
}

type ProductConnection {
  edges: [ProductEdge!]!
  pageInfo: PageInfo!
  totalCount: Int!
}

type ProductEdge {
  node: Product!
  cursor: String!
}

type PageInfo {
  hasNextPage: Boolean!
  hasPreviousPage: Boolean!
  startCursor: String
  endCursor: String
}

# Input types
input CreateUserInput {
  email: Email!
  name: String!
  password: String!
  role: UserRole = USER
}

input UpdateUserInput {
  name: String
  role: UserRole
  isActive: Boolean
}

input CreateOrderInput {
  items: [OrderItemInput!]!
  shippingAddress: AddressInput!
}

input OrderItemInput {
  productId: ID!
  quantity: Int!
}

input AddressInput {
  street: String!
  city: String!
  state: String!
  zipCode: String!
  country: String!
}

# Payload types
type CreateUserPayload {
  user: User
  errors: [UserError!]
}

type UpdateUserPayload {
  user: User
  errors: [UserError!]
}

type DeleteUserPayload {
  success: Boolean!
  message: String
}

type CreateOrderPayload {
  order: Order
  errors: [OrderError!]
}

# Error types
interface Error {
  message: String!
  path: [String!]
}

type UserError implements Error {
  message: String!
  path: [String!]
  code: UserErrorCode!
}

enum UserErrorCode {
  EMAIL_ALREADY_EXISTS
  INVALID_EMAIL
  WEAK_PASSWORD
  UNAUTHORIZED
  NOT_FOUND
}

type OrderError implements Error {
  message: String!
  path: [String!]
  code: OrderErrorCode!
}

enum OrderErrorCode {
  INSUFFICIENT_STOCK
  INVALID_PRODUCT
  INVALID_ADDRESS
  PAYMENT_FAILED
}

# Sort enums
enum OrderSortField {
  CREATED_AT
  TOTAL
  STATUS
}

enum SortOrder {
  ASC
  DESC
}

# Query root
type Query {
  # User queries
  user(id: ID!): User
  users(
    first: Int = 20
    after: String
    role: UserRole
    search: String
    isActive: Boolean
  ): UserConnection!
  
  # Order queries
  order(id: ID!): Order
  orders(
    first: Int = 20
    after: String
    status: OrderStatus
    userId: ID
  ): OrderConnection!
  
  # Product queries
  product(id: ID!): Product
  products(
    first: Int = 20
    after: String
    categoryId: ID
    search: String
    minPrice: Money
    maxPrice: Money
  ): ProductConnection!
  
  # Current user (requires authentication)
  me: User
}

# Mutation root
type Mutation {
  # User mutations
  createUser(input: CreateUserInput!): CreateUserPayload!
  updateUser(id: ID!, input: UpdateUserInput!): UpdateUserPayload!
  deleteUser(id: ID!): DeleteUserPayload!
  
  # Order mutations
  createOrder(input: CreateOrderInput!): CreateOrderPayload!
  updateOrderStatus(id: ID!, status: OrderStatus!): Order
  cancelOrder(id: ID!): Order
  
  # Auth mutations
  login(email: Email!, password: String!): AuthPayload!
  logout: Boolean!
  refreshToken: AuthPayload!
}

type AuthPayload {
  token: String!
  refreshToken: String!
  user: User!
}

# Subscription root
type Subscription {
  orderUpdated(userId: ID!): Order!
  orderStatusChanged(orderId: ID!): OrderStatus!
}
```

### Pattern 2: Advanced Resolver Implementation

```python
from ariadne import QueryType, MutationType, ObjectType, SubscriptionType
from typing import Optional, List, Dict, Any
from dataclasses import dataclass
import asyncio

query = QueryType()
mutation = MutationType()
user_type = ObjectType("User")
order_type = ObjectType("Order")
subscription = SubscriptionType()

# Context setup with DataLoaders
from aiodataloader import DataLoader

class UserLoader(DataLoader):
    async def batch_load_fn(self, user_ids: List[str]) -> List[Optional[Dict]]:
        """Batch load users by IDs."""
        users = await db.users.find_by_ids(user_ids)
        user_map = {user["id"]: user for user in users}
        return [user_map.get(uid) for uid in user_ids]

class OrdersByUserLoader(DataLoader):
    async def batch_load_fn(self, user_ids: List[str]) -> List[List[Dict]]:
        """Batch load orders grouped by user ID."""
        orders = await db.orders.find_by_user_ids(user_ids)
        
        orders_by_user = {}
        for order in orders:
            uid = order["user_id"]
            if uid not in orders_by_user:
                orders_by_user[uid] = []
            orders_by_user[uid].append(order)
        
        return [orders_by_user.get(uid, []) for uid in user_ids]

def create_loaders():
    """Create DataLoader instances for context."""
    return {
        "user": UserLoader(),
        "orders_by_user": OrdersByUserLoader()
    }

# Query resolvers
@query.field("user")
async def resolve_user(obj, info, id: str) -> Optional[Dict]:
    """Resolve single user."""
    loader = info.context["loaders"]["user"]
    return await loader.load(id)

@query.field("users")
async def resolve_users(
    obj,
    info,
    first: int = 20,
    after: Optional[str] = None,
    role: Optional[str] = None,
    search: Optional[str] = None,
    isActive: Optional[bool] = None
) -> Dict:
    """Resolve paginated users with filtering."""
    
    # Decode cursor
    offset = decode_cursor(after) if after else 0
    
    # Build filters
    filters = {}
    if role:
        filters["role"] = role
    if isActive is not None:
        filters["is_active"] = isActive
    if search:
        filters["search"] = search
    
    # Fetch users (limit + 1 to check hasNextPage)
    users = await db.users.find(
        filters=filters,
        limit=first + 1,
        offset=offset
    )
    
    # Pagination
    has_next = len(users) > first
    if has_next:
        users = users[:first]
    
    # Build edges
    edges = [
        {
            "node": user,
            "cursor": encode_cursor(offset + i)
        }
        for i, user in enumerate(users)
    ]
    
    # Total count
    total_count = await db.users.count(filters=filters)
    
    return {
        "edges": edges,
        "pageInfo": {
            "hasNextPage": has_next,
            "hasPreviousPage": offset > 0,
            "startCursor": edges[0]["cursor"] if edges else None,
            "endCursor": edges[-1]["cursor"] if edges else None
        },
        "totalCount": total_count
    }

# Object field resolvers
@user_type.field("orders")
async def resolve_user_orders(
    user: Dict,
    info,
    first: int = 20,
    after: Optional[str] = None,
    status: Optional[str] = None
) -> Dict:
    """Resolve user's orders with DataLoader."""
    
    # Use DataLoader to prevent N+1
    loader = info.context["loaders"]["orders_by_user"]
    all_orders = await loader.load(user["id"])
    
    # Filter by status if provided
    if status:
        all_orders = [o for o in all_orders if o["status"] == status]
    
    # Paginate
    offset = decode_cursor(after) if after else 0
    orders = all_orders[offset:offset + first + 1]
    
    has_next = len(orders) > first
    if has_next:
        orders = orders[:first]
    
    edges = [
        {"node": order, "cursor": encode_cursor(offset + i)}
        for i, order in enumerate(orders)
    ]
    
    return {
        "edges": edges,
        "pageInfo": {
            "hasNextPage": has_next,
            "hasPreviousPage": offset > 0,
            "startCursor": edges[0]["cursor"] if edges else None,
            "endCursor": edges[-1]["cursor"] if edges else None
        },
        "totalCount": len(all_orders)
    }

@user_type.field("totalOrders")
async def resolve_total_orders(user: Dict, info) -> int:
    """Computed field: total order count."""
    return await db.orders.count_by_user(user["id"])

@user_type.field("totalSpent")
async def resolve_total_spent(user: Dict, info) -> float:
    """Computed field: total amount spent."""
    return await db.orders.sum_total_by_user(user["id"])

# Mutation resolvers
@mutation.field("createUser")
async def resolve_create_user(obj, info, input: Dict) -> Dict:
    """Create new user mutation."""
    
    try:
        # Validate email uniqueness
        existing = await db.users.find_by_email(input["email"])
        if existing:
            return {
                "user": None,
                "errors": [{
                    "message": "Email already exists",
                    "path": ["input", "email"],
                    "code": "EMAIL_ALREADY_EXISTS"
                }]
            }
        
        # Validate password strength
        if len(input["password"]) < 8:
            return {
                "user": None,
                "errors": [{
                    "message": "Password must be at least 8 characters",
                    "path": ["input", "password"],
                    "code": "WEAK_PASSWORD"
                }]
            }
        
        # Hash password
        hashed_password = hash_password(input["password"])
        
        # Create user
        user = await db.users.create(
            email=input["email"],
            name=input["name"],
            password=hashed_password,
            role=input.get("role", "USER")
        )
        
        # Clear loader cache
        info.context["loaders"]["user"].clear(user["id"])
        
        return {
            "user": user,
            "errors": []
        }
        
    except Exception as e:
        return {
            "user": None,
            "errors": [{
                "message": str(e),
                "path": [],
                "code": "INTERNAL_ERROR"
            }]
        }

# Subscription resolvers
@subscription.field("orderUpdated")
async def subscribe_order_updated(obj, info, userId: str):
    """Subscribe to order updates for specific user."""
    
    # Create async generator
    async def order_updates():
        queue = asyncio.Queue()
        
        # Register listener
        listener_id = await pubsub.subscribe(
            channel=f"user:{userId}:orders",
            callback=lambda msg: queue.put_nowait(msg)
        )
        
        try:
            while True:
                order = await queue.get()
                yield order
        finally:
            await pubsub.unsubscribe(listener_id)
    
    return order_updates()
```

### Pattern 3: Query Complexity & Depth Limiting

```python
from ariadne import SchemaDirectiveVisitor
from graphql import default_field_resolver, GraphQLError

class ComplexityAnalyzer:
    """Calculate and limit query complexity."""
    
    def __init__(self, max_complexity: int = 1000):
        self.max_complexity = max_complexity
    
    def calculate_complexity(self, query_ast, variables: dict) -> int:
        """Calculate total query complexity."""
        complexity = 0
        
        for field in query_ast.selection_set.selections:
            complexity += self._calculate_field_complexity(field, variables)
        
        return complexity
    
    def _calculate_field_complexity(self, field, variables: dict) -> int:
        """Calculate complexity for single field."""
        base_complexity = 1
        
        # Check for list fields
        if hasattr(field, "selection_set") and field.selection_set:
            # Nested fields
            nested_complexity = sum(
                self._calculate_field_complexity(f, variables)
                for f in field.selection_set.selections
            )
            
            # Multiply by limit/first argument
            limit = self._get_list_limit(field, variables)
            return base_complexity + (nested_complexity * limit)
        
        return base_complexity
    
    def _get_list_limit(self, field, variables: dict) -> int:
        """Extract limit from field arguments."""
        for arg in field.arguments:
            if arg.name.value in ("first", "limit"):
                value = arg.value
                if hasattr(value, "value"):
                    return int(value.value)
                elif hasattr(value, "name"):  # Variable
                    var_name = value.name.value
                    return int(variables.get(var_name, 20))
        return 20  # Default

# Middleware
from graphql import GraphQLResolveInfo

async def complexity_middleware(next, root, info: GraphQLResolveInfo, **kwargs):
    """Check query complexity before execution."""
    
    # Skip for non-query operations
    if info.operation.operation != "query":
        return await next(root, info, **kwargs)
    
    # Calculate complexity
    analyzer = ComplexityAnalyzer(max_complexity=1000)
    complexity = analyzer.calculate_complexity(
        info.operation,
        info.variable_values
    )
    
    # Reject if too complex
    if complexity > analyzer.max_complexity:
        raise GraphQLError(
            f"Query too complex: {complexity} exceeds limit {analyzer.max_complexity}",
            extensions={"code": "QUERY_TOO_COMPLEX", "complexity": complexity}
        )
    
    # Add complexity to context for monitoring
    info.context["query_complexity"] = complexity
    
    return await next(root, info, **kwargs)
```

---

## Versioning Strategies

### Strategy 1: URL Versioning (Most Common)

```python
# FastAPI with versioned routers
from fastapi import APIRouter

# Version 1
router_v1 = APIRouter(prefix="/api/v1")

@router_v1.get("/users")
async def list_users_v1():
    """V1: Simple user list."""
    return await db.users.find()

# Version 2
router_v2 = APIRouter(prefix="/api/v2")

@router_v2.get("/users")
async def list_users_v2(
    page: int = 1,
    page_size: int = 20
):
    """V2: Adds pagination."""
    offset = (page - 1) * page_size
    users = await db.users.find(limit=page_size, offset=offset)
    total = await db.users.count()
    
    return {
        "data": users,
        "meta": {
            "page": page,
            "page_size": page_size,
            "total": total,
            "pages": (total + page_size - 1) // page_size
        }
    }

# Register routers
app.include_router(router_v1)
app.include_router(router_v2)
```

### Strategy 2: GraphQL Schema Versioning

```graphql
# Use @deprecated directive
type User {
  id: ID!
  email: String!
  
  # Deprecated field
  fullName: String @deprecated(reason: "Use `name` instead")
  
  # New field
  name: String!
}

# Version through field evolution
type Query {
  # V1 (deprecated)
  users: [User!]! @deprecated(reason: "Use `usersPaginated` for better performance")
  
  # V2 (current)
  usersPaginated(first: Int!, after: String): UserConnection!
}
```

---

## Authentication & Security

### JWT Authentication

```python
from fastapi import Depends, HTTPException, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
import jwt
from datetime import datetime, timedelta

security = HTTPBearer()

SECRET_KEY = "your-secret-key"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

def create_access_token(user_id: str) -> str:
    """Create JWT access token."""
    expire = datetime.utcnow() + timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    to_encode = {"sub": user_id, "exp": expire}
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)

async def get_current_user(
    credentials: HTTPAuthorizationCredentials = Depends(security)
) -> Dict:
    """Extract and validate current user from JWT."""
    token = credentials.credentials
    
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id = payload.get("sub")
        if not user_id:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid token"
            )
    except jwt.ExpiredSignatureError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token expired"
        )
    except jwt.JWTError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid token"
        )
    
    user = await db.users.find_by_id(user_id)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="User not found"
        )
    
    return user

# Protected endpoint
@app.get("/api/me", response_model=UserResponse)
async def get_current_user_info(
    current_user: Dict = Depends(get_current_user)
):
    """Get current authenticated user."""
    return current_user
```

---

## Performance Optimization

### Caching Strategies

```python
from functools import wraps
from typing import Optional
import hashlib
import json

class Cache:
    """Simple async cache with TTL."""
    
    def __init__(self):
        self._cache = {}
    
    async def get(self, key: str) -> Optional[Any]:
        """Get value from cache."""
        if key in self._cache:
            value, expires = self._cache[key]
            if datetime.utcnow() < expires:
                return value
            else:
                del self._cache[key]
        return None
    
    async def set(self, key: str, value: Any, ttl: int = 300):
        """Set value in cache with TTL (seconds)."""
        expires = datetime.utcnow() + timedelta(seconds=ttl)
        self._cache[key] = (value, expires)

cache = Cache()

def cached(ttl: int = 300):
    """Decorator for caching endpoint responses."""
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, **kwargs):
            # Generate cache key from function name and arguments
            key_data = {
                "func": func.__name__,
                "args": str(args),
                "kwargs": json.dumps(kwargs, default=str, sort_keys=True)
            }
            cache_key = hashlib.md5(
                json.dumps(key_data).encode()
            ).hexdigest()
            
            # Try cache first
            cached_value = await cache.get(cache_key)
            if cached_value is not None:
                return cached_value
            
            # Execute function
            result = await func(*args, **kwargs)
            
            # Store in cache
            await cache.set(cache_key, result, ttl=ttl)
            
            return result
        return wrapper
    return decorator

# Usage
@app.get("/api/users", response_model=List[UserResponse])
@cached(ttl=60)  # Cache for 60 seconds
async def list_users():
    return await db.users.find()
```

---

For more details on specific topics, see:
- `read .claude/skills/api-design-principles/references/rest-best-practices.md`
- `read .claude/skills/api-design-principles/references/graphql-schema-design.md`
- `read .claude/skills/api-design-principles/references/api-versioning-strategies.md`

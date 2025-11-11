# API Versioning Strategies

Comprehensive guide to API versioning approaches, migration strategies, and deprecation policies.

## Why Version APIs?

**Breaking changes are inevitable:**
- Field name changes
- Field type changes
- Removing fields
- Changing response structure
- Authentication changes
- Business logic changes

**Versioning allows:**
- Backward compatibility
- Gradual migration
- Support multiple client versions
- Clear deprecation path

---

## Versioning Strategies Comparison

| Strategy | Pros | Cons | Use When |
|----------|------|------|----------|
| URL Versioning | Clear, visible, easy to cache | URL pollution, duplicate endpoints | Public APIs, RESTful services |
| Header Versioning | Clean URLs, flexible | Less visible, harder to test | Internal APIs, microservices |
| Query Parameter | Easy to implement | URL pollution, poor caching | Simple APIs, prototypes |
| Content Negotiation | RESTful, semantic | Complex, non-obvious | Mature APIs, content-type matters |

---

## Strategy 1: URL Versioning (Most Common)

### Pattern: `/api/v{n}/resource`

```
https://api.example.com/api/v1/users
https://api.example.com/api/v2/users
https://api.example.com/api/v3/users
```

### Implementation (FastAPI)

```python
from fastapi import APIRouter, FastAPI

app = FastAPI()

# Version 1
router_v1 = APIRouter(prefix="/api/v1", tags=["v1"])

@router_v1.get("/users")
async def list_users_v1():
    """V1: Simple list without pagination."""
    return await db.users.find()

@router_v1.get("/users/{user_id}")
async def get_user_v1(user_id: int):
    """V1: Returns user by numeric ID."""
    return await db.users.find_by_id(user_id)

# Version 2
router_v2 = APIRouter(prefix="/api/v2", tags=["v2"])

@router_v2.get("/users")
async def list_users_v2(
    page: int = 1,
    page_size: int = 20,
    sort: str = "created_at"
):
    """V2: Adds pagination and sorting."""
    offset = (page - 1) * page_size
    users = await db.users.find(
        limit=page_size,
        offset=offset,
        order_by=sort
    )
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

@router_v2.get("/users/{user_id}")
async def get_user_v2(user_id: str):
    """V2: Uses UUID strings instead of numeric IDs."""
    return await db.users.find_by_uuid(user_id)

# Register routers
app.include_router(router_v1)
app.include_router(router_v2)
```

### Benefits:
- ✅ Immediately visible in URL
- ✅ Easy to route, cache, and test
- ✅ Clear separation of versions
- ✅ Can run multiple versions simultaneously

### Drawbacks:
- ❌ URL proliferation
- ❌ Code duplication
- ❌ Client must change URLs for upgrade

---

## Strategy 2: Header Versioning

### Pattern: Custom `API-Version` header

```
GET /api/users
API-Version: 1

GET /api/users
API-Version: 2
```

### Implementation

```python
from fastapi import Header, HTTPException

async def get_api_version(api_version: str = Header(default="1")) -> int:
    """Extract and validate API version from header."""
    try:
        version = int(api_version)
        if version not in [1, 2, 3]:
            raise HTTPException(
                status_code=400,
                detail=f"Unsupported API version: {version}"
            )
        return version
    except ValueError:
        raise HTTPException(
            status_code=400,
            detail="Invalid API-Version header format"
        )

@app.get("/api/users")
async def list_users(version: int = Depends(get_api_version)):
    """Route to different implementations based on version."""
    if version == 1:
        return await list_users_v1()
    elif version == 2:
        return await list_users_v2()
    else:
        # Latest version
        return await list_users_v3()

async def list_users_v1():
    """V1 implementation."""
    return await db.users.find()

async def list_users_v2():
    """V2 implementation with pagination."""
    # ... pagination logic
    pass
```

### Alternative: Accept Header

```
Accept: application/vnd.example.v1+json
Accept: application/vnd.example.v2+json
```

```python
from fastapi import Header

async def get_version_from_accept(
    accept: str = Header(default="application/json")
) -> int:
    """Parse version from Accept header."""
    import re
    match = re.search(r'application/vnd\.example\.v(\d+)\+json', accept)
    if match:
        return int(match.group(1))
    return 1  # Default to v1

@app.get("/api/users")
async def list_users(version: int = Depends(get_version_from_accept)):
    # ... route based on version
    pass
```

### Benefits:
- ✅ Clean URLs
- ✅ Same endpoint, multiple versions
- ✅ Easy to add new versions
- ✅ RESTful (uses HTTP semantics)

### Drawbacks:
- ❌ Less visible (not in URL)
- ❌ Harder to test/debug
- ❌ Caching more complex
- ❌ Clients must set headers correctly

---

## Strategy 3: Query Parameter Versioning

### Pattern: `?version=N`

```
GET /api/users?version=1
GET /api/users?version=2
```

### Implementation

```python
from fastapi import Query

@app.get("/api/users")
async def list_users(version: int = Query(default=1, ge=1, le=3)):
    """Route based on query parameter."""
    if version == 1:
        return await list_users_v1()
    elif version == 2:
        return await list_users_v2()
    else:
        return await list_users_v3()
```

### Benefits:
- ✅ Very simple to implement
- ✅ Visible in URL
- ✅ Easy to test

### Drawbacks:
- ❌ URL pollution
- ❌ Caching issues
- ❌ Feels hacky
- ❌ Not RESTful

**Best for:** Internal tools, prototypes, temporary versioning.

---

## Strategy 4: No Explicit Version (Evolution Only)

### Pattern: Additive changes only

**Rules:**
1. Never remove fields
2. Never change field types
3. Only add new optional fields
4. Deprecate instead of delete

```graphql
# Initial schema
type User {
  id: ID!
  name: String!
}

# Evolution: ADD new field, keep old one
type User {
  id: ID!
  name: String! @deprecated(reason: "Use firstName and lastName")
  firstName: String!
  lastName: String!
}
```

```python
# REST equivalent
{
  "id": 123,
  "name": "John Doe",           # Deprecated but still works
  "first_name": "John",          # New field
  "last_name": "Doe"             # New field
}
```

### Benefits:
- ✅ No breaking changes
- ✅ Clients upgrade at their pace
- ✅ Simple to maintain

### Drawbacks:
- ❌ Schema bloat over time
- ❌ Can't fix design mistakes
- ❌ Eventually need major version

**Best for:** GraphQL APIs, internal APIs, microservices.

---

## Migration Strategies

### Strategy 1: Big Bang (Not Recommended)

```
V1 deployed → V2 deployed → V1 REMOVED immediately
```

**Problems:**
- Breaks all clients
- Requires coordinated deployment
- High risk

**Only use for:** Internal services with single client.

### Strategy 2: Parallel Run (Recommended)

```
Phase 1: V1 only
Phase 2: V1 + V2 (both active)
Phase 3: V2 only (after migration period)
```

**Timeline example:**

```
Jan 1: Launch V2 (V1 still active)
       - Documentation updated
       - Migration guide published
       - Clients notified

Mar 1: V1 marked deprecated
       - Warning headers added
       - Deprecation notices in responses

Jun 1: V1 sunset (removed)
       - Only V2 available
```

### Implementation

```python
from datetime import datetime, date

V1_SUNSET_DATE = date(2024, 6, 1)

@router_v1.get("/users")
async def list_users_v1(response: Response):
    """V1 endpoint with deprecation warnings."""
    
    # Add deprecation headers
    response.headers["X-API-Version"] = "1"
    response.headers["X-API-Deprecated"] = "true"
    response.headers["X-API-Sunset-Date"] = V1_SUNSET_DATE.isoformat()
    response.headers["X-API-Upgrade-Guide"] = "https://docs.example.com/api/v2-migration"
    response.headers["Warning"] = '299 - "API v1 is deprecated. Upgrade to v2 by 2024-06-01"'
    
    # Check if past sunset
    if date.today() > V1_SUNSET_DATE:
        raise HTTPException(
            status_code=410,  # Gone
            detail={
                "error": "API_VERSION_RETIRED",
                "message": "API v1 has been retired. Please upgrade to v2.",
                "sunset_date": V1_SUNSET_DATE.isoformat(),
                "docs": "https://docs.example.com/api/v2-migration"
            }
        )
    
    return await db.users.find()
```

### Strategy 3: Adapter Pattern

**Share logic between versions:**

```python
# Core business logic (version-agnostic)
class UserService:
    async def list_users(
        self,
        offset: int = 0,
        limit: int = 20,
        sort: str = "created_at"
    ):
        """Core logic shared by all versions."""
        return await db.users.find(
            limit=limit,
            offset=offset,
            order_by=sort
        )

# Version adapters
class UserAdapterV1:
    """Adapt core service to V1 format."""
    
    def __init__(self, service: UserService):
        self.service = service
    
    async def list_users(self):
        """V1: Simple list, no pagination."""
        users = await self.service.list_users(limit=1000)
        return users

class UserAdapterV2:
    """Adapt core service to V2 format."""
    
    def __init__(self, service: UserService):
        self.service = service
    
    async def list_users(self, page: int, page_size: int):
        """V2: Paginated response."""
        offset = (page - 1) * page_size
        users = await self.service.list_users(
            offset=offset,
            limit=page_size
        )
        total = await db.users.count()
        
        return {
            "data": users,
            "meta": {
                "page": page,
                "page_size": page_size,
                "total": total
            }
        }

# Endpoints use adapters
service = UserService()

@router_v1.get("/users")
async def list_users_v1():
    adapter = UserAdapterV1(service)
    return await adapter.list_users()

@router_v2.get("/users")
async def list_users_v2(page: int = 1, page_size: int = 20):
    adapter = UserAdapterV2(service)
    return await adapter.list_users(page, page_size)
```

---

## Deprecation Best Practices

### 1. Communication

```
# Deprecation notice in response
{
  "data": [...],
  "_meta": {
    "deprecated": true,
    "sunset_date": "2024-06-01",
    "replacement": "/api/v2/users",
    "migration_guide": "https://docs.example.com/migration/v1-to-v2"
  }
}
```

### 2. Monitoring

```python
import logging
from collections import Counter

# Track V1 usage
v1_usage = Counter()

@router_v1.get("/users")
async def list_users_v1(request: Request):
    # Log deprecated usage
    client_id = request.headers.get("X-Client-ID", "unknown")
    v1_usage[client_id] += 1
    
    logging.warning(
        f"V1 API called by client {client_id}. "
        f"Total calls: {v1_usage[client_id]}"
    )
    
    # Alert if heavy usage close to sunset
    if date.today() > (V1_SUNSET_DATE - timedelta(days=30)):
        if v1_usage[client_id] > 1000:
            send_alert(
                f"Client {client_id} still using V1 heavily. "
                f"Sunset in {(V1_SUNSET_DATE - date.today()).days} days!"
            )
    
    return await db.users.find()
```

### 3. Gradual Degradation

```python
# Slow down deprecated endpoints
@router_v1.get("/users")
async def list_users_v1():
    # Add artificial delay to encourage migration
    if date.today() > (V1_SUNSET_DATE - timedelta(days=60)):
        await asyncio.sleep(1)  # 1 second delay
    
    return await db.users.find()
```

---

## GraphQL Versioning

### Pattern 1: Schema Evolution (Recommended)

```graphql
type User {
  id: ID!
  
  # V1 field (deprecated)
  name: String! @deprecated(reason: "Use firstName and lastName instead. Will be removed 2024-06-01")
  
  # V2 fields
  firstName: String!
  lastName: String!
}

type Query {
  # V1 (deprecated)
  users: [User!]! @deprecated(reason: "Use usersPaginated for better performance")
  
  # V2 (current)
  usersPaginated(first: Int!, after: String): UserConnection!
}
```

### Pattern 2: Namespace Versioning

```graphql
type Query {
  v1: V1Query
  v2: V2Query
}

type V1Query {
  users: [User!]!
}

type V2Query {
  users(first: Int!, after: String): UserConnection!
}
```

**Usage:**
```graphql
query {
  v2 {
    users(first: 10) {
      edges {
        node {
          firstName
          lastName
        }
      }
    }
  }
}
```

---

## Version Negotiation

### Auto-upgrade clients

```python
@app.middleware("http")
async def auto_upgrade_middleware(request: Request, call_next):
    """Automatically upgrade compatible V1 requests to V2."""
    
    # Check if V1 endpoint
    if request.url.path.startswith("/api/v1/"):
        # Check if client supports V2
        accept_version = request.headers.get("X-API-Accept-Version", "")
        
        if "2" in accept_version:
            # Rewrite to V2 endpoint
            new_path = request.url.path.replace("/api/v1/", "/api/v2/")
            request.scope["path"] = new_path
            
            # Add upgrade header to response
            response = await call_next(request)
            response.headers["X-API-Upgraded-From"] = "v1"
            response.headers["X-API-Current-Version"] = "v2"
            return response
    
    return await call_next(request)
```

---

## Testing Multiple Versions

```python
import pytest

@pytest.mark.parametrize("version", [1, 2])
@pytest.mark.asyncio
async def test_list_users_all_versions(version: int):
    """Test endpoint works across all versions."""
    async with AsyncClient(app=app, base_url="http://test") as client:
        response = await client.get(
            f"/api/v{version}/users",
            headers={"Authorization": f"Bearer {token}"}
        )
    
    assert response.status_code == 200
    data = response.json()
    
    if version == 1:
        # V1: simple list
        assert isinstance(data, list)
    elif version == 2:
        # V2: paginated
        assert "data" in data
        assert "meta" in data
```

---

## Key Takeaways

1. **Choose URL versioning for public APIs** (most visible, cacheable)
2. **Use header versioning for microservices** (clean URLs)
3. **GraphQL: use schema evolution** (@deprecated directive)
4. **Support old versions for 6-12 months** minimum
5. **Communicate clearly:** deprecation notices, sunset dates
6. **Monitor usage:** know who's still on old versions
7. **Never break clients without warning**

---

For related topics:
- REST best practices: `read .claude/skills/api-design-principles/references/rest-best-practices.md`
- GraphQL schema design: `read .claude/skills/api-design-principles/references/graphql-schema-design.md`

# Common Patterns & Solutions

## Anti-Pattern Detection Regex

Use these patterns to search codebase for potential issues:

```bash
# Find .collect() without filters (Convex)
rg "\.collect\(\)" --type ts

# Find potential N+1 in loops
rg "for.*await.*db\.|forEach.*await.*db\." --type ts

# Find Array.find() in map (potential O(n²))
rg "\.map\(.*\.find\(" --type ts

# Find queries without limit
rg "query\([^)]+\)(?!.*\.take|.*\.first|.*\.paginate)" --type ts

# Find potential full table scans
rg "findMany\(\s*\)" --type ts
rg "find\(\s*\{\s*\}\s*\)" --type ts
```

---

## Solution Templates

### Template 1: Batch Load Related Data

```typescript
async function loadWithRelations<T extends { relatedId: Id<"related"> }>(
  ctx: QueryCtx,
  items: T[]
): Promise<(T & { related: Related | null })[]> {
  // 1. Collect unique IDs
  const relatedIds = [...new Set(items.map(i => i.relatedId))];
  
  // 2. Batch load
  const related = await Promise.all(
    relatedIds.map(id => ctx.db.get(id))
  );
  
  // 3. Create lookup map
  const relatedMap = new Map(
    related.filter(Boolean).map(r => [r._id, r])
  );
  
  // 4. Attach to items
  return items.map(item => ({
    ...item,
    related: relatedMap.get(item.relatedId) ?? null,
  }));
}
```

### Template 2: Paginated Query with Filters

```typescript
export const listPaginated = query({
  args: {
    filters: v.object({
      status: v.optional(v.string()),
      type: v.optional(v.string()),
      userId: v.optional(v.id("users")),
    }),
    paginationOpts: paginationOptsValidator,
  },
  handler: async (ctx, { filters, paginationOpts }) => {
    // Choose best index based on filter selectivity
    const indexChoice = chooseIndex(filters);
    
    let query = ctx.db.query("items");
    
    if (indexChoice) {
      query = query.withIndex(indexChoice.name, indexChoice.filter);
    }
    
    // Apply remaining filters
    const results = await query.paginate(paginationOpts);
    
    // Filter remaining criteria in memory (if small result set)
    let filtered = results.page;
    if (indexChoice?.remainingFilters) {
      filtered = applyRemainingFilters(filtered, indexChoice.remainingFilters);
    }
    
    return {
      items: filtered,
      cursor: results.continueCursor,
      isDone: results.isDone,
    };
  }
});

function chooseIndex(filters: Filters) {
  // Order by selectivity (highest first)
  if (filters.userId) {
    return {
      name: "by_user",
      filter: (q: any) => q.eq("userId", filters.userId),
      remainingFilters: { status: filters.status, type: filters.type },
    };
  }
  if (filters.status) {
    return {
      name: "by_status",
      filter: (q: any) => q.eq("status", filters.status),
      remainingFilters: { type: filters.type },
    };
  }
  if (filters.type) {
    return {
      name: "by_type",
      filter: (q: any) => q.eq("type", filters.type),
      remainingFilters: {},
    };
  }
  return null;
}
```

### Template 3: Aggregation Counter

```typescript
// Schema
const stats = defineTable({
  key: v.string(),
  value: v.number(),
}).index("by_key", ["key"]);

// Helper functions
async function incrementStat(ctx: MutationCtx, key: string, amount = 1) {
  const existing = await ctx.db
    .query("stats")
    .withIndex("by_key", q => q.eq("key", key))
    .first();
  
  if (existing) {
    await ctx.db.patch(existing._id, { value: existing.value + amount });
  } else {
    await ctx.db.insert("stats", { key, value: amount });
  }
}

async function decrementStat(ctx: MutationCtx, key: string, amount = 1) {
  await incrementStat(ctx, key, -amount);
}

async function getStat(ctx: QueryCtx, key: string): Promise<number> {
  const stat = await ctx.db
    .query("stats")
    .withIndex("by_key", q => q.eq("key", key))
    .first();
  return stat?.value ?? 0;
}

// Usage in mutations
export const createPost = mutation({
  handler: async (ctx, args) => {
    const postId = await ctx.db.insert("posts", args);
    await incrementStat(ctx, "posts_total");
    await incrementStat(ctx, `posts_by_category_${args.category}`);
    return postId;
  }
});

export const deletePost = mutation({
  args: { id: v.id("posts") },
  handler: async (ctx, { id }) => {
    const post = await ctx.db.get(id);
    if (post) {
      await ctx.db.delete(id);
      await decrementStat(ctx, "posts_total");
      await decrementStat(ctx, `posts_by_category_${post.category}`);
    }
  }
});
```

### Template 4: Cached Expensive Query

```typescript
// For queries that are expensive but don't need real-time updates
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

const cache = defineTable({
  key: v.string(),
  data: v.any(),
  expiresAt: v.number(),
}).index("by_key", ["key"]);

export const getExpensiveData = query({
  args: { key: v.string() },
  handler: async (ctx, { key }) => {
    // Check cache
    const cached = await ctx.db
      .query("cache")
      .withIndex("by_key", q => q.eq("key", key))
      .first();
    
    if (cached && cached.expiresAt > Date.now()) {
      return cached.data;
    }
    
    // Cache miss - compute expensive data
    // This would typically be done in a mutation with scheduler
    return null;
  }
});

export const refreshExpensiveData = mutation({
  args: { key: v.string() },
  handler: async (ctx, { key }) => {
    // Compute expensive data
    const data = await computeExpensiveData(ctx);
    
    // Update cache
    const existing = await ctx.db
      .query("cache")
      .withIndex("by_key", q => q.eq("key", key))
      .first();
    
    if (existing) {
      await ctx.db.patch(existing._id, {
        data,
        expiresAt: Date.now() + CACHE_TTL,
      });
    } else {
      await ctx.db.insert("cache", {
        key,
        data,
        expiresAt: Date.now() + CACHE_TTL,
      });
    }
    
    return data;
  }
});
```

---

## Index Design Patterns

### Pattern 1: Single Field Index

```typescript
// For simple equality filters
.index("by_status", ["status"])

// Usage
.withIndex("by_status", q => q.eq("status", "active"))
```

### Pattern 2: Compound Index

```typescript
// For multiple filters or filter + sort
.index("by_user_created", ["userId", "createdAt"])

// Usage - can use prefix
.withIndex("by_user_created", q => q.eq("userId", userId))
.withIndex("by_user_created", q => 
  q.eq("userId", userId).gte("createdAt", startDate)
)
```

### Pattern 3: Index for Sort

```typescript
// Index field order matters for sorting
.index("by_created", ["createdAt"])

// Usage with order
.withIndex("by_created")
.order("desc")
```

### Pattern 4: Covering Index

```typescript
// Include all fields needed in query
// Avoids table lookup
.index("by_status_type_name", ["status", "type", "name"])

// Query that's fully covered
.withIndex("by_status_type_name", q => 
  q.eq("status", "active").eq("type", "premium")
)
// Returns name without additional lookup
```

---

## Performance Comparison Table

| Pattern | Before | After | Improvement |
|---------|--------|-------|-------------|
| Filter at DB vs JS | 10,000 records | 100 records | 99% |
| N+1 → Batch | 101 queries | 2 queries | 98% |
| find() → Map | O(n²) | O(n) | Quadratic → Linear |
| Sequential → Parallel | 3 × latency | 1 × latency | 66% |
| Full scan → Index | O(n) | O(log n) | Logarithmic |
| Count all → Aggregation | 100,000 reads | 1 read | 99.999% |

---

## Common Mistakes by Framework

### Convex

```typescript
// ❌ Wrong
.filter(q => q.eq(q.field("status"), "active")) // After collect
// ✅ Right
.withIndex("by_status", q => q.eq("status", "active")) // Uses index
```

### Prisma

```typescript
// ❌ Wrong
const users = await prisma.user.findMany();
const filtered = users.filter(u => u.role === "admin");

// ✅ Right
const users = await prisma.user.findMany({
  where: { role: "admin" }
});
```

### Firebase

```typescript
// ❌ Wrong
const snapshot = await getDocs(collection(db, "users"));
const admins = snapshot.docs.filter(d => d.data().role === "admin");

// ✅ Right
const q = query(
  collection(db, "users"),
  where("role", "==", "admin")
);
const snapshot = await getDocs(q);
```

### MongoDB

```typescript
// ❌ Wrong
const users = await User.find({});
const admins = users.filter(u => u.role === "admin");

// ✅ Right
const admins = await User.find({ role: "admin" });
```

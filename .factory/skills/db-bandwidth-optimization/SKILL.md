---
name: db-bandwidth-optimization
description: "Tối ưu Database Bandwidth và Cloud Costs để tránh bill shock. Sử dụng khi: (1) Review code có query database, (2) Phát hiện N+1 problem, (3) Tối ưu queries chậm, (4) Giảm cloud costs, (5) User hỏi về database performance, bandwidth, query optimization, hoặc khi thấy patterns như .collect(), .findAll(), fetch all then filter. CRITICAL: Sử dụng skill này TRƯỚC KHI deploy để tránh thảm họa tài chính."
version: 1.0.0
---

# Database Bandwidth & Cloud Cost Optimization

## Overview

Skill này giúp phát hiện và sửa các anti-patterns gây lãng phí Database Bandwidth, dẫn đến cloud bills khổng lồ. Một fresher có thể gây ra hàng nghìn đô la cloud costs chỉ với vài dòng code sai.

**Real-world impact:**
- Fetch ALL 100,000 records thay vì 10 cần thiết = 10,000x bandwidth
- N+1 queries với 1000 items = 1001 queries thay vì 2
- Missing index = Full table scan mỗi request

## Khi nào sử dụng Skill này

- Review code có database queries
- Thấy patterns nguy hiểm: `.collect()`, `.findAll()`, `.toArray()`
- Debug slow queries hoặc high database costs
- Trước khi deploy features mới có database operations
- Khi Convex/Firebase/Supabase dashboard báo bandwidth cao

---

## Phase 1: Phát hiện Anti-Patterns (Code Review)

### 1.1. CRITICAL ANTI-PATTERNS - Phải sửa ngay

```typescript
// ❌ THẢM HỌA #1: Fetch ALL rồi filter JS
const allUsers = await db.query("users").collect();
const activeUsers = allUsers.filter(u => u.status === "active");

// ✅ FIX: Filter ở database
const activeUsers = await db
  .query("users")
  .withIndex("by_status", q => q.eq("status", "active"))
  .collect();
```

```typescript
// ❌ THẢM HỌA #2: N+1 Problem
const posts = await db.query("posts").take(100);
for (const post of posts) {
  post.author = await db.get(post.authorId); // 100 queries!
}

// ✅ FIX: Batch loading
const posts = await db.query("posts").take(100);
const authorIds = [...new Set(posts.map(p => p.authorId))];
const authors = await Promise.all(authorIds.map(id => db.get(id)));
const authorMap = new Map(authors.map(a => [a._id, a]));
posts.forEach(p => p.author = authorMap.get(p.authorId));
```

```typescript
// ❌ THẢM HỌA #3: Fetch ALL để count
const allOrders = await db.query("orders").collect();
const count = allOrders.length;

// ✅ FIX: Dùng aggregation hoặc counter table
const stats = await db.query("orderStats").first();
const count = stats?.totalOrders ?? 0;
```

```typescript
// ❌ THẢM HỌA #4: No limit
const results = await db.query("logs").collect(); // Có thể là 1 triệu records!

// ✅ FIX: Luôn có limit
const results = await db.query("logs").order("desc").take(100);
```

### 1.2. WARNING PATTERNS - Review cẩn thận

```typescript
// ⚠️ WARNING: .collect() không có filter
await db.query("tableName").collect()

// ⚠️ WARNING: Loop với database calls
for (const item of items) {
  await db.get(item.relatedId);
}

// ⚠️ WARNING: Filter sau khi fetch
const data = await fetch("/api/items");
const filtered = data.filter(x => x.type === "specific");

// ⚠️ WARNING: Array.find() cho lookups
items.map(i => ({
  ...i,
  related: relatedItems.find(r => r.id === i.relatedId) // O(n²)
}));
```

---

## Phase 2: Index Strategy

### 2.1. Rule: Mọi filter/sort cần index

```typescript
// Schema definition với indexes
defineTable({
  userId: v.id("users"),
  status: v.string(),
  type: v.string(),
  createdAt: v.number(),
  amount: v.number(),
})
.index("by_user", ["userId"])              // Foreign key
.index("by_status", ["status"])            // Filter
.index("by_type", ["type"])                // Filter
.index("by_created", ["createdAt"])        // Sort
.index("by_user_status", ["userId", "status"])  // Compound
.index("by_status_created", ["status", "createdAt"]) // Filter + Sort
```

### 2.2. Index Selectivity - Chọn index tối ưu

```
Selectivity = Unique Values / Total Records

High selectivity (tốt):
- id, email, unique fields → ~1 record
- createdAt, timestamp → phân bố đều

Low selectivity (kém):
- status (active/inactive) → 50% records mỗi value
- type (3-5 values) → 20-33% records mỗi value
- boolean fields → 50% mỗi value
```

```typescript
// Khi có nhiều filters, ưu tiên theo selectivity
function chooseIndex(filters) {
  if (filters.id) return "by_id";           // Highest
  if (filters.userId) return "by_user";      // High
  if (filters.createdAt) return "by_created"; // Medium
  if (filters.status) return "by_status";    // Low
  return null; // Full scan - NGUY HIỂM
}
```

### 2.3. Compound Index Order

```typescript
// Rule: Equality filters trước, Range/Sort sau
.index("by_status_created", ["status", "createdAt"])

// ✅ Dùng được
.withIndex("by_status_created", q => q.eq("status", "active"))
.withIndex("by_status_created", q => q.eq("status", "active").gte("createdAt", timestamp))

// ❌ Không tối ưu - chỉ dùng được phần đầu
.withIndex("by_status_created", q => q.gte("createdAt", timestamp)) // Không dùng status!
```

---

## Phase 3: Pagination Patterns

### 3.1. Cursor-based Pagination (Recommended)

```typescript
// Convex
const results = await ctx.db
  .query("items")
  .withIndex("by_created")
  .order("desc")
  .paginate(paginationOpts);

return {
  items: results.page,
  cursor: results.continueCursor,
  hasMore: !results.isDone,
};

// Prisma
const results = await prisma.post.findMany({
  take: 20,
  skip: 1,
  cursor: { id: lastId },
  orderBy: { createdAt: 'desc' },
});
```

### 3.2. Offset Pagination (Simple but slow for large offsets)

```typescript
// ⚠️ Offset pagination - O(offset) complexity
// OK cho small datasets, BAD cho large
const results = await db
  .query("items")
  .order("desc")
  .take(offset + limit)
  .slice(offset);

// Performance degrades: offset=1000 → đọc 1000 records
```

### 3.3. Always Set Limits

```typescript
// ✅ Constants for limits
const LIMITS = {
  DEFAULT_PAGE_SIZE: 20,
  MAX_PAGE_SIZE: 100,
  ADMIN_MAX: 500,
};

// ✅ Validate and cap
const limit = Math.min(requestedLimit || LIMITS.DEFAULT_PAGE_SIZE, LIMITS.MAX_PAGE_SIZE);
```

---

## Phase 4: Batch Loading & Caching

### 4.1. DataLoader Pattern

```typescript
// dataloader.ts
import DataLoader from 'dataloader';

const userLoader = new DataLoader(async (ids: string[]) => {
  const users = await db.query("users")
    .filter(q => q.or(...ids.map(id => q.eq(q.field("_id"), id))))
    .collect();
  
  const userMap = new Map(users.map(u => [u._id, u]));
  return ids.map(id => userMap.get(id));
});

// Usage - automatically batches and caches
const user1 = await userLoader.load(id1);
const user2 = await userLoader.load(id2);
// → Only 1 query for both!
```

### 4.2. Manual Batch Loading

```typescript
async function loadWithRelations(posts: Post[]) {
  // 1. Collect unique IDs
  const authorIds = [...new Set(posts.map(p => p.authorId))];
  const categoryIds = [...new Set(posts.map(p => p.categoryId))];
  
  // 2. Batch load in parallel
  const [authors, categories] = await Promise.all([
    Promise.all(authorIds.map(id => db.get(id))),
    Promise.all(categoryIds.map(id => db.get(id))),
  ]);
  
  // 3. Create lookup maps - O(1) access
  const authorMap = new Map(authors.filter(Boolean).map(a => [a._id, a]));
  const categoryMap = new Map(categories.filter(Boolean).map(c => [c._id, c]));
  
  // 4. Attach relations
  return posts.map(post => ({
    ...post,
    author: authorMap.get(post.authorId),
    category: categoryMap.get(post.categoryId),
  }));
}
```

### 4.3. Query-level Caching

```typescript
// React Query / TanStack Query
const { data } = useQuery({
  queryKey: ['users', filters],
  queryFn: () => fetchUsers(filters),
  staleTime: 5 * 60 * 1000,  // Cache 5 minutes
  cacheTime: 30 * 60 * 1000, // Keep in memory 30 minutes
});

// SWR
const { data } = useSWR(['users', filters], fetcher, {
  revalidateOnFocus: false,
  dedupingInterval: 60000,
});
```

---

## Phase 5: Platform-Specific Optimizations

### 5.1. Convex

```typescript
// ✅ Use indexes
.withIndex("by_field", q => q.eq("field", value))

// ✅ Pagination
.paginate(paginationOpts)

// ✅ Limit results
.take(100)

// ❌ Avoid
.collect() // without filter
.filter() // JS filter after fetch
```

### 5.2. Prisma

```typescript
// ✅ Select only needed fields
const users = await prisma.user.findMany({
  select: { id: true, name: true, email: true },
  where: { status: 'active' },
  take: 20,
});

// ✅ Include relations efficiently
const posts = await prisma.post.findMany({
  include: {
    author: { select: { id: true, name: true } },
  },
  take: 20,
});

// ❌ Avoid
findMany() // without where/take
include: { author: true } // full object when only need name
```

### 5.3. Firebase/Firestore

```typescript
// ✅ Use compound queries
const q = query(
  collection(db, "posts"),
  where("status", "==", "published"),
  orderBy("createdAt", "desc"),
  limit(20)
);

// ✅ Pagination with cursors
const next = query(q, startAfter(lastDoc));

// ❌ Avoid
getDocs(collection(db, "posts")) // fetches ALL documents
```

### 5.4. MongoDB

```typescript
// ✅ Projection - only needed fields
db.users.find(
  { status: "active" },
  { projection: { name: 1, email: 1 } }
).limit(20);

// ✅ Covered queries - index covers all fields
db.users.createIndex({ status: 1, name: 1, email: 1 });

// ✅ Aggregation for complex queries
db.orders.aggregate([
  { $match: { status: "completed" } },
  { $group: { _id: "$userId", total: { $sum: "$amount" } } },
  { $limit: 100 }
]);

// ❌ Avoid
db.users.find() // no filter, no limit
```

---

## Phase 6: Monitoring & Alerts

### 6.1. Set Up Alerts

```
Convex Dashboard:
- Functions → Sort by "Database Bandwidth"
- Set alert: bandwidth > X GB/day

Firebase:
- Usage & billing → Set budget alerts
- Firestore → Monitor read/write counts

AWS/GCP:
- CloudWatch/Cloud Monitoring alerts
- Budget alerts with thresholds
```

### 6.2. Query Profiling

```typescript
// Add timing to critical queries
async function profiledQuery(name: string, queryFn: () => Promise<any>) {
  const start = Date.now();
  const result = await queryFn();
  const duration = Date.now() - start;
  
  console.log(`[Query] ${name}: ${duration}ms, ${result.length} results`);
  
  if (duration > 1000) {
    console.warn(`[SLOW QUERY] ${name} took ${duration}ms`);
  }
  
  return result;
}
```

### 6.3. Bandwidth Estimation

```
Estimate formula:
Bandwidth = Records × Average_Record_Size × Requests_per_Day

Example:
- 10,000 records × 1KB × 1000 requests = 10 GB/day
- With proper filtering (100 records): 0.1 GB/day
- Savings: 99%!
```

---

## Quick Reference Checklist

### Before Code Review
```
□ Search for .collect(), .findAll(), .toArray() without filters
□ Search for database calls inside loops
□ Check for Array.find() used for lookups (should be Map)
□ Verify all queries have limits
□ Check indexes exist for all filtered/sorted fields
```

### Before Deploy
```
□ All queries use indexes
□ All list queries have pagination
□ All queries have reasonable limits
□ No N+1 patterns
□ Batch loading for relations
□ Monitoring/alerts configured
```

### Cost Estimation
```
□ Estimate daily bandwidth for each endpoint
□ Calculate cost at 10x, 100x current traffic
□ Set budget alerts before launch
□ Document expected costs in PR
```

---

## Red Flags to Watch

| Pattern | Risk Level | Action |
|---------|------------|--------|
| `.collect()` no filter | 🔴 CRITICAL | Add index + filter |
| Loop with DB calls | 🔴 CRITICAL | Batch load |
| `Array.find()` in map | 🟡 HIGH | Use Map |
| No pagination | 🟡 HIGH | Add pagination |
| Missing index | 🟡 HIGH | Create index |
| Offset pagination large | 🟠 MEDIUM | Switch to cursor |
| No query limit | 🟠 MEDIUM | Add limit |

---

## Additional Resources

For detailed examples and platform-specific guides:

- [examples.md](examples.md) - Real-world optimization examples
- [patterns.md](patterns.md) - Common patterns and solutions
- [monitoring.md](monitoring.md) - Setting up monitoring and alerts

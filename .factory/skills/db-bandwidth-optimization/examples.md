# Real-World Optimization Examples

## Case Study 1: Trade Post Listing (Convex)

### Before: 46 GB/day bandwidth

```typescript
// ❌ DISASTER CODE
export const listByCard = query({
  args: { cardId: v.id("cards") },
  handler: async (ctx, { cardId }) => {
    // Fetch ALL traders (10,000 records)
    const allTraders = await ctx.db.query("traders").collect();
    
    // Fetch ALL cards (5,000 records)  
    const allCards = await ctx.db.query("cards").collect();
    
    // Fetch ALL trade post cards (50,000 records)
    const allTradePostCards = await ctx.db.query("tradePostCards").collect();
    
    // Filter in JavaScript
    const relevantPostCards = allTradePostCards.filter(
      tpc => tpc.cardId === cardId
    );
    
    // More JS filtering...
    const posts = await ctx.db.query("tradePosts").collect();
    return posts.filter(p => 
      relevantPostCards.some(tpc => tpc.postId === p._id)
    );
  }
});
// Total: 65,000+ records fetched for ~50 results
```

### After: ~3 GB/day bandwidth

```typescript
// ✅ OPTIMIZED CODE
export const listByCard = query({
  args: { cardId: v.id("cards") },
  handler: async (ctx, { cardId }) => {
    // 1. Use index to get only relevant trade post cards
    const tradePostCards = await ctx.db
      .query("tradePostCards")
      .withIndex("by_card", q => q.eq("cardId", cardId))
      .collect();
    // ~50 records instead of 50,000
    
    // 2. Get unique post IDs
    const postIds = [...new Set(tradePostCards.map(tpc => tpc.postId))];
    
    // 3. Batch load only needed posts
    const posts = await Promise.all(
      postIds.map(id => ctx.db.get(id))
    );
    // ~50 records instead of scanning all
    
    // 4. Get unique trader IDs from posts
    const traderIds = [...new Set(
      posts.filter(Boolean).map(p => p.traderId)
    )];
    
    // 5. Batch load only needed traders
    const traders = await Promise.all(
      traderIds.map(id => ctx.db.get(id))
    );
    // ~30 records instead of 10,000
    
    // 6. Create lookup maps
    const traderMap = new Map(
      traders.filter(Boolean).map(t => [t._id, t])
    );
    
    // 7. Build response
    return posts.filter(Boolean).map(post => ({
      ...post,
      trader: traderMap.get(post.traderId),
    }));
  }
});
// Total: ~130 records fetched for ~50 results
// Reduction: 99.8%
```

---

## Case Study 2: Chat Messages with Senders

### Before: N+1 Problem

```typescript
// ❌ N+1 PROBLEM
export const listByChat = query({
  args: { chatId: v.id("chats") },
  handler: async (ctx, { chatId }) => {
    const messages = await ctx.db
      .query("messages")
      .withIndex("by_chat", q => q.eq("chatId", chatId))
      .order("desc")
      .take(50);
    
    // N+1: One query per message!
    const messagesWithSenders = await Promise.all(
      messages.map(async (msg) => {
        const sender = await ctx.db.get(msg.senderId);
        return { ...msg, sender };
      })
    );
    
    return messagesWithSenders;
  }
});
// 51 queries total (1 + 50)
```

### After: Batch Loading

```typescript
// ✅ BATCH LOADING
export const listByChat = query({
  args: { chatId: v.id("chats") },
  handler: async (ctx, { chatId }) => {
    const messages = await ctx.db
      .query("messages")
      .withIndex("by_chat", q => q.eq("chatId", chatId))
      .order("desc")
      .take(50);
    
    // Collect unique sender IDs
    const senderIds = [...new Set(messages.map(m => m.senderId))];
    
    // Single batch load (parallel)
    const senders = await Promise.all(
      senderIds.map(id => ctx.db.get(id))
    );
    
    // O(1) lookup map
    const senderMap = new Map(
      senders.filter(Boolean).map(s => [s._id, s])
    );
    
    return messages.map(msg => ({
      ...msg,
      sender: senderMap.get(msg.senderId),
    }));
  }
});
// 2 query batches total (messages + senders)
// Reduction: 96%
```

---

## Case Study 3: Paginated Card List with Filters

### Before: Fetch All + JS Filter

```typescript
// ❌ FETCH ALL
export const listPaginated = query({
  args: {
    type: v.optional(v.string()),
    rarity: v.optional(v.string()),
    page: v.number(),
    limit: v.number(),
  },
  handler: async (ctx, { type, rarity, page, limit }) => {
    // Fetch ALL cards
    let cards = await ctx.db.query("cards").collect();
    
    // Filter in JS
    if (type) cards = cards.filter(c => c.type === type);
    if (rarity) cards = cards.filter(c => c.rarity === rarity);
    
    // Manual pagination
    const start = page * limit;
    return {
      cards: cards.slice(start, start + limit),
      total: cards.length,
    };
  }
});
```

### After: Index-based Filtering

```typescript
// ✅ INDEX-BASED
export const listPaginated = query({
  args: {
    type: v.optional(v.string()),
    rarity: v.optional(v.string()),
    paginationOpts: paginationOptsValidator,
  },
  handler: async (ctx, { type, rarity, paginationOpts }) => {
    // Choose best index based on filter selectivity
    let query = ctx.db.query("cards");
    
    // Priority: rarity > type (rarity has higher selectivity)
    if (rarity) {
      query = query.withIndex("by_rarity", q => q.eq("rarity", rarity));
      // Apply secondary filter if needed
      if (type) {
        const results = await query.collect();
        const filtered = results.filter(c => c.type === type);
        // Paginate filtered results
        const start = 0; // Use cursor in real implementation
        return {
          cards: filtered.slice(start, start + paginationOpts.numItems),
          total: filtered.length,
        };
      }
    } else if (type) {
      query = query.withIndex("by_type", q => q.eq("type", type));
    }
    
    // Use built-in pagination
    const results = await query.paginate(paginationOpts);
    
    return {
      cards: results.page,
      cursor: results.continueCursor,
      isDone: results.isDone,
    };
  }
});
```

---

## Case Study 4: Admin Dashboard Counts

### Before: Count by Fetching All

```typescript
// ❌ EXPENSIVE COUNTS
export const getDashboardStats = query({
  handler: async (ctx) => {
    const allUsers = await ctx.db.query("users").collect();
    const allPosts = await ctx.db.query("posts").collect();
    const allOrders = await ctx.db.query("orders").collect();
    
    return {
      totalUsers: allUsers.length,
      activeUsers: allUsers.filter(u => u.status === "active").length,
      totalPosts: allPosts.length,
      publishedPosts: allPosts.filter(p => p.status === "published").length,
      totalOrders: allOrders.length,
      revenue: allOrders
        .filter(o => o.status === "completed")
        .reduce((sum, o) => sum + o.amount, 0),
    };
  }
});
// Fetches potentially millions of records
```

### After: Aggregation Table

```typescript
// ✅ AGGREGATION TABLE
// Schema
defineTable({
  key: v.string(), // "users_total", "users_active", etc.
  value: v.number(),
  updatedAt: v.number(),
}).index("by_key", ["key"]);

// Update on user create
export const createUser = mutation({
  handler: async (ctx, args) => {
    await ctx.db.insert("users", args);
    
    // Increment counters
    await incrementStat(ctx, "users_total");
    if (args.status === "active") {
      await incrementStat(ctx, "users_active");
    }
  }
});

// Fast dashboard query
export const getDashboardStats = query({
  handler: async (ctx) => {
    const stats = await ctx.db.query("stats").collect();
    const statMap = new Map(stats.map(s => [s.key, s.value]));
    
    return {
      totalUsers: statMap.get("users_total") ?? 0,
      activeUsers: statMap.get("users_active") ?? 0,
      totalPosts: statMap.get("posts_total") ?? 0,
      // etc.
    };
  }
});
// Fetches ~10 records instead of millions
```

---

## Case Study 5: Search with Multiple Criteria

### Before: Client-side Search

```typescript
// ❌ CLIENT-SIDE SEARCH
export const searchProducts = query({
  args: { query: v.string() },
  handler: async (ctx, { query }) => {
    const allProducts = await ctx.db.query("products").collect();
    
    const searchLower = query.toLowerCase();
    return allProducts.filter(p => 
      p.name.toLowerCase().includes(searchLower) ||
      p.description.toLowerCase().includes(searchLower) ||
      p.category.toLowerCase().includes(searchLower)
    );
  }
});
```

### After: Search Index + Pagination

```typescript
// ✅ SEARCH INDEX
// Use Convex search index
defineTable({
  name: v.string(),
  description: v.string(),
  category: v.string(),
})
.searchIndex("search_products", {
  searchField: "name",
  filterFields: ["category"],
});

export const searchProducts = query({
  args: { 
    query: v.string(),
    category: v.optional(v.string()),
    limit: v.optional(v.number()),
  },
  handler: async (ctx, { query, category, limit = 20 }) => {
    let searchQuery = ctx.db
      .query("products")
      .withSearchIndex("search_products", q => {
        let search = q.search("name", query);
        if (category) {
          search = search.eq("category", category);
        }
        return search;
      });
    
    return await searchQuery.take(Math.min(limit, 100));
  }
});
```

---

## Quick Optimization Patterns

### Pattern 1: Replace find() with Map

```typescript
// ❌ O(n²)
posts.map(p => ({
  ...p,
  author: authors.find(a => a._id === p.authorId)
}));

// ✅ O(n)
const authorMap = new Map(authors.map(a => [a._id, a]));
posts.map(p => ({
  ...p,
  author: authorMap.get(p.authorId)
}));
```

### Pattern 2: Parallel Loading

```typescript
// ❌ Sequential
const users = await db.query("users").collect();
const posts = await db.query("posts").collect();
const comments = await db.query("comments").collect();

// ✅ Parallel
const [users, posts, comments] = await Promise.all([
  db.query("users").collect(),
  db.query("posts").collect(),
  db.query("comments").collect(),
]);
```

### Pattern 3: Early Exit

```typescript
// ❌ Always fetch all
const items = await db.query("items").collect();
const found = items.find(i => i.id === targetId);

// ✅ Stop when found
const found = await db
  .query("items")
  .withIndex("by_id", q => q.eq("id", targetId))
  .first();
```

### Pattern 4: Projection (Select Fields)

```typescript
// ❌ Fetch all fields
const users = await prisma.user.findMany();
// Returns: id, name, email, password, address, bio, avatar, settings...

// ✅ Select only needed
const users = await prisma.user.findMany({
  select: { id: true, name: true, email: true }
});
// Returns: id, name, email only
```

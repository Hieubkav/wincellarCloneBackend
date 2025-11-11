# Indexing Strategies

Indexes are the most powerful optimization tool.

## Index Types

### B-Tree (Default)
Good for equality and range queries

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_orders_created ON orders(created_at);
```

### Hash
Only for equality (=) comparisons

```sql
CREATE INDEX idx_users_email_hash ON users USING HASH(email);
```

### GIN (Generalized Inverted Index)
Full-text search, array queries, JSONB

```sql
-- Full-text search
CREATE INDEX idx_posts_search ON posts
USING GIN(to_tsvector('english', title || ' ' || body));

-- JSONB index
CREATE INDEX idx_metadata ON events USING GIN(metadata);

-- Array index
CREATE INDEX idx_tags ON posts USING GIN(tags);
```

### GiST (Generalized Search Tree)
Geometric data, full-text search

```sql
-- Geometric data
CREATE INDEX idx_locations ON stores USING GiST(location);
```

### BRIN (Block Range INdex)
Very large tables with correlation

```sql
CREATE INDEX idx_logs_created ON logs USING BRIN(created_at);
```

## Composite Indexes

**Order matters!**

```sql
-- Good for: WHERE user_id = X AND status = Y
CREATE INDEX idx_orders_user_status ON orders(user_id, status);

-- Also good for: WHERE user_id = X (uses first column)
-- NOT good for: WHERE status = Y (doesn't use first column)

-- Best practice: Put most selective column first
CREATE INDEX idx_orders_status_user ON orders(status, user_id)
WHERE status IN ('pending', 'processing');
```

## Partial Indexes

Index subset of rows to save space

```sql
-- Only index active users
CREATE INDEX idx_active_users ON users(email)
WHERE status = 'active';

-- Only index recent orders
CREATE INDEX idx_recent_orders ON orders(created_at)
WHERE created_at > NOW() - INTERVAL '90 days';

-- Only index non-null values
CREATE INDEX idx_users_phone ON users(phone)
WHERE phone IS NOT NULL;
```

## Expression Indexes

Index computed values

```sql
-- Case-insensitive search
CREATE INDEX idx_users_email_lower ON users(LOWER(email));

-- Then query with:
SELECT * FROM users WHERE LOWER(email) = 'user@example.com';

-- Date truncation
CREATE INDEX idx_orders_date ON orders(DATE(created_at));

-- Complex expression
CREATE INDEX idx_users_full_name ON users((first_name || ' ' || last_name));
```

## Covering Indexes

Include additional columns to avoid table lookup

```sql
-- Include frequently accessed columns
CREATE INDEX idx_users_email_covering ON users(email)
INCLUDE (name, created_at);

-- Now this query uses index-only scan:
SELECT email, name, created_at
FROM users
WHERE email = 'user@example.com';
```

## Index Maintenance

```sql
-- Update statistics
ANALYZE users;
ANALYZE VERBOSE orders;

-- Reindex when fragmented
REINDEX INDEX idx_users_email;
REINDEX TABLE users;

-- Drop unused indexes
DROP INDEX IF EXISTS idx_unused;

-- Check index size
SELECT
    schemaname,
    tablename,
    indexname,
    pg_size_pretty(pg_relation_size(indexrelid)) as index_size
FROM pg_stat_user_indexes
ORDER BY pg_relation_size(indexrelid) DESC;
```

## When to Create Indexes

✅ **DO create indexes on:**
- Primary keys (automatic)
- Foreign keys (manual)
- Columns in WHERE clauses
- Columns in JOIN conditions
- Columns in ORDER BY
- Columns in GROUP BY
- Frequently searched columns

❌ **DON'T create indexes on:**
- Small tables (<1000 rows)
- Columns with low selectivity (e.g., boolean, small enums)
- Columns frequently updated
- Wide columns (use hash or expression index instead)
- When table has many writes vs reads

## Index Strategy Checklist

1. **Identify slow queries** (use EXPLAIN ANALYZE)
2. **Check if index exists** on WHERE/JOIN columns
3. **Verify index is used** (not ignored by planner)
4. **Consider composite index** for multi-column filters
5. **Use partial index** if filtering subset
6. **Add covering columns** to avoid table lookups
7. **Monitor index usage** and drop unused ones
8. **Keep statistics updated** with ANALYZE

## Common Index Pitfalls

### Over-Indexing
Each index slows down INSERT/UPDATE/DELETE

```sql
-- Bad: Too many overlapping indexes
CREATE INDEX idx1 ON orders(user_id);
CREATE INDEX idx2 ON orders(user_id, status);
CREATE INDEX idx3 ON orders(user_id, status, created_at);
-- Drop idx1 and idx2, keep idx3
```

### Wrong Column Order
```sql
-- Bad: Rarely filter by status alone
CREATE INDEX idx_orders ON orders(status, user_id);

-- Good: Always filter by user_id
CREATE INDEX idx_orders ON orders(user_id, status);
```

### Function Prevents Index Usage
```sql
-- Bad: LOWER() prevents index usage
SELECT * FROM users WHERE LOWER(email) = 'user@example.com';

-- Good: Create functional index
CREATE INDEX idx_users_email_lower ON users(LOWER(email));
```

### Leading Wildcard
```sql
-- Bad: Can't use index
SELECT * FROM users WHERE email LIKE '%@example.com';

-- Good: Can use index
SELECT * FROM users WHERE email LIKE 'user@%';

-- Better: Use full-text search for contains
CREATE INDEX idx_users_email_gin ON users USING GIN(email gin_trgm_ops);
-- Requires: CREATE EXTENSION pg_trgm;
SELECT * FROM users WHERE email LIKE '%@example.com';
```

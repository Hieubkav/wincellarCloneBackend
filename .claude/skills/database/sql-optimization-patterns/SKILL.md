---
name: sql-optimization-patterns
description: Master SQL query optimization, indexing strategies, and EXPLAIN analysis to dramatically improve database performance and eliminate slow queries. Use when debugging slow queries, designing database schemas, or optimizing application performance.
---

# SQL Optimization Patterns

Transform slow database queries into lightning-fast operations through systematic optimization, proper indexing, and query plan analysis.

## When to Use This Skill

- Debugging slow-running queries
- Designing performant database schemas
- Optimizing application response times
- Reducing database load and costs
- Improving scalability for growing datasets
- Analyzing EXPLAIN query plans
- Implementing efficient indexes
- Resolving N+1 query problems

## Quick Start: Optimization Workflow

### 1. Identify Slow Queries

```sql
-- PostgreSQL: Enable pg_stat_statements
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;

-- Find slowest queries
SELECT query, calls, mean_time, total_time
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;
```

### 2. Analyze with EXPLAIN

```sql
-- Check query execution plan
EXPLAIN ANALYZE
SELECT u.*, o.order_total
FROM users u
JOIN orders o ON u.id = o.user_id
WHERE u.created_at > NOW() - INTERVAL '30 days';
```

**Look for:**
- ❌ Seq Scan (full table scan)
- ✅ Index Scan or Index Only Scan
- Check: Cost, Rows, Actual Time

### 3. Add Indexes Strategically

```sql
-- Simple index
CREATE INDEX idx_users_created ON users(created_at);

-- Composite index (order matters!)
CREATE INDEX idx_orders_user_date ON orders(user_id, created_at);

-- Partial index (index subset)
CREATE INDEX idx_active_users ON users(email)
WHERE status = 'active';

-- Covering index (avoid table lookup)
CREATE INDEX idx_users_email_covering ON users(email)
INCLUDE (name, created_at);
```

### 4. Optimize Query Structure

```sql
-- ❌ Bad: SELECT *
SELECT * FROM users WHERE id = 123;

-- ✅ Good: Select only needed columns
SELECT id, email, name FROM users WHERE id = 123;

-- ❌ Bad: Function on column
SELECT * FROM users WHERE LOWER(email) = 'user@example.com';

-- ✅ Good: Use functional index
CREATE INDEX idx_users_email_lower ON users(LOWER(email));
```

## Core Optimization Concepts

### Query Execution Plans

Understanding EXPLAIN is fundamental to optimization.

**Key metrics:**
- **Seq Scan**: Full table scan (slow for large tables)
- **Index Scan**: Using index (good)
- **Index Only Scan**: Index contains all needed data (best)
- **Cost**: Query cost estimate (lower is better)
- **Actual Time**: Real execution time

**→ Details:** `read references/explain-analysis.md`

### Indexing Strategies

Indexes are your most powerful optimization tool.

**Index types:**
- **B-Tree**: Default, for equality and ranges
- **Hash**: Equality only
- **GIN**: Full-text search, JSONB, arrays
- **GiST**: Geometric data
- **BRIN**: Very large tables with correlation

**→ Details:** `read references/indexing-strategies.md`

### Common Optimization Patterns

**Pattern 1: Eliminate N+1 Queries**
- Use JOINs or batch loading instead of loops

**Pattern 2: Cursor-Based Pagination**
- Replace OFFSET with WHERE cursor for large datasets

**Pattern 3: Efficient Aggregation**
- Filter before aggregating, use covering indexes

**Pattern 4: Subquery Optimization**
- Transform correlated subqueries to JOINs

**Pattern 5: Batch Operations**
- Batch INSERT/UPDATE instead of individual statements

**→ Details:** `read references/optimization-patterns.md`

### Advanced Techniques

- **Materialized Views**: Pre-compute expensive queries
- **Partitioning**: Split large tables by range/list/hash
- **Query Hints**: Force specific execution plans
- **Parallel Queries**: Use multiple CPU cores
- **Connection Pooling**: Reuse database connections

**→ Details:** `read references/advanced-techniques.md`

### Monitoring & Maintenance

Track query performance and identify bottlenecks.

**PostgreSQL:**
- pg_stat_statements for query statistics
- Find missing/unused indexes
- Monitor cache hit ratio (should be > 99%)
- Check table bloat

**MySQL:**
- Slow query log
- Performance Schema
- Identify unused indexes

**→ Details:** `read references/monitoring-queries.md`

## Best Practices

### Index Management
✅ Index columns in WHERE, JOIN, ORDER BY  
✅ Use composite indexes for multi-column filters  
✅ Create partial indexes for subsets  
❌ Don't over-index (slows writes)  
❌ Drop unused indexes

### Query Optimization
✅ Select only needed columns  
✅ Filter before joining  
✅ Use batch operations  
✅ Implement cursor-based pagination  
❌ Avoid SELECT *  
❌ Don't use functions on indexed columns  
❌ Avoid N+1 queries

### Maintenance
✅ Run ANALYZE regularly  
✅ VACUUM to reclaim space (PostgreSQL)  
✅ Monitor slow query log  
✅ Update statistics after bulk operations  
✅ Reindex when fragmented

## Common Pitfalls

1. **Over-Indexing**: Each index slows INSERT/UPDATE/DELETE
2. **Function on WHERE Column**: Prevents index usage
3. **Implicit Type Conversion**: Forces full scan
4. **LIKE '%pattern'**: Leading wildcard can't use index
5. **OR Conditions**: May prevent index usage
6. **Missing Statistics**: Run ANALYZE to update

## Quick Reference

```sql
-- Check index usage
EXPLAIN ANALYZE your_query_here;

-- Find slow queries (PostgreSQL)
SELECT query, mean_time FROM pg_stat_statements
ORDER BY mean_time DESC LIMIT 10;

-- Find missing indexes (high seq_scan)
SELECT schemaname, tablename, seq_scan, idx_scan
FROM pg_stat_user_tables
WHERE seq_scan > idx_scan AND seq_scan > 1000;

-- Find unused indexes
SELECT schemaname, tablename, indexname, idx_scan
FROM pg_stat_user_indexes
WHERE idx_scan = 0;

-- Update statistics
ANALYZE tablename;

-- Vacuum (PostgreSQL)
VACUUM ANALYZE tablename;
```

## Reference Files

**Core Concepts:**
- `references/explain-analysis.md` - EXPLAIN plan interpretation
- `references/indexing-strategies.md` - Index types and when to use them
- `references/optimization-patterns.md` - 10 proven optimization patterns

**Advanced Topics:**
- `references/advanced-techniques.md` - Materialized views, partitioning, hints
- `references/monitoring-queries.md` - Performance monitoring and alerts

## Resources

- PostgreSQL EXPLAIN: https://www.postgresql.org/docs/current/using-explain.html
- MySQL EXPLAIN: https://dev.mysql.com/doc/refman/8.0/en/explain.html
- Use The Index, Luke: https://use-the-index-luke.com/
- PostgreSQL Performance: https://www.postgresql.org/docs/current/performance-tips.html

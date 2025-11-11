# Query Execution Plans (EXPLAIN)

Understanding EXPLAIN output is fundamental to optimization.

## PostgreSQL EXPLAIN

```sql
-- Basic explain
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';

-- With actual execution stats
EXPLAIN ANALYZE
SELECT * FROM users WHERE email = 'user@example.com';

-- Verbose output with more details
EXPLAIN (ANALYZE, BUFFERS, VERBOSE)
SELECT u.*, o.order_total
FROM users u
JOIN orders o ON u.id = o.user_id
WHERE u.created_at > NOW() - INTERVAL '30 days';
```

## Key Metrics to Watch

- **Seq Scan**: Full table scan (usually slow for large tables)
- **Index Scan**: Using index (good)
- **Index Only Scan**: Using index without touching table (best)
- **Nested Loop**: Join method (okay for small datasets)
- **Hash Join**: Join method (good for larger datasets)
- **Merge Join**: Join method (good for sorted data)
- **Cost**: Estimated query cost (lower is better)
- **Rows**: Estimated rows returned
- **Actual Time**: Real execution time

## MySQL EXPLAIN

```sql
-- Basic explain
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';

-- Extended information
EXPLAIN EXTENDED
SELECT u.*, o.order_total
FROM users u
JOIN orders o ON u.id = o.user_id
WHERE u.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Show warnings after EXPLAIN EXTENDED
SHOW WARNINGS;

-- JSON format (MySQL 5.7+)
EXPLAIN FORMAT=JSON
SELECT * FROM users WHERE email = 'user@example.com';
```

## Common EXPLAIN Patterns

### Good Patterns

✅ **Index Only Scan**
```
Index Only Scan on users_email_idx
  Heap Fetches: 0
```

✅ **Index Scan with Small Result Set**
```
Index Scan on idx_users_email
  Rows Removed by Filter: 0
```

### Bad Patterns

❌ **Sequential Scan on Large Table**
```
Seq Scan on users (cost=0..10000 rows=500000)
```

❌ **Nested Loop with Large Outer Table**
```
Nested Loop (cost=0..1000000 rows=1000000)
  -> Seq Scan on users
  -> Index Scan on orders
```

## Optimization Strategy Based on EXPLAIN

1. **See Seq Scan** → Add index on WHERE/JOIN columns
2. **High Cost** → Check if indexes are being used
3. **Rows estimate way off** → Run ANALYZE to update statistics
4. **Nested Loop on large tables** → Consider forcing Hash/Merge join
5. **Index Scan but still slow** → Check index selectivity, consider covering index

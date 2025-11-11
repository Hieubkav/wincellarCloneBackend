# Advanced Optimization Techniques

## Materialized Views

Pre-compute expensive queries for instant access.

### Creating Materialized Views

```sql
-- Create materialized view
CREATE MATERIALIZED VIEW user_order_summary AS
SELECT
    u.id,
    u.name,
    COUNT(o.id) as total_orders,
    SUM(o.total) as total_spent,
    MAX(o.created_at) as last_order_date
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id, u.name;

-- Add index to materialized view
CREATE INDEX idx_user_summary_spent ON user_order_summary(total_spent DESC);
CREATE INDEX idx_user_summary_orders ON user_order_summary(total_orders DESC);
```

### Refreshing Materialized Views

```sql
-- Refresh materialized view (blocks reads)
REFRESH MATERIALIZED VIEW user_order_summary;

-- Concurrent refresh (PostgreSQL, doesn't block reads)
-- Requires unique index
CREATE UNIQUE INDEX idx_user_summary_id ON user_order_summary(id);
REFRESH MATERIALIZED VIEW CONCURRENTLY user_order_summary;

-- Scheduled refresh with cron
-- 0 2 * * * psql -d mydb -c "REFRESH MATERIALIZED VIEW CONCURRENTLY user_order_summary;"
```

### Querying Materialized Views

```sql
-- Query materialized view (very fast)
SELECT * FROM user_order_summary
WHERE total_spent > 1000
ORDER BY total_spent DESC
LIMIT 10;

-- Compare with original query
-- Original (slow):
SELECT u.id, u.name, COUNT(o.id), SUM(o.total)
FROM users u LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id, u.name
HAVING SUM(o.total) > 1000
ORDER BY SUM(o.total) DESC LIMIT 10;
```

## Table Partitioning

Split large tables for better performance and maintenance.

### Range Partitioning (PostgreSQL)

```sql
-- Create partitioned table
CREATE TABLE orders (
    id SERIAL,
    user_id INT,
    total DECIMAL,
    created_at TIMESTAMP NOT NULL
) PARTITION BY RANGE (created_at);

-- Create partitions
CREATE TABLE orders_2024_q1 PARTITION OF orders
    FOR VALUES FROM ('2024-01-01') TO ('2024-04-01');

CREATE TABLE orders_2024_q2 PARTITION OF orders
    FOR VALUES FROM ('2024-04-01') TO ('2024-07-01');

CREATE TABLE orders_2024_q3 PARTITION OF orders
    FOR VALUES FROM ('2024-07-01') TO ('2024-10-01');

CREATE TABLE orders_2024_q4 PARTITION OF orders
    FOR VALUES FROM ('2024-10-01') TO ('2025-01-01');

-- Create indexes on each partition
CREATE INDEX idx_orders_2024_q1_user ON orders_2024_q1(user_id);
CREATE INDEX idx_orders_2024_q2_user ON orders_2024_q2(user_id);

-- Queries automatically use appropriate partition
SELECT * FROM orders
WHERE created_at BETWEEN '2024-02-01' AND '2024-02-28';
-- Only scans orders_2024_q1 partition
```

### List Partitioning

```sql
-- Partition by discrete values
CREATE TABLE users (
    id SERIAL,
    email VARCHAR,
    country_code CHAR(2) NOT NULL
) PARTITION BY LIST (country_code);

CREATE TABLE users_us PARTITION OF users
    FOR VALUES IN ('US');

CREATE TABLE users_uk PARTITION OF users
    FOR VALUES IN ('UK');

CREATE TABLE users_other PARTITION OF users
    DEFAULT;
```

### Hash Partitioning

```sql
-- Distribute data evenly
CREATE TABLE logs (
    id SERIAL,
    message TEXT,
    created_at TIMESTAMP
) PARTITION BY HASH (id);

CREATE TABLE logs_p0 PARTITION OF logs
    FOR VALUES WITH (MODULUS 4, REMAINDER 0);

CREATE TABLE logs_p1 PARTITION OF logs
    FOR VALUES WITH (MODULUS 4, REMAINDER 1);

CREATE TABLE logs_p2 PARTITION OF logs
    FOR VALUES WITH (MODULUS 4, REMAINDER 2);

CREATE TABLE logs_p3 PARTITION OF logs
    FOR VALUES WITH (MODULUS 4, REMAINDER 3);
```

## Query Hints and Optimization

### Force Index Usage (MySQL)

```sql
-- Force specific index
SELECT * FROM users
USE INDEX (idx_users_email)
WHERE email = 'user@example.com';

-- Ignore specific index
SELECT * FROM users
IGNORE INDEX (idx_users_name)
WHERE name = 'John';

-- Force index for join
SELECT u.*, o.*
FROM users u
FORCE INDEX (idx_users_email)
JOIN orders o ON u.id = o.user_id
WHERE u.email = 'user@example.com';
```

### Parallel Queries (PostgreSQL)

```sql
-- Enable parallel query execution
SET max_parallel_workers_per_gather = 4;

-- Force parallel scan
SET parallel_setup_cost = 0;
SET parallel_tuple_cost = 0;

-- Query will use parallel workers
SELECT COUNT(*) FROM large_table WHERE condition;

-- Check if parallel was used
EXPLAIN ANALYZE
SELECT COUNT(*) FROM large_table WHERE condition;
-- Look for "Parallel Seq Scan" or "Parallel Index Scan"
```

### Join Method Hints (PostgreSQL)

```sql
-- Disable nested loop (force hash or merge join)
SET enable_nestloop = OFF;

-- Force hash join
SET enable_mergejoin = OFF;
SET enable_nestloop = OFF;

-- Force merge join
SET enable_hashjoin = OFF;
SET enable_nestloop = OFF;

-- Reset to defaults
RESET enable_nestloop;
RESET enable_hashjoin;
RESET enable_mergejoin;
```

## Connection Pooling

### PgBouncer Configuration

```ini
[databases]
mydb = host=localhost port=5432 dbname=mydb

[pgbouncer]
listen_addr = *
listen_port = 6432
auth_type = md5
auth_file = /etc/pgbouncer/userlist.txt

# Pool modes
pool_mode = transaction  # Best for web apps
# session - Client stays connected
# transaction - Connection returned after each transaction
# statement - Connection returned after each statement

# Pool sizing
default_pool_size = 20
max_client_conn = 100
```

## Caching Strategies

### Application-Level Caching

```python
# Redis cache example
import redis
import json

cache = redis.Redis(host='localhost', port=6379, db=0)

def get_user_orders(user_id):
    # Try cache first
    cache_key = f"user:{user_id}:orders"
    cached = cache.get(cache_key)
    
    if cached:
        return json.loads(cached)
    
    # Cache miss - query database
    orders = db.query(
        "SELECT * FROM orders WHERE user_id = ?",
        user_id
    )
    
    # Cache for 5 minutes
    cache.setex(cache_key, 300, json.dumps(orders))
    
    return orders
```

### Query Result Caching (MySQL)

```sql
-- Enable query cache (MySQL 5.7 and earlier)
SET GLOBAL query_cache_size = 67108864;  -- 64MB
SET GLOBAL query_cache_type = ON;

-- Check query cache status
SHOW STATUS LIKE 'Qcache%';

-- Use SQL_CACHE hint
SELECT SQL_CACHE * FROM users WHERE status = 'active';

-- Bypass cache for specific query
SELECT SQL_NO_CACHE * FROM users WHERE status = 'active';
```

## Full-Text Search Optimization

### PostgreSQL Full-Text Search

```sql
-- Create GIN index for full-text search
CREATE INDEX idx_posts_search ON posts
USING GIN(to_tsvector('english', title || ' ' || body));

-- Query with full-text search
SELECT id, title, ts_rank(
    to_tsvector('english', title || ' ' || body),
    to_tsquery('english', 'database & optimization')
) as rank
FROM posts
WHERE to_tsvector('english', title || ' ' || body) @@
      to_tsquery('english', 'database & optimization')
ORDER BY rank DESC
LIMIT 10;

-- Use materialized column for better performance
ALTER TABLE posts ADD COLUMN search_vector tsvector;

UPDATE posts
SET search_vector = to_tsvector('english', title || ' ' || body);

CREATE INDEX idx_posts_search_vector ON posts USING GIN(search_vector);

-- Trigger to keep it updated
CREATE TRIGGER posts_search_vector_update
BEFORE INSERT OR UPDATE ON posts
FOR EACH ROW EXECUTE FUNCTION
tsvector_update_trigger(search_vector, 'pg_catalog.english', title, body);
```

## Vacuum and Maintenance

### Regular Maintenance (PostgreSQL)

```sql
-- Vacuum to reclaim space and update statistics
VACUUM ANALYZE users;

-- Verbose output
VACUUM VERBOSE ANALYZE users;

-- Full vacuum (locks table, reclaims all space)
VACUUM FULL users;

-- Auto-vacuum configuration
ALTER TABLE users SET (
    autovacuum_vacuum_scale_factor = 0.1,
    autovacuum_analyze_scale_factor = 0.05
);

-- Check bloat
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size,
    n_dead_tup
FROM pg_stat_user_tables
WHERE n_dead_tup > 1000
ORDER BY n_dead_tup DESC;
```

### Optimize Tables (MySQL)

```sql
-- Optimize table (rebuild, reclaim space)
OPTIMIZE TABLE users;

-- Analyze table (update statistics)
ANALYZE TABLE users;

-- Check table for errors
CHECK TABLE users;

-- Repair table
REPAIR TABLE users;
```

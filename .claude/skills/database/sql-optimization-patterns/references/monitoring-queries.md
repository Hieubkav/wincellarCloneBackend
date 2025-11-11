# Monitoring Query Performance

## PostgreSQL Monitoring

### pg_stat_statements Extension

```sql
-- Enable extension (requires restart)
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;

-- Find slowest queries by mean time
SELECT
    query,
    calls,
    total_time,
    mean_time,
    max_time,
    stddev_time
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;

-- Find queries consuming most total time
SELECT
    query,
    calls,
    total_time,
    mean_time,
    rows
FROM pg_stat_statements
ORDER BY total_time DESC
LIMIT 10;

-- Find most frequently called queries
SELECT
    query,
    calls,
    mean_time,
    total_time
FROM pg_stat_statements
ORDER BY calls DESC
LIMIT 10;

-- Reset statistics
SELECT pg_stat_statements_reset();
```

### Find Missing Indexes

```sql
-- Tables with high sequential scan ratio
SELECT
    schemaname,
    tablename,
    seq_scan,
    seq_tup_read,
    idx_scan,
    seq_tup_read / NULLIF(seq_scan, 0) AS avg_seq_tup_read,
    CASE
        WHEN seq_scan > 0 AND idx_scan = 0 THEN 'No index scans'
        WHEN seq_scan > idx_scan THEN 'More seq scans than index'
        ELSE 'OK'
    END AS status
FROM pg_stat_user_tables
WHERE seq_scan > 0
ORDER BY seq_tup_read DESC
LIMIT 20;

-- Queries that might benefit from indexes
SELECT
    schemaname,
    tablename,
    attname,
    n_distinct,
    correlation
FROM pg_stats
WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
    AND n_distinct > 100
    AND correlation < 0.1
ORDER BY schemaname, tablename, attname;
```

### Find Unused Indexes

```sql
-- Indexes with zero or low usage
SELECT
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch,
    pg_size_pretty(pg_relation_size(indexrelid)) as size
FROM pg_stat_user_indexes
WHERE idx_scan < 50  -- Adjust threshold
    AND schemaname NOT IN ('pg_catalog')
ORDER BY pg_relation_size(indexrelid) DESC;

-- Total size of unused indexes
SELECT pg_size_pretty(SUM(pg_relation_size(indexrelid))) as total_unused_index_size
FROM pg_stat_user_indexes
WHERE idx_scan = 0
    AND schemaname NOT IN ('pg_catalog');
```

### Table Bloat Analysis

```sql
-- Check table bloat
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as total_size,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as table_size,
    n_live_tup,
    n_dead_tup,
    ROUND(100 * n_dead_tup / NULLIF(n_live_tup + n_dead_tup, 0), 2) as dead_ratio
FROM pg_stat_user_tables
WHERE n_dead_tup > 1000
ORDER BY n_dead_tup DESC
LIMIT 20;
```

### Cache Hit Ratio

```sql
-- Database-wide cache hit ratio (should be > 99%)
SELECT
    sum(heap_blks_read) as heap_read,
    sum(heap_blks_hit) as heap_hit,
    sum(heap_blks_hit) / (sum(heap_blks_hit) + sum(heap_blks_read)) as ratio
FROM pg_statio_user_tables;

-- Per-table cache hit ratio
SELECT
    schemaname,
    tablename,
    heap_blks_read,
    heap_blks_hit,
    CASE
        WHEN heap_blks_hit + heap_blks_read = 0 THEN NULL
        ELSE ROUND(100.0 * heap_blks_hit / (heap_blks_hit + heap_blks_read), 2)
    END as cache_hit_ratio
FROM pg_statio_user_tables
WHERE heap_blks_read > 0
ORDER BY heap_blks_read DESC
LIMIT 20;
```

### Active Queries

```sql
-- Current running queries
SELECT
    pid,
    usename,
    application_name,
    client_addr,
    state,
    query_start,
    NOW() - query_start as duration,
    query
FROM pg_stat_activity
WHERE state != 'idle'
    AND query NOT LIKE '%pg_stat_activity%'
ORDER BY query_start;

-- Long-running queries (> 5 minutes)
SELECT
    pid,
    NOW() - query_start as duration,
    query,
    state
FROM pg_stat_activity
WHERE state != 'idle'
    AND NOW() - query_start > INTERVAL '5 minutes'
ORDER BY query_start;

-- Kill a query
SELECT pg_cancel_backend(pid);  -- Graceful
SELECT pg_terminate_backend(pid);  -- Force
```

## MySQL Monitoring

### Slow Query Log

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;  -- Queries > 2 seconds
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';

-- Log queries not using indexes
SET GLOBAL log_queries_not_using_indexes = 'ON';

-- Check slow query log status
SHOW VARIABLES LIKE 'slow_query%';
SHOW VARIABLES LIKE 'long_query_time';
```

### Performance Schema

```sql
-- Enable performance schema
SET GLOBAL performance_schema = ON;

-- Top 10 slowest statements
SELECT
    DIGEST_TEXT as query,
    COUNT_STAR as exec_count,
    AVG_TIMER_WAIT / 1000000000 as avg_time_ms,
    SUM_TIMER_WAIT / 1000000000 as total_time_ms
FROM performance_schema.events_statements_summary_by_digest
ORDER BY AVG_TIMER_WAIT DESC
LIMIT 10;

-- Table access patterns
SELECT
    OBJECT_SCHEMA,
    OBJECT_NAME,
    COUNT_STAR,
    COUNT_READ,
    COUNT_WRITE,
    SUM_TIMER_WAIT / 1000000000 as total_time_ms
FROM performance_schema.table_io_waits_summary_by_table
WHERE OBJECT_SCHEMA NOT IN ('mysql', 'performance_schema')
ORDER BY SUM_TIMER_WAIT DESC
LIMIT 20;
```

### Find Unused Indexes (MySQL)

```sql
-- Indexes never used
SELECT
    t.TABLE_SCHEMA,
    t.TABLE_NAME,
    t.INDEX_NAME,
    t.INDEX_TYPE
FROM information_schema.STATISTICS t
LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage p
    ON t.TABLE_SCHEMA = p.OBJECT_SCHEMA
    AND t.TABLE_NAME = p.OBJECT_NAME
    AND t.INDEX_NAME = p.INDEX_NAME
WHERE p.INDEX_NAME IS NULL
    AND t.TABLE_SCHEMA NOT IN ('mysql', 'performance_schema', 'sys')
ORDER BY t.TABLE_SCHEMA, t.TABLE_NAME;
```

### Current Processes

```sql
-- Show running queries
SHOW FULL PROCESSLIST;

-- Kill a query
KILL QUERY process_id;
KILL CONNECTION process_id;
```

## Query Profiling

### PostgreSQL Auto_explain

```sql
-- Load auto_explain module (in postgresql.conf or session)
LOAD 'auto_explain';

-- Configure auto_explain
SET auto_explain.log_min_duration = 1000;  -- Log queries > 1s
SET auto_explain.log_analyze = true;
SET auto_explain.log_buffers = true;
SET auto_explain.log_timing = true;
SET auto_explain.log_verbose = true;

-- Queries will automatically log EXPLAIN output
```

### MySQL Profiling

```sql
-- Enable profiling
SET profiling = 1;

-- Run your query
SELECT * FROM users WHERE email = 'user@example.com';

-- Show profiles
SHOW PROFILES;

-- Show detailed profile for query
SHOW PROFILE FOR QUERY 1;

-- Show specific metrics
SHOW PROFILE CPU FOR QUERY 1;
SHOW PROFILE BLOCK IO FOR QUERY 1;
SHOW PROFILE MEMORY FOR QUERY 1;
```

## Alerting Thresholds

### Recommended Alert Conditions

**PostgreSQL:**
- Cache hit ratio < 99%
- Dead tuple ratio > 20%
- Long-running queries > 10 minutes
- Connection pool utilization > 80%
- Replication lag > 10 seconds
- Bloat > 30% of table size

**MySQL:**
- Slow query count increasing
- Table locks waiting
- InnoDB buffer pool hit rate < 99%
- Connection usage > 80%
- Replication lag > 10 seconds
- Disk space < 20%

## Monitoring Tools

### PostgreSQL
- **pgBadger**: Log analyzer
- **pg_stat_statements**: Query statistics
- **pgAdmin**: GUI with monitoring
- **Datadog/NewRelic**: APM with database monitoring
- **Prometheus + postgres_exporter**: Metrics collection

### MySQL
- **MySQLTuner**: Configuration analyzer
- **Percona Toolkit**: Query analysis tools
- **MySQL Workbench**: GUI with performance dashboard
- **Datadog/NewRelic**: APM with database monitoring
- **Prometheus + mysqld_exporter**: Metrics collection

## Best Practices

1. **Enable query logging** for slow queries
2. **Monitor cache hit ratios** regularly
3. **Track query execution times** with pg_stat_statements or Performance Schema
4. **Set up alerts** for long-running queries
5. **Review slow query log** weekly
6. **Analyze unused indexes** monthly
7. **Check table bloat** monthly
8. **Review and optimize** top 10 slowest queries quarterly
9. **Update statistics** after bulk operations
10. **Vacuum regularly** (PostgreSQL) or optimize tables (MySQL)

# Monitoring & Cost Prevention

## Thiết lập Alerts TRƯỚC KHI Deploy

### Convex

1. **Dashboard Monitoring**
   ```
   https://dashboard.convex.dev → Your Project → Functions
   - Sort by "Database Bandwidth" (descending)
   - Identify top bandwidth consumers
   - Set internal alerts for functions > X MB/day
   ```

2. **Function-level Tracking**
   ```typescript
   // Add to expensive queries
   export const expensiveQuery = query({
     handler: async (ctx, args) => {
       const start = Date.now();
       
       // ... query logic ...
       
       const duration = Date.now() - start;
       if (duration > 1000) {
         console.warn(`[SLOW] expensiveQuery took ${duration}ms`);
       }
       
       return result;
     }
   });
   ```

### Firebase/Firestore

1. **Budget Alerts**
   ```
   Google Cloud Console → Billing → Budgets & Alerts
   - Create budget for project
   - Set alerts at 50%, 90%, 100%
   - Email notifications to team
   ```

2. **Usage Monitoring**
   ```
   Firebase Console → Firestore → Usage
   - Monitor reads/writes/deletes
   - Set up Cloud Monitoring alerts
   ```

3. **Query Profiling**
   ```typescript
   // Enable performance monitoring
   import { getPerformance } from "firebase/performance";
   const perf = getPerformance();
   
   // Custom traces
   const trace = perf.trace("expensive_query");
   trace.start();
   // ... query ...
   trace.stop();
   ```

### AWS (DynamoDB, RDS)

1. **CloudWatch Alarms**
   ```
   CloudWatch → Alarms → Create Alarm
   - DynamoDB: ConsumedReadCapacityUnits, ConsumedWriteCapacityUnits
   - RDS: DatabaseConnections, ReadIOPS, WriteIOPS
   - Set thresholds based on expected usage
   ```

2. **Cost Explorer**
   ```
   AWS Cost Explorer → Create Report
   - Filter by service (DynamoDB, RDS)
   - Set up daily/weekly cost anomaly detection
   ```

### Supabase

1. **Dashboard Monitoring**
   ```
   Supabase Dashboard → Project → Database → Query Performance
   - Identify slow queries
   - Check index usage
   ```

2. **Billing Alerts**
   ```
   Organization Settings → Billing
   - Set spend caps
   - Configure usage alerts
   ```

---

## Pre-Deploy Checklist

### Code Review Requirements

```markdown
## Database Query Checklist

### Queries
- [ ] All queries use appropriate indexes
- [ ] All list queries have pagination
- [ ] All queries have reasonable limits (< 1000)
- [ ] No .collect() without filters
- [ ] No database calls inside loops

### Performance
- [ ] Estimated bandwidth per request: ___ KB
- [ ] Expected requests/day: ___
- [ ] Estimated daily bandwidth: ___ GB
- [ ] Estimated monthly cost: $___

### Testing
- [ ] Tested with production-like data volume
- [ ] Load tested with expected traffic
- [ ] Verified query execution plans
```

### Bandwidth Estimation Formula

```
Daily Bandwidth = 
  Average_Response_Size × 
  Requests_per_Day × 
  (1 + Cache_Miss_Rate)

Example:
- Response: 50 KB average
- Requests: 10,000/day
- Cache miss: 30%

Daily = 50 KB × 10,000 × 1.3 = 650 MB/day
Monthly = 650 MB × 30 = 19.5 GB/month
```

### Cost Estimation by Provider

| Provider | Free Tier | Cost After |
|----------|-----------|------------|
| Convex | 1 GB/month | $0.20/GB |
| Firestore | 50K reads/day | $0.06/100K reads |
| DynamoDB | 25 GB storage | $0.25/million reads |
| Supabase | 500 MB DB | $0.09/GB transfer |
| PlanetScale | 1B row reads | $1/billion reads |

---

## Emergency Response Plan

### When Bill Spike Detected

1. **Immediate Actions**
   ```
   □ Identify the problematic function/endpoint
   □ Check for infinite loops or recursive calls
   □ Verify no missing filters on queries
   □ Check for sudden traffic spike (DDoS?)
   ```

2. **Quick Fixes**
   ```typescript
   // Add emergency rate limiting
   const EMERGENCY_LIMIT = 100;
   
   export const expensiveQuery = query({
     handler: async (ctx, args) => {
       // Emergency limit
       const results = await ctx.db
         .query("items")
         .take(EMERGENCY_LIMIT);
       
       return results;
     }
   });
   ```

3. **Rollback if Necessary**
   ```bash
   # Git rollback to last known good state
   git revert HEAD
   git push origin main
   
   # Or redeploy previous version
   npx convex deploy --preview last-good-version
   ```

### Post-Mortem Template

```markdown
## Incident Report: Database Bandwidth Spike

### Summary
- Date: YYYY-MM-DD
- Duration: X hours
- Cost Impact: $XXX
- Root Cause: [Brief description]

### Timeline
- HH:MM - Spike detected
- HH:MM - Investigation started
- HH:MM - Root cause identified
- HH:MM - Fix deployed
- HH:MM - Normal operation restored

### Root Cause
[Detailed explanation of what went wrong]

### Fix Applied
[Code changes made]

### Prevention Measures
- [ ] Added monitoring for X
- [ ] Implemented rate limiting
- [ ] Added code review checklist item
- [ ] Updated documentation

### Lessons Learned
[What we learned from this incident]
```

---

## Automated Monitoring Setup

### GitHub Action for Query Analysis

```yaml
# .github/workflows/query-check.yml
name: Database Query Check

on:
  pull_request:
    paths:
      - 'convex/**'
      - 'src/**/*.ts'

jobs:
  query-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Check for dangerous patterns
        run: |
          # Check for .collect() without filters
          if grep -r "\.collect()" --include="*.ts" | grep -v "withIndex\|filter"; then
            echo "⚠️ Found .collect() without index/filter"
            exit 1
          fi
          
          # Check for loops with await
          if grep -rP "for.*\{[\s\S]*await.*db\." --include="*.ts"; then
            echo "⚠️ Found database call inside loop"
            exit 1
          fi
          
          echo "✅ No dangerous query patterns found"
```

### Slack Alert Integration

```typescript
// monitoring/alerts.ts
async function sendSlackAlert(message: string, severity: 'info' | 'warning' | 'critical') {
  const colors = {
    info: '#36a64f',
    warning: '#ff9800',
    critical: '#ff0000',
  };
  
  await fetch(process.env.SLACK_WEBHOOK_URL!, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      attachments: [{
        color: colors[severity],
        title: `Database Alert: ${severity.toUpperCase()}`,
        text: message,
        ts: Math.floor(Date.now() / 1000),
      }],
    }),
  });
}

// Usage
if (bandwidthUsage > THRESHOLD) {
  await sendSlackAlert(
    `Bandwidth usage exceeded threshold: ${bandwidthUsage}GB > ${THRESHOLD}GB`,
    'critical'
  );
}
```

---

## Dashboard Metrics to Track

### Daily Metrics
- Total database reads/writes
- Bandwidth consumption (GB)
- Average query latency (ms)
- Error rate (%)
- Top 10 slowest queries

### Weekly Metrics
- Bandwidth trend (up/down %)
- Cost per user
- Query efficiency ratio
- Index hit rate

### Monthly Metrics
- Total cost
- Cost per feature
- Performance trends
- Capacity planning needs

---

## Query Performance Logging

```typescript
// utils/queryLogger.ts
interface QueryLog {
  name: string;
  duration: number;
  resultCount: number;
  timestamp: number;
}

const queryLogs: QueryLog[] = [];

export function logQuery(name: string, duration: number, resultCount: number) {
  queryLogs.push({
    name,
    duration,
    resultCount,
    timestamp: Date.now(),
  });
  
  // Alert on slow queries
  if (duration > 1000) {
    console.warn(`[SLOW QUERY] ${name}: ${duration}ms, ${resultCount} results`);
  }
  
  // Alert on large result sets
  if (resultCount > 1000) {
    console.warn(`[LARGE RESULT] ${name}: ${resultCount} results`);
  }
}

// Wrapper for queries
export async function trackedQuery<T>(
  name: string,
  queryFn: () => Promise<T[]>
): Promise<T[]> {
  const start = Date.now();
  const result = await queryFn();
  const duration = Date.now() - start;
  
  logQuery(name, duration, result.length);
  
  return result;
}

// Usage
const users = await trackedQuery("getActiveUsers", () =>
  ctx.db.query("users")
    .withIndex("by_status", q => q.eq("status", "active"))
    .collect()
);
```

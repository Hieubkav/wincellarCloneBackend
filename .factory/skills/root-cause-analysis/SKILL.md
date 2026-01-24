---
name: root-cause-analysis
description: "Systematic debugging and root cause analysis for bugs, errors, and performance issues using industry best practices (5 Whys, Fishbone/Ishikawa, Fault Tree Analysis). Use when: (1) User reports a bug/error needing investigation, (2) Application behaves unexpectedly, (3) Performance degradation, (4) Debugging complex multi-layer issues (frontend/backend/database), (5) User mentions 'debug', 'fix', 'why', 'error', 'bug', 'crash', 'slow', 'not working', (6) Incident postmortem/review needed"
version: 2.0.0
---

# Root Cause Analysis Best Practice

Systematic approach to identify the TRUE cause of bugs and issues, not just symptoms. Based on industry standards: Google SRE, Toyota Production System, and modern DevOps practices.

## Core Philosophy

> "Every incident is a learning opportunity, not a blame game."

**Key Principles:**
- **Blameless Culture**: Focus on systems and processes, not individuals
- **Evidence-Based**: Use data, logs, and metrics - don't guess
- **Systematic**: Follow structured methodology
- **Preventive**: Fix root cause to prevent recurrence

---

## Decision Flow

```
1. GATHER → Collect information, don't assume
   └─ Logs, metrics, user reports, timeline
2. REPRODUCE → Can you make the bug happen?
   ├─ Yes → Document exact steps
   └─ No → Analyze logs, check race conditions
3. ISOLATE → Where exactly does it fail?
   ├─ Frontend → Browser DevTools, React DevTools
   ├─ API → Network tab, API logs
   ├─ Backend → Laravel logs, dd(), dump()
   └─ Database → Query logs, slow queries
4. ANALYZE → Why does it fail? (Use 5 Whys/Fishbone)
   ├─ Ask "Why?" 5 times minimum
   ├─ Create causal chain
   └─ Identify contributing factors
5. FIX → Apply targeted fix (minimal change)
6. VERIFY → Confirm fix works without side effects
7. DOCUMENT → Blameless postmortem for learning
```

---

## RCA Techniques

### Technique 1: The 5 Whys (Toyota Method)

**What**: Ask "Why?" repeatedly (typically 5 times) until you reach the root cause.

**When to use**: Simple to moderately complex issues with likely single cause.

**Process**:
```markdown
Problem: Users cannot log in

Why #1: Why can't users log in?
→ The authentication API returns 500 error

Why #2: Why does the API return 500?
→ Database connection timeout

Why #3: Why is there a connection timeout?
→ Connection pool exhausted

Why #4: Why is the pool exhausted?
→ Connections not being released after queries

Why #5: Why aren't connections released?
→ Missing finally block in database transaction code

ROOT CAUSE: Missing resource cleanup in transaction handling
```

**Rules**:
- Each answer must be **evidence-based**, not assumptions
- Stop when you reach something you can **act on**
- May need more or fewer than 5 "whys"
- Multiple branches are possible (multiple causes)

**Anti-patterns to avoid**:
- ❌ "Human error" as root cause - dig deeper into WHY the error was possible
- ❌ Stopping at symptoms
- ❌ Blaming individuals instead of systems

---

### Technique 2: Fishbone/Ishikawa Diagram

**What**: Visual diagram mapping causes categorized by type.

**When to use**: Complex issues with multiple potential contributing factors.

**Categories for Software (6Ms adapted)**:

```
                    ┌─ People (Developers/Users)
                    │   └─ Skills, Training, Communication
                    ├─ Process (Development/Deployment)
                    │   └─ Code review, Testing, CI/CD
                    ├─ Technology (Tools/Platforms)
PROBLEM ◄───────────┤   └─ Frameworks, Libraries, Infrastructure
                    ├─ Data (Input/Storage)
                    │   └─ Validation, Quality, Integrity
                    ├─ Environment (Runtime)
                    │   └─ Config, Dependencies, Resources
                    └─ External (Third-party)
                        └─ APIs, Services, Network
```

**Example Application**:
```
Problem: Checkout process fails intermittently

People:
├─ New developer unfamiliar with payment flow
└─ User enters invalid card format

Process:
├─ Missing integration tests for payment
└─ No retry logic in code review

Technology:
├─ Payment gateway timeout configuration
└─ Database connection pooling

Data:
├─ Stale shopping cart data
└─ Currency conversion precision

Environment:
├─ Different configs dev vs prod
└─ Memory pressure during peak

External:
├─ Payment gateway rate limiting
└─ Network latency spikes
```

---

### Technique 3: Fault Tree Analysis (FTA)

**What**: Top-down, deductive analysis using Boolean logic (AND/OR gates).

**When to use**: Safety-critical systems, complex failure scenarios.

**Symbols**:
- **OR gate**: Any single cause can trigger the event
- **AND gate**: All causes must occur together

```
              [System Outage]
                    │
                   OR
           ┌───────┴───────┐
           │               │
    [App Failure]   [Infra Failure]
           │               │
          OR              OR
      ┌───┴───┐       ┌───┴───┐
      │       │       │       │
   [OOM]  [Crash]  [DB Down] [Network]
      │       │       │
     AND     OR      OR
    ┌─┴─┐   ┌┴┐    ┌─┴─┐
    │   │   │ │    │   │
 [Leak][Traffic][Bug][Dep] [HW][Config]
```

---

## Phase 1: Information Gathering

### Questions to Ask (CRITICAL)

```markdown
1. **What happened?** 
   - Exact error message (copy-paste, screenshot)
   - What was the user trying to do?

2. **What should have happened?** 
   - Expected vs actual behavior

3. **When did it start?** 
   - Timestamp of first occurrence
   - After deployment? After specific action?
   - Pattern: Always? Sometimes? Specific time?

4. **Who is affected?**
   - All users? Specific users? Specific regions?
   - Can you reproduce it?

5. **What changed recently?**
   - Code deployments
   - Configuration changes
   - Infrastructure changes
   - Third-party updates

6. **Environment details?**
   - Browser/OS version
   - API version
   - Database state
```

### Gather Context (Commands)

```bash
# Recent commits - check what changed
git log --oneline -20
git log --since="2 days ago" --oneline

# Changes since last working state
git diff HEAD~5
git diff --stat HEAD~10

# Find commits that touched specific file
git log --oneline -p -- path/to/file

# Check deployment history
git tag --sort=-creatordate | head -10

# Current branch and uncommitted changes
git status
git stash list
```

### Create Timeline

```markdown
| Time | Event | Source |
|------|-------|--------|
| 09:00 | Deployment v2.3.1 | CI/CD logs |
| 09:15 | First error reported | Sentry |
| 09:20 | Error rate spike to 5% | Monitoring |
| 09:25 | User complaints start | Support |
| 09:30 | Identified failing endpoint | APM |
```

---

## Phase 2: Reproduce the Issue

### Reproduction Checklist

| Check | Action | Why |
|-------|--------|-----|
| Same environment | Local/staging/production? | Env-specific issues |
| Same data | Test with same input | Data-dependent bugs |
| Same user | Same account/permissions | Auth/permission issues |
| Same browser | Same browser/version | Frontend-specific |
| Same steps | Exact sequence | Order-dependent bugs |
| Same timing | Peak hours? Concurrent? | Race conditions |

### If Cannot Reproduce

1. **Check logs** for the exact timestamp
2. **Review user session** data (Sentry, LogRocket)
3. **Look for race conditions** - timing-dependent bugs
4. **Check for state** - user-specific data causing issue
5. **Compare environments** - local vs staging vs production
6. **Check for flaky behavior** - network, external APIs

### Hypothesis-Driven Testing

```markdown
Hypothesis: "Bug occurs when user has more than 100 items in cart"

Test Plan:
1. Create user with 50 items → Expected: Works
2. Create user with 100 items → Expected: Works
3. Create user with 101 items → Expected: Fails

Result: [CONFIRMED/REJECTED]
```

---

## Phase 3: Isolate the Problem

### Layer-by-Layer Debugging

```
[User] → [Browser] → [Network] → [API Gateway] → [Backend] → [Database]
                                                      ↓
                                              [External Services]

For each layer, verify:
✓ Input is correct
✓ Processing is correct
✓ Output is correct
```

#### Frontend (Next.js/React)

```javascript
// Strategic console logging
console.group('[DEBUG] ComponentName');
console.log('Props:', props);
console.log('State:', state);
console.log('Context:', contextValue);
console.groupEnd();

// Network request debugging
console.log('[API Request]', { url, method, body });
console.log('[API Response]', { status, data, headers });

// React DevTools
// - Components tab: Inspect props, state, hooks
// - Profiler tab: Performance analysis

// Network tab checklist:
// ✓ Request URL correct?
// ✓ Request headers (Auth token present?)
// ✓ Request payload correct?
// ✓ Response status code
// ✓ Response body (actual data vs expected)
// ✓ Response time
```

#### API Layer (Network Tab)

```javascript
// Status codes to investigate:
// 400 Bad Request → Check request payload
// 401 Unauthorized → Check auth token
// 403 Forbidden → Check permissions
// 404 Not Found → Check URL/routing
// 422 Validation Error → Check input data
// 429 Too Many Requests → Rate limiting
// 500 Internal Server Error → Check backend logs
// 502/503/504 → Infrastructure issues
```

#### Backend (Laravel)

```php
// Quick debug (stops execution)
dd($variable);

// Dump without stopping
dump($variable);

// Log to file (storage/logs/laravel.log)
Log::debug('Checkpoint reached', ['data' => $data]);
Log::info('User action', ['user_id' => $user->id, 'action' => 'checkout']);
Log::warning('Potential issue', ['context' => $context]);
Log::error('Error occurred', ['exception' => $e->getMessage()]);

// Database query logging
DB::enableQueryLog();
// ... your code ...
$queries = DB::getQueryLog();
Log::debug('Queries executed', ['queries' => $queries]);

// Request debugging
Log::debug('Request details', [
    'url' => request()->fullUrl(),
    'method' => request()->method(),
    'input' => request()->all(),
    'headers' => request()->headers->all(),
    'user' => auth()->user()?->id,
]);

// Telescope (development) - visit /telescope
// Clockwork (browser extension)
```

#### Database

```sql
-- Check slow queries
SHOW PROCESSLIST;

-- Analyze query performance
EXPLAIN ANALYZE SELECT * FROM orders WHERE user_id = 123;

-- Check for locks
SHOW OPEN TABLES WHERE In_use > 0;

-- Check indexes
SHOW INDEX FROM table_name;

-- Enable query logging (MySQL)
SET GLOBAL general_log = 'ON';
SET GLOBAL log_output = 'TABLE';
SELECT * FROM mysql.general_log ORDER BY event_time DESC LIMIT 50;
```

---

## Phase 4: Common Root Causes Reference

### Category: Data Issues

| Symptom | Possible Causes | Investigation | Solution |
|---------|-----------------|---------------|----------|
| Null/undefined errors | Missing relationship, deleted record | Check foreign keys, soft deletes | Eager loading, null checks |
| Wrong data displayed | Stale cache, race condition | Check cache TTL, request timing | Clear cache, add locking |
| No data returned | Wrong query conditions, permissions | Log query, check filters | Fix query, check policies |
| Duplicate data | Missing unique constraint, retry logic | Check DB constraints, logs | Add constraint, idempotency |
| Data inconsistency | Partial transaction, no ACID | Check transaction boundaries | Wrap in transaction |

### Category: Authentication/Authorization

| Symptom | Possible Causes | Investigation | Solution |
|---------|-----------------|---------------|----------|
| 401 Unauthorized | Token expired, invalid, malformed | Check token expiry, decode JWT | Refresh token logic |
| 403 Forbidden | Missing permission, wrong role | Check policies, user roles | Update permissions |
| CORS error | Missing headers, wrong origin | Check CORS config | Configure CORS properly |
| Session lost | Cookie issues, domain mismatch | Check cookies in DevTools | Fix domain/path/secure flags |
| Infinite login loop | Redirect misconfiguration | Check auth flow, redirects | Fix redirect URIs |

### Category: Performance

| Symptom | Possible Causes | Investigation | Solution |
|---------|-----------------|---------------|----------|
| Slow page load | N+1 queries, large payload | Query log, Network tab | Eager loading, pagination |
| High memory usage | Memory leak, large arrays | Profiling, memory dumps | Chunking, generators |
| Timeout errors | Slow external API, heavy computation | APM, request timing | Async processing, caching |
| Database slowness | Missing index, table locks | EXPLAIN, SHOW PROCESSLIST | Add index, optimize query |
| API rate limiting | Too many requests | Check response headers | Implement backoff, caching |

### Category: Integration/External

| Symptom | Possible Causes | Investigation | Solution |
|---------|-----------------|---------------|----------|
| API 500 from third-party | Their bug, our bad request | Check their status page, our payload | Contact support, add fallback |
| Empty response | Serialization error, null body | Log raw response | Fix response handling |
| Webhook failures | URL unreachable, timeout | Check firewall, response time | Retry logic, monitoring |
| File upload fails | Size limit, type restriction | Check php.ini, nginx config | Increase limits, validate type |
| Payment failures | Invalid card, gateway error | Check gateway dashboard | Better error handling |

---

## Phase 5: Debugging Tools Reference

### Laravel Backend

```bash
# Clear all caches
php artisan optimize:clear

# Specific cache clearing
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Check routes
php artisan route:list
php artisan route:list --name=api
php artisan route:list --path=users

# Database commands
php artisan migrate:status
php artisan db:show

# Interactive debugging
php artisan tinker
>>> User::find(1);
>>> DB::table('orders')->where('status', 'pending')->count();

# Queue debugging
php artisan queue:failed
php artisan queue:retry all
```

### Next.js Frontend

```bash
# Clear cache and rebuild
rm -rf .next
npm run build

# Environment check
npx next info

# Bundle analysis
npm run build -- --analyze
# or
ANALYZE=true npm run build
```

### Git Investigation

```bash
# Find when bug was introduced (binary search)
git bisect start
git bisect bad HEAD
git bisect good v1.0.0
# Test, then: git bisect good/bad
git bisect reset

# Search commit messages
git log --grep="checkout" --oneline
git log --grep="fix" --oneline

# Find who changed a line
git blame path/to/file
git blame -L 10,20 path/to/file  # Lines 10-20

# Show what changed in a file over time
git log -p --follow -- path/to/file

# Find commits that changed specific code
git log -S "functionName" --oneline
git log -G "regex pattern" --oneline
```

### Monitoring & Observability

```bash
# Laravel Telescope
# Visit: http://localhost:8000/telescope

# Database query analysis
# Enable in config/database.php
'mysql' => [
    'log_queries' => true,
]

# Application logs
tail -f storage/logs/laravel.log
# or with filtering
tail -f storage/logs/laravel.log | grep -i error
```

---

## Phase 6: Fix and Verify

### Before Fixing

```bash
# Create a fix branch
git checkout -b fix/issue-description

# Document what you found
# Add comment in code explaining WHY, not just WHAT
```

### Fix Guidelines

1. **Minimal change** - Only fix what's broken
2. **One fix per commit** - Easier to revert if needed
3. **Add tests** - Prevent regression
4. **Update documentation** - If behavior changes
5. **Review side effects** - Check related functionality
6. **Consider edge cases** - Don't create new bugs

### Code Review Checklist

```markdown
- [ ] Fix addresses root cause, not just symptom
- [ ] No new bugs introduced
- [ ] Edge cases handled
- [ ] Error handling added
- [ ] Tests added/updated
- [ ] Documentation updated (if needed)
- [ ] Performance impact considered
```

### Verification Checklist

```markdown
- [ ] Original bug is fixed (tested with original steps)
- [ ] No new errors in console/logs
- [ ] Related features still work
- [ ] All tests pass
- [ ] Tested with edge cases
- [ ] Code review completed
- [ ] Tested in staging (if critical)
- [ ] Rollback plan ready
```

---

## Phase 7: Blameless Postmortem

### Why Blameless?

> "Human error is not a root cause. It's a symptom of a system that allowed the error to happen."

**Benefits:**
- Encourages honest reporting
- Identifies systemic issues
- Improves processes
- Builds team trust

### Postmortem Template

```markdown
# Incident Postmortem: [Title]

**Date**: YYYY-MM-DD
**Severity**: Critical/High/Medium/Low
**Duration**: HH:MM
**Author**: [Name]
**Participants**: [Team members involved]

## Executive Summary
One paragraph summary for leadership/stakeholders.

## Timeline
| Time | Event |
|------|-------|
| HH:MM | First alert triggered |
| HH:MM | Team notified |
| HH:MM | Root cause identified |
| HH:MM | Fix deployed |
| HH:MM | Incident resolved |

## Impact
- Users affected: X
- Revenue impact: $Y (if applicable)
- Duration of degraded service: Z minutes

## Root Cause
Detailed explanation of the actual root cause.
Use 5 Whys or Fishbone diagram results here.

## What Went Well
- Quick detection
- Effective communication
- Fast rollback

## What Went Wrong
- Missing monitoring for X
- No automated test for Y
- Documentation outdated

## Action Items
| Action | Owner | Due Date | Status |
|--------|-------|----------|--------|
| Add monitoring for X | @person | YYYY-MM-DD | TODO |
| Write regression test | @person | YYYY-MM-DD | TODO |
| Update runbook | @person | YYYY-MM-DD | TODO |

## Lessons Learned
What can we do differently to prevent this class of issues?

## References
- Related incidents: #123, #456
- Relevant documentation links
```

### Postmortem Meeting Guidelines

1. **Schedule within 48-72 hours** while memory is fresh
2. **Invite all relevant parties** - engineers, support, product
3. **Review timeline together** - build shared understanding
4. **Focus on systems, not people** - "What allowed this to happen?"
5. **Generate actionable items** - specific, assigned, with deadlines
6. **Share findings** - make postmortem accessible to org
7. **Follow up** - track action items to completion

---

## Best Practices Summary

### The DO's

1. ✅ **Gather evidence first** - Logs, metrics, reproduction steps
2. ✅ **One change at a time** - Easier to identify what fixed it
3. ✅ **Ask "Why?" 5 times** - Get to root cause, not symptoms
4. ✅ **Check recent changes** - `git log` is your best friend
5. ✅ **Read the error message** - It often tells you exactly what's wrong
6. ✅ **Reproduce before fixing** - Can't verify without reproduction
7. ✅ **Document as you go** - Future you will thank present you
8. ✅ **Ask for help after 30 mins** - Fresh eyes spot issues quickly
9. ✅ **Create timeline** - Sequence of events reveals causality
10. ✅ **Conduct postmortem** - Every incident is learning opportunity

### The DON'Ts

1. ❌ **Don't guess** - Use data and evidence
2. ❌ **Don't blame individuals** - Focus on systems
3. ❌ **Don't stop at symptoms** - Dig to root cause
4. ❌ **Don't skip verification** - Test the fix thoroughly
5. ❌ **Don't ignore patterns** - Similar bugs suggest systemic issues
6. ❌ **Don't rush fixes** - Quick fixes often create new bugs
7. ❌ **Don't fix and forget** - Document and prevent recurrence
8. ❌ **Don't work in isolation** - Collaborate and communicate

---

## Quick Reference

### Debugging Checklist

```markdown
□ What is the exact error message?
□ When did it start happening?
□ Can I reproduce it?
□ What changed recently? (git log)
□ Which layer is failing? (Frontend/API/Backend/DB)
□ Are there relevant logs?
□ Asked "Why?" at least 5 times?
□ Fix addresses root cause (not symptom)?
□ Tests added?
□ Postmortem documented?
```

### Emergency Commands

```bash
# Laravel - Check what's happening
tail -f storage/logs/laravel.log
php artisan telescope:watch

# Clear everything
php artisan optimize:clear

# Database - Check for issues
php artisan migrate:status
php artisan db:show

# Git - What changed?
git log --oneline -10
git diff HEAD~3

# Rollback last migration
php artisan migrate:rollback
```

---

## Output Format

When performing root cause analysis, report using this structure:

```markdown
## Root Cause Analysis Report

### Issue Summary
[One paragraph: What happened, impact, duration]

### Investigation Steps
1. [Step 1: What was checked, what was found]
2. [Step 2: What was checked, what was found]
...

### 5 Whys Analysis
- Why #1: [Question] → [Answer]
- Why #2: [Question] → [Answer]
- Why #3: [Question] → [Answer]
- Why #4: [Question] → [Answer]
- Why #5: [Question] → [Answer]

### Root Cause
[The actual root cause - specific and actionable]

### Solution
[Fix applied or recommended]

### Prevention
[How to prevent recurrence]
- [ ] Action item 1
- [ ] Action item 2

### References
- Related logs/commits
- Documentation links
```

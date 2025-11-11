# Skill Orchestration Guide

Advanced patterns for coordinating multiple skills: sequential, parallel, conditional, and hierarchical execution.

## Core Concepts

### Two-Tier Agent Model
```
Primary Agent (choose-skill)
    ├─ Analyzes context
    ├─ Recommends skills
    └─ Defines execution order
    
Subagents (Specialized Skills)
    ├─ Execute specific tasks
    ├─ Stateless (no memory between calls)
    └─ Can be chained or parallelized
```

---

## Orchestration Patterns

### 1. Sequential Orchestration (Pipeline)

**When to Use:**  
Tasks have dependencies - output of Skill A is input for Skill B.

**Pattern:**
```
Skill A → Skill B → Skill C
```

**Example 1: New API Development**
```
api-design-principles → backend-dev-guidelines → api-documentation-writer
     (Design)               (Implement)              (Document)

Flow:
1. api-design-principles: Design API structure, endpoints, schemas
2. backend-dev-guidelines: Implement routes, controllers, services
3. api-documentation-writer: Generate OpenAPI spec from implementation
```

**Example 2: Database Migration**
```
database-backup → designing-database-schemas → generating-orm-code
   (Safety)             (Design)                  (Generate)

Flow:
1. database-backup: Create backup before changes
2. designing-database-schemas: Design new schema/changes
3. generating-orm-code: Generate Eloquent models from schema
```

**Characteristics:**
- ✅ Clear dependencies
- ✅ Predictable order
- ✅ Each step builds on previous
- ❌ Can't parallelize
- ❌ Total time = sum of all steps

---

### 2. Parallel Orchestration

**When to Use:**  
Tasks are independent and can run simultaneously.

**Pattern:**
```
        ┌─ Skill A ─┐
Task ───┼─ Skill B ─┼─→ Combine results
        └─ Skill C ─┘
```

**Example 1: Full SEO Audit**
```
                ┌─ google-official-seo-guide (Technical SEO)
SEO Audit ──────┼─ seo-content-optimizer (Content SEO)
                └─ web-performance-audit (Performance)

All 3 can run in parallel, then combine findings
```

**Example 2: Database Health Check**
```
                       ┌─ validating-database-integrity
Database Audit ────────┼─ scanning-database-security
                       └─ analyzing-database-indexes

Independent checks, combine results for full report
```

**Characteristics:**
- ✅ Faster total execution (parallel)
- ✅ Independent tasks
- ✅ Comprehensive coverage
- ❌ Requires coordination of results
- ❌ More complex error handling

---

### 3. Conditional Orchestration (Decision Tree)

**When to Use:**  
Next skill depends on results/conditions from previous skill.

**Pattern:**
```
Skill A → [Condition?]
            ├─ True  → Skill B
            └─ False → Skill C
```

**Example 1: Bug Investigation**
```
systematic-debugging → [Bug type identified?]
                            ├─ Filament error → filament-form-debugger
                            ├─ DB slow query → analyzing-query-performance
                            ├─ API issue → backend-dev-guidelines
                            └─ Frontend → frontend-dev-guidelines
```

**Example 2: Performance Optimization**
```
web-performance-audit → [Bottleneck location?]
                            ├─ Frontend assets → frontend-dev-guidelines
                            ├─ API calls → api-cache-invalidation
                            ├─ Database → analyzing-query-performance
                            └─ Images → image-management
```

**Characteristics:**
- ✅ Adaptive workflow
- ✅ Efficient (only run needed skills)
- ❌ Requires intermediate decision points
- ❌ More complex logic

---

### 4. Hierarchical Orchestration (Parent-Child)

**When to Use:**  
One primary skill with optional supporting skills.

**Pattern:**
```
Primary Skill (Always)
    ├─ Supporting Skill 1 (Conditional)
    ├─ Supporting Skill 2 (Conditional)
    └─ Supporting Skill 3 (Conditional)
```

**Example 1: Filament Resource Creation**
```
filament-resource-generator (Primary)
    ├─ image-management (If model has images)
    ├─ filament-rules (If complex forms)
    └─ database-backup (If migration needed)
```

**Example 2: API Development**
```
backend-dev-guidelines (Primary)
    ├─ api-design-principles (If new API design)
    ├─ api-cache-invalidation (If high traffic)
    └─ api-documentation-writer (If public API)
```

**Characteristics:**
- ✅ Clear primary task
- ✅ Flexible additions
- ✅ Easy to understand
- ❌ Can grow complex with many conditionals

---

## Skill Dependencies

### Hard Dependencies (Sequential Required)

**Rule:** Skill B cannot start until Skill A completes.

**Examples:**

1. **Database Operations:**
   ```
   database-backup MUST precede any risky database operation
   ├─ Reason: Safety - can rollback if migration fails
   └─ Violation consequence: Data loss risk
   ```

2. **Design Before Implementation:**
   ```
   designing-database-schemas MUST precede generating-orm-code
   ├─ Reason: Models generated from schema
   └─ Violation consequence: Incorrect models, need regeneration
   ```

3. **Debug Before Fix:**
   ```
   systematic-debugging MUST precede domain-specific fix skill
   ├─ Reason: Understand root cause before fixing
   └─ Violation consequence: Wrong fix, new bugs
   ```

### Soft Dependencies (Recommended Order)

**Rule:** Skill B benefits from Skill A but can run independently.

**Examples:**

1. **API Documentation:**
   ```
   api-design-principles → api-documentation-writer
   ├─ Better: Design-first approach, cleaner docs
   └─ Alternative: Document existing API (works but not ideal)
   ```

2. **Performance Optimization:**
   ```
   analyzing-query-performance → sql-optimization-patterns
   ├─ Better: Identify slow queries first, then optimize
   └─ Alternative: Proactive optimization (less targeted)
   ```

### No Dependencies (Parallel Safe)

**Rule:** Skills can run in any order or simultaneously.

**Examples:**

1. **SEO Audit:**
   ```
   google-official-seo-guide || seo-content-optimizer || web-performance-audit
   All independent, can run in parallel
   ```

2. **Database Health:**
   ```
   validating-database-integrity || scanning-database-security || analyzing-database-indexes
   All read-only checks, no conflicts
   ```

---

## Execution Strategies

### Strategy 1: Waterfall (Strictly Sequential)

**When:** Strong dependencies, each step builds on previous.

**Example:**
```
Task: "Deploy new feature to production"

Steps:
1. systematic-debugging (Ensure no bugs)
   ├─ Status: Must complete
   └─ Next: Only if tests pass

2. database-backup (Safety net)
   ├─ Status: Must complete
   └─ Next: Only if backup succeeds

3. [Deploy code]
   ├─ Status: Must complete
   └─ Next: Only if deployment succeeds

4. web-performance-audit (Post-deployment check)
   ├─ Status: Must complete
   └─ Next: Done if metrics OK
```

**Characteristics:**
- Most cautious approach
- Slowest (sequential)
- Clearest rollback points

---

### Strategy 2: Fan-Out/Fan-In (Parallel with Merge)

**When:** Multiple independent analyses, combine at end.

**Example:**
```
Task: "Full stack audit before launch"

       ┌─ frontend-dev-guidelines (Frontend audit)
       ├─ backend-dev-guidelines (Backend audit)
Start ─┼─ analyzing-query-performance (DB audit)
       ├─ scanning-database-security (Security audit)
       └─ web-performance-audit (Performance audit)
              ↓
         [Combine all reports]
              ↓
         [Action plan]
```

**Characteristics:**
- Fastest (parallel execution)
- Comprehensive coverage
- Requires report aggregation

---

### Strategy 3: Iterative Refinement

**When:** Repeat skill until condition met.

**Example:**
```
Task: "Optimize query to <100ms"

Loop:
1. analyzing-query-performance (Measure: 500ms)
   ↓
2. sql-optimization-patterns (Apply optimization #1)
   ↓
3. analyzing-query-performance (Measure: 200ms)
   ↓
4. sql-optimization-patterns (Apply optimization #2)
   ↓
5. analyzing-query-performance (Measure: 80ms) ✓ Done!
```

**Characteristics:**
- Goal-oriented
- Multiple iterations expected
- Each iteration gets closer to target

---

### Strategy 4: Progressive Disclosure

**When:** Start simple, add complexity only if needed.

**Example:**
```
Task: "Fix Filament form error"

Tier 1 (Simple):
  filament-form-debugger
  ├─ 80% of issues resolved here
  └─ If resolved → Done

Tier 2 (Moderate):
  filament-form-debugger + filament-rules
  ├─ 95% of issues resolved
  └─ If resolved → Done

Tier 3 (Complex):
  systematic-debugging → filament-form-debugger → filament-rules
  └─ 99% of issues resolved
```

**Characteristics:**
- Efficient (don't over-engineer)
- Start simple, escalate if needed
- Most tasks resolve at Tier 1

---

## Common Orchestration Recipes

### Recipe 1: The Guardian Pattern
**Purpose:** Always protect before risky operations.

```
[Guardian Skill] → [Risky Operation] → [Verification Skill]

Example:
database-backup → [Run migration] → validating-database-integrity
```

---

### Recipe 2: The Investigator Pattern
**Purpose:** Understand before acting.

```
[Debug/Audit Skill] → [Decision Point] → [Action Skill]

Example:
systematic-debugging → [Identify cause] → [Domain-specific fix skill]
```

---

### Recipe 3: The Builder Pattern
**Purpose:** Design → Implement → Document.

```
[Design Skill] → [Implementation Skill] → [Documentation Skill]

Example:
api-design-principles → backend-dev-guidelines → api-documentation-writer
```

---

### Recipe 4: The Optimizer Pattern
**Purpose:** Measure → Optimize → Measure.

```
[Audit Skill] → [Optimization Skill] → [Audit Skill]

Example:
web-performance-audit → frontend-dev-guidelines → web-performance-audit
```

---

### Recipe 5: The Full Stack Pattern
**Purpose:** Frontend + Backend + Database in parallel.

```
        ┌─ frontend-dev-guidelines
Task ───┼─ backend-dev-guidelines
        └─ designing-database-schemas
              ↓
         [Integration phase]
```

---

## Anti-Patterns (Avoid)

### ❌ Anti-Pattern 1: Skip the Guardian
```
❌ Bad:
   [Run migration directly without backup]

✅ Good:
   database-backup → [Run migration]
```

---

### ❌ Anti-Pattern 2: Fix Before Debug
```
❌ Bad:
   "I think it's X, let me try fixing it"
   [Jump to fix skill]

✅ Good:
   systematic-debugging → [Identify root cause] → [Fix skill]
```

---

### ❌ Anti-Pattern 3: Parallel with Dependencies
```
❌ Bad:
   designing-database-schemas || generating-orm-code
   (Can't generate models before schema designed!)

✅ Good:
   designing-database-schemas → generating-orm-code
```

---

### ❌ Anti-Pattern 4: Too Many Skills
```
❌ Bad:
   Skill A → B → C → D → E → F → G
   (7 skills is too complex)

✅ Good:
   Break into 2-3 skill combos max
   Or use hierarchical with optional skills
```

---

### ❌ Anti-Pattern 5: Wrong Execution Order
```
❌ Bad:
   api-documentation-writer → api-design-principles
   (Documenting before designing!)

✅ Good:
   api-design-principles → api-documentation-writer
```

---

## Orchestration Decision Tree

```
Start: Analyze Task
    ↓
[Are there dependencies?]
    ├─ Yes → Sequential Orchestration
    │         ├─ Hard dependencies? → Waterfall Strategy
    │         └─ Soft dependencies? → Pipeline with checkpoints
    │
    └─ No → Can tasks run in parallel?
              ├─ Yes → Parallel Orchestration
              │         └─ Use Fan-Out/Fan-In
              │
              └─ No → Conditional Orchestration
                        └─ Use Decision Tree pattern
```

---

## Performance Considerations

### Execution Time Estimates

| Pattern | Time Formula | Example |
|---------|--------------|---------|
| **Sequential** | T = T₁ + T₂ + T₃ | 5 + 10 + 8 = 23 min |
| **Parallel** | T = max(T₁, T₂, T₃) | max(5, 10, 8) = 10 min |
| **Conditional** | T = T_diagnosis + T_branch | 5 + 10 = 15 min |

**Optimization Tips:**
1. **Parallelize** when possible (2-3x speedup)
2. **Early exit** in conditionals (save time if condition false)
3. **Progressive disclosure** (don't load all skills upfront)

---

## Monitoring & Error Handling

### Checkpoint Pattern
```
Skill A
  ↓
[Checkpoint: Verify A succeeded]
  ├─ Success → Continue to Skill B
  └─ Failure → Rollback or alert
       ↓
     Skill B
```

### Rollback Strategy
```
[State 0: Original]
    ↓
Skill A changes to [State 1]
    ↓
[Checkpoint: OK?]
    ├─ Yes → Continue
    └─ No → Rollback to State 0

Example: database-backup allows rollback after failed migration
```

---

## Real-World Examples

### Example 1: E-commerce Product Launch
```
Parallel Phase:
  ┌─ frontend-dev-guidelines (Product page)
  ├─ backend-dev-guidelines (API endpoints)
  └─ designing-database-schemas (Product schema)
      ↓
Sequential Phase:
  database-backup
      ↓
  [Deploy schema & code]
      ↓
  api-documentation-writer
      ↓
  web-performance-audit
      ↓
  google-official-seo-guide
```

### Example 2: Production Bug Fix
```
systematic-debugging (Phase 1-2: Investigate)
    ↓
[Identify: Database query slow]
    ↓
analyzing-query-performance (Measure)
    ↓
sql-optimization-patterns (Fix)
    ↓
analyzing-query-performance (Verify)
    ↓
[Checkpoint: Performance OK?]
    ├─ Yes → Deploy
    └─ No → Iterate sql-optimization-patterns
```

### Example 3: Full SEO Campaign
```
Sequential Foundation:
  google-official-seo-guide (Technical setup)
      ↓
Parallel Content Creation:
  ┌─ seo-content-optimizer (Blog posts)
  ├─ seo-content-optimizer (Product pages)
  └─ seo-content-optimizer (Category pages)
      ↓
Sequential Verification:
  web-performance-audit (Core Web Vitals)
      ↓
  google-official-seo-guide (Search Console check)
```

---

**Version:** 1.0  
**Last Updated:** 2025-11-11  
**Key Patterns:** 5 orchestration patterns, 5 recipes, 5 anti-patterns

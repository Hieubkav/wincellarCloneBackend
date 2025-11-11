# Skill Recommendation Patterns

Common task patterns with pre-built skill combos and decision trees.

## Pattern Categories

1. [New Feature Development](#new-feature-development)
2. [Bug Fixing & Debugging](#bug-fixing--debugging)
3. [Performance Optimization](#performance-optimization)
4. [API Development](#api-development)
5. [Database Operations](#database-operations)
6. [Content & SEO](#content--seo)
7. [Testing & QA](#testing--qa)
8. [Documentation](#documentation)

---

## New Feature Development

### Pattern 1: Admin Resource Creation (Filament)
**Trigger:** "Tạo resource mới", "Create admin panel for X"

**Combo:** `filament-resource-generator` → `filament-rules` → `image-management` (if needed)

**Decision Tree:**
```
Has images? 
  ├─ Yes → Add image-management
  └─ No  → Skip

Complex forms?
  ├─ Yes → Review filament-rules for advanced patterns
  └─ No  → Standard generator sufficient
```

**Example:**
```
Task: "Tạo resource cho Product có gallery"
Combo: filament-resource-generator → image-management → filament-rules
```

---

### Pattern 2: REST API Endpoint
**Trigger:** "Tạo API endpoint", "New REST API for X"

**Combo:** `api-design-principles` → `backend-dev-guidelines` → `api-documentation-writer`

**Decision Tree:**
```
Need caching?
  ├─ Yes → Add api-cache-invalidation
  └─ No  → Standard combo

GraphQL instead?
  ├─ Yes → Use api-design-principles (GraphQL section)
  └─ No  → REST patterns
```

**Example:**
```
Task: "Tạo API lấy danh sách products với cache"
Combo: api-design-principles → backend-dev-guidelines → api-cache-invalidation → api-documentation-writer
```

---

### Pattern 3: Frontend Feature
**Trigger:** "Tạo component", "Build page for X"

**Combo:** `frontend-dev-guidelines` → `ux-designer` → `ui-styling`

**Decision Tree:**
```
Design provided?
  ├─ Yes → Skip ux-designer, go straight to implementation
  └─ No  → Start with ux-designer

Using shadcn?
  ├─ Yes → Include ui-styling
  └─ No  → MUI patterns in frontend-dev-guidelines
```

**Example:**
```
Task: "Tạo product detail page với responsive design"
Combo: ux-designer → frontend-dev-guidelines → ui-styling
```

---

### Pattern 4: Database Schema Design
**Trigger:** "Design database", "Create schema for X"

**Combo:** `designing-database-schemas` → `generating-orm-code` → `database-backup` (before migrate)

**Decision Tree:**
```
Need seed data?
  ├─ Yes → Add generating-database-seed-data
  └─ No  → Skip

Complex relationships?
  ├─ Yes → Read designing-database-schemas thoroughly
  └─ No  → Quick schema design
```

**Example:**
```
Task: "Design schema cho e-commerce với orders, products, users"
Combo: designing-database-schemas → generating-orm-code → generating-database-seed-data → database-backup
```

---

## Bug Fixing & Debugging

### Pattern 5: Mysterious Bug
**Trigger:** "Bug không rõ nguyên nhân", "Strange behavior", "Test fails randomly"

**Combo:** `systematic-debugging` → [domain-specific-skill]

**Decision Tree:**
```
Bug type?
  ├─ Filament error → systematic-debugging → filament-form-debugger
  ├─ Database issue → systematic-debugging → analyzing-query-performance
  ├─ API issue → systematic-debugging → backend-dev-guidelines
  └─ Frontend → systematic-debugging → frontend-dev-guidelines

Tried 3+ fixes already?
  ├─ Yes → STOP, systematic-debugging Phase 1 from scratch
  └─ No  → Follow systematic-debugging process
```

**Example:**
```
Task: "Filament form lỗi 'Class not found' nhưng không biết tại sao"
Combo: systematic-debugging → filament-form-debugger
```

---

### Pattern 6: Performance Problem
**Trigger:** "Slow", "Chậm", "Performance issue"

**Combo:** `systematic-debugging` → [performance-skill]

**Decision Tree:**
```
Problem area?
  ├─ Database queries → analyzing-query-performance → sql-optimization-patterns
  ├─ API response → api-cache-invalidation OR analyzing-query-performance
  ├─ Web page load → web-performance-audit
  └─ Unknown → systematic-debugging first!
```

**Example:**
```
Task: "API /products chậm, mất 5 giây"
Combo: systematic-debugging → analyzing-query-performance → api-cache-invalidation
```

---

## Performance Optimization

### Pattern 7: Database Query Optimization
**Trigger:** "Query chậm", "Optimize database", "N+1 problem"

**Combo:** `analyzing-query-performance` → `analyzing-database-indexes` → `sql-optimization-patterns`

**Decision Tree:**
```
Have slow query log?
  ├─ Yes → analyzing-query-performance (EXPLAIN)
  └─ No  → Enable slow query log first

Missing indexes?
  ├─ Yes → analyzing-database-indexes
  └─ No  → Query rewrite needed (sql-optimization-patterns)
```

**Example:**
```
Task: "Query products với nhiều joins chậm"
Combo: analyzing-query-performance → analyzing-database-indexes → sql-optimization-patterns
```

---

### Pattern 8: Web Performance
**Trigger:** "Page load chậm", "Core Web Vitals", "Lighthouse score thấp"

**Combo:** `web-performance-audit` → `frontend-dev-guidelines` → `backend-dev-guidelines` (if API slow)

**Decision Tree:**
```
Problem source?
  ├─ Frontend assets → web-performance-audit → frontend-dev-guidelines (lazy loading, code splitting)
  ├─ API calls → backend-dev-guidelines → api-cache-invalidation
  ├─ Images → image-management (WebP conversion)
  └─ Mixed → Start with web-performance-audit for audit
```

**Example:**
```
Task: "Homepage load 8 giây, cần giảm xuống <3s"
Combo: web-performance-audit → frontend-dev-guidelines → api-cache-invalidation
```

---

## API Development

### Pattern 9: Complete API Development
**Trigger:** "Develop API for X", "Build REST API", "Create GraphQL schema"

**Combo:** `api-design-principles` → `backend-dev-guidelines` → `api-documentation-writer` → `api-cache-invalidation`

**Decision Tree:**
```
Public API?
  ├─ Yes → Must have api-documentation-writer
  └─ No  → Optional documentation

High traffic expected?
  ├─ Yes → Include api-cache-invalidation
  └─ No  → Skip caching initially

Real-time updates needed?
  ├─ Yes → Add api-cache-invalidation (with revalidation)
  └─ No  → Standard REST
```

**Example:**
```
Task: "Tạo public API cho products với documentation và caching"
Combo: api-design-principles → backend-dev-guidelines → api-cache-invalidation → api-documentation-writer
```

---

### Pattern 10: Cache Synchronization
**Trigger:** "Phải Ctrl+F5", "Data không update", "Stale cache"

**Combo:** `api-cache-invalidation` → `backend-dev-guidelines` (Observer setup)

**Decision Tree:**
```
Using Next.js frontend?
  ├─ Yes → api-cache-invalidation (On-Demand Revalidation)
  └─ No  → Standard cache invalidation patterns

Multiple cache layers?
  ├─ Yes → Review cache hierarchy in api-cache-invalidation
  └─ No  → Simple Observer pattern sufficient
```

**Example:**
```
Task: "Admin update product nhưng frontend không thấy data mới"
Combo: api-cache-invalidation → backend-dev-guidelines
```

---

## Database Operations

### Pattern 11: Schema Migration
**Trigger:** "Migration", "Change database schema", "Add new table"

**Combo:** `database-backup` → `designing-database-schemas` → `generating-orm-code`

**Decision Tree:**
```
Production database?
  ├─ Yes → MUST use database-backup first
  └─ No  → Optional but recommended

Schema changes complex?
  ├─ Yes → comparing-database-schemas (review impact)
  └─ No  → Standard migration

Data migration needed?
  ├─ Yes → Add generating-database-seed-data patterns
  └─ No  → Schema only
```

**Example:**
```
Task: "Thêm bảng orders và relationships vào production"
Combo: database-backup → comparing-database-schemas → designing-database-schemas → generating-orm-code
```

---

### Pattern 12: Database Health Check
**Trigger:** "Check database", "Database audit", "Data integrity"

**Combo:** `validating-database-integrity` → `scanning-database-security` → `analyzing-database-indexes`

**Decision Tree:**
```
Security concern?
  ├─ Yes → scanning-database-security first
  └─ No  → Start with integrity check

Performance issues?
  ├─ Yes → Add analyzing-query-performance
  └─ No  → Focus on integrity and security
```

**Example:**
```
Task: "Full database audit trước khi launch production"
Combo: scanning-database-security → validating-database-integrity → analyzing-database-indexes → analyzing-query-performance
```

---

## Content & SEO

### Pattern 13: SEO Optimization
**Trigger:** "SEO", "Improve ranking", "Google search visibility"

**Combo:** `google-official-seo-guide` → `seo-content-optimizer` → `web-performance-audit`

**Decision Tree:**
```
Technical SEO?
  ├─ Yes → google-official-seo-guide (crawling, indexing, structured data)
  └─ No  → seo-content-optimizer (content only)

Performance issue?
  ├─ Yes → Add web-performance-audit (Core Web Vitals affect SEO)
  └─ No  → Focus on content and technical SEO
```

**Example:**
```
Task: "Optimize website cho Google search, hiện tại không xuất hiện"
Combo: google-official-seo-guide → web-performance-audit → seo-content-optimizer
```

---

### Pattern 14: Content Creation & Optimization
**Trigger:** "Viết blog post", "Optimize content", "Keyword research"

**Combo:** `seo-content-optimizer` → `google-official-seo-guide` (structured data)

**Decision Tree:**
```
New content?
  ├─ Yes → Start with seo-content-optimizer (keyword research)
  └─ No  → Optimize existing (readability, meta)

Need structured data?
  ├─ Yes → google-official-seo-guide (Schema.org)
  └─ No  → Content optimization only
```

**Example:**
```
Task: "Viết blog post về Laravel 12 với SEO optimization"
Combo: seo-content-optimizer → google-official-seo-guide
```

---

## Testing & QA

### Pattern 15: E2E Testing
**Trigger:** "Browser test", "E2E test", "UI testing"

**Combo:** `laravel-dusk` → `systematic-debugging` (if tests fail)

**Decision Tree:**
```
Tests failing?
  ├─ Yes → systematic-debugging → laravel-dusk
  └─ No  → laravel-dusk only

JavaScript heavy?
  ├─ Yes → laravel-dusk (browser automation)
  └─ No  → Consider unit/integration tests instead
```

**Example:**
```
Task: "Viết test cho checkout flow với payment gateway"
Combo: laravel-dusk → systematic-debugging (if issues arise)
```

---

### Pattern 16: Test Data Generation
**Trigger:** "Generate test data", "Need fixtures", "Factory patterns"

**Combo:** `generating-test-data` → `generating-database-seed-data`

**Decision Tree:**
```
Unit tests?
  ├─ Yes → generating-test-data (factories, fixtures)
  └─ No  → generating-database-seed-data (seeders)

Complex relationships?
  ├─ Yes → Review relationship handling in both skills
  └─ No  → Simple factories
```

**Example:**
```
Task: "Tạo test data cho order system với users, products, payments"
Combo: generating-test-data → generating-database-seed-data
```

---

## Documentation

### Pattern 17: API Documentation
**Trigger:** "Document API", "Create API docs", "OpenAPI spec"

**Combo:** `api-documentation-writer` → `api-design-principles` (review)

**Decision Tree:**
```
Existing API?
  ├─ Yes → api-documentation-writer (document as-is)
  └─ No  → api-design-principles → api-documentation-writer

Need SDK?
  ├─ Yes → api-documentation-writer (SDK generation)
  └─ No  → Documentation only
```

**Example:**
```
Task: "Document existing REST API với OpenAPI và generate SDK"
Combo: api-documentation-writer → api-design-principles
```

---

### Pattern 18: Database Documentation
**Trigger:** "Document database", "ERD", "Data dictionary"

**Combo:** `generating-database-documentation` → `designing-database-schemas` (ERD)

**Decision Tree:**
```
Need ERD?
  ├─ Yes → designing-database-schemas (generate ERD)
  └─ No  → generating-database-documentation only

Team onboarding?
  ├─ Yes → Full documentation with examples
  └─ No  → Basic data dictionary
```

**Example:**
```
Task: "Tạo documentation cho database để onboard team mới"
Combo: designing-database-schemas → generating-database-documentation
```

---

## Quick Decision Matrix

| Task Type | First Skill | Add If... | Always Include |
|-----------|-------------|-----------|----------------|
| **New Filament Resource** | filament-resource-generator | Has images → image-management | filament-rules |
| **New API** | api-design-principles | Public → api-documentation-writer | backend-dev-guidelines |
| **Bug Fix** | systematic-debugging | Domain-specific skill after Phase 1 | - |
| **Performance** | systematic-debugging | DB → analyzing-query-performance | - |
| **Database Change** | database-backup | Complex → comparing-database-schemas | designing-database-schemas |
| **SEO** | google-official-seo-guide | Content → seo-content-optimizer | web-performance-audit |

---

## Anti-Patterns (Avoid)

❌ **Don't:**
1. **Skip systematic-debugging** when bug cause is unclear
2. **Skip database-backup** before production migrations
3. **Skip api-design-principles** when creating new APIs
4. **Jump to optimization** before profiling (use web-performance-audit or analyzing-query-performance first)
5. **Recommend 4+ skills** in one combo (too complex)

✅ **Do:**
1. **Always debug first** before optimizing
2. **Always backup** before risky operations
3. **Start with design** (api-design-principles, designing-database-schemas) before implementation
4. **Measure first** (audits, profiling) before optimizing
5. **Keep combos simple** (1-3 skills max)

---

**Version:** 1.0  
**Last Updated:** 2025-11-11  
**Total Patterns:** 18 common patterns across 8 categories

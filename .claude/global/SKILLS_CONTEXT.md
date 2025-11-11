# Skills Context - Quick Reference

> **Single Source of Truth** for skills organization and structure.  
> Auto-synced by `sync_skills_context.py` - DO NOT edit manually.

**Total Skills:** 51 skills across 11 categories  
**Last Updated:** 2025-11-11 (Optimized - merged 10 duplicate/small skills)

---

## Quick Summary Table

| Category | Count | Description |
|----------|-------|-------------|
| **api/** | 3 | API Design & Documentation |
| **database/** | 8 | Database Management & Optimization |
| **filament/** | 4 | Filament 4.x (Laravel 12) |
| **frontend/** | 8 | Frontend Development |
| **fullstack/** | 7 | Full-Stack Development |
| **laravel/** | 3 | Laravel Framework & Tools |
| **marketing/** | 1 | Content & SEO Marketing |
| **meta/** | 2 | Skill Management |
| **optimize/** | 2 | Performance & SEO |
| **testing/** | 3 | Testing & QA |
| **workflows/** | 10 | Development Workflows |

---

## Category Details

### filament/ (4 skills)
**Description:** Laravel 12 + Filament 4.x specific skills for admin panel development.

**Skills:**
- `filament-rules` - Filament 4.x coding standards (Schema namespace, Vietnamese UI, Observer patterns)
- `filament-resource-generator` - Automated Filament resource generation
- `filament-form-debugger` - Diagnose and fix Filament form errors
- `image-management` - Centralized polymorphic image management system

**Key Features:**
- Schema namespace quirks (NOT Form namespace)
- Vietnamese UI labels
- Observer patterns for automation
- Polymorphic image management

---

### laravel/ (3 skills)
**Description:** Laravel Framework & Tools for building modern PHP applications.

**Skills:**
- `laravel` - Laravel v12 framework (routing, Eloquent, authentication, APIs)
- `laravel-dusk` - Browser automation and E2E testing
- `laravel-prompts` - Interactive CLI commands with prompts

**Key Features:**
- Comprehensive Laravel framework coverage
- Browser testing with ChromeDriver
- Beautiful CLI interfaces

---

### fullstack/ (4 skills)
**Description:** Full-stack development guidelines and design patterns.

**Skills:**
- `backend-dev-guidelines` - Node.js/Express/TypeScript backend patterns
- `frontend-dev-guidelines` - React/TypeScript/Suspense frontend patterns
- `ux-designer` - Expert UI/UX design guidance
- `ui-styling` - shadcn/ui components + Tailwind styling

**Key Features:**
- Node.js/Express/TypeScript backend
- React/TypeScript/Suspense frontend
- UI/UX design principles
- shadcn/ui + Tailwind CSS

---

### workflows/ (7 skills)
**Description:** Development workflows, debugging, planning, and automation.

**Skills:**
- `database-backup` - Safe database migration workflow with Spatie backup
- `systematic-debugging` - Four-phase debugging framework
- `product-search-scoring` - Advanced product search with Vietnamese text
- `docs-seeker` - Technical documentation discovery (llms.txt, GitHub)
- `brainstorming` - Turn rough ideas into designs through dialogue
- `sequential-thinking` - Step-by-step reasoning for complex problems
- `writing-plans` - Detailed implementation plans for engineers

**Key Features:**
- Safe database operations
- Systematic problem-solving
- Search optimization
- Documentation discovery
- Idea refinement workflows

---

### api/ (3 skills)
**Description:** API design, caching, and documentation.

**Skills:**
- `api-design-patterns` - REST & GraphQL design patterns, best practices, versioning, auth (merged from api-design-principles + api-best-practices)
- `api-cache-invalidation` - Automatic cache invalidation with Laravel Observers
- `api-documentation-writer` - Comprehensive API documentation generation

**Key Features:**
- REST/GraphQL design patterns
- API versioning strategies
- Authentication patterns (JWT, OAuth, API Keys)
- Cache invalidation strategies
- OpenAPI/Swagger documentation

**✨ Optimized:** Merged 2 duplicate skills into 1 comprehensive guide

---

### meta/ (2 skills)
**Description:** Skills for managing skills themselves.

**Skills:**
- `create-skill` - Skill creation with intelligent category placement
- `choose-skill` - Meta-agent for skill recommendation

**Key Features:**
- Skill creation, validation, packaging
- AI-powered category suggestions
- Refactor opportunity detection
- Skill recommendation engine

**✨ Optimized:** Removed skill-skeleton (empty template, functionality in create-skill)

---

### optimize/ (2 skills)
**Description:** Performance & SEO optimization.

**Skills:**
- `web-performance-audit` - Core Web Vitals, page speed, bottleneck analysis
- `google-official-seo-guide` - Official Google SEO guide (Search Console, structured data)

**Key Features:**
- Core Web Vitals measurement
- Performance optimization strategies
- Google SEO best practices
- Structured data implementation

---

### marketing/ (1 skill)
**Description:** Content & SEO marketing.

**Skills:**
- `seo-content-optimizer` - SEO content optimization (keyword analysis, meta descriptions)

**Key Features:**
- Keyword analysis and optimization
- Meta description crafting
- Content SEO strategies

**⚠️ Status:** Underutilized (1 skill) - Consider growing to 3+ skills or merging with optimize/

---

### database/ (8 skills)
**Description:** Database management, optimization, and code generation.

**Skills:**
- `databases` - PostgreSQL, MySQL, MongoDB queries and patterns
- `database-performance` - Index analysis + query profiling optimization (merged from analyzing-database-indexes + analyzing-query-performance)
- `database-data-generation` - Seed data + test fixtures generation (merged from generating-database-seed-data + generating-test-data)
- `database-validation` - Security scanning + integrity validation (merged from scanning-database-security + validating-database-integrity)
- `designing-database-schemas` - ERD generation + schema design + documentation (merged with generating-database-documentation)
- `comparing-database-schemas` - Schema comparison and migration generation
- `generating-orm-code` - ORM model generation (Prisma, TypeORM, Eloquent)
- `sql-optimization-patterns` - SQL query optimization patterns

**Key Features:**
- Multi-database support (PostgreSQL, MySQL, MongoDB)
- Comprehensive performance optimization (indexes + queries)
- Complete data generation (seeds + test data)
- Full validation suite (security + integrity)
- Schema design with auto-documentation

**✨ Optimized:** Merged 8 small/duplicate skills into 3 comprehensive guides (12 → 8 skills)

---

## Organization Health

### ✅ Well-Organized Categories
- **filament/** (4) - Optimal size, focused domain
- **laravel/** (3) - Clean, framework-focused
- **fullstack/** (4) - Balanced frontend/backend
- **workflows/** (7) - Slightly large but cohesive
- **api/** (3) - Clear domain focus

### ⚠️ Attention Needed
- **database/** (12) - Overcrowded, consider splitting
- **marketing/** (1) - Underutilized, needs growth or merge
- **meta/** (2) - Small but critical, acceptable
- **optimize/** (2) - Small, could merge with marketing

### 💡 Refactor Opportunities
1. **Split database/** into 3 categories:
   - `database-design/` - designing-database-schemas, comparing-database-schemas
   - `database-performance/` - analyzing-database-indexes, analyzing-query-performance, sql-optimization-patterns
   - `database-tooling/` - generating-* skills (7 skills)

2. **Merge marketing + optimize** → `seo-performance/` (5 skills total)
   - web-performance-audit
   - google-official-seo-guide
   - seo-content-optimizer

3. **Consider future categories** as project grows:
   - `testing/` - Test frameworks, TDD patterns (currently scattered)
   - `deployment/` - CI/CD, Docker, cloud deployment
   - `security/` - Security best practices, auth patterns

---

## Usage

**For Skills:** Reference this file when need context about categories or organization.

```markdown
**Quick Category Check:** `read .claude/global/SKILLS_CONTEXT.md`
**Full Skill Details:** `read .claude/skills/meta/choose-skill/references/skills-catalog.md`
```

**For Scripts:** Parse this file to get current structure.

**Update Frequency:** Auto-synced when skills are added/moved via `sync_skills_context.py`.

---

## Path Patterns

**Skill Locations:**
```
.claude/skills/{category}/{skill-name}/SKILL.md
```

**Examples:**
```
.claude/skills/filament/filament-rules/SKILL.md
.claude/skills/workflows/brainstorming/SKILL.md
.claude/skills/database/analyzing-database-indexes/SKILL.md
```

**SYSTEM.md Registration:**
```xml
<skill>
<name>skill-name</name>
<location>user/{category}</location>
</skill>
```

---

**Maintained by:** `sync_skills_context.py`  
**Source Repository:** `.claude/skills/`  
**Last Sync:** 2025-11-11 (Manual optimization - merged duplicate skills)

---

## Optimization Summary (v6.1)

**Before:** 57 skills  
**After:** 51 skills  
**Removed:** 10 duplicate/small skills merged into comprehensive guides

**Merged Skills:**
1. `api-design-principles` + `api-best-practices` → `api-design-patterns`
2. `analyzing-database-indexes` + `analyzing-query-performance` → `database-performance`
3. `generating-database-seed-data` + `generating-test-data` → `database-data-generation`
4. `scanning-database-security` + `validating-database-integrity` → `database-validation`
5. `generating-database-documentation` merged into `designing-database-schemas`

**Removed Skills:**
- `landing-page-guide` (too specific, not reusable)
- `skill-skeleton` (empty template, redundant)

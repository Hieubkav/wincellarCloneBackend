# 🤖 Agent Guidelines - Wincellar Clone

**Trả lời bằng tiếng việt**

## 🎯 System & Skills

**Foundation:** `read .claude/global/SYSTEM.md` - All project standards

**Skills auto-activate** via natural language (no explicit calls needed):

```
"Tạo resource mới cho Product"       → filament-resource-generator
"Class not found Tabs"                → filament-form-debugger
"Thêm gallery vào Article"           → image-management
"Chạy migration"                      → database-backup
"Tạo skill mới"                       → create-skill
"Package skill"                       → create-skill
"Validate skill"                      → create-skill
"Suggest category for skill"          → create-skill (intelligent grouping)
"Check skill organization"            → create-skill (refactor analysis)
"Skill này nên ở category nào?"      → create-skill (intelligent grouping)
"Refactor categories"                 → create-skill (refactor analysis)
"Skill nào phù hợp cho task này?"    → choose-skill
"Không biết dùng skill nào"          → choose-skill
"Recommend skills for X"              → choose-skill
"Phải Ctrl+F5 mới thấy data mới"    → api-cache-invalidation
"Tìm tài liệu cho Next.js"           → docs-seeker
"Bug này không fix được"              → systematic-debugging
"Test fail liên tục"                  → systematic-debugging
"Brainstorm ý tưởng"                  → brainstorming
"Thiết kế feature mới"                → brainstorming
"Refine requirements"                 → brainstorming
"Giải quyết vấn đề phức tạp"         → sequential-thinking
"Step-by-step reasoning"              → sequential-thinking
"Multi-stage analysis"                → sequential-thinking
"Viết implementation plan"            → writing-plans
"Tạo task breakdown"                  → writing-plans
"Create step-by-step guide"           → writing-plans
"Tạo controller mới"                  → backend-dev-guidelines
"Tạo component React"                 → frontend-dev-guidelines
"Thiết kế giao diện"                  → ux-designer
"Thêm shadcn component"               → ui-styling
"Tạo design system"                   → ui-styling
"Tìm kiếm sản phẩm"                   → product-search-scoring
"Thêm tính năng search"               → product-search-scoring
"Optimize search algorithm"           → product-search-scoring
"Viết document API"                   → api-documentation-writer
"Tạo API docs"                        → api-documentation-writer
"Generate API documentation"          → api-documentation-writer
"Tạo route mới"                       → laravel
"Eloquent relationship"               → laravel
"Laravel authentication"              → laravel
"Viết browser test"                   → laravel-dusk
"Test UI với Dusk"                    → laravel-dusk
"E2E testing"                         → laravel-dusk
"Tạo Artisan command"                 → laravel-prompts
"Interactive CLI prompt"              → laravel-prompts
"Laravel console command"             → laravel-prompts
"Optimize web performance"            → web-performance-audit
"Đo page speed"                       → web-performance-audit
"Core Web Vitals"                     → web-performance-audit
"Google SEO"                          → google-official-seo-guide
"Structured data VideoObject"         → google-official-seo-guide
"Search Console"                      → google-official-seo-guide
"Optimize content cho SEO"            → seo-content-optimizer
"Keyword analysis"                    → seo-content-optimizer
"Meta description optimization"       → seo-content-optimizer
"Design database schema"              → designing-database-schemas
"Generate ERD diagram"                → designing-database-schemas
"Document database schema"            → designing-database-schemas
"Optimize slow query"                 → database-performance
"Analyze database indexes"            → database-performance
"Query profiling"                     → database-performance
"Compare database schemas"            → comparing-database-schemas
"Generate migration script"           → comparing-database-schemas
"Generate ORM models"                 → generating-orm-code
"Create TypeORM entities"             → generating-orm-code
"Seed database"                       → database-data-generation
"Generate test data"                  → database-data-generation
"Database security scan"              → database-validation
"Validate database integrity"         → database-validation
"SQL optimization"                    → sql-optimization-patterns
"PostgreSQL queries"                  → databases
"MongoDB aggregation"                 → databases
"Tạo component React"                 → frontend-components
"Responsive design"                   → frontend-responsive
"Mobile-first layout"                 → frontend-responsive
"Next.js App Router"                  → nextjs
"Server Components"                   → nextjs
"React hooks pattern"                 → react-component-architecture
"Tailwind styling"                    → tailwind-css
"Dark mode Tailwind"                  → tailwind-css
"Design tokens"                       → ui-design-system
"Zustand state"                       → zustand-state-management
"Cache optimization"                  → cache-optimization
"E2E testing"                         → e2e-testing-patterns
"Playwright test"                     → playwright-automation
"Browser automation"                  → playwright-automation
"Quality verification"                → qa-verification
"API design patterns"                 → api-design-patterns
"REST API best practices"             → api-design-patterns
"GraphQL schema design"               → api-design-patterns
"Authentication patterns"             → auth-implementation-patterns
"JWT implementation"                  → auth-implementation-patterns
"Better Auth setup"                   → better-auth
"Cloudflare D1 auth"                  → better-auth
"FastAPI template"                    → fastapi-templates
"Code review"                         → code-review-excellence
"Git commit message"                  → git-commit-helper
"Package repository"                  → repomix
"Repomix analysis"                    → repomix
"Skill template"                      → skill-skeleton
```

```

## 📚 Skills (Organized by Category)

**filament/** - Filament 4.x (Laravel 12)
- filament-rules, filament-resource-generator, filament-form-debugger, image-management

**laravel/** - Laravel Framework & Tools
- laravel, laravel-dusk, laravel-prompts

**frontend/** - Frontend Development (NEW!)
- frontend-components, frontend-responsive, landing-page-guide, nextjs, react-component-architecture, tailwind-css, ui-design-system, zustand-state-management, cache-optimization

**testing/** - Testing & QA (NEW!)
- e2e-testing-patterns, playwright-automation, qa-verification

**fullstack/** - Full-Stack Development
- backend-dev-guidelines, frontend-dev-guidelines, ux-designer, ui-styling, auth-implementation-patterns, better-auth, fastapi-templates

**workflows/** - Development Workflows
- database-backup, systematic-debugging, product-search-scoring, docs-seeker, brainstorming, sequential-thinking, writing-plans, code-review-excellence, git-commit-helper, repomix

**api/** - API Design & Documentation
- api-design-patterns, api-cache-invalidation, api-documentation-writer

**meta/** - Skill Management
- create-skill (init, validate, package, intelligent grouping, refactor analysis), choose-skill

**optimize/** - Performance & SEO Optimization
- web-performance-audit, google-official-seo-guide

**marketing/** - Content & SEO Marketing
- seo-content-optimizer

**database/** - Database Management & Optimization
- databases, database-performance, database-data-generation, database-validation, designing-database-schemas, comparing-database-schemas, generating-orm-code, sql-optimization-patterns

**Access:** `read .claude/skills/[category]/[skill-name]/SKILL.md`
**Details:** Each skill < 200 lines, references/ for deep dive, scripts/ for automation

## ⚠️ Critical Rules

**API Changes:** Always update `docs/api/API_ENDPOINTS.md` + `resources/views/api-documentation.blade.php`

**Database:** Backup before migrate (`database-backup` skill)

**Filament:** Use `Schema` namespace NOT `Form` (`filament-rules` skill)

**Skills Changes:** ⚠️ **AUTO-SYNC REQUIRED!** After ANY skills changes (add/merge/remove):
1. Update `.claude/global/SKILLS_CONTEXT.md` (single source of truth)
2. **AUTO-RUN:** `python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py`
3. Verify `skills-catalog.md` updated
4. Commit ALL changes together

**Why?** Choose-skill meta-agent needs latest skills info to recommend correctly.

**Resources:**
- `PLAN.md` - Architecture decisions
- `mermaid.rb` - Database schema
- `.claude/global/SYSTEM.md` - All project standards
- `.claude/global/SKILLS_CONTEXT.md` - Skills catalog (single source of truth)

---

## 🔄 Auto-Sync Workflow for Skills Changes

**IMPORTANT:** When you (AI agent) make ANY skills changes:

### Step 1: Detect Skills Changes
```python
# If you just did:
- Created new skill folder
- Merged/deleted skills
- Updated SKILLS_CONTEXT.md
# → THEN auto-trigger sync!
```

### Step 2: Auto-Run Sync Script
```bash
# AI agent should AUTOMATICALLY run:
python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
```

### Step 3: Verify & Report
```
✅ Check output shows:
   - Updated skills-catalog.md
   - Updated recommendation-patterns.md
   - Summary matches expected counts
```

### Example Workflow:
```
User: "Gộp skill A và B thành skill C"

AI Agent actions:
1. Create new skill C (merge A+B content)
2. Delete skills A and B
3. Update SKILLS_CONTEXT.md
4. **AUTO-RUN:** sync_choose_skill.py  ← KEY!
5. Verify output
6. Report completion to user
```

**Why critical?** Choose-skill meta-agent reads `skills-catalog.md` to recommend skills. Without sync, it recommends outdated/deleted skills!

---

**Skills Architecture:**
- Organized into 11 categories (filament, laravel, frontend, testing, fullstack, workflows, api, meta, optimize, marketing, database)
- Each skill < 200 lines (SKILL.md = essentials, references/ = details, scripts/ = tools)
- Progressive disclosure for efficient context management
- **Auto-sync:** skills-catalog.md synced via `sync_choose_skill.py` after ANY skills changes

v6.1 | Updated: 2025-11-11 | 51/51 skills optimized & merged | OPTIMIZED: Merged 10 duplicate/small skills

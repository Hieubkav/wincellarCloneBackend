# 🤖 Hướng Dẫn Agent - Wincellar Clone

**Trả lời bằng tiếng Việt**

## 🎯 Hệ Thống & Skills

**Nền tảng:** `read .claude/global/SYSTEM.md` - Tất cả các tiêu chuẩn dự án

**Skills tự động kích hoạt** qua ngôn ngữ tự nhiên (không cần gọi rõ ràng):

```
"Tạo resource mới cho Product"       → filament-resource-generator
"Class not found Tabs"                → filament-form-debugger
"Thêm gallery vào Article"           → image-management
"Chạy migration"                      → database-backup
"Tạo skill mới"                       → create-skill
"Package skill"                       → create-skill
"Validate skill"                      → create-skill
"Gợi ý category cho skill"            → create-skill (phân loại thông minh)
"Kiểm tra tổ chức skill"              → create-skill (phân tích tái cấu trúc)
"Skill này nên ở category nào?"      → create-skill (phân loại thông minh)
"Tái cấu trúc categories"             → create-skill (phân tích tái cấu trúc)
"Skill nào phù hợp cho task này?"    → choose-skill
"Không biết dùng skill nào"          → choose-skill
"Gợi ý skills cho X"                  → choose-skill
"Phải Ctrl+F5 mới thấy data mới"    → api-cache-invalidation
"Tìm tài liệu cho Next.js"           → docs-seeker
"Bug này không fix được"              → systematic-debugging
"Test fail liên tục"                  → systematic-debugging
"Brainstorm ý tưởng"                  → brainstorming
"Thiết kế feature mới"                → brainstorming
"Uốn nắn requirements"                → brainstorming
"Giải quyết vấn đề phức tạp"         → sequential-thinking
"Suy luận từng bước"                  → sequential-thinking
"Phân tích đa giai đoạn"              → sequential-thinking
"Viết implementation plan"            → writing-plans
"Tạo task breakdown"                  → writing-plans
"Tạo hướng dẫn từng bước"             → writing-plans
"Tạo controller mới"                  → backend-dev-guidelines
"Tạo component React"                 → frontend-dev-guidelines
"Thiết kế giao diện"                  → ux-designer
"Thêm shadcn component"               → ui-styling
"Tạo design system"                   → ui-styling
"Tìm kiếm sản phẩm"                   → product-search-scoring
"Thêm tính năng search"               → product-search-scoring
"Tối ưu thuật toán search"            → product-search-scoring
"Viết document API"                   → api-documentation-writer
"Tạo API docs"                        → api-documentation-writer
"Tạo tài liệu API"                    → api-documentation-writer
"Tạo route mới"                       → laravel
"Eloquent relationship"               → laravel
"Xác thực Laravel"                    → laravel
"Viết browser test"                   → laravel-dusk
"Test UI với Dusk"                    → laravel-dusk
"E2E testing"                         → laravel-dusk
"Tạo Artisan command"                 → laravel-prompts
"Tương tác CLI prompt"                → laravel-prompts
"Lệnh console Laravel"                → laravel-prompts
"Tối ưu web performance"              → web-performance-audit
"Đo page speed"                       → web-performance-audit
"Core Web Vitals"                     → web-performance-audit
"Google SEO"                          → google-official-seo-guide
"Structured data VideoObject"         → google-official-seo-guide
"Search Console"                      → google-official-seo-guide
"Tối ưu nội dung cho SEO"             → seo-content-optimizer
"Phân tích từ khóa"                   → seo-content-optimizer
"Tối ưu meta description"             → seo-content-optimizer
"Thiết kế database schema"            → designing-database-schemas
"Tạo biểu đồ ERD"                     → designing-database-schemas
"Tài liệu database schema"            → designing-database-schemas
"Tối ưu slow query"                   → database-performance
"Phân tích database indexes"           → database-performance
"Query profiling"                     → database-performance
"So sánh database schemas"            → comparing-database-schemas
"Tạo migration script"                → comparing-database-schemas
"Tạo ORM models"                      → generating-orm-code
"Tạo TypeORM entities"                → generating-orm-code
"Seed database"                       → database-data-generation
"Tạo test data"                       → database-data-generation
"Quét bảo mật database"               → database-validation
"Kiểm tra tính toàn vẹn database"     → database-validation
"Tối ưu SQL"                          → sql-optimization-patterns
"PostgreSQL queries"                  → databases
"MongoDB aggregation"                 → databases
"Tạo component React"                 → frontend-components
"Thiết kế responsive"                 → frontend-responsive
"Mobile-first layout"                 → frontend-responsive
"Next.js App Router"                  → nextjs
"Server Components"                   → nextjs
"React hooks pattern"                 → react-component-architecture
"Tailwind styling"                    → tailwind-css
"Dark mode Tailwind"                  → tailwind-css
"Design tokens"                       → ui-design-system
"Zustand state"                       → zustand-state-management
"Tối ưu cache"                        → cache-optimization
"E2E testing"                         → e2e-testing-patterns
"Playwright test"                     → playwright-automation
"Browser automation"                  → playwright-automation
"Kiểm tra chất lượng"                 → qa-verification
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

## 📚 Skills (Tổ chức theo Danh Mục)

**filament/** - Filament 4.x (Laravel 12)
- filament-rules, filament-resource-generator, filament-form-debugger, image-management

**laravel/** - Laravel Framework & Công Cụ
- laravel, laravel-dusk, laravel-prompts

**frontend/** - Frontend Development (MỚI!)
- frontend-components, frontend-responsive, landing-page-guide, nextjs, react-component-architecture, tailwind-css, ui-design-system, zustand-state-management, cache-optimization

**testing/** - Testing & QA (MỚI!)
- e2e-testing-patterns, playwright-automation, qa-verification

**fullstack/** - Full-Stack Development
- backend-dev-guidelines, frontend-dev-guidelines, ux-designer, ui-styling, auth-implementation-patterns, better-auth, fastapi-templates

**workflows/** - Development Workflows
- database-backup, systematic-debugging, product-search-scoring, docs-seeker, brainstorming, sequential-thinking, writing-plans, code-review-excellence, git-commit-helper, repomix

**api/** - API Design & Tài Liệu
- api-design-patterns, api-cache-invalidation, api-documentation-writer

**meta/** - Quản Lý Skills
- create-skill (init, validate, package, intelligent grouping, refactor analysis), choose-skill

**optimize/** - Tối Ưu Hiệu Suất & SEO
- web-performance-audit, google-official-seo-guide

**marketing/** - Content & SEO Marketing
- seo-content-optimizer

**database/** - Quản Lý & Tối Ưu Database
- databases, database-performance, database-data-generation, database-validation, designing-database-schemas, comparing-database-schemas, generating-orm-code, sql-optimization-patterns

**Truy cập:** `read .claude/skills/[category]/[skill-name]/SKILL.md`
**Chi tiết:** Mỗi skill < 200 dòng, references/ cho deep dive, scripts/ cho automation

## ⚠️ Quy Tắc Quan Trọng

**API Changes:** Luôn cập nhật `docs/api/API_ENDPOINTS.md` + `resources/views/api-documentation.blade.php`

**Database:** Backup trước khi chạy migration (`database-backup` skill)

**Filament:** Dùng namespace `Schema` KHÔNG PHẢI `Form` (`filament-rules` skill)

**Skills Changes:** ⚠️ **AUTO-SYNC BẮT BUỘC!** Sau BẤT KỲ thay đổi skills nào (add/merge/remove):
1. Cập nhật `.claude/global/SKILLS_CONTEXT.md` (single source of truth)
2. **AUTO-RUN:** `python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py`
3. Kiểm tra `skills-catalog.md` đã cập nhật
4. Commit TẤT CẢ changes cùng lúc

**Tại sao?** Meta-agent choose-skill cần thông tin skills mới nhất để gợi ý đúng.

**Tài liệu tham khảo:**
- `PLAN.md` - Quyết định kiến trúc
- `mermaid.rb` - Sơ đồ database
- `.claude/global/SYSTEM.md` - Tất cả tiêu chuẩn dự án
- `.claude/global/SKILLS_CONTEXT.md` - Danh mục skills (single source of truth)

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

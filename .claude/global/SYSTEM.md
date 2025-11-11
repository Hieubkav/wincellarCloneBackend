# Wincellar Clone - Laravel 12 + Filament 4.x Project

**Coding Agent Guidelines for AI Assistants**

Trả lời bằng tiếng việt (Always respond in Vietnamese)

---

## 🎯 Project Overview

**Dự án:** Wincellar Clone - E-commerce platform for wine products
**Stack:** Laravel 12.x, Filament 4.x, MySQL/MariaDB
**Location:** E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend

---

## 📚 Available Skills

<available_skills>

<skill>
<name>create-skill</name>
<description>Guide for creating effective skills with automation tools (init_skill.py, package_skill.py, quick_validate.py) and bundled resources (scripts/, references/, assets/). Includes distribution via .zip files and progressive disclosure principles. USE WHEN user says 'tạo skill mới', 'create new skill', 'add skill for', 'package skill', 'validate skill', or wants to extend capabilities with specialized workflows, tool integrations, or bundled resources.</description>
<location>user/meta</location>
</skill>

<skill>
<name>choose-skill</name>
<description>Meta-agent that analyzes tasks and recommends optimal skill combinations with Feynman-style explanations. READ-ONLY analyzer that NEVER modifies code, only provides recommendations. USE WHEN feeling overwhelmed by 34+ skills, uncertain which skills to apply, need guidance on skill orchestration patterns (sequential/parallel/conditional), want to understand skill synergies, or need help choosing the right skills for a task. Returns 1-3 combo recommendations with simple Vietnamese explanations.</description>
<location>user/meta</location>
</skill>

<skill>
<name>filament-rules</name>
<description>Filament 4.x coding standards for Laravel 12 project with custom Schema namespace (not Form), Vietnamese UI, Observer patterns, Image management. USE WHEN creating Filament resources, fixing namespace errors (Class not found), implementing forms, RelationManagers, or any Filament development task.</description>
<location>user/filament</location>
</skill>

<skill>
<name>image-management</name>
<description>Centralized polymorphic image management system with CheckboxList picker, WebP auto-conversion, order management, soft deletes. USE WHEN adding images/gallery to models, implementing image upload, working with ImagesRelationManager, or troubleshooting image-related issues.</description>
<location>user/filament</location>
</skill>

<skill>
<name>database-backup</name>
<description>Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema. USE WHEN creating migrations, running migrations, restoring database, or managing database schema changes.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>filament-resource-generator</name>
<description>Automated Filament resource generation with correct namespace imports, Vietnamese labels, standard structure, and best practices. USE WHEN user says 'tạo resource mới', 'create new resource', 'generate Filament resource', 'scaffold admin resource'.</description>
<location>user/filament</location>
</skill>

<skill>
<name>filament-form-debugger</name>
<description>Diagnose and fix common Filament form errors (namespace issues, class not found, type mismatch, argument errors). USE WHEN encountering 'Class not found', 'Argument must be of type', 'Trait not found', or any Filament-related errors.</description>
<location>user/filament</location>
</skill>

<skill>
<name>api-design-principles</name>
<description>Master REST and GraphQL API design principles to build intuitive, scalable, and maintainable APIs that delight developers. USE WHEN designing new APIs, reviewing API specifications, establishing API design standards, implementing RESTful endpoints, or working with API architecture.</description>
<location>user/api</location>
</skill>

<skill>
<name>api-cache-invalidation</name>
<description>Automatic cache invalidation system với Laravel Observers và Next.js On-Demand Revalidation. Tự động sync data real-time giữa backend và frontend khi admin update. USE WHEN user phàn nàn "phải Ctrl+F5 mới thấy data mới", cần setup cache management, sync frontend-backend, hoặc optimize API performance với ISR.</description>
<location>user/api</location>
</skill>

<skill>
<name>docs-seeker</name>
<description>Searching internet for technical documentation using llms.txt standard, GitHub repositories via Repomix, and parallel exploration. USE WHEN user needs latest documentation for libraries/frameworks, documentation in llms.txt format, GitHub repository analysis, or comprehensive documentation discovery across multiple sources.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>systematic-debugging</name>
<description>Four-phase systematic debugging framework that mandates root cause investigation before fixes. STOP random fixes and symptom patches. USE WHEN encountering bugs, test failures, unexpected behavior, errors, or when fixes fail repeatedly. ESPECIALLY USE when under time pressure or tempted to 'quick fix'.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>backend-dev-guidelines</name>
<description>Comprehensive backend development guide for Node.js/Express/TypeScript microservices. Use when creating routes, controllers, services, repositories, middleware, or working with Express APIs, Prisma database access, Sentry error tracking, Zod validation, unifiedConfig, dependency injection, or async patterns. Covers layered architecture (routes → controllers → services → repositories), BaseController pattern, error handling, performance monitoring, testing strategies, and migration from legacy patterns.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>frontend-dev-guidelines</name>
<description>Frontend development guidelines for React/TypeScript applications. Modern patterns including Suspense, lazy loading, useSuspenseQuery, file organization with features directory, MUI v7 styling, TanStack Router, performance optimization, and TypeScript best practices. Use when creating components, pages, features, fetching data, styling, routing, or working with frontend code.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>ux-designer</name>
<description>Expert UI/UX design guidance for building unique, accessible, and user-centered interfaces. Use when designing interfaces, making visual design decisions, choosing colors/typography, implementing responsive layouts, or when user mentions design, UI, UX, styling, or visual appearance. Always ask before making design decisions.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>ui-styling</name>
<description>Create beautiful, accessible user interfaces with shadcn/ui components (Radix UI + Tailwind CSS), canvas-based visual designs, and responsive layouts. USE WHEN building user interfaces, implementing design systems, adding accessible components (dialogs, dropdowns, forms, tables), customizing themes/colors, implementing dark mode, generating visual designs/posters, or establishing consistent styling patterns.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>product-search-scoring</name>
<description>Advanced product search system with keyword scoring, Vietnamese text normalization, multi-field matching, and search result ranking. Multi-layer system: text normalization (Vietnamese accents), keyword processing (stop word filtering), query building with filters, and caching strategy. USE WHEN implementing search functionality, adding keyword scoring to products, optimizing search algorithm, improving search relevance, handling Vietnamese text with accents, or building e-commerce search features.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>api-documentation-writer</name>
<description>Generate comprehensive API documentation for REST, GraphQL, WebSocket APIs with OpenAPI specs, endpoint descriptions, request/response examples, error codes, authentication guides, and SDKs. Developer-friendly reference materials. USE WHEN user says 'viết document API', 'tạo API docs', 'generate API documentation', 'document REST endpoints', hoặc cần tạo technical reference cho developers.</description>
<location>user/api</location>
</skill>

<skill>
<name>laravel</name>
<description>Laravel v12 - The PHP Framework For Web Artisans. Comprehensive assistance with routing, Eloquent ORM, migrations, authentication, API development, modern PHP patterns, relationships, middleware, service providers, queues, cache, validation, Laravel Sanctum/Passport. USE WHEN building Laravel applications/APIs, working with Eloquent models, creating migrations/seeders/factories, implementing authentication/authorization, troubleshooting Laravel errors, or following Laravel best practices.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>laravel-dusk</name>
<description>Laravel Dusk - Browser automation and testing API for Laravel applications. Comprehensive assistance with writing browser tests, automating UI testing, testing JavaScript interactions, implementing end-to-end tests, using Page Object pattern, configuring ChromeDriver, waiting for JavaScript events. USE WHEN writing/debugging browser tests, testing user interfaces, implementing E2E testing workflows, working with form submissions/authentication flows, or troubleshooting browser test failures/timing issues.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>laravel-prompts</name>
<description>Laravel Prompts - Beautiful and user-friendly forms for command-line applications with browser-like features including placeholder text and validation. Comprehensive assistance with building interactive Artisan commands, text input, select menus, confirmation dialogs, progress bars, loading spinners, tables in CLI. USE WHEN building Laravel Artisan commands with interactive prompts, creating CLI applications in PHP, implementing form validation in command-line tools, or testing console commands with prompts.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>web-performance-audit</name>
<description>Conduct comprehensive web performance audits measuring Core Web Vitals (LCP, FID, CLS), page speed, bottleneck identification, and optimization recommendations. Includes performance metrics analysis, optimization strategies (quick wins, medium effort, long-term), monitoring setup, and performance budgets. USE WHEN optimizing web performance, improving page speed, analyzing Core Web Vitals, setting up performance monitoring, identifying performance bottlenecks, or implementing performance improvements.</description>
<location>user/optimize</location>
</skill>

<skill>
<name>google-official-seo-guide</name>
<description>Official Google SEO guide covering search optimization, Search Console, crawling/indexing, structured data (VideoObject, BroadcastEvent, Clip), mobile-first indexing, internationalization, and search visibility improvements. Comprehensive reference files for appearance, crawling, fundamentals, guides, indexing, and specialty topics. USE WHEN implementing SEO best practices, adding structured data, optimizing for Google Search, fixing crawling/indexing issues, implementing schema.org markup, or improving search visibility.</description>
<location>user/optimize</location>
</skill>

<skill>
<name>seo-content-optimizer</name>
<description>Optimize content for search engines with keyword analysis, readability scoring (Flesch Reading Ease), meta descriptions generation, content structure evaluation, and competitor comparison. Provides actionable SEO recommendations prioritized by impact. USE WHEN optimizing blog posts/articles for SEO, analyzing keyword density, improving content readability, generating meta tags, identifying content gaps, or improving search rankings.</description>
<location>user/marketing</location>
</skill>

<skill>
<name>databases</name>
<description>Work with MongoDB (document database, BSON documents, aggregation pipelines, Atlas cloud) and PostgreSQL (relational database, SQL queries, psql CLI, pgAdmin). USE WHEN designing database schemas, writing queries and aggregations, optimizing indexes for performance, performing database migrations, configuring replication and sharding, implementing backup and restore strategies, managing database users and permissions, analyzing query performance, or administering production databases.</description>
<location>user/database</location>
</skill>

<skill>
<name>analyzing-database-indexes</name>
<description>Analyze query patterns and recommend optimal database indexes using database-index-advisor plugin. Identifies missing indexes to improve query performance and unused indexes for removal. USE WHEN optimizing slow queries, finding missing indexes, removing unused indexes, or implementing database index optimization strategies.</description>
<location>user/database</location>
</skill>

<skill>
<name>analyzing-query-performance</name>
<description>Analyze and optimize database query performance using query-performance-analyzer plugin. Interprets EXPLAIN plans, identifies performance bottlenecks (slow queries, missing indexes), and suggests specific optimization strategies. USE WHEN analyzing EXPLAIN plans, debugging slow queries, identifying performance bottlenecks, or improving database query execution speed and resource utilization.</description>
<location>user/database</location>
</skill>

<skill>
<name>comparing-database-schemas</name>
<description>Compare database schemas, generate migration scripts, and provide rollback procedures using database-diff-tool plugin. Supports PostgreSQL and MySQL. USE WHEN comparing database schemas across environments, generating migration scripts, creating rollback procedures, synchronizing database schemas, or validating changes before deployment.</description>
<location>user/database</location>
</skill>

<skill>
<name>designing-database-schemas</name>
<description>Design and visualize database schemas with normalization guidance (1NF through BCNF), relationship mapping, and ERD generation. USE WHEN designing new database schemas, creating database models, generating ERD diagrams, normalizing databases, or implementing database design best practices.</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-database-documentation</name>
<description>Automatically generate comprehensive documentation for existing database schemas using database-documentation-gen plugin. Includes ERD diagrams, table relationships, column descriptions, indexes, triggers, stored procedures, and interactive HTML documentation. USE WHEN documenting database schemas for team onboarding, architectural reviews, data governance, or creating data dictionaries.</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-database-seed-data</name>
<description>Generate realistic test data and database seed scripts using Faker libraries. Maintains relational integrity and allows configurable data volumes. USE WHEN seeding databases, generating test data, creating seed scripts, populating databases with realistic data for development, testing, or demonstration purposes.</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-orm-code</name>
<description>Generate ORM models and database schemas for various ORMs (TypeORM, Prisma, Sequelize, SQLAlchemy, Django ORM, Entity Framework, Hibernate). Supports both database-to-code and code-to-database schema generation. USE WHEN creating ORM models, generating database schemas, creating entities, generating migrations, or working with specific ORM frameworks.</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-test-data</name>
<description>Generate realistic test data using test-data-generator plugin for users, products, orders, and custom schemas. Useful for populating testing environments or creating sample data for demonstrations. USE WHEN generating test data, creating fake users, populating databases, generating product/order data, or generating data based on custom schemas.</description>
<location>user/database</location>
</skill>

<skill>
<name>scanning-database-security</name>
<description>Perform comprehensive database security scans using database-security-scanner plugin with OWASP guidelines. Identifies vulnerabilities like weak passwords, SQL injection risks, and insecure configurations. Supports PostgreSQL and MySQL. USE WHEN assessing database security, checking for vulnerabilities, performing OWASP compliance checks, or improving database security posture.</description>
<location>user/database</location>
</skill>

<skill>
<name>sql-optimization-patterns</name>
<description>Master SQL query optimization, indexing strategies, and EXPLAIN analysis to dramatically improve database performance and eliminate slow queries. USE WHEN debugging slow queries, designing database schemas, optimizing application performance, or implementing SQL optimization best practices.</description>
<location>user/database</location>
</skill>

<skill>
<name>validating-database-integrity</name>
<description>Ensure database integrity using data-validation-engine plugin. Automatically validates data types, ranges, formats, referential integrity, and business rules. Supports multi-database environments and production-ready implementations. USE WHEN implementing data validation, enforcing constraints, improving data quality, or validating data input within applications.</description>
<location>user/database</location>
</skill>

</available_skills>

---

## 🔧 Core Principles

### 1. Code Quality
- Không để logic hoặc file quá 500 dòng
- Chia logic hợp lý, kế thừa đúng cách
- Tham khảo PLAN.md để hiểu dự án

### 2. Filament 4.x Standards
- **CRITICAL**: Dự án dùng `Schema` thay vì `Form`
- Layout components → `Filament\Schemas\Components\*`
- Form fields → `Filament\Forms\Components\*`
- Get utility → `Filament\Schemas\Components\Utilities\Get`
- **NEVER** use Alpine.js custom code (use built-in components)

### 3. Database Management
- **ALWAYS** backup before migration: `php artisan backup:run --only-db`
- Update `mermaid.rb` khi tạo/sửa migration
- Giữ tối đa 10 bản backup gần nhất

### 4. Vietnamese First
- Tất cả labels, messages phải tiếng Việt
- Date format: `d/m/Y H:i` (31/12/2024 14:30)
- Exception: Code, comments, commit messages (English OK)

---

## 🚨 Critical Coding Standards

### Test/Debug Files Policy

**RULE: Test files belong in /tests, cleanup immediately**

**Correct placement:**
```bash
# ✅ ALWAYS put in /tests directory
tests/Feature/CheckSomethingTest.php
tests/Unit/FeatureTest.php
tests/Debug/DebugIssueTest.php

# ❌ NEVER in project root
check_something.php  # Wrong!
test_feature.php     # Wrong!
```

**Process:**
1. Create test file → ONLY in `/tests` directory
2. Run test & verify
3. **DELETE immediately after use**
4. Document findings in `/docs` if needed

**Quick cleanup:**
```powershell
# Remove any test files accidentally created in root
Get-ChildItem -Filter "*test*.php","*check*.php","*debug*.php","*fix*.php" | 
    Where-Object { $_.DirectoryName -notmatch "\\tests\\?" } | 
    Remove-Item -Force
```

### Documentation Organization

**RULE: Tổ chức docs theo chuyên đề, không để rải rác**

```
/docs
├── /setup/              # Initial setup guides
├── /architecture/       # System design & database schema
├── /phases/             # Development history
├── /api/                # API documentation
├── /database/           # Database docs
├── /features/           # Feature documentation
├── /features-detailed/  # Deep-dive feature docs
└── /deprecated/         # Outdated documentation
```

**Principles:**
- New features → `/docs/[topic]/*.md`
- Setup guides → `/docs/setup/`
- Architecture → `/docs/architecture/`
- Outdated docs → `/docs/deprecated/` or delete

---

## 🗂️ Project Structure

```
E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend\
├── .claude/
│   ├── global/
│   │   └── SYSTEM.md              # This file
│   └── skills/
│       ├── create-skill/          # Skill creation framework
│       ├── filament-rules/        # Filament coding standards
│       ├── image-management/      # Image system guide
│       ├── database-backup/       # Backup workflow
│       ├── filament-resource-generator/
│       └── filament-form-debugger/
├── docs/                          # Legacy docs (will be deprecated)
├── app/
│   ├── Filament/Resources/
│   ├── Models/
│   └── Observers/
├── database/
│   ├── migrations/
│   └── backups/
├── AGENTS.md                      # Legacy (now references .claude/)
├── PLAN.md                        # Project roadmap
└── mermaid.rb                     # Database schema
```

---

## 📖 How to Use Skills

Skills are **automatically activated** when you request relevant tasks using natural language.

**Examples:**

```
User: "Tạo resource mới cho Product"
→ Activates: filament-resource-generator

User: "Class not found Tabs"
→ Activates: filament-form-debugger

User: "Thêm gallery ảnh vào Article"
→ Activates: image-management

User: "Chạy migration mới"
→ Activates: database-backup

User: "Tạo skill cho AI Agent"
→ Activates: create-skill
```

You **don't need** to explicitly say "use skill X" - I will automatically detect and activate the relevant skill based on your request.

---

## 🚀 Quick Reference

### Common Commands
```bash
# Development
php artisan serve
npm run dev

# Database
php artisan backup:run --only-db
php artisan migrate
php artisan db:seed

# Filament
php artisan make:filament-resource ResourceName
```

### Important Files
- **Skills**: `.claude/skills/[skill-name]/SKILL.md`
- **Deep docs**: `.claude/skills/[skill-name]/CLAUDE.md`
- **Project plan**: `PLAN.md`
- **Database schema**: `mermaid.rb`

---

## 🎯 Workflow Examples

### Create New Filament Resource
1. Request: "Tạo resource mới cho Category"
2. I activate `filament-resource-generator` skill
3. Generate resource with correct namespaces, Vietnamese labels
4. Add ImagesRelationManager if needed
5. Create Observer for SEO fields
6. Test and verify

### Add Image Gallery to Model
1. Request: "Thêm gallery vào Product"
2. I activate `image-management` skill
3. Add morphMany relationship
4. Create ImagesRelationManager
5. Implement CheckboxList picker
6. Test upload and ordering

### Run Database Migration
1. Request: "Chạy migration X"
2. I activate `database-backup` skill
3. Backup database first
4. Run migration
5. Update mermaid.rb
6. Verify success

---

## 💡 Key Principles Reminder

1. **Progressive Disclosure**: Skills load context as needed (SKILL.md → CLAUDE.md)
2. **No Duplication**: Reference this global context, don't copy
3. **Vietnamese First**: UI must be 100% Vietnamese
4. **Backup First**: Always backup before risky operations
5. **Standards Compliance**: Follow Filament 4.x patterns
6. **Living Documents**: Skills are updated as we learn

---

## 🔗 Legacy References

**Old system (being deprecated):**
- `AGENTS.md` → Now references `.claude/` structure
- `docs/filament/` → Migrated to `.claude/skills/filament-rules/`
- `docs/IMAGE_MANAGEMENT.md` → `.claude/skills/image-management/`
- `docs/spatie_backup.md` → `.claude/skills/database-backup/`

**Use new skill-based system for all future work.**

---

**Last Updated:** 2025-11-09  
**System Version:** 2.0 (Skill-based architecture)

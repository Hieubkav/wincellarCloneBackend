# Wincellar Clone - Laravel 12 + Filament 4.x Project

**Coding Agent Guidelines for AI Assistants**

Trả lời bằng tiếng việt (Always respond in Vietnamese)

---

## 🎯 Project Overview

**Dự án:** Wincellar Clone - E-commerce platform for wine products
**Stack:** Laravel 12.x, Filament 4.x, MySQL/MariaDB
**Location:** E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend

**⚠️ CRITICAL PROTOCOLS:** `read .claude/global/AI_AGENT_REMINDERS.md` before making skills changes!

---

## ⚠️ CRITICAL: Skills Auto-Sync Protocol

**FOR AI AGENTS:** After ANY skills changes, you MUST auto-run sync script!

### When to Auto-Sync:
```
IF you just did ANY of these:
  ✓ Created new skill (e.g., new folder in .claude/skills/)
  ✓ Merged skills (deleted old, created new merged skill)
  ✓ Deleted/removed skills
  ✓ Updated SKILLS_CONTEXT.md
THEN:
  → IMMEDIATELY run: python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
  → Verify output shows updated counts
  → Include sync results in your response to user
```

### Why Critical?
- `choose-skill` meta-agent reads `skills-catalog.md` for recommendations
- Without sync → recommends deleted/outdated skills → BREAKS workflow
- Sync keeps choose-skill intelligent and accurate

### Example:
```
User: "Gộp skill A và B"
AI: 
1. Merge skills ✓
2. Update SKILLS_CONTEXT.md ✓
3. AUTO-RUN sync script ✓  ← DON'T FORGET!
4. Report: "Đã gộp và sync choose-skill"
```

---

## 📚 Available Skills

<available_skills>

<skill>
<name>create-skill</name>
<description>Guide for creating effective skills with intelligent category placement, automation tools (init_skill.py, suggest_skill_group.py, sync_to_choose_skill.py), and bundled resources (scripts/, references/, assets/). NEW: AI-powered grouping intelligence analyzes skill domains, suggests optimal categories with confidence scores, detects new category opportunities (3+ related skills), and identifies refactor needs (overcrowded/underutilized categories). Prevents category sprawl and maintains optimal organization. USE WHEN user says 'tạo skill mới', 'suggest category for skill', 'check skill organization', 'refactor categories', or wants to extend capabilities with specialized workflows.</description>
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
<name>api-design-patterns</name>
<description>Comprehensive REST and GraphQL API design patterns, best practices, OpenAPI specifications, versioning, authentication, error handling, pagination, rate limiting, and security. USE WHEN designing APIs, creating endpoints, reviewing specifications, implementing authentication, building scalable backend services, or establishing API standards. (Merged from api-design-principles + api-best-practices)</description>
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
<name>brainstorming</name>
<description>Use when creating or developing ideas, before writing code or implementation plans - refines rough ideas into fully-formed designs through collaborative questioning, alternative exploration, and incremental validation. Ask questions one at a time, explore 2-3 approaches with trade-offs, present design in sections (200-300 words), and validate incrementally. Document validated designs to docs/plans/. USE WHEN turning rough ideas into designs, planning new features, exploring architecture options, before implementation, or when user needs help refining requirements. Don't use during clear mechanical processes.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>sequential-thinking</name>
<description>Use when complex problems require systematic step-by-step reasoning with ability to revise thoughts, branch into alternative approaches, or dynamically adjust scope. Enables iterative reasoning, revision tracking, branch exploration, and maintained context throughout analysis. Ideal for multi-stage analysis, design planning, problem decomposition, or tasks with initially unclear scope. USE WHEN problem requires multiple interconnected reasoning steps, initial scope is uncertain, need to filter through complexity, may need to backtrack or revise conclusions, or want to explore alternative solution paths. Don't use for simple queries or single-step tasks.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>writing-plans</name>
<description>Use when design is complete and you need detailed implementation tasks for engineers with zero codebase context - creates comprehensive implementation plans with exact file paths, complete code examples, and verification steps assuming engineer has minimal domain knowledge. Write bite-sized tasks (2-5 min each), include exact commands with expected output, follow TDD/DRY/YAGNI principles, and save plans to docs/plans/. USE WHEN creating implementation plans, breaking down features into tasks, documenting step-by-step instructions, after design/brainstorming phase, or when user needs detailed execution guide. Offer execution choice: subagent-driven or parallel session.</description>
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
<name>database-performance</name>
<description>Analyze and optimize database performance through index analysis and query profiling. Identify missing/unused indexes, interpret EXPLAIN plans, find bottlenecks, and recommend optimization strategies. USE WHEN optimizing slow queries, analyzing database workloads, improving query execution speed, or managing database indexes. (Merged from analyzing-database-indexes + analyzing-query-performance)</description>
<location>user/database</location>
</skill>

<skill>
<name>comparing-database-schemas</name>
<description>Compare database schemas, generate migration scripts, and provide rollback procedures using database-diff-tool plugin. Supports PostgreSQL and MySQL. USE WHEN comparing database schemas across environments, generating migration scripts, creating rollback procedures, synchronizing database schemas, or validating changes before deployment.</description>
<location>user/database</location>
</skill>

<skill>
<name>designing-database-schemas</name>
<description>Design, visualize, and document database schemas with ERD generation, normalization guidance (1NF-BCNF), relationship mapping, and automated documentation. Create efficient database structures, generate SQL statements, produce interactive HTML docs, and maintain data dictionaries. USE WHEN designing schemas, creating database models, generating ERD diagrams, normalizing databases, or documenting existing databases. (Includes database documentation generation)</description>
<location>user/database</location>
</skill>

<skill>
<name>database-data-generation</name>
<description>Generate realistic database seed data and test fixtures for development, testing, and demonstrations. Creates realistic users, products, orders, and custom schemas using Faker libraries while maintaining relational integrity and data consistency. USE WHEN populating databases, creating test fixtures, seeding development environments, or generating demo data. (Merged from generating-database-seed-data + generating-test-data)</description>
<location>user/database</location>
</skill>

<skill>
<name>database-validation</name>
<description>Comprehensive database security scanning and data integrity validation. Identify security vulnerabilities, enforce OWASP compliance, validate data types/formats/ranges, ensure referential integrity, and implement business rules. USE WHEN assessing database security, checking compliance, validating data integrity, or enforcing constraints. (Merged from scanning-database-security + validating-database-integrity)</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-orm-code</name>
<description>Generate ORM models and database schemas for various ORMs (TypeORM, Prisma, Sequelize, SQLAlchemy, Django ORM, Entity Framework, Hibernate). Supports both database-to-code and code-to-database schema generation. USE WHEN creating ORM models, generating database schemas, creating entities, generating migrations, or working with specific ORM frameworks.</description>
<location>user/database</location>
</skill>

<skill>
<name>sql-optimization-patterns</name>
<description>Master SQL query optimization, indexing strategies, and EXPLAIN analysis to dramatically improve database performance and eliminate slow queries. USE WHEN debugging slow queries, designing database schemas, optimizing application performance, or implementing SQL optimization best practices.</description>
<location>user/database</location>
</skill>



<!-- NEW FRONTEND SKILLS -->

<skill>
<name>frontend-components</name>
<description>Design reusable, composable UI components following single responsibility principle with clear interfaces, encapsulation, and minimal props. USE WHEN creating or modifying component files (.jsx, .tsx, .vue, .svelte), defining component props/interfaces, implementing composition patterns, managing component-level state, creating reusable UI elements (buttons, forms, cards, modals), documenting component APIs, or refactoring components for better reusability.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>frontend-responsive</name>
<description>Build responsive, mobile-first layouts using fluid containers, flexible units, media queries, and touch-friendly design. USE WHEN creating layouts for mobile/tablet/desktop, implementing mobile-first design, writing media queries/breakpoints, using flexible units (rem, em, %), implementing fluid layouts with flexbox/grid, ensuring touch targets meet 44x44px minimum, optimizing images for different screens, or testing UI across multiple device sizes.</description>
<location>user/frontend</location>
</skill>



<skill>
<name>nextjs</name>
<description>Next.js 16 App Router patterns: Server Components, Server Actions, Cache Components with "use cache", async params/searchParams, proxy.ts (replaces middleware.ts), React 19.2, Metadata API, Turbopack. Covers breaking changes, hydration fixes, performance optimization, TypeScript configuration. USE WHEN building Next.js apps, implementing Server Components/Actions, handling SSR/hydration, using App Router, or troubleshooting Next.js 16 issues.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>react-component-architecture</name>
<description>Design scalable React components using functional components, hooks, composition patterns, and TypeScript. Covers custom hooks, HOCs, render props, compound components, and performance optimization. USE WHEN building component libraries, designing reusable UI patterns, creating custom hooks, implementing component composition, or optimizing React performance.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>tailwind-css</name>
<description>Utility-first CSS framework for rapid UI development with responsive design, dark mode, component patterns, and production optimization. Covers core utilities, breakpoints, state variants, theme customization, and best practices. USE WHEN styling with Tailwind, implementing responsive designs, customizing themes, extracting components, or optimizing Tailwind for production.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>ui-design-system</name>
<description>UI design system toolkit for generating design tokens (colors, typography, spacing), component documentation, responsive calculations, and developer handoff. Includes design_token_generator.py script. USE WHEN creating design systems, maintaining visual consistency, generating design tokens, or facilitating design-dev collaboration.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>zustand-state-management</name>
<description>Production-ready Zustand state management for React with TypeScript, persist middleware, devtools, slices pattern, and Next.js SSR hydration. Prevents 5 documented issues: hydration mismatches, TypeScript errors, infinite renders, persist middleware problems, slices type inference. USE WHEN setting up global state, implementing persist with localStorage, handling Next.js hydration, or migrating from Redux/Context API.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>cache-optimization</name>
<description>Analyze and improve application caching strategies: cache hit rates, TTL configurations, cache key design, invalidation strategies. USE WHEN optimizing cache performance, improving caching strategy, analyzing cache hit rate, designing cache keys, optimizing TTL, or resolving cache-related bottlenecks.</description>
<location>user/frontend</location>
</skill>

<!-- NEW TESTING SKILLS -->

<skill>
<name>e2e-testing-patterns</name>
<description>Master end-to-end testing with Playwright and Cypress: Page Object Model, fixtures, waiting strategies, network mocking, visual regression, accessibility testing. USE WHEN implementing E2E tests, debugging flaky tests, testing critical user workflows, setting up CI/CD test pipelines, testing across browsers, or establishing E2E testing standards.</description>
<location>user/testing</location>
</skill>

<skill>
<name>playwright-automation</name>
<description>Complete browser automation with Playwright: auto-detects dev servers, writes clean test scripts to /tmp, tests pages/forms/responsiveness, takes screenshots, validates UX. USE WHEN testing websites, automating browser interactions, validating web functionality, performing any browser-based testing, or automating UI tasks.</description>
<location>user/testing</location>
</skill>

<skill>
<name>qa-verification</name>
<description>Comprehensive truth scoring (0.0-1.0 scale), code quality verification, and automatic rollback system with 0.95 accuracy threshold. Real-time reliability metrics for code, agents, tasks. Automated correctness, security, best practices validation. USE WHEN ensuring code quality, implementing verification checks, tracking quality metrics, setting up automatic rollback, or integrating quality gates into CI/CD.</description>
<location>user/testing</location>
</skill>

<!-- NEW API SKILL -->



<!-- NEW FULLSTACK SKILLS -->

<skill>
<name>auth-implementation-patterns</name>
<description>Master authentication/authorization patterns: JWT (access/refresh tokens), session-based auth, OAuth2/social login, RBAC, permission-based access control, resource ownership, password security (bcrypt), rate limiting. USE WHEN implementing auth systems, securing APIs, adding OAuth2/social login, implementing RBAC, designing session management, migrating auth systems, or debugging security issues.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>better-auth</name>
<description>Production-ready authentication framework for TypeScript with Cloudflare D1 support via Drizzle ORM or Kysely. Self-hosted alternative to Clerk/Auth.js. Supports social providers (Google, GitHub, Microsoft, Apple), email/password, magic links, 2FA, passkeys, organizations, RBAC. CRITICAL: Requires Drizzle ORM or Kysely (NO direct D1 adapter). Prevents 12 common auth errors. USE WHEN building auth for Cloudflare Workers + D1, need self-hosted auth solution, migrating from Clerk, implementing multi-tenant SaaS, or requiring advanced features (2FA, organizations, RBAC).</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>fastapi-templates</name>
<description>Create production-ready FastAPI projects with async patterns, dependency injection, comprehensive error handling. Project structure: api/routes, core/config, models, schemas, services, repositories. Includes CRUD repository pattern, service layer, async database operations. USE WHEN starting FastAPI projects, building async REST APIs, creating high-performance web services, setting up API projects with proper structure/testing.</description>
<location>user/fullstack</location>
</skill>

<!-- NEW WORKFLOWS SKILLS -->

<skill>
<name>code-review-excellence</name>
<description>Master effective code review practices: constructive feedback, bug catching, knowledge sharing, team morale maintenance. Four-phase process (context gathering, high-level review, line-by-line, summary). Covers feedback techniques, severity differentiation, language-specific patterns, architectural review, test quality, security review. USE WHEN reviewing pull requests, establishing review standards, mentoring developers, conducting architecture reviews, creating review checklists, or improving team collaboration.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>git-commit-helper</name>
<description>Generate descriptive commit messages by analyzing git diffs following conventional commits format: type(scope): description. Types: feat, fix, docs, style, refactor, test, chore. Covers multi-file commits, breaking changes, scope examples, validation checklist. USE WHEN writing commit messages, reviewing staged changes, analyzing git diff, or standardizing commit message format.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>repomix</name>
<description>Package entire code repositories into single AI-friendly files using Repomix. Capabilities: pack codebases with include/exclude patterns, generate XML/Markdown/JSON/plain text formats, preserve file structure/context, optimize for AI consumption with token counting, filter by file types/directories. USE WHEN packaging codebases for AI analysis, creating repository snapshots for LLM context, analyzing third-party libraries, preparing for security audits, generating documentation context, or evaluating unfamiliar codebases.</description>
<location>user/workflows</location>
</skill>

<!-- NEW META SKILL -->



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

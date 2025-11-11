# Skills Catalog - Complete Reference

**Total Skills:** 37 skills across 9 categories  
**Last Updated:** 2025-11-11

## Quick Navigation

- [Filament (4 skills)](#filament---filament-4x-laravel-12)
- [Laravel (3 skills)](#laravel---laravel-framework--tools)
- [Fullstack (4 skills)](#fullstack---full-stack-development)
- [Workflows (7 skills)](#workflows---development-workflows)
- [API (3 skills)](#api---api-design--documentation)
- [Meta (2 skills)](#meta---skill-management)
- [Optimize (2 skills)](#optimize---performance--seo)
- [Marketing (1 skill)](#marketing---content--seo-marketing)
- [Database (12 skills)](#database---database-management--optimization)

---

## Filament - Filament 4.x (Laravel 12)

### 1. filament-rules
**Path:** `.claude/skills/filament/filament-rules/SKILL.md`

**Description:**  
Filament 4.x coding standards for Laravel 12. Custom Schema namespace (NOT Form), Vietnamese UI, Observer patterns, Image management.

**When to Use:**
- Creating Filament resources
- Fixing namespace errors (Class not found: Form\Tabs)
- Implementing forms, tables, RelationManagers
- Working with Settings pages
- Any Filament development work

**Key Features:**
- Correct namespace: `Filament\Forms\Components\` → `Filament\Support\Facades\Schema\`
- Vietnamese labels and UI text
- Observer patterns for automated tasks
- Image management integration

---

### 2. filament-resource-generator
**Path:** `.claude/skills/filament/filament-resource-generator/SKILL.md`

**Description:**  
Automated Filament resource generation with correct namespace imports (Schemas vs Forms), Vietnamese labels, standard structure, Observer patterns, ImagesRelationManager integration.

**When to Use:**
- "Tạo resource mới cho Product"
- "Create new Filament resource"
- "Generate admin resource for User"
- Scaffolding new entity for admin panel

**Key Features:**
- Auto-generates Resource class with correct namespaces
- Vietnamese labels by default
- Observer pattern setup
- ImagesRelationManager integration for models with images

---

### 3. filament-form-debugger
**Path:** `.claude/skills/filament/filament-form-debugger/SKILL.md`

**Description:**  
Diagnose and fix common Filament 4.x form errors - namespace issues (Tabs/Grid/Get), type mismatch, trait errors.

**When to Use:**
- "Class not found: Form\Tabs"
- "Argument must be of type X, Y given"
- Namespace errors in Filament
- Compilation or runtime errors in Filament resources

**Key Features:**
- Namespace error detection and fixing
- Type mismatch resolution
- Trait error debugging
- Quick diagnostic workflow

---

### 4. image-management
**Path:** `.claude/skills/filament/image-management/SKILL.md`

**Description:**  
Centralized polymorphic image management with CheckboxList picker, WebP auto-conversion, order management (order=0 for cover), soft deletes.

**When to Use:**
- Adding images/gallery to models
- Implementing image upload functionality
- Working with ImagesRelationManager
- Fixing image-related errors

**Key Features:**
- Polymorphic relationship (images table for all models)
- Auto WebP conversion
- Order management (order=0 = cover image)
- Soft deletes support

---

## Laravel - Laravel Framework & Tools

### 5. laravel
**Path:** `.claude/skills/laravel/laravel/SKILL.md`

**Description:**  
Laravel v12 - The PHP Framework For Web Artisans

**When to Use:**
- "Tạo route mới"
- "Eloquent relationship"
- "Laravel authentication"
- General Laravel development tasks

**Key Features:**
- Routing, middleware, controllers
- Eloquent ORM and relationships
- Authentication and authorization
- Artisan commands

---

### 6. laravel-dusk
**Path:** `.claude/skills/laravel/laravel-dusk/SKILL.md`

**Description:**  
Laravel Dusk - Browser automation and testing API for Laravel applications.

**When to Use:**
- "Viết browser test"
- "Test UI với Dusk"
- "E2E testing"
- Automating UI testing
- Testing JavaScript interactions

**Key Features:**
- Browser automation
- End-to-end testing
- JavaScript interaction testing
- Headless Chrome support

---

### 7. laravel-prompts
**Path:** `.claude/skills/laravel/laravel-prompts/SKILL.md`

**Description:**  
Laravel Prompts - Beautiful and user-friendly forms for command-line applications with browser-like features including placeholder text and validation.

**When to Use:**
- "Tạo Artisan command"
- "Interactive CLI prompt"
- "Laravel console command"
- Creating interactive CLI tools

**Key Features:**
- Beautiful CLI forms
- Placeholder text support
- Built-in validation
- Browser-like features in terminal

---

## Fullstack - Full-Stack Development

### 8. backend-dev-guidelines
**Path:** `.claude/skills/fullstack/backend-dev-guidelines/SKILL.md`

**Description:**  
Node.js/Express/TypeScript microservices development. Layered architecture (routes → controllers → services → repositories), BaseController, error handling, Sentry monitoring, Prisma, Zod validation, dependency injection.

**When to Use:**
- "Tạo controller mới"
- Creating routes, services, repositories
- API endpoint development
- Microservices architecture
- Error handling and monitoring

**Key Features:**
- Layered architecture pattern
- BaseController for consistent responses
- Sentry error tracking
- Prisma ORM integration
- Zod validation
- Dependency injection

---

### 9. frontend-dev-guidelines
**Path:** `.claude/skills/fullstack/frontend-dev-guidelines/SKILL.md`

**Description:**  
React/TypeScript development guidelines. Suspense, lazy loading, useSuspenseQuery, features directory, MUI v7, TanStack Router, performance optimization.

**When to Use:**
- "Tạo component React"
- Creating pages, features
- Data fetching with TanStack Query
- Routing with TanStack Router
- Frontend styling with MUI v7

**Key Features:**
- React Suspense and lazy loading
- useSuspenseQuery pattern
- Features-based directory structure
- MUI v7 styling system
- TanStack Router and Query
- TypeScript best practices

---

### 10. ux-designer
**Path:** `.claude/skills/fullstack/ux-designer/SKILL.md`

**Description:**  
Expert UI/UX design guidance for building unique, accessible, and user-centered interfaces.

**When to Use:**
- "Thiết kế giao diện"
- Making visual design decisions
- Choosing colors and typography
- Implementing responsive layouts
- User mentions: design, UI, UX, styling, visual appearance

**Key Features:**
- Design principles and patterns
- Accessibility guidelines (WCAG)
- Color and typography systems
- Responsive design strategies
- User-centered design approach

**Important:** Always ask before making design decisions!

---

### 11. ui-styling
**Path:** `.claude/skills/fullstack/ui-styling/SKILL.md`

**Description:**  
UI component styling with shadcn/ui, Tailwind CSS, and design system implementation.

**When to Use:**
- "Thêm shadcn component"
- "Tạo design system"
- Implementing consistent styling
- Component library integration

**Key Features:**
- shadcn/ui component library
- Tailwind CSS utilities
- Design system patterns
- Consistent styling approach

---

## Workflows - Development Workflows

### 12. database-backup
**Path:** `.claude/skills/workflows/database-backup/SKILL.md`

**Description:**  
Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema, keep max 10 recent backups.

**When to Use:**
- "Chạy migration"
- Before any risky database operations
- Creating or running migrations
- Restoring database
- Managing schema changes

**Key Features:**
- Spatie Laravel Backup integration
- Automatic backup before migrations
- mermaid.rb schema sync
- Max 10 recent backups retention

**Critical Rule:** ALWAYS backup before migrations!

---

### 13. systematic-debugging
**Path:** `.claude/skills/workflows/systematic-debugging/SKILL.md`

**Description:**  
Four-phase debugging framework ensuring root cause investigation before fixes. Never jump to solutions.

**When to Use:**
- "Bug này không fix được"
- "Test fail liên tục"
- ANY technical issue: bugs, test failures, performance problems
- When fixes fail repeatedly
- Under time pressure (counterintuitively!)

**Key Features:**
- Phase 1: Root Cause Investigation
- Phase 2: Pattern Analysis
- Phase 3: Hypothesis & Testing
- Phase 4: Implementation

**Iron Law:** NO FIXES WITHOUT ROOT CAUSE INVESTIGATION

---

### 14. product-search-scoring
**Path:** `.claude/skills/workflows/product-search-scoring/SKILL.md`

**Description:**  
Advanced product search system with keyword scoring, Vietnamese text normalization, multi-field matching, and search result ranking.

**When to Use:**
- "Tìm kiếm sản phẩm"
- "Thêm tính năng search"
- "Optimize search algorithm"
- Implementing search functionality
- Improving search relevance

**Key Features:**
- Keyword scoring algorithm
- Vietnamese text normalization
- Multi-field matching (name, description, tags)
- Search result ranking
- Relevance optimization

---

### 15. docs-seeker
**Path:** `.claude/skills/workflows/docs-seeker/SKILL.md`

**Description:**  
Searching internet for technical documentation using llms.txt standard, GitHub repositories via Repomix, and parallel exploration.

**When to Use:**
- "Tìm tài liệu cho Next.js"
- Need latest documentation for libraries/frameworks
- Looking for llms.txt format documentation
- GitHub repository analysis
- Multiple documentation sources needed

**Key Features:**
- llms.txt standard support
- GitHub repository analysis (Repomix)
- Parallel documentation search
- Multi-source aggregation

---

### 16. brainstorming
**Path:** `.claude/skills/workflows/brainstorming/SKILL.md`

**Description:**  
Use when creating or developing ideas, before writing code or implementation plans - refines rough ideas into fully-formed designs through collaborative questioning, alternative exploration, and incremental validation.

**When to Use:**
- "Brainstorm ý tưởng"
- "Thiết kế feature mới"
- Turning rough ideas into designs
- Planning new features
- Exploring architecture options
- Before implementation

**Key Features:**
- One question at a time approach
- Multiple choice preferred
- Explore 2-3 alternatives
- Incremental validation
- Document validated designs

---

### 17. sequential-thinking
**Path:** `.claude/skills/workflows/sequential-thinking/SKILL.md`

**Description:**  
Use when complex problems require systematic step-by-step reasoning with ability to revise thoughts, branch into alternative approaches, or dynamically adjust scope.

**When to Use:**
- "Giải quyết vấn đề phức tạp"
- "Step-by-step reasoning"
- Multi-stage analysis
- Problem decomposition
- Tasks with unclear scope
- Need to backtrack or revise

**Key Features:**
- Iterative reasoning
- Revision tracking
- Branch exploration
- Dynamic scope adjustment
- Maintained context

---

### 18. writing-plans
**Path:** `.claude/skills/workflows/writing-plans/SKILL.md`

**Description:**  
Use when design is complete and you need detailed implementation tasks for engineers with zero codebase context - creates comprehensive implementation plans with exact file paths, complete code examples, and verification steps.

**When to Use:**
- "Viết implementation plan"
- "Tạo task breakdown"
- Creating step-by-step guides
- After brainstorming phase
- Breaking features into tasks

**Key Features:**
- Bite-sized tasks (2-5 min each)
- Exact file paths
- Complete code examples
- TDD/DRY/YAGNI principles
- Execution handoff options

---

## API - API Design & Documentation

### 19. api-design-principles
**Path:** `.claude/skills/api/api-design-principles/SKILL.md`

**Description:**  
Master REST and GraphQL API design principles to build intuitive, scalable, and maintainable APIs that delight developers.

**When to Use:**
- Designing new APIs
- Reviewing API specifications
- Establishing API design standards
- Implementing RESTful endpoints
- Working with GraphQL schemas

**Key Features:**
- REST best practices
- GraphQL design patterns
- Resource naming conventions
- Error handling standards
- Versioning strategies
- API security patterns

---

### 20. api-cache-invalidation
**Path:** `.claude/skills/api/api-cache-invalidation/SKILL.md`

**Description:**  
Automatic cache invalidation system with Laravel Observers and Next.js On-Demand Revalidation. Auto sync data real-time between backend and frontend.

**When to Use:**
- "Phải Ctrl+F5 mới thấy data mới"
- Setup cache management
- Sync frontend-backend
- API cache strategy
- Real-time data synchronization

**Key Features:**
- Laravel Observer integration
- Next.js On-Demand Revalidation
- Automatic cache invalidation
- Real-time sync
- No manual cache clearing needed

---

### 21. api-documentation-writer
**Path:** `.claude/skills/api/api-documentation-writer/SKILL.md`

**Description:**  
Generate comprehensive API documentation for REST, GraphQL, WebSocket APIs with OpenAPI specs, endpoint descriptions, request/response examples, error codes, authentication guides, and SDKs.

**When to Use:**
- "Viết document API"
- "Tạo API docs"
- "Generate API documentation"
- Document REST endpoints
- Create technical reference for developers

**Key Features:**
- OpenAPI/Swagger specs
- REST, GraphQL, WebSocket support
- Request/response examples
- Error code documentation
- Authentication guides
- SDK generation

---

## Meta - Skill Management

### 22. create-skill
**Path:** `.claude/skills/meta/create-skill/SKILL.md`

**Description:**  
Guide for creating effective skills. Extends Claude's capabilities with specialized knowledge, workflows, or tool integrations.

**When to Use:**
- "Tạo skill mới"
- "Package skill"
- "Validate skill"
- Creating new skills
- Updating existing skills

**Key Features:**
- Skill initialization (`scripts/init_skill.py`)
- Skill validation (`scripts/quick_validate.py`)
- Skill packaging (`scripts/package_skill.py`)
- Progressive disclosure design
- Category organization

**Critical:** Always register skill in SYSTEM.md and AGENTS.md!

---

### 23. choose-skill
**Path:** `.claude/skills/meta/choose-skill/SKILL.md`

**Description:**  
Meta-agent that analyzes tasks and recommends optimal skill combinations with Feynman-style explanations.

**When to Use:**
- Feeling overwhelmed by 34+ skills
- Uncertain which skills to apply
- Need guidance on skill orchestration
- Want to understand skill synergies

**Key Features:**
- Task analysis and decomposition
- Skill combo recommendations (1-3 options)
- Feynman-style explanations (simple Vietnamese)
- Why each skill is recommended
- Execution order guidance

**Critical:** This is READ-ONLY analyzer - recommends but NEVER modifies code!

---

## Optimize - Performance & SEO

### 24. web-performance-audit
**Path:** `.claude/skills/optimize/web-performance-audit/SKILL.md`

**Description:**  
Conduct comprehensive web performance audits. Measure page speed, identify bottlenecks, and recommend optimizations to improve user experience and SEO.

**When to Use:**
- "Optimize web performance"
- "Đo page speed"
- "Core Web Vitals"
- Performance bottleneck investigation
- SEO performance optimization

**Key Features:**
- Page speed measurement
- Core Web Vitals analysis
- Bottleneck identification
- Optimization recommendations
- Lighthouse integration
- Performance metrics tracking

---

### 25. google-official-seo-guide
**Path:** `.claude/skills/optimize/google-official-seo-guide/SKILL.md`

**Description:**  
Official Google SEO guide covering search optimization, best practices, Search Console, crawling, indexing, and improving website search visibility based on official Google documentation.

**When to Use:**
- "Google SEO"
- "Structured data VideoObject"
- "Search Console"
- SEO best practices
- Improving search visibility
- Implementing structured data

**Key Features:**
- Official Google guidelines
- Search Console integration
- Crawling and indexing optimization
- Structured data implementation
- Search visibility improvement
- SERP optimization

---

## Marketing - Content & SEO Marketing

### 26. seo-content-optimizer
**Path:** `.claude/skills/marketing/seo-content-optimizer/SKILL.md`

**Description:**  
Optimize content for search engines with keyword analysis, readability scoring, meta descriptions, and competitor comparison.

**When to Use:**
- "Optimize content cho SEO"
- "Keyword analysis"
- "Meta description optimization"
- Improve blog post SEO
- Analyze content for search performance

**Key Features:**
- Keyword density analysis
- Readability scoring (Flesch-Kincaid)
- Meta description optimization
- Competitor content comparison
- Content gap analysis
- On-page SEO recommendations

---

## Database - Database Management & Optimization

### 27. databases
**Path:** `.claude/skills/database/databases/SKILL.md`

**Description:**  
Work with MongoDB (document database, BSON documents, aggregation pipelines, Atlas cloud) and PostgreSQL (relational database, SQL queries, psql CLI, pgAdmin).

**When to Use:**
- "PostgreSQL queries"
- "MongoDB aggregation"
- Designing database schemas
- Writing queries and aggregations
- Optimizing indexes for performance
- Database migrations
- Replication and sharding
- Backup and restore strategies

**Key Features:**
- MongoDB + PostgreSQL support
- Query writing and optimization
- Index optimization
- Migration strategies
- Backup/restore procedures
- User and permission management

---

### 28. analyzing-database-indexes
**Path:** `.claude/skills/database/analyzing-database-indexes/SKILL.md`

**Description:**  
Analyze database indexes for performance optimization, identify missing indexes, redundant indexes, and index usage patterns.

**When to Use:**
- "Analyze database indexes"
- Slow query investigation
- Index optimization
- Performance tuning
- Finding missing indexes

**Key Features:**
- Index usage analysis
- Missing index detection
- Redundant index identification
- Index performance metrics
- Optimization recommendations

---

### 29. analyzing-query-performance
**Path:** `.claude/skills/database/analyzing-query-performance/SKILL.md`

**Description:**  
Analyze slow queries, use EXPLAIN plans, identify bottlenecks, and optimize database query performance.

**When to Use:**
- "Optimize slow query"
- Query performance issues
- EXPLAIN plan analysis
- Database bottleneck identification

**Key Features:**
- EXPLAIN/EXPLAIN ANALYZE
- Query execution plan analysis
- Bottleneck identification
- Query optimization strategies
- Performance benchmarking

---

### 30. comparing-database-schemas
**Path:** `.claude/skills/database/comparing-database-schemas/SKILL.md`

**Description:**  
Compare database schemas between environments, generate migration scripts, identify schema drift.

**When to Use:**
- "Compare database schemas"
- "Generate migration script"
- Schema drift detection
- Environment synchronization

**Key Features:**
- Schema comparison (dev/staging/prod)
- Migration script generation
- Schema drift detection
- Synchronization recommendations

---

### 31. designing-database-schemas
**Path:** `.claude/skills/database/designing-database-schemas/SKILL.md`

**Description:**  
Design optimal database schemas, normalization, relationships, constraints, and ERD diagrams.

**When to Use:**
- "Design database schema"
- "Generate ERD diagram"
- New database design
- Schema refactoring
- Relationship design

**Key Features:**
- ERD diagram generation
- Normalization guidance (1NF, 2NF, 3NF)
- Relationship design
- Constraint definition
- Best practices for schema design

---

### 32. generating-database-documentation
**Path:** `.claude/skills/database/generating-database-documentation/SKILL.md`

**Description:**  
Generate comprehensive database documentation including schema diagrams, table descriptions, relationships, and data dictionaries.

**When to Use:**
- "Document database schema"
- Create data dictionary
- Generate schema documentation
- Team onboarding documentation

**Key Features:**
- Schema diagram generation
- Table and column descriptions
- Relationship documentation
- Data dictionary creation
- Documentation templates

---

### 33. generating-database-seed-data
**Path:** `.claude/skills/database/generating-database-seed-data/SKILL.md`

**Description:**  
Generate realistic seed data for database testing, development environments, and demos.

**When to Use:**
- "Seed database"
- Create development data
- Testing data generation
- Demo environment setup

**Key Features:**
- Realistic data generation
- Relationship-aware seeding
- Faker integration
- Custom seed patterns
- Laravel seeder support

---

### 34. generating-orm-code
**Path:** `.claude/skills/database/generating-orm-code/SKILL.md`

**Description:**  
Generate ORM models, entities, and relationships from database schemas for Eloquent, Prisma, TypeORM.

**When to Use:**
- "Generate ORM models"
- "Create TypeORM entities"
- Generate Eloquent models
- ORM code generation

**Key Features:**
- Eloquent model generation
- Prisma schema generation
- TypeORM entity generation
- Relationship mapping
- Validation rules

---

### 35. generating-test-data
**Path:** `.claude/skills/database/generating-test-data/SKILL.md`

**Description:**  
Generate test data for unit tests, integration tests, and end-to-end tests with factories and fixtures.

**When to Use:**
- "Generate test data"
- Create test fixtures
- Factory pattern implementation
- Test database seeding

**Key Features:**
- Test factory generation
- Fixture creation
- Mock data generation
- Relationship handling
- Test scenario setup

---

### 36. scanning-database-security
**Path:** `.claude/skills/database/scanning-database-security/SKILL.md`

**Description:**  
Scan database for security vulnerabilities, SQL injection risks, permission issues, and sensitive data exposure.

**When to Use:**
- "Database security scan"
- Security audit
- SQL injection detection
- Permission review

**Key Features:**
- Vulnerability scanning
- SQL injection detection
- Permission audit
- Sensitive data identification
- Security recommendations

---

### 37. validating-database-integrity
**Path:** `.claude/skills/database/validating-database-integrity/SKILL.md`

**Description:**  
Validate database integrity with constraint checks, referential integrity validation, data consistency checks.

**When to Use:**
- "Validate database integrity"
- Data consistency checks
- Referential integrity validation
- Orphaned record detection

**Key Features:**
- Constraint validation
- Referential integrity checks
- Orphaned record detection
- Data consistency validation
- Integrity repair recommendations

---

### 38. sql-optimization-patterns
**Path:** `.claude/skills/database/sql-optimization-patterns/SKILL.md`

**Description:**  
Master SQL query optimization, indexing strategies, and EXPLAIN analysis to dramatically improve database performance and eliminate slow queries.

**When to Use:**
- "SQL optimization"
- Query performance tuning
- Index strategy design
- EXPLAIN plan analysis

**Key Features:**
- Query optimization patterns
- Indexing strategies
- JOIN optimization
- Subquery optimization
- Query rewriting techniques
- Performance best practices

---

## Category Summary

| Category | Count | Primary Use Cases |
|----------|-------|-------------------|
| **Filament** | 4 | Admin panel development, resource generation, form debugging |
| **Laravel** | 3 | Framework features, browser testing, CLI tools |
| **Fullstack** | 4 | Backend/frontend development, UI/UX design, styling |
| **Workflows** | 4 | Debugging, search, documentation, database safety |
| **API** | 3 | API design, documentation, caching |
| **Meta** | 2 | Skill creation, skill recommendation |
| **Optimize** | 2 | Performance auditing, SEO |
| **Marketing** | 1 | Content SEO optimization |
| **Database** | 12 | Schema design, query optimization, security, documentation |

---

**Total:** 34 skills ready to use  
**Format:** Each skill < 200 lines with progressive disclosure  
**Access:** `read .claude/skills/[category]/[skill-name]/SKILL.md`

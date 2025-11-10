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
<description>Framework for creating new Claude skills with proper structure, YAML frontmatter, and progressive disclosure. USE WHEN user says 'tạo skill mới', 'create new skill', 'add skill for', or wants to extend capabilities systematically.</description>
<location>user</location>
</skill>

<skill>
<name>filament-rules</name>
<description>Filament 4.x coding standards for Laravel 12 project with custom Schema namespace (not Form), Vietnamese UI, Observer patterns, Image management. USE WHEN creating Filament resources, fixing namespace errors (Class not found), implementing forms, RelationManagers, or any Filament development task.</description>
<location>user</location>
</skill>

<skill>
<name>image-management</name>
<description>Centralized polymorphic image management system with CheckboxList picker, WebP auto-conversion, order management, soft deletes. USE WHEN adding images/gallery to models, implementing image upload, working with ImagesRelationManager, or troubleshooting image-related issues.</description>
<location>user</location>
</skill>

<skill>
<name>database-backup</name>
<description>Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema. USE WHEN creating migrations, running migrations, restoring database, or managing database schema changes.</description>
<location>user</location>
</skill>

<skill>
<name>filament-resource-generator</name>
<description>Automated Filament resource generation with correct namespace imports, Vietnamese labels, standard structure, and best practices. USE WHEN user says 'tạo resource mới', 'create new resource', 'generate Filament resource', 'scaffold admin resource'.</description>
<location>user</location>
</skill>

<skill>
<name>filament-form-debugger</name>
<description>Diagnose and fix common Filament form errors (namespace issues, class not found, type mismatch, argument errors). USE WHEN encountering 'Class not found', 'Argument must be of type', 'Trait not found', or any Filament-related errors.</description>
<location>user</location>
</skill>

<skill>
<name>api-design-principles</name>
<description>Master REST and GraphQL API design principles to build intuitive, scalable, and maintainable APIs that delight developers. USE WHEN designing new APIs, reviewing API specifications, establishing API design standards, implementing RESTful endpoints, or working with API architecture.</description>
<location>user</location>
</skill>

<skill>
<name>api-cache-invalidation</name>
<description>Automatic cache invalidation system với Laravel Observers và Next.js On-Demand Revalidation. Tự động sync data real-time giữa backend và frontend khi admin update. USE WHEN user phàn nàn "phải Ctrl+F5 mới thấy data mới", cần setup cache management, sync frontend-backend, hoặc optimize API performance với ISR.</description>
<location>user</location>
</skill>

<skill>
<name>docs-seeker</name>
<description>Searching internet for technical documentation using llms.txt standard, GitHub repositories via Repomix, and parallel exploration. USE WHEN user needs latest documentation for libraries/frameworks, documentation in llms.txt format, GitHub repository analysis, or comprehensive documentation discovery across multiple sources.</description>
<location>user</location>
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

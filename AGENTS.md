# 🤖 Coding Agent Guidelines - Wincellar Clone

**Trả lời bằng tiếng việt**

---

## 🚨 CRITICAL RULES

### ⚠️ Test/Debug Files - ALWAYS Follow

**RULE: Test files belong in /tests, cleanup immediately**

```bash
# ❌ NEVER DO THIS - Files in root!
check_something.php
test_feature.php
debug_issue.php
fix_problem.php

# ✅ CORRECT - Files in /tests directory
tests/Feature/CheckSomethingTest.php
tests/Unit/FeatureTest.php
tests/Debug/DebugIssueTest.php

# Or use temporary PHP scripts
php -r "echo 'Quick test';"
php artisan tinker --execute="..."
```

**Process:**
1. 🔧 Create test file → ONLY in `/tests` directory
2. ✅ Run test & verify
3. 🗑️ **DELETE immediately after use**
4. 📝 Document findings in `/docs` if needed

**Auto-cleanup check:**
```bash
# After creating any test file, run this:
Get-ChildItem -Filter "*test*.php","*check*.php","*debug*.php","*fix*.php" | 
    Where-Object { $_.DirectoryName -notmatch "\\tests\\?" } | 
    Remove-Item -Force
```

---

### 📁 Documentation Organization - /docs Structure

**RULE: Tổ chức docs theo chuyên đề, không để rải rác**

```
/docs
├─ /setup/                      # Hướng dẫn thiết lập ban đầu
│  ├─ README.md                 # Tổng quan docs
│  ├─ TESTING_SETUP_GUIDE.md
│  └─ spatie_backup.md
│
├─ /architecture/               # Thiết kế kiến trúc tổng thể
│  ├─ FINAL_SUMMARY.md
│  └─ mermaid.rb                # Database diagram
│
├─ /phases/                     # Lịch sử phát triển theo phase
│  ├─ PHASE_1_IMPLEMENTATION_SUMMARY.md
│  ├─ PHASE_2_IMPLEMENTATION_SUMMARY.md
│  └─ PHASE_3_IMPLEMENTATION_SUMMARY.md
│
├─ /api/                        # API documentation (keep as is)
├─ /database/                   # Database migrations (keep as is)
├─ /features/                   # Feature documentation (keep as is)
├─ /filament/                   # Filament admin (keep as is)
│
├─ /features-detailed/          # Chi tiết từng feature lớn
│  ├─ IMAGE_MANAGEMENT.md
│  └─ IMAGE_DELETE_PROTECTION.md
│
└─ /deprecated/                 # Tài liệu cũ
   └─ DEPRECATED.md
```

**Quy tắc:**
- Mỗi **PHASE** hoặc tính năng **MỚI** → `/docs/[chuyên-đề]/*.md`
- **Setup guide** → `/docs/setup/`
- **Architecture overview** → `/docs/architecture/`
- **API/DB/Feature chi tiết** → trong thư mục chuyên đề tương ứng
- **Tài liệu cũ** → `/docs/deprecated/` hoặc xóa nếu không cần

---

## 🎯 BẮT ĐẦU TẠI ĐÂY

Đọc file global context:
```
read .claude/global/SYSTEM.md
```

**Chứa:**
- Available skills (tự động activate)
- Project structure
- Core principles

---

## 🚀 Cách Dùng

**Nói tự nhiên:**

```
"Tạo resource mới cho Product"       → filament-resource-generator
"Class not found Tabs"                → filament-form-debugger
"Thêm gallery vào Article"           → image-management
"Chạy migration"                      → database-backup
"Tạo skill mới"                       → create-skill
"Phải Ctrl+F5 mới thấy data mới"    → api-cache-invalidation
```

**Skills tự động activate** - không cần gọi tên!

---

## 📚 Skills Available

1. **create-skill** - Tạo skills mới
2. **filament-rules** - Filament 4.x standards
3. **image-management** - Polymorphic image system
4. **database-backup** - Safe migrations
5. **filament-resource-generator** - Auto scaffolding
6. **filament-form-debugger** - Fix errors
7. **api-design-principles** - REST/GraphQL API best practices
8. **api-cache-invalidation** - Auto sync frontend-backend với Observer + ISR

**Chi tiết:** `.claude/skills/[skill-name]/SKILL.md`

---

## 💡 Quick Reference

### Filament Critical
- Schema NOT Form (`Filament\Schemas\Schema`)
- Layout: `Schemas\Components\*`
- Fields: `Forms\Components\*`
- Get: `Schemas\Components\Utilities\Get`

### Database
- Backup first: `php artisan backup:run --only-db`
- Update `mermaid.rb` sau migration

### Images
- Polymorphic (`images` table)
- CheckboxList picker (NO Alpine.js)
- WebP 85% conversion

---

## 🎓 Learning Path

```
1. read .claude/global/SYSTEM.md
2. read .claude/skills/create-skill/SKILL.md
3. read .claude/skills/filament-rules/SKILL.md
4. Skills tự activate when needed!
```

---

**Version:** 2.0 (Skill-based) ✅  
**Updated:** 2025-11-09

**🎯 Just ask naturally - skills auto-activate!**

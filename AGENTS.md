# 🤖 Coding Agent Guidelines - Wincellar Clone

**Trả lời bằng tiếng việt**

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

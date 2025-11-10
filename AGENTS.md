# 🤖 Coding Agent Guidelines - Wincellar Clone

**Trả lời bằng tiếng việt**

---

## 🎯 START HERE

**First time?** Read the system foundation:
```
read .claude/global/SYSTEM.md
```

**Contains:**
- Available skills (auto-activate)
- Project structure & core principles
- Coding standards & critical rules
- Quick reference commands

---

## 🚀 How to Work with Skills

Skills **automatically activate** when you use natural language:

```
"Tạo resource mới cho Product"       → filament-resource-generator
"Class not found Tabs"                → filament-form-debugger
"Thêm gallery vào Article"           → image-management
"Chạy migration"                      → database-backup
"Tạo skill mới"                       → create-skill
"Phải Ctrl+F5 mới thấy data mới"    → api-cache-invalidation
"Tìm tài liệu cho Next.js"           → docs-seeker
"Bug này không fix được"              → systematic-debugging
"Test fail liên tục"                  → systematic-debugging
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
```

**You don't need to explicitly call skills** - just describe what you want!

---

## 📚 Available Skills

**Core Development:**
- **filament-rules** - Filament 4.x standards (Schema namespace, Vietnamese UI)
- **filament-resource-generator** - Auto scaffolding resources
- **filament-form-debugger** - Fix "Class not found" errors
- **image-management** - Polymorphic image system
- **database-backup** - Safe migration workflow
- **systematic-debugging** - 4-phase debugging framework (root cause investigation)
- **product-search-scoring** - Advanced product search with keyword scoring & Vietnamese text normalization

**Infrastructure:**
- **api-design-principles** - REST/GraphQL best practices
- **api-cache-invalidation** - Auto sync frontend-backend
- **api-documentation-writer** - Generate comprehensive API documentation
- **docs-seeker** - Find technical documentation

**Development Guidelines:**
- **backend-dev-guidelines** - Node.js/Express/TypeScript microservices patterns
- **frontend-dev-guidelines** - React/TypeScript with Suspense and modern patterns
- **ux-designer** - UI/UX design guidance for unique, accessible interfaces
- **ui-styling** - shadcn/ui + Tailwind CSS component library and styling system

**Meta:**
- **create-skill** - Create new skills

**Details:** `read .claude/skills/[skill-name]/SKILL.md`

---

## 🎓 Learning Path

```
1. read .claude/global/SYSTEM.md        (foundation)
2. Natural language requests             (skills auto-activate)
3. read .claude/skills/[name]/SKILL.md  (when you need depth)
```

---

## 📖 Additional Resources

**Project-specific:**
- `PLAN.md` - Project roadmap and architecture decisions
- `mermaid.rb` - Database schema diagram
- `docs/` - Detailed documentation by topic

**Critical standards:**
- All project rules are in `.claude/global/SYSTEM.md`
- No need to memorize - skills load context as needed

---

---

## 🔄 API Documentation Sync Rule

**QUAN TRỌNG:** Khi cập nhật API backend (thêm endpoint, thay đổi parameters, sửa response format):

1. **Cập nhật file tài liệu:** `docs/api/API_ENDPOINTS.md`
2. **Cập nhật giao diện homepage:** `resources/views/api-documentation.blade.php`
   - Thêm/sửa endpoint description
   - Cập nhật parameters, query filters
   - Cập nhật response examples
3. **Kiểm tra:** Homepage tại `http://127.0.0.1:8000/` phải hiển thị API docs mới nhất

**Tại sao?** Để developers luôn có tài liệu API mới nhất khi truy cập trang chủ backend.

---

**Version:** 3.1 (With API Sync Rule) ✅  
**Updated:** 2025-11-11

**🎯 Just ask naturally - skills auto-activate!**

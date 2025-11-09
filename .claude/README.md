# Claude Skills System - Wincellar Clone Project

> **Version:** 2.0  
> **Migration Date:** 2025-11-09  
> **Status:** Production Ready ✅

---

## 📚 Tổng Quan

Dự án này sử dụng **Claude Skills architecture** với progressive disclosure và auto-activation theo standards của Anthropic và Daniel Miessler's Personal AI Infrastructure.

## 🏗️ Cấu Trúc

```
.claude/
├── global/
│   └── SYSTEM.md              # Global context, available skills list
└── skills/
    ├── create-skill/          # ⭐ Meta-skill: Tạo skills mới
    │   ├── SKILL.md
    │   └── CLAUDE.md
    ├── filament-rules/        # Filament 4.x standards
    │   ├── SKILL.md
    │   └── CLAUDE.md
    ├── image-management/      # Polymorphic image system
    │   ├── SKILL.md
    │   └── CLAUDE.md
    ├── database-backup/       # Safe migration workflow
    │   ├── SKILL.md
    │   └── CLAUDE.md
    ├── filament-resource-generator/  # Auto resource scaffolding
    │   └── SKILL.md
    └── filament-form-debugger/  # Troubleshooting
        └── SKILL.md
```

## 🎯 Skills Available

### 1. create-skill (Meta-Skill)
**Purpose:** Framework for creating new Claude skills  
**Activate:** "tạo skill mới", "create new skill", "extend capabilities"  
**Files:** SKILL.md (quick guide) + CLAUDE.md (comprehensive)

### 2. filament-rules
**Purpose:** Filament 4.x coding standards for Laravel 12  
**Activate:** "create resource", "fix namespace error", "Filament development"  
**Files:** SKILL.md (critical rules) + CLAUDE.md (full FILAMENT_RULES.md)

### 3. image-management
**Purpose:** Centralized polymorphic image system  
**Activate:** "add images", "gallery", "image upload", "CheckboxList picker"  
**Files:** SKILL.md (patterns) + CLAUDE.md (full IMAGE_MANAGEMENT.md)

### 4. database-backup
**Purpose:** Safe database migration workflow  
**Activate:** "migration", "backup database", "restore"  
**Files:** SKILL.md (workflow) + CLAUDE.md (full spatie_backup.md)

### 5. filament-resource-generator
**Purpose:** Automated Filament resource generation  
**Activate:** "tạo resource mới", "create new resource", "scaffold admin"  
**Files:** SKILL.md (comprehensive workflow)

### 6. filament-form-debugger
**Purpose:** Diagnose and fix Filament errors  
**Activate:** "Class not found", "namespace error", "Filament error"  
**Files:** SKILL.md (error patterns + fixes)

## 🚀 Cách Sử Dụng

### For AI Agents (Like Me!)

**Step 1:** Đọc global context
```
read .claude/global/SYSTEM.md
```

**Step 2:** Skills tự động activate khi user request tasks

**Step 3:** Nếu cần deep dive, đọc CLAUDE.md
```
read .claude/skills/[skill-name]/CLAUDE.md
```

### For Developers

**Just ask naturally!**

```bash
# Examples:
"Tạo resource mới cho Product"
→ Activates: filament-resource-generator

"Class not found Tabs"  
→ Activates: filament-form-debugger

"Thêm gallery ảnh vào Article"
→ Activates: image-management

"Chạy migration"
→ Activates: database-backup

"Tạo skill cho API testing"
→ Activates: create-skill
```

**NO need to say** "use skill X" - AI auto-detects!

## 📖 Documentation Structure

### Progressive Disclosure

Skills follow 3-layer loading:

**Layer 1: YAML Frontmatter** (Always loaded)
```yaml
---
name: skill-name
description: What it does + USE WHEN triggers
---
```

**Layer 2: SKILL.md Body** (Loaded when activated)
- Quick reference (100-300 lines)
- Core workflows
- Examples
- Key principles

**Layer 3: CLAUDE.md** (Loaded on demand)
- Comprehensive methodology (500-2000 lines)
- All patterns
- Troubleshooting
- Full documentation

## 🔄 Migration from v1.0

| Old (docs-based) | New (skill-based) |
|------------------|-------------------|
| Manual `docs/filament/FILAMENT_RULES.md` | Auto-activated `filament-rules` skill |
| Manual `docs/IMAGE_MANAGEMENT.md` | Auto-activated `image-management` skill |
| Manual `docs/spatie_backup.md` | Auto-activated `database-backup` skill |
| No automation | `filament-resource-generator` skill |
| Manual debugging | `filament-form-debugger` skill |

**Legacy files preserved** in `docs/` for reference.

## 🎓 Creating New Skills

Use the **create-skill** meta-skill:

```bash
# Read the guide
read .claude/skills/create-skill/SKILL.md

# Follow 7-step workflow:
# 1. Understand purpose
# 2. Choose simple vs complex
# 3. Create directory structure
# 4. Write SKILL.md with triggers
# 5. Write CLAUDE.md if complex
# 6. Add to SYSTEM.md available_skills
# 7. Test with natural language
```

**Example:**
```bash
mkdir .claude/skills/api-testing
# Create SKILL.md with proper YAML frontmatter
# Add to .claude/global/SYSTEM.md
# Test: "run API tests"
```

## 💡 Key Principles

1. **Progressive Disclosure**: SKILL.md = quick → CLAUDE.md = deep
2. **Auto-Activation**: Skills activate based on description triggers
3. **Imperative Form**: "Create", "Execute" NOT "You should"
4. **No Duplication**: Reference global context
5. **Vietnamese First**: All user-facing content tiếng Việt
6. **Living Documents**: Skills evolve continuously

## 🔧 Maintenance

### Updating Skills

```bash
# Edit skill files
vim .claude/skills/[skill-name]/SKILL.md
vim .claude/skills/[skill-name]/CLAUDE.md

# Update global context
vim .claude/global/SYSTEM.md

# Commit changes
git add .claude/
git commit -m "docs(skill): update [skill-name] - [description]"
```

### Adding New Skills

Follow create-skill workflow → Always update SYSTEM.md!

### Deprecating Skills

1. Mark as deprecated in description
2. Point to replacement
3. Keep for grace period (1-2 months)
4. Archive (don't delete immediately)

## 📊 Benefits

**vs. Old System (docs-based):**
- ✅ Auto-activation (no manual reference needed)
- ✅ Token efficiency (progressive disclosure)
- ✅ Faster responses (load only needed)
- ✅ Easier maintenance (modular structure)
- ✅ Scalable (add skills without complexity)

**vs. Pure Skills (no docs):**
- ✅ Vietnamese support (maintained)
- ✅ Comprehensive documentation (CLAUDE.md)
- ✅ Project-specific conventions (preserved)
- ✅ Gradual learning curve (hybrid approach)

## 🎯 Success Metrics

**System is working if:**
- ✅ Skills activate automatically with natural language
- ✅ AI agents find relevant skills without explicit mention
- ✅ New capabilities added by creating new skills
- ✅ Team understands skill system within 1 session
- ✅ Documentation stays current (living documents)

## 🚀 Next Steps

1. **Test the system:**
   - Try natural language requests
   - Verify skills activate correctly
   - Check documentation completeness

2. **Train team:**
   - Share AGENTS.md
   - Demo auto-activation
   - Practice creating skills

3. **Iterate:**
   - Add new skills as needed
   - Update existing skills based on usage
   - Collect feedback

## 📞 Support

**Questions?**
- Read: `.claude/skills/create-skill/CLAUDE.md`
- Ask: "How do I create a new skill?"
- Reference: Daniel Miessler's PAI (https://github.com/danielmiessler/Personal_AI_Infrastructure)

**Issues?**
- Check: `.claude/skills/filament-form-debugger/SKILL.md`
- Test: Run `php artisan optimize:clear`
- Debug: Read error carefully, match to patterns

---

**Built with:** Anthropic Claude Skills + Daniel Miessler's Personal AI Infrastructure patterns  
**Maintained by:** AI agents + development team  
**License:** Project-specific (Wincellar Clone)

**🎉 Happy coding with skills! 🚀**

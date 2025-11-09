# ⚠️ DEPRECATED - Docs Folder

**This folder is deprecated and replaced by `.claude/skills/` system.**

---

## 🚨 Migration Status

All documentation has been migrated to the new skill-based system:

| Old File | New Location | Status |
|----------|--------------|--------|
| `filament/FILAMENT_RULES.md` | `.claude/skills/filament-rules/CLAUDE.md` | ✅ Migrated |
| `IMAGE_MANAGEMENT.md` | `.claude/skills/image-management/CLAUDE.md` | ✅ Migrated |
| `spatie_backup.md` | `.claude/skills/database-backup/CLAUDE.md` | ✅ Migrated |
| `IMAGE_DELETE_PROTECTION.md` | Integrated into image-management skill | ✅ Migrated |
| `README.md` | `.claude/README.md` | ✅ Migrated |

---

## 📝 What to Do

**Option 1: Delete this folder** (recommended)
```bash
rm -rf docs/
```

**Option 2: Keep for reference** (if uncertain)
- Folder preserved but not actively used
- All content duplicated in `.claude/skills/`

---

## 🎯 Use New System Instead

**Read global context:**
```
read .claude/global/SYSTEM.md
```

**Browse skills:**
```
ls .claude/skills/
```

**Read specific skill:**
```
read .claude/skills/filament-rules/SKILL.md
```

---

**Migrated on:** 2025-11-09  
**Safe to delete:** ✅ Yes  
**Reason:** All content preserved in `.claude/skills/` with better organization

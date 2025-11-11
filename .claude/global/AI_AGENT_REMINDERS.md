# AI Agent Critical Reminders

> **Auto-loaded by SYSTEM.md** - These are critical workflows you MUST follow

---

## 🔄 Skills Auto-Sync Protocol ⚠️ CRITICAL

**TRIGGER:** After ANY skills changes, you MUST auto-run sync script!

### Detection Rules:
```
IF you just completed ANY of:
  ✓ Created new skill folder in .claude/skills/
  ✓ Merged skills (deleted old skills, created merged skill)  
  ✓ Deleted/removed any skills
  ✓ Modified SKILLS_CONTEXT.md
  
THEN:
  → IMMEDIATELY Execute: python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
  → Wait for completion (should take <5 seconds)
  → Verify output shows: "Sync complete!" + updated counts
  → Report sync status to user in your response
```

### Why This Is CRITICAL:

**Without sync:**
- ❌ choose-skill recommends deleted skills
- ❌ choose-skill missing new skills  
- ❌ Broken workflow when user asks "which skill should I use?"
- ❌ Outdated skills-catalog.md confuses everyone

**With sync:**
- ✅ choose-skill always accurate
- ✅ Latest skills immediately available
- ✅ Intelligent recommendations work perfectly
- ✅ System stays consistent

### Example Workflow:

```markdown
User: "Tạo skill mới để handle webhooks"

AI Agent (YOU):
1. Create skill structure in .claude/skills/api/webhook-handler/
2. Write SKILL.md with description
3. Update SKILLS_CONTEXT.md (add webhook-handler to api category)
4. **AUTO-RUN:** Execute sync_choose_skill.py  ← DON'T FORGET!
5. Report to user:
   "✅ Đã tạo webhook-handler skill
    ✅ Đã sync choose-skill (51 → 52 skills)
    ✅ Choose-skill giờ có thể recommend skill mới này"
```

### Quick Check:

**Before responding to user, ask yourself:**
- Did I just modify any skills? → YES → Did I run sync? → NO → **RUN SYNC NOW!**
- Did I update SKILLS_CONTEXT.md? → YES → **RUN SYNC NOW!**
- User asked about skills organization? → Check if sync needed

### Script Location:
```bash
python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
```

### Expected Output:
```
[*] Syncing choose-skill with SKILLS_CONTEXT.md...
[+] Reading SKILLS_CONTEXT.md...
[+] Extracting skills information...
    Found: XX skills across YY categories
[+] Generating skills-catalog.md...
[OK] Generated skills-catalog.md
[+] Updating recommendation-patterns.md...
[OK] Updated recommendation-patterns.md
[*] Sync complete!
```

### Common Mistakes to AVOID:

❌ **WRONG:**
```
AI: "Đã tạo skill mới xong!"
[Forgets to run sync - choose-skill now outdated]
```

✅ **CORRECT:**
```
AI: "Đã tạo skill mới. Đang sync choose-skill..."
[Runs sync script]
AI: "✅ Hoàn tất! Choose-skill đã cập nhật (51 → 52 skills)"
```

---

## 📝 Other Critical Protocols

### API Changes
- Update `docs/api/API_ENDPOINTS.md`
- Update `resources/views/api-documentation.blade.php`

### Database Changes
- Run `database-backup` skill before migrations
- Update `mermaid.rb` with schema changes

### Filament Resources
- Always use `Schema` namespace, NOT `Form`
- Follow `filament-rules` skill conventions

---

**Version:** 1.0  
**Last Updated:** 2025-11-11  
**Priority:** CRITICAL - Must follow these protocols

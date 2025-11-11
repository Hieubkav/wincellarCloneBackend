# Choose-Skill Sync Scripts

Auto-sync scripts to keep choose-skill meta-agent up-to-date with skills changes.

## Scripts

### `sync_choose_skill.py`

Automatically syncs choose-skill references with SKILLS_CONTEXT.md.

**What it does:**
- Reads SKILLS_CONTEXT.md (single source of truth)
- Auto-generates skills-catalog.md with all current skills
- Updates recommendation-patterns.md with merged skill names
- Ensures choose-skill always has latest skill information

**Usage:**

```bash
# Run from anywhere
python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py

# Or from scripts directory
cd .claude/skills/meta/choose-skill/scripts
python sync_choose_skill.py
```

**When to run:**
- After adding new skills
- After merging/removing skills
- After updating skill descriptions in SKILLS_CONTEXT.md
- Before committing major skills changes

**Output:**
- Updates `references/skills-catalog.md` (auto-generated, don't edit manually)
- Updates `references/recommendation-patterns.md` (replaces old skill names)
- Prints summary with total skills/categories

## Workflow

### Adding a new skill

1. Create skill in `.claude/skills/[category]/[skill-name]/`
2. Update `SKILLS_CONTEXT.md` with skill info
3. Run `sync_choose_skill.py`
4. Review generated `skills-catalog.md`
5. Commit changes

### Merging skills

1. Merge skill files (create new, delete old)
2. Update `SKILLS_CONTEXT.md` with merged skill
3. Run `sync_choose_skill.py` (auto-updates references)
4. Review changes in `recommendation-patterns.md`
5. Commit changes

### Updating skill descriptions

1. Edit skill descriptions in `SKILLS_CONTEXT.md`
2. Run `sync_choose_skill.py`
3. Verify `skills-catalog.md` has updates
4. Commit changes

## Files Modified

**Auto-generated (don't edit manually):**
- `references/skills-catalog.md` - Complete skills list with details

**Auto-updated (script replaces old skill names):**
- `references/recommendation-patterns.md` - Task patterns with skill combos

**Manual (edit these):**
- `SKILL.md` - Main choose-skill documentation
- `references/orchestration-guide.md` - Skill orchestration patterns

## Configuration

Edit `sync_choose_skill.py` to modify:
- Skill name replacements (line ~192)
- Output format
- Parsing logic

## Troubleshooting

### "SKILLS_CONTEXT.md not found"
Make sure you're running from repo root or script can find paths correctly.

### "No categories found"
Check SKILLS_CONTEXT.md format matches the parser regex pattern (line ~41).

### "Missing frontend/testing categories"
Ensure SKILLS_CONTEXT.md has all categories with proper format:
```markdown
### category/ (X skills)
**Description:** ...
**Skills:**
- `skill-name` - Description
```

## Future Enhancements

- [ ] Add validation for skill file existence
- [ ] Generate orchestration patterns automatically
- [ ] Add CI/CD integration (auto-run on SKILLS_CONTEXT.md changes)
- [ ] Extract more metadata from skill files (examples, code snippets)

---

**Version:** 1.0.0  
**Last Updated:** 2025-11-11  
**Maintainer:** Droid AI

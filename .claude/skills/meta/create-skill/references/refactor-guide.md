# Skills Refactor Guide - < 200 Lines

## ✅ Done
1. **filament-rules** - 224 → 127 lines ✅
2. **AGENTS.md** - 133 → 79 lines ✅

## 📋 To Refactor (15 skills)

### Priority 1 (High Usage - Do First)
- [ ] filament-resource-generator (299 → <200)
- [ ] systematic-debugging (296 → <200)
- [ ] frontend-dev-guidelines (399 → <200)
- [ ] backend-dev-guidelines (303 → <200)

### Priority 2 (Medium Usage)
- [ ] api-cache-invalidation (326 → <200)
- [ ] api-documentation-writer (306 → <200)
- [ ] ui-styling (322 → <200)
- [ ] ux-designer (447 → <200)
- [ ] product-search-scoring (272 → <200)

### Priority 3 (Lower Usage)
- [ ] database-backup (255 → <200)
- [ ] api-design-principles (227 → <200)
- [ ] filament-form-debugger (210 → <200)
- [ ] image-management (212 → <200)
- [ ] docs-seeker (205 → <200)
- [ ] create-skill (237 → <200)

## 🚀 Quick Refactor Method (3-5 phút/skill)

### Step 1: Identify Extract Targets

Read SKILL.md và identify sections có nhiều lines:
- Detailed examples
- Advanced patterns  
- Long code blocks
- Troubleshooting guides

**Target:** Extract 80-120 lines

### Step 2: Create references/

```bash
mkdir .claude/skills/[skill-name]/references
```

### Step 3: Extract Content

Move detailed sections vào references/:
- `references/examples.md` - Code examples chi tiết
- `references/advanced.md` - Advanced patterns
- `references/troubleshooting.md` - Common issues

### Step 4: Update SKILL.md

Replace extracted sections với brief summary + link:

**Before (verbose):**
```markdown
## Advanced Patterns

### Pattern 1: Complex Implementation

Detailed explanation with 30 lines of code...

### Pattern 2: Another Pattern

More details with 25 lines...
```

**After (concise):**
```markdown
## Advanced Patterns

Brief 2-3 line summary of patterns available.

Details: `read .claude/skills/[name]/references/advanced.md`
```

### Step 5: Compact Code Blocks

**Before:**
```php
public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin chính')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Tên')
                        ->required(),
                    
                    Select::make('category_id')
                        ->label('Danh mục')
                        ->relationship('category', 'name')
                        ->searchable(),
                ]),
            ]),
        ])->columnSpanFull(),
    ]);
}
```

**After (compact):**
```php
public static function form(Schema $schema): Schema {
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Thông tin')->schema([
                TextInput::make('name')->label('Tên')->required(),
                Select::make('category_id')->label('Danh mục'),
            ]),
        ])->columnSpanFull(),
    ]);
}
```

Saved: ~8 lines

### Step 6: Validate

```bash
# Count lines
wc -l .claude/skills/[skill-name]/SKILL.md

# Or PowerShell
(Get-Content .claude/skills/[skill-name]/SKILL.md).Count
```

Target: < 200 lines

## 📊 Extraction Targets by Skill

### filament-resource-generator (299 lines)
Extract to references/:
- `workflow-details.md` - Detailed step-by-step (60 lines)
- `examples.md` - Complete resource examples (80 lines)
- Target: 299 → 160 lines

### systematic-debugging (296 lines)
Extract:
- `phase-details.md` - 4 phases detailed guide (100 lines)
- `examples.md` - Real debugging scenarios (50 lines)
- Target: 296 → 145 lines

### frontend-dev-guidelines (399 lines)
Extract:
- `architecture.md` - Architecture patterns (100 lines)
- `component-patterns.md` - Component examples (80 lines)
- `performance.md` - Performance optimization (60 lines)
- Target: 399 → 160 lines

### backend-dev-guidelines (303 lines)
Extract:
- `layered-architecture.md` - Architecture details (80 lines)
- `patterns.md` - Service/Repository patterns (70 lines)
- Target: 303 → 150 lines

### api-cache-invalidation (326 lines)
Extract:
- `implementation.md` - Implementation details (120 lines)
- `examples.md` - Code examples (60 lines)
- Target: 326 → 145 lines

### ux-designer (447 lines - LONGEST!)
Extract:
- `color-theory.md` - Color theory & palettes (100 lines)
- `typography.md` - Typography guide (80 lines)
- `accessibility.md` - Already exists, reference it (0 lines moved)
- `responsive-design.md` - Already exists, reference it (0 lines moved)
- `examples.md` - Design examples (100 lines)
- Target: 447 → 165 lines

## 🎯 Success Criteria

After refactor:
- ✅ SKILL.md < 200 lines
- ✅ Essential info still in SKILL.md
- ✅ Detailed content in references/
- ✅ Clear links to references
- ✅ No loss of information
- ✅ Passes validation: `python .claude/skills/create-skill/scripts/quick_validate.py .claude/skills/[name]`

## ⚡ Batch Command

Refactor all at once (experienced users):

```bash
# List all skills needing refactor
python .claude/skills/create-skill/scripts/auto_refactor_skills.py --skills-dir .claude/skills

# Manual refactor one by one
# Follow steps 1-6 above for each skill
```

## 💡 Tips

1. **Keep core workflows** - Không extract essential workflows
2. **Extract examples** - Move detailed code examples ra ngoài
3. **Compact syntax** - Single line PHP methods khi có thể
4. **Remove spacing** - Remove excessive blank lines
5. **Brief summaries** - Replace long explanations với 2-3 line summaries
6. **Clear references** - Always link to extracted content

## 🔍 Validation Script

```bash
# Check skill compliance
python .claude/skills/create-skill/scripts/auto_refactor_skills.py --skills-dir .claude/skills

# Expected output after refactor:
# ✅ OK: 16/16 skills under 200 lines
```

---

**Target:** Refactor all 15 remaining skills trong 1-2 giờ

**Strategy:** Do Priority 1 first (4 skills), test, then batch remaining

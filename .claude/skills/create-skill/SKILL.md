---
name: create-skill
description: Framework for creating new Claude skills with proper YAML frontmatter, progressive disclosure (SKILL.md + CLAUDE.md), and integration with global context. USE WHEN user says 'tạo skill mới', 'create new skill', 'add skill for', 'extend capabilities', or wants to create systematic documentation for AI agents.
---

# Create Skill - Skill Creation Framework

## When to Activate This Skill

- User says "tạo skill mới"
- User says "create new skill for X"
- User wants to "add skill" for a capability
- User wants to "extend" AI capabilities systematically
- User needs to document workflows for future AI use
- User mentions "skill" in context of creating documentation

## Core Skill Creation Workflow

### Bước 1: Hiểu Rõ Mục Đích

Hỏi user các câu hỏi sau:
- **Skill này làm gì?** (Mục đích cụ thể)
- **Khi nào activate?** (Trigger conditions, user phrases)
- **Dùng tools/commands nào?** (Dependencies)
- **Simple hay Complex?** (Quyết định cấu trúc)

### Bước 2: Chọn Loại Skill

**Simple Skill** (chỉ SKILL.md):
- Single focused capability
- < 100 lines instructions
- No sub-components needed
- Minimal context required

**Complex Skill** (SKILL.md + CLAUDE.md + subdirs):
- Multi-step workflows
- Extensive methodology
- Multiple components
- Deep context documentation

### Bước 3: Tạo Cấu Trúc Thư Mục

```bash
# Simple skill
.claude/skills/[skill-name]/
└── SKILL.md

# Complex skill
.claude/skills/[skill-name]/
├── SKILL.md           # Quick reference (50-200 lines)
├── CLAUDE.md          # Comprehensive guide (500+ lines)
└── [subdirectories]/  # Components, templates, patterns
```

### Bước 4: Viết SKILL.md (Bắt Buộc)

**CRITICAL**: Use absolute paths for cross-references, use relative paths only for files within same skill directory.

Template chuẩn:

```markdown
---
name: skill-name
description: Clear description với activation triggers. USE WHEN user says 'trigger 1', 'trigger 2', or requests this capability.
---

# Skill Name

## When to Activate This Skill
- User says "trigger phrase 1"
- User requests X
- Task involves Y

## Core Workflow

### Phase 1: Setup
[Imperative instructions: "Create", "Execute", "Run"]

### Phase 2: Execution
[More steps...]

## Available Tools / Commands
- Tool 1: Purpose
- Tool 2: Purpose

## Examples

\`\`\`bash
# Example 1
command input --flag

# Example 2
command -a | process
\`\`\`

## Key Principles
1. Principle 1
2. Principle 2

## Supplementary Resources
For full guide: `read .claude/skills/[name]/CLAUDE.md`
For components: `read .claude/skills/[name]/[component]/`

**NOTE**: Always use backtick commands (\`read ...\`) instead of markdown links for skill-to-skill references to avoid broken links.
```

### Bước 5: Viết CLAUDE.md (Nếu Complex)

Bao gồm:
- Comprehensive methodology
- Detailed workflows với examples
- Component documentation
- Advanced usage patterns
- Integration instructions
- Troubleshooting guides
- Best practices

### Bước 6: Validate All Links

**CRITICAL**: Before integration, verify all file references work:

1. **Check internal links**: Markdown links `[text](./path)` must point to existing files
2. **Update after rename**: If you rename files (e.g., WORKFLOWS.md → CLAUDE.md), update ALL references
3. **Verify command references**: Backtick commands `read .claude/skills/...` must use correct paths
4. **Test relative paths**: Ensure `./references/file.md` exists in skill directory

**Common scenarios:**
- Copying skill from external source → May contain links to old file names
- Renaming WORKFLOWS.md to CLAUDE.md → Must update SKILL.md references
- Subdirectory references → Verify all `./subdir/file.md` links work

### Bước 7: Thêm vào Global Context

**File 1: `.claude/global/SYSTEM.md`**

Cập nhật trong phần `<available_skills>`:

```xml
<skill>
<name>your-new-skill</name>
<description>Your description here with USE WHEN triggers</description>
<location>user</location>
</skill>
```

**File 2: `AGENTS.md`**

Cập nhật 2 sections:

**Section 1: Skills Available**
```markdown
## 📚 Skills Available

1. **existing-skill** - Description
...
8. **your-new-skill** - Short description

**Chi tiết:** `.claude/skills/[skill-name]/SKILL.md`
```

**Section 2: Natural Language Examples**
```markdown
"Natural trigger phrase"              → your-new-skill
```

### Bước 8: Test Skill

1. Dùng natural language trigger phrases
2. Verify skill loads correctly
3. Check tất cả file references hoạt động
4. Validate workflow với examples

## Skill Naming Conventions

- **Format**: `lowercase-with-hyphens`
- **Descriptive**: `filament-resource-generator` not `generator`
- **Action/domain focused**: Clear purpose from name

**Good examples:**
- `filament-rules`
- `image-management`
- `database-backup`
- `create-skill`

**Bad examples:**
- `helper` (too generic)
- `FilamentRules` (capitals)
- `filament_rules` (underscores)

## Description Best Practices

**CRITICAL**: Description drives skill discovery!

**Must include:**
- ✅ What it does (clear purpose)
- ✅ Key methods/tools mentioned
- ✅ **USE WHEN** triggers (explicit phrases)
- ✅ Unique characteristics

**Good example:**
```yaml
description: Filament 4.x coding standards for Laravel 12 with custom Schema namespace, Vietnamese UI, Observer patterns. USE WHEN creating resources, fixing namespace errors, implementing forms, or any Filament development task.
```

**Bad example:**
```yaml
description: A skill for development
```

## Instruction Writing Standards

**Use imperative form** (verb-first):
- ✅ "Create directory structure"
- ✅ "Execute backup command"
- ❌ "You should create a directory"
- ❌ "We will execute"

**Be specific and actionable:**
- ✅ "Run `php artisan backup:run --only-db`"
- ✅ "Execute `read .claude/skills/filament-rules/CLAUDE.md`"
- ❌ "Do a backup"
- ❌ "Read the docs"

**Reference, don't duplicate:**
- ✅ "Use project structure from global context"
- ✅ "Follow Vietnamese-first principle"
- ❌ [Copying entire global context into skill]

## Progressive Disclosure Pattern

**3-Layer Loading:**

```
Layer 1: YAML frontmatter (always loaded)
    ↓
Layer 2: SKILL.md body (when skill activated)
    ↓
Layer 3: CLAUDE.md + subdirs (on demand)
```

**Benefits:**
- Saves tokens (only load what's needed)
- Fast discovery (metadata always available)
- Deep context when required

## Templates Available

### Template 1: Simple Skill
Quick reference only, single file.

### Template 2: Complex Skill
SKILL.md (quick ref) + CLAUDE.md (comprehensive).

### Template 3: Skill with Patterns
Includes subdirectories for reusable components.

## Common Mistakes to Avoid

❌ **Vague descriptions**: "A skill for X"
✅ **Specific**: "X capability using Y tools. USE WHEN user says Z"

❌ **Missing triggers**: No "USE WHEN" phrases
✅ **Clear triggers**: List all activation phrases

❌ **Imperative violations**: "You should do X"
✅ **Imperative form**: "Do X"

❌ **Duplicating context**: Copying global rules
✅ **Referencing**: "Follow global principles"

❌ **No examples**: Only abstract instructions
✅ **Concrete examples**: Bash commands, code snippets

❌ **Broken references**: Files that don't exist, outdated links after renaming
✅ **Verified paths**: Test all file references, update links when renaming files (e.g., WORKFLOWS.md → CLAUDE.md requires updating all references in SKILL.md)

## Integration Checklist

Before declaring skill complete:

- [ ] **Purpose** - Crystal clear what skill does
- [ ] **Structure** - Simple/complex pattern chosen correctly
- [ ] **Description** - Includes USE WHEN triggers
- [ ] **Instructions** - Imperative form, actionable
- [ ] **Examples** - Concrete usage scenarios
- [ ] **References** - All paths work, no broken links after file renames
- [ ] **Integration SYSTEM.md** - Added to `<available_skills>`
- [ ] **Integration AGENTS.md** - Added to Skills Available list + trigger examples
- [ ] **Testing** - Validated with trigger phrases
- [ ] **Documentation** - CLAUDE.md if complex
- [ ] **Quality** - Reviewed against standards

## Key Principles

1. **Progressive disclosure**: Quick ref → Deep dive as needed
2. **Clear activation**: USE WHEN phrases in description
3. **Imperative instructions**: Verb-first, actionable
4. **No duplication**: Reference global context
5. **Self-contained**: Work independently with clear deps
6. **Template-driven**: Use consistent structure
7. **Test thoroughly**: Natural language validation
8. **Iterate constantly**: Skills are living documents

## Supplementary Resources

For comprehensive guide: `read .claude/skills/create-skill/CLAUDE.md`

For real examples:
- Simple: `read .claude/skills/filament-resource-generator/SKILL.md`
- Complex: `read .claude/skills/filament-rules/SKILL.md`
- With patterns: `read .claude/skills/image-management/`

## Quick Start Examples

### Example 1: Create Simple Skill
```
User: "Tạo skill cho PDF extraction"
→ Ask purpose, tools, triggers
→ Create .claude/skills/pdf-extraction/
→ Write SKILL.md with examples
→ Add to SYSTEM.md <available_skills>
→ Add to AGENTS.md (Skills Available + trigger)
→ Test with "extract PDF text"
```

### Example 2: Create Complex Skill
```
User: "Tạo skill cho API testing workflow"
→ Determine multi-phase workflow
→ Create directory with SKILL.md + CLAUDE.md
→ Add subdirs for test-templates/
→ Write comprehensive methodology
→ Add to SYSTEM.md <available_skills>
→ Add to AGENTS.md (Skills Available + trigger)
→ Test with realistic scenarios
```

## Critical Success Factors

**Description quality** = Skill discovery success
**Clear triggers** = Auto-activation reliability
**Imperative form** = Instruction clarity
**Progressive disclosure** = Token efficiency
**Testing** = Production readiness

Nếu thiếu bất kỳ yếu tố nào → Skill sẽ không hoạt động tốt!

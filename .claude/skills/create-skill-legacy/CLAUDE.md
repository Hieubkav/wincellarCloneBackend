# Create Skill - Comprehensive Skill Creation Guide

## 🎯 MỤC ĐÍCH: MỞ RỘNG KHẢ NĂNG THÔNG QUA MODULAR SKILLS

**Skills là các gói modular, self-contained để mở rộng khả năng của Claude với knowledge, workflows, và tools chuyên biệt.**

Guide này kết hợp:
- Anthropic's official skill methodology
- Daniel Miessler's Personal AI Infrastructure patterns
- Best practices từ dự án Laravel + Filament này
- Template-driven quality standards

---

## 📚 SKILLS LÀ GÌ?

### Định Nghĩa

Skills là contextual packages có các đặc điểm:

1. **Extend capabilities**: Thêm knowledge hoặc workflows chuyên biệt
2. **Load progressively**: Metadata → Instructions → Resources
3. **Activate intelligently**: Match user intent với skill descriptions
4. **Work independently**: Self-contained nhưng inherit global context
5. **Follow standards**: Consistent structure across all skills

### Skills vs Commands vs Agents

**Skills**:
- Contextual knowledge và workflows
- Always available trong system prompt (metadata)
- Triggered tự động khi match user intent
- Có thể reference commands hoặc agents

**Commands** (Laravel artisan, npm scripts):
- Executable workflows
- Must be explicitly invoked
- Typically orchestrate multiple tools
- Live in bash/shell/artisan

**Agents** (AI assistants với specialized roles):
- Autonomous workers
- Can execute Skills + Commands
- Have specific training/voice
- May run in parallel (up to 10)

**Relationship**: 
```
User Intent → Skill (knowledge) → Command (execution) → Agent (orchestration)
```

---

## 🏗️ KIẾN TRÚC SKILL

### Hệ Thống 3 Lớp (Progressive Disclosure)

**Layer 1: Metadata (Always Loaded)**
```yaml
---
name: skill-name
description: Clear description with activation triggers
---
```
- Xuất hiện trong `<available_skills>` ở system prompt
- Dùng cho intent matching
- Phải concise nhưng complete

**Layer 2: SKILL.md Body (Loaded When Activated)**
- Quick reference instructions
- Core workflows
- Key commands/tools
- Examples
- References to deeper resources

**Layer 3: Supporting Resources (Loaded As Needed)**
- CLAUDE.md (comprehensive methodology)
- Subdirectories (components, templates, patterns)
- Scripts, references, assets

### Directory Structure Patterns

#### Simple Skill Structure
```
.claude/skills/filament-resource-generator/
└── SKILL.md          # Everything in one file (100-200 lines)
```

**Use when:**
- Single focused capability
- Straightforward workflow
- Minimal context needed
- Quick reference suffices

#### Complex Skill Structure
```
.claude/skills/filament-rules/
├── SKILL.md                      # Quick reference (100-300 lines)
├── CLAUDE.md                     # Full methodology (500-2000 lines)
├── common-patterns/              # Reusable patterns
│   ├── resource-structure.md
│   ├── relation-manager.md
│   └── observer-patterns.md
└── troubleshooting/              # Debug guides
    ├── namespace-errors.md
    └── class-not-found.md
```

**Use when:**
- Multi-step workflows
- Extensive methodology
- Multiple sub-components
- Deep context required

#### Skill with Patterns
```
.claude/skills/image-management/
├── SKILL.md                      # Quick reference
├── CLAUDE.md                     # Architecture guide
└── patterns/
    ├── product-gallery.md        # Pattern 1
    ├── single-image.md           # Pattern 2
    └── checkboxlist-picker.md    # Pattern 3
```

**Use when:**
- Multiple usage patterns
- Reusable components
- Pattern library needed

---

## ✍️ VIẾT EFFECTIVE SKILLS

### SKILL.md Structure Template

```markdown
---
name: skill-name
description: What it does, when to use it, key methods/tools. USE WHEN user says 'trigger 1', 'trigger 2', or requests this capability.
---

# Skill Name

## When to Activate This Skill
- User says "trigger phrase 1"
- User requests X capability
- Task involves Y workflow
- User mentions Z keyword

## Core Workflow

### Phase 1: [Step Name]
[Imperative instructions: "Create", "Execute", "Run"]

**Command:**
\`\`\`bash
command-example --flag value
\`\`\`

**Output:** What gets created/modified

### Phase 2: [Step Name]
[Continue with clear steps...]

## Available Tools / Commands

- **Tool 1**: Purpose and usage
- **Command 2**: What it does
- **Pattern 3**: When to apply

## Common Patterns

### Pattern 1: [Use Case Name]
[Instructions for this pattern]

### Pattern 2: [Use Case Name]
[Instructions for another pattern]

## Examples

\`\`\`bash
# Example 1: Basic usage
command "input" --flag value

# Example 2: Advanced usage
command -a -b | process

# Example 3: With context
cd path && command --option
\`\`\`

## Critical Requirements

- Requirement 1 (mandatory)
- Requirement 2 (best practice)
- Requirement 3 (convention)

## Key Principles

1. Principle 1 with explanation
2. Principle 2 with explanation
3. Principle 3 with explanation

## Supplementary Resources

For full methodology: \`read .claude/skills/[name]/CLAUDE.md\`
For patterns: \`read .claude/skills/[name]/patterns/\`
For troubleshooting: \`read .claude/skills/[name]/troubleshooting/\`

## Related Skills

- **skill-1**: Use together for X
- **skill-2**: Alternative for Y
```

### Description Writing Guidelines

**CRITICAL**: Description is the most important part!

**Must include:**
1. **What it does**: Clear capability statement
2. **Key methods/tools**: Mention specific technologies
3. **Activation triggers**: "USE WHEN user says..." phrases
4. **Unique characteristics**: What makes this special

**Formula:**
```
[Capability statement] [Key tools/methods]. USE WHEN user says '[trigger 1]', '[trigger 2]', '[trigger 3]', or [general trigger description].
```

**Real Examples from This Project:**

✅ **Good - filament-rules:**
```yaml
description: Filament 4.x coding standards for Laravel 12 project with custom Schema namespace (not Form), Vietnamese UI, Observer patterns, Image management. USE WHEN creating Filament resources, fixing namespace errors (Class not found), implementing forms, RelationManagers, or any Filament development task.
```
- Clear what: Filament coding standards
- Mentions quirk: Schema not Form
- Lists features: Vietnamese UI, Observers
- Explicit triggers: resource creation, namespace errors, forms

✅ **Good - image-management:**
```yaml
description: Centralized polymorphic image management system with CheckboxList picker, WebP auto-conversion, order management, soft deletes. USE WHEN adding images/gallery to models, implementing image upload, working with ImagesRelationManager, or troubleshooting image-related issues.
```
- Clear architecture: Centralized polymorphic
- Key features: CheckboxList, WebP, order
- Specific triggers: gallery, upload, troubleshooting

✅ **Good - database-backup:**
```yaml
description: Safe database migration workflow with Spatie backup integration. Always backup before migration, update mermaid.rb schema. USE WHEN creating migrations, running migrations, restoring database, or managing database schema changes.
```
- Clear purpose: Safe migration workflow
- Tool: Spatie backup
- Mandatory rule: Backup first
- Triggers: migrations, restore

❌ **Bad examples:**
```yaml
# Too vague
description: A skill for Filament work

# No triggers
description: Handles image uploads and galleries

# Missing tools
description: Database management. USE WHEN working with database.
```

### Instruction Writing Standards

**ALWAYS use imperative/infinitive form** (verb-first):

✅ **Correct:**
- "Create directory structure"
- "Execute backup command"
- "Update global context"
- "Run `php artisan migrate`"

❌ **Wrong:**
- "You should create a directory"
- "We will execute a backup"
- "The user needs to update"
- "It's recommended to run"

**Be specific and actionable:**

✅ **Correct:**
```bash
Execute backup:
php artisan backup:run --only-db
```

✅ **Correct:**
```bash
Read comprehensive guide:
read .claude/skills/filament-rules/CLAUDE.md
```

❌ **Wrong:**
```
Do a backup of the database
```

❌ **Wrong:**
```
Check the documentation
```

**Reference, don't duplicate:**

✅ **Correct:**
- "Use Vietnamese labels (see global SYSTEM.md)"
- "Follow Filament namespace rules from global context"
- "Apply image upload standards (see image-management skill)"

❌ **Wrong:**
- [Copying entire namespace rules into every skill]
- [Duplicating Vietnamese UI guidelines]

---

## 📋 QUY TRÌNH TẠO SKILL (7 BƯỚC CHI TIẾT)

### Phase 1: Planning (10-30 phút)

**Questions to answer:**
1. Skill này giải quyết vấn đề gì?
2. Khi nào nên activate? (User phrases cụ thể)
3. Dùng tools/commands nào? (Dependencies)
4. Simple hay complex?
5. Có skill tương tự không? (Check existing)
6. Cần resources gì? (Docs, scripts, templates)

**Decision Matrix: Simple vs Complex**

Choose **SIMPLE** if:
- ✅ Single focused capability
- ✅ < 100 lines of instructions
- ✅ No sub-components
- ✅ Quick reference sufficient
- ✅ Examples: filament-resource-generator, database-backup

Choose **COMPLEX** if:
- ✅ Multi-phase workflow
- ✅ Requires extensive methodology (500+ lines)
- ✅ Has multiple components/patterns
- ✅ Needs deep context documentation
- ✅ Examples: filament-rules, image-management

### Phase 2: Structure Creation (5 phút)

**For Simple Skill:**
```bash
mkdir .claude/skills/[skill-name]
```

**For Complex Skill:**
```bash
mkdir .claude/skills/[skill-name]
mkdir .claude/skills/[skill-name]/patterns
mkdir .claude/skills/[skill-name]/troubleshooting
```

**Project-specific paths:**
```
E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend\.claude\skills\
```

### Phase 3: Content Writing (30 phút - 2 giờ)

**Step 1: Write description first** (10 phút)
- This drives everything else
- Test by asking: "Would Claude activate for relevant requests?"
- Include **USE WHEN** with at least 3 trigger phrases

**Step 2: Document activation triggers** (5 phút)
- List explicit user phrases
- Include natural language variations
- Think: How would user express this need?

**Step 3: Write core instructions** (30-60 phút)
- Use imperative form consistently
- Be specific and actionable
- Include concrete examples
- Reference deeper resources if complex

**Step 4: Add examples** (15 phút)
- Bash commands with flags
- Code snippets with context
- Real-world scenarios

**Step 5: Add supporting resources** (nếu complex, 1-2 giờ)
- CLAUDE.md for comprehensive methodology
- Pattern files for reusable components
- Troubleshooting guides for common issues

### Phase 4: Integration (10 phút)

**Update global context:**

**File 1: Edit `.claude/global/SYSTEM.md`:**

```markdown
<available_skills>

<!-- Existing skills... -->

<skill>
<name>your-new-skill</name>
<description>Your description with USE WHEN triggers here</description>
<location>user</location>
</skill>

</available_skills>
```

**Verify location tag:**
- User-created skills: `<location>user</location>`
- System skills: `<location>system</location>` (rare)

### Phase 5: Testing (15 phút)

**Test 1: Activation**
```
Try phrases:
"tạo resource mới"
"create new resource"
"generate Filament resource"
```
→ Skill should load automatically

**Test 2: Instructions**
- Follow each step manually
- Verify all commands work
- Check file references resolve
- Validate examples execute

**Test 3: References**
```bash
# Test file references
cat .claude/skills/[name]/SKILL.md
cat .claude/skills/[name]/CLAUDE.md
ls .claude/skills/[name]/patterns/
```

**Test 4: Workflow**
- Complete real-world scenario
- From trigger → execution → result
- Document any issues

### Phase 6: Quality Review (10 phút)

**Checklist:**
- [ ] YAML frontmatter complete (name, description)
- [ ] Description has USE WHEN triggers
- [ ] Instructions in imperative form
- [ ] Concrete examples included
- [ ] All file references work
- [ ] Added to SYSTEM.md `<available_skills>`
- [ ] Added to AGENTS.md (Skills Available + trigger)
- [ ] No duplication of global context
- [ ] Tested with natural language

### Phase 7: Iteration (Ongoing)

**Update based on:**
- Actual usage patterns
- User feedback
- Tool updates (Laravel/Filament versions)
- Methodology improvements
- Bug discoveries

**Skills are living documents** - always evolving!

---

## 🎨 SKILL TEMPLATES

### Template 1: Simple Skill (Workflow Automation)

**Use for:** Single-purpose workflow skills

```markdown
---
name: skill-name
description: [Capability] using [tool/method]. USE WHEN user says '[trigger 1]', '[trigger 2]', or [general trigger].
---

# Skill Name

## When to Activate This Skill
- User says "trigger phrase 1"
- User requests X
- Task involves Y

## Core Workflow

### Step 1: Preparation
Execute preparation command:
\`\`\`bash
command prep --flag
\`\`\`

### Step 2: Execution
Run main workflow:
\`\`\`bash
command execute input
\`\`\`

### Step 3: Validation
Verify result:
\`\`\`bash
command verify --check
\`\`\`

## Common Options

- `--flag1`: Purpose
- `--flag2`: Purpose

## Examples

\`\`\`bash
# Example 1: Basic usage
command "input"

# Example 2: With options
command "input" --flag1 --flag2

# Example 3: Piped
command "input" | process
\`\`\`

## Key Principles

1. Always prepare before execution
2. Verify output
3. Handle errors gracefully

## Supplementary Resources
For advanced usage: \`read .claude/skills/[name]/CLAUDE.md\`
```

### Template 2: Complex Skill (Comprehensive Guide)

**Use for:** Multi-phase skills with extensive methodology

**SKILL.md** (Quick Reference):
```markdown
---
name: skill-name
description: [Comprehensive capability] with [key features]. USE WHEN [triggers].
---

# Skill Name - Quick Reference

## When to Activate This Skill
- Trigger 1
- Trigger 2
- Trigger 3

## Core Workflow Summary

### Phase 1: [Name]
Brief description - See CLAUDE.md for details

### Phase 2: [Name]
Brief description - See CLAUDE.md for details

### Phase 3: [Name]
Brief description - See CLAUDE.md for details

## Quick Start

Most common usage:
\`\`\`bash
command basic-usage
\`\`\`

## Critical Requirements

- ⚠️ Requirement 1 (MUST)
- ⚠️ Requirement 2 (MUST)
- ✅ Best practice 3

## Common Patterns

### Pattern 1: [Use Case]
Brief instructions + example

### Pattern 2: [Use Case]
Brief instructions + example

## Key Principles

1. Principle 1
2. Principle 2
3. Principle 3

## Supplementary Resources

**Full methodology:** \`read .claude/skills/[name]/CLAUDE.md\`
**Patterns:** \`read .claude/skills/[name]/patterns/\`
**Troubleshooting:** \`read .claude/skills/[name]/troubleshooting/\`

## Related Skills

- **skill-1**: Use for X
- **skill-2**: Alternative for Y
```

**CLAUDE.md** (Comprehensive Guide):
```markdown
# Skill Name - Comprehensive Guide

## 🎯 MỤC ĐÍCH: [HIGH-LEVEL GOAL]

**[Value proposition and core capability]**

This guide covers:
- [Topic 1]
- [Topic 2]
- [Topic 3]

---

## 📚 [CAPABILITY] LÀ GÌ?

### Định Nghĩa
[Detailed explanation of what this skill enables]

### Key Concepts
- **Concept 1**: Explanation with examples
- **Concept 2**: Explanation with examples

### [This Approach] vs [Alternative Approach]
| Aspect | This Approach | Alternative |
|--------|---------------|-------------|
| Feature 1 | ✅ Benefit | ❌ Drawback |
| Feature 2 | ✅ Benefit | ❌ Drawback |

---

## 🏗️ ARCHITECTURE / METHODOLOGY

### [Main Framework Name]
[Detailed explanation with diagrams if needed]

```
[ASCII diagram or structure]
```

### Components

**Component 1:**
- Purpose
- Usage
- Examples

**Component 2:**
[Repeat structure]

---

## 🔧 [MAIN WORKFLOW SECTION]

### Phase 1: [Step Name]

**Purpose:** What this phase accomplishes

**Command:**
\`\`\`bash
command phase1 --options
\`\`\`

**Detailed Instructions:**
1. Substep 1
2. Substep 2
3. Substep 3

**Output:** What gets created

**Common Issues:**
- Issue 1: Solution
- Issue 2: Solution

### Phase 2: [Step Name]
[Repeat structure for each phase]

---

## 💡 BEST PRACTICES

### [Category 1]
- Practice 1 with explanation
- Practice 2 with explanation

### [Category 2]
- Practice 1 with explanation
- Practice 2 with explanation

---

## 🛠️ TOOLS AND TECHNOLOGIES

**Tool 1:**
- Purpose
- Installation
- Configuration
- Usage examples

**Tool 2:**
[Repeat structure]

---

## 📊 PATTERNS LIBRARY

### Pattern 1: [Use Case Name]

**When to use:** Description

**Structure:**
\`\`\`php
// Code example
\`\`\`

**Example:**
\`\`\`bash
# Real-world usage
\`\`\`

### Pattern 2: [Use Case Name]
[Repeat structure]

---

## 🔗 INTEGRATION POINTS

### With Other Skills
- **skill-1**: How they work together
- **skill-2**: When to use both

### With Tools
- **tool-1**: Integration method
- **tool-2**: Integration method

---

## 🚨 CRITICAL WARNINGS

⚠️ **Warning 1:** Description of what NOT to do
- Consequence if ignored
- Correct approach instead

⚠️ **Warning 2:** [Repeat structure]

---

## 🐛 TROUBLESHOOTING

### Issue 1: [Problem Description]

**Symptoms:**
- Symptom 1
- Symptom 2

**Solution:**
\`\`\`bash
# Fix command
\`\`\`

**Explanation:** Why this works

### Issue 2: [Problem Description]
[Repeat structure]

---

## 🎯 KEY PRINCIPLES

1. **Principle 1** - Detailed explanation with why it matters
2. **Principle 2** - Detailed explanation with examples
3. **Principle 3** - Detailed explanation with warnings

---

## 📚 FURTHER READING

- External doc 1: URL or path
- External doc 2: URL or path
- Related skill: path to SKILL.md
```

### Template 3: Skill with Patterns

**Use for:** Skills with multiple usage patterns

```
.claude/skills/skill-name/
├── SKILL.md
├── CLAUDE.md
└── patterns/
    ├── pattern-1.md
    ├── pattern-2.md
    └── pattern-3.md
```

**Each pattern file:**
```markdown
# Pattern Name

## When to Use This Pattern
- Use case 1
- Use case 2

## Structure

\`\`\`php
// Pattern structure
\`\`\`

## Implementation Steps

1. Step 1
2. Step 2
3. Step 3

## Example

\`\`\`php
// Complete working example
\`\`\`

## Variations

### Variation 1
[How to adapt]

### Variation 2
[How to adapt]

## Common Mistakes

❌ **Mistake 1:** Description
✅ **Solution:** Fix

❌ **Mistake 2:** Description
✅ **Solution:** Fix
```

---

## 🎯 REAL-WORLD EXAMPLES FROM THIS PROJECT

### Example 1: Simple Skill (filament-resource-generator)

**Analysis:**
- ✅ Single capability (generate Filament resource)
- ✅ Straightforward workflow
- ✅ No sub-components needed
- ✅ Only SKILL.md required

**Structure:**
```
.claude/skills/filament-resource-generator/
└── SKILL.md (150 lines)
```

**Key features:**
- Clear triggers: "tạo resource", "create resource"
- Step-by-step workflow
- Concrete examples with commands
- References filament-rules for details

### Example 2: Complex Skill (filament-rules)

**Analysis:**
- ✅ Comprehensive coding standards (1000+ lines content)
- ✅ Multiple components (namespace rules, UI standards, observers)
- ✅ Extensive examples and troubleshooting
- ✅ Requires SKILL.md + CLAUDE.md

**Structure:**
```
.claude/skills/filament-rules/
├── SKILL.md (200 lines quick ref)
└── CLAUDE.md (full FILAMENT_RULES.md content)
```

**Key features:**
- SKILL.md = critical rules + common patterns
- CLAUDE.md = full 1000+ line guide
- Progressive disclosure
- No duplication between files

### Example 3: Skill with Patterns (image-management)

**Analysis:**
- ✅ Multiple usage patterns (gallery, single image, picker)
- ✅ Reusable components
- ✅ Pattern library needed

**Structure:**
```
.claude/skills/image-management/
├── SKILL.md (quick ref)
├── CLAUDE.md (architecture guide)
└── patterns/
    ├── product-gallery.md
    ├── single-image.md
    └── checkboxlist-picker.md
```

**Key features:**
- Each pattern = self-contained guide
- SKILL.md references patterns
- Easy to add new patterns
- Reusable across models

---

## 🔧 NAMING CONVENTIONS

### Skill Name (Directory & Metadata)

**Format:** `lowercase-with-hyphens`

**Good examples:**
- `filament-rules` (domain-specific)
- `image-management` (capability-focused)
- `database-backup` (action-focused)
- `create-skill` (meta-skill)
- `filament-resource-generator` (workflow-focused)

**Bad examples:**
- `filament_rules` (underscores)
- `FilamentRules` (camelCase)
- `rules` (too generic)
- `helper` (meaningless)

### File Names

**Standard files:**
- `SKILL.md` - Always exactly this (uppercase)
- `CLAUDE.md` - Always exactly this for comprehensive guides

**Pattern files:**
- `product-gallery.md` (descriptive kebab-case)
- `single-image-pattern.md`
- `checkboxlist-picker.md`

**Troubleshooting files:**
- `namespace-errors.md`
- `class-not-found.md`
- `common-issues.md`

---

## 📊 QUALITY CHECKLIST

### Before Creating Skill

- [ ] Clearly defined purpose
- [ ] Identified activation triggers (at least 3 phrases)
- [ ] Checked for existing similar skills
- [ ] Determined simple vs complex structure
- [ ] Listed required tools/commands
- [ ] Identified supporting resources needed

### SKILL.md Quality

- [ ] Complete YAML frontmatter (name, description)
- [ ] Description includes "USE WHEN" triggers
- [ ] "When to Activate" section present
- [ ] Instructions in imperative form (verb-first)
- [ ] Concrete examples with commands included
- [ ] References to deeper resources (if applicable)
- [ ] No duplication of global context
- [ ] Tested with realistic user requests
- [ ] File size reasonable (< 500 lines for simple)

### CLAUDE.md Quality (if complex)

- [ ] Clear purpose statement at top with emoji
- [ ] Comprehensive methodology documented
- [ ] All components explained with examples
- [ ] Patterns library included
- [ ] Best practices section
- [ ] Integration points documented
- [ ] Troubleshooting section with solutions
- [ ] Critical warnings highlighted
- [ ] Consistent formatting throughout (headers, code blocks)
- [ ] Cross-references work correctly

### Integration Quality

- [ ] Added to `.claude/global/SYSTEM.md` available_skills
- [ ] Added to `AGENTS.md` Skills Available list + trigger examples
- [ ] All file references work correctly
- [ ] Commands exist and are accessible
- [ ] Tools available (Laravel, Filament, npm, etc.)
- [ ] Pattern files present (if referenced)
- [ ] No broken links or paths

### Testing Validation

- [ ] Skill activates with natural language triggers
- [ ] All instructions execute correctly
- [ ] Examples work as documented
- [ ] File references resolve
- [ ] Commands run successfully
- [ ] Workflow completes end-to-end
- [ ] Tested with Vietnamese phrases
- [ ] Tested with English phrases

---

## 🚀 ADVANCED PATTERNS

### Pattern 1: Skill Composition

Skills can reference và work with other skills:

```markdown
## Related Skills

This skill works best with:
- **filament-rules**: For resource structure standards
- **image-management**: For adding image galleries
- **database-backup**: Before running migrations
```

### Pattern 2: Conditional Loading

Use progressive disclosure intelligently:

```markdown
## Quick Start
[Minimal instructions for common case]

## Advanced Usage
For comprehensive methodology: \`read .claude/skills/[name]/CLAUDE.md\`

## Specific Patterns
For product gallery pattern: \`read .claude/skills/[name]/patterns/product-gallery.md\`
For single image pattern: \`read .claude/skills/[name]/patterns/single-image.md\`
```

### Pattern 3: Tool Stack Documentation

Document full tool ecosystem:

```markdown
## Required Tools

### Primary
- **Laravel 12.x** - Framework
- **Filament 4.x** - Admin panel
- **PHP 8.2+** - Runtime

### Optional
- **Intervention Image** - Image processing
- **Spatie Backup** - Database backups

### Commands
\`\`\`bash
# Check versions
php artisan --version
npm --version
\`\`\`
```

### Pattern 4: Troubleshooting Library

Build knowledge base of solutions:

```
.claude/skills/skill-name/troubleshooting/
├── common-errors.md
├── namespace-issues.md
├── permission-problems.md
└── performance-tips.md
```

---

## 📚 INTEGRATION WITH PROJECT

### Global Context Inheritance

Skills automatically inherit from `.claude/global/SYSTEM.md`:
- Project structure
- Vietnamese-first principle
- Core tools (Laravel, Filament, npm)
- Database backup rules
- Filament namespace quirks

**Don't duplicate - reference!**

### Cross-Skill References

Skills can reference each other:

```markdown
## Prerequisites

Before using this skill:
1. Understand Filament basics: \`read .claude/skills/filament-rules/SKILL.md\`
2. Know image system: \`read .claude/skills/image-management/SKILL.md\`
```

### Command Integration

Skills describe WHEN and HOW to use commands:

```markdown
## Execution

**Run backup:**
\`\`\`bash
php artisan backup:run --only-db
\`\`\`

**Run migration:**
\`\`\`bash
php artisan migrate
\`\`\`

**Update schema:**
Edit \`mermaid.rb\` to reflect changes.
```

---

## 🎯 SKILL MAINTENANCE

### When to Update Skills

- Laravel/Filament version upgrades
- New best practices discovered
- User feedback reveals gaps
- Bugs found and fixed
- New patterns identified

### Version Control

**Track changes:**
```bash
git add .claude/skills/[skill-name]/
git commit -m "docs(skill): update [skill-name] - [what changed]"
```

**Document changes in CLAUDE.md:**
```markdown
## Changelog

### v2.0 (2025-11-09)
- Added CheckboxList pattern
- Removed custom ViewField approach
- Fixed namespace imports guide

### v1.0 (2025-11-08)
- Initial skill creation
```

### Deprecation

If skill becomes obsolete:
1. Mark deprecated in description: "(DEPRECATED - use X instead)"
2. Point to replacement skill in SKILL.md
3. Keep for grace period (1-2 months)
4. Remove from SYSTEM.md available_skills
5. Archive directory (don't delete immediately)

---

## 💡 KEY PRINCIPLES (RECAP)

1. **Progressive disclosure**: SKILL.md = quick → CLAUDE.md = deep
2. **Clear activation**: USE WHEN phrases drive discovery
3. **Imperative instructions**: Verb-first, actionable
4. **No duplication**: Reference global context
5. **Self-contained**: Work independently with clear deps
6. **Template-driven**: Consistent structure
7. **Test thoroughly**: Natural language validation
8. **Iterate constantly**: Living documents
9. **Document clearly**: Future AI agents will thank you
10. **Follow standards**: Anthropic + project conventions

---

## 🚨 COMMON MISTAKES TO AVOID

### Mistake 1: Vague Descriptions
❌ "A skill for development work"
✅ "Filament 4.x resource generation with namespace fixes. USE WHEN 'tạo resource', 'create resource'"

### Mistake 2: Missing USE WHEN
❌ "Handles image uploads in Laravel"
✅ "Image upload with WebP conversion. USE WHEN 'thêm ảnh', 'upload image', 'add gallery'"

### Mistake 3: Not Imperative Form
❌ "You should create a backup before migration"
✅ "Create backup before migration: `php artisan backup:run --only-db`"

### Mistake 4: Duplicating Global Context
❌ Copying entire Filament namespace rules into every skill
✅ "Follow Filament namespace rules from global context"

### Mistake 5: No Examples
❌ "Use the command to process files"
✅ 
```bash
# Process single file
command file.txt

# Process directory
command --dir ./images/
```

### Mistake 6: Over-complicating Simple Skills
❌ Creating CLAUDE.md + 5 subdirs for 50-line workflow
✅ Keep it simple with just SKILL.md

### Mistake 7: Under-documenting Complex Skills
❌ Putting 1000 lines in SKILL.md without CLAUDE.md
✅ Split: SKILL.md (quick ref) + CLAUDE.md (full guide)

### Mistake 8: Broken References
❌ `read .claude/skills/nonexistent/file.md`
✅ Verify all paths before committing

### Mistake 9: Skipping Testing
❌ Writing skill and committing without validation
✅ Test with actual trigger phrases first

### Mistake 10: Forgetting SYSTEM.md Update
❌ Creating skill but not adding to available_skills
✅ Always update global context

---

## 🎓 LEARNING FROM EXISTING SKILLS

### Study These Examples in This Project

**For simple skills:**
- `filament-resource-generator` - Clean workflow automation
- `database-backup` - Straightforward safety workflow

**For complex skills:**
- `filament-rules` - Comprehensive standards (1000+ lines)
- `image-management` - Architecture guide with patterns

**For meta skills:**
- `create-skill` - This skill itself! (meta-documentation)

**Read their structure, understand patterns, apply to new skills.**

---

## 🔧 TROUBLESHOOTING SKILL CREATION

### Skill Won't Activate

**Check:**
1. Is it in SYSTEM.md `<available_skills>`?
2. Is it in AGENTS.md Skills Available list?
3. Does description have "USE WHEN" triggers?
4. Are triggers specific enough?
5. Test with exact trigger phrases

**Debug:**
```bash
# Verify skill exists
ls .claude/skills/[skill-name]/SKILL.md

# Check SYSTEM.md
grep -A 3 "<name>[skill-name]</name>" .claude/global/SYSTEM.md

# Check AGENTS.md
grep "your-skill-name" AGENTS.md
```

### Instructions Don't Work

**Check:**
1. Are all paths absolute or correctly relative?
2. Are commands available? (`which php`, `which npm`)
3. Are files referenced present?
4. Test step-by-step manually

### Complex Skill Overwhelming

**Solution:**
1. Split into SKILL.md (200 lines) + CLAUDE.md (full)
2. Create pattern subdirectories
3. Use progressive disclosure
4. Reference rather than include

### Skill Too Generic

**Solution:**
1. Narrow the scope
2. Add specific trigger phrases
3. Define clear boundaries
4. Mention specific tools/methods in description

---

## 📚 FURTHER READING

### Anthropic Resources
- Official Claude skills documentation: https://code.claude.com/docs/en/skills
- Anthropic skills repository: https://github.com/anthropics/skills
- Best practices guide: https://docs.claude.com/en/docs/agents-and-tools/agent-skills/best-practices

### Daniel Miessler's Personal AI Infrastructure
- GitHub: https://github.com/danielmiessler/Personal_AI_Infrastructure
- create-skill example: `.claude/skills/create-skill/`
- Blog: https://danielmiessler.com/blog/when-to-use-skills-vs-commands-vs-agents

### Project-Specific Resources
- Global context: `.claude/global/SYSTEM.md`
- Existing skills: `.claude/skills/`
- Legacy docs: `docs/` (being migrated)
- Project plan: `PLAN.md`

---

## 🎯 FINAL CHECKLIST: BEFORE DECLARING SKILL COMPLETE

- [ ] **Purpose** - Crystal clear what skill does
- [ ] **Structure** - Correct simple/complex pattern chosen
- [ ] **Description** - Includes USE WHEN with 3+ triggers
- [ ] **Instructions** - Imperative form throughout
- [ ] **Examples** - Concrete bash/code examples
- [ ] **References** - All paths verified working
- [ ] **Integration SYSTEM.md** - Added to available_skills
- [ ] **Integration AGENTS.md** - Added to Skills Available + trigger examples
- [ ] **Testing** - Validated with Vietnamese + English phrases
- [ ] **Documentation** - CLAUDE.md if complex (500+ lines content)
- [ ] **Patterns** - Subdirectories if multiple patterns
- [ ] **Quality** - Reviewed against full checklist
- [ ] **Version Control** - Committed with descriptive message
- [ ] **Vietnamese** - All user-facing content in Vietnamese
- [ ] **No Duplication** - References global context, not copying

**If all checked, skill is production-ready! 🚀**

---

## 📞 NEED HELP?

**Creating your first skill?**
1. Start with simple workflow skill
2. Use filament-resource-generator as template
3. Test with natural language
4. Iterate based on usage

**Stuck on complex skill?**
1. Study filament-rules structure
2. Split into SKILL.md + CLAUDE.md
3. Add patterns gradually
4. Progressive disclosure is key

**Skill not activating?**
1. Check triggers in description
2. Test with exact phrases
3. Verify SYSTEM.md integration
4. Read troubleshooting section

**Remember: Skills improve over time. Start simple, iterate constantly!**

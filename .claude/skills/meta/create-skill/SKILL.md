---
name: skill-creator
description: Guide for creating effective skills. This skill should be used when users want to create a new skill (or update an existing skill) that extends Claude's capabilities with specialized knowledge, workflows, or tool integrations.
license: Complete terms in LICENSE.txt
---
## About Skills

Skills are modular, self-contained packages that extend Claude's capabilities by providing
specialized knowledge, workflows, and tools. Think of them as "onboarding guides" for specific
domains or tasks—they transform Claude from a general-purpose agent into a specialized agent
equipped with procedural knowledge that no model can fully possess.

### What Skills Provide

1. Specialized workflows - Multi-step procedures for specific domains
2. Tool integrations - Instructions for working with specific file formats or APIs
3. Domain expertise - Company-specific knowledge, schemas, business logic
4. Bundled resources - Scripts, references, and assets for complex and repetitive tasks

### Anatomy of a Skill

Every skill consists of a required SKILL.md file and optional bundled resources:

```
skill-name/
├── SKILL.md (required)
│   ├── YAML frontmatter metadata (required)
│   │   ├── name: (required)
│   │   └── description: (required)
│   └── Markdown instructions (required)
└── Bundled Resources (optional)
    ├── scripts/          - Executable code (Python/Bash/etc.)
    ├── references/       - Documentation intended to be loaded into context as needed
    └── assets/           - Files used in output (templates, icons, fonts, etc.)
```

#### Requirements (important)

- Skill should be combined into specific topics, for example: `cloudflare`, `cloudflare-r2`, `cloudflare-workers`, `docker`, `gcloud` should be combined into `devops`
- `SKILL.md` should be **less than 200 lines** and include the references of related markdown files and scripts.
- Each script or referenced markdown file should be also **less than 200 lines**, remember that you can always split them into multiple files (**progressive disclosure** principle).
- Descriptions in metadata of `SKILL.md` files should be both concise and still contains enough usecases of the references and scripts, this will help skills can be activated automatically during the implementation process of Claude Code.
- **Referenced markdowns**:
  - Sacrifice grammar for the sake of concision when writing these files.
  - Can reference other markdown files or scripts as well.
- **Referenced scripts**:
  - Prefer nodejs or python scripts instead of bash script, because bash scripts are not well-supported on Windows.
  - If you're going to write python scripts, make sure you have `requirements.txt`
  - Make sure scripts respect `.env` file follow this order: `process.env` > `.claude/skills/${SKILL}/.env` > `.claude/skills/.env` > `.claude/.env` 
  - Create `.env.example` file to show the required environment variables.
  - Always write tests for these scripts.

**Why?**
Better **context engineering**: inspired from **progressive disclosure** technique of Agent Skills, when agent skills are activated, Claude Code will consider to load only relevant files into the context, instead of reading all long `SKILL.md` as before.

#### SKILL.md (required)

**File name:** `SKILL.md` (uppercase)
**File size:** Under 200 lines, if you need more, plit it to multiple files in `references` folder.

**Metadata Quality:** The `name` and `description` in YAML frontmatter determine when Claude will use the skill. Be specific about what the skill does and when to use it. Use the third-person (e.g. "This skill should be used when..." instead of "Use this skill when...").

#### Bundled Resources (optional)

##### Scripts (`scripts/`)

Executable code (Python/Bash/etc.) for tasks that require deterministic reliability or are repeatedly rewritten.

- **When to include**: When the same code is being rewritten repeatedly or deterministic reliability is needed
- **Example**: `scripts/rotate_pdf.py` for PDF rotation tasks
- **Benefits**: Token efficient, deterministic, may be executed without loading into context
- **Note**: Scripts may still need to be read by Claude for patching or environment-specific adjustments

##### References (`references/`)

Documentation and reference material intended to be loaded as needed into context to inform Claude's process and thinking.

- **When to include**: For documentation that Claude should reference while working
- **Examples**: `references/finance.md` for financial schemas, `references/mnda.md` for company NDA template, `references/policies.md` for company policies, `references/api_docs.md` for API specifications
- **Use cases**: Database schemas, API documentation, domain knowledge, company policies, detailed workflow guides
- **Benefits**: Keeps SKILL.md lean, loaded only when Claude determines it's needed
- **Best practice**: If files are large (>10k words), include grep search patterns in SKILL.md
- **Avoid duplication**: Information should live in either SKILL.md or references files, not both. Prefer references files for detailed information unless it's truly core to the skill—this keeps SKILL.md lean while making information discoverable without hogging the context window. Keep only essential procedural instructions and workflow guidance in SKILL.md; move detailed reference material, schemas, and examples to references files.

##### Assets (`assets/`)

Files not intended to be loaded into context, but rather used within the output Claude produces.

- **When to include**: When the skill needs files that will be used in the final output
- **Examples**: `assets/logo.png` for brand assets, `assets/slides.pptx` for PowerPoint templates, `assets/frontend-template/` for HTML/React boilerplate, `assets/font.ttf` for typography
- **Use cases**: Templates, images, icons, boilerplate code, fonts, sample documents that get copied or modified
- **Benefits**: Separates output resources from documentation, enables Claude to use files without loading them into context

### Progressive Disclosure Design Principle

Skills use a three-level loading system to manage context efficiently:

1. **Metadata (name + description)** - Always in context (~100 words)
2. **SKILL.md body** - When skill triggers (<5k words)
3. **Bundled resources** - As needed by Claude (Unlimited*)

*Unlimited because scripts can be executed without reading into context window.

## References
- [Agent Skills](https://docs.claude.com/en/docs/claude-code/skills.md)
- [Agent Skills Spec](.claude/skills/agent_skills_spec.md)
- [Agent Skills Overview](https://docs.claude.com/en/docs/agents-and-tools/agent-skills/overview.md)
- [Best Practices](https://docs.claude.com/en/docs/agents-and-tools/agent-skills/best-practices.md)

---

## Skill Creation Workflow

**Full Process (9 Steps):**
1. Understand the skill with concrete examples
2. **Analyze category placement** ⚠️ NEW - Use intelligent grouping
3. Plan reusable skill contents (scripts, references, assets)
4. Initialize skill using `scripts/init_skill.py`
5. Edit SKILL.md and bundled resources
6. **Register skill in SYSTEM.md and AGENTS.md** ⚠️ CRITICAL
7. **Sync to choose-skill** ⚠️ CRITICAL
8. Package skill using `scripts/package_skill.py`
9. Iterate based on testing feedback

**⚠️ NEW Step 2 - Intelligent Category Placement:**
Run `scripts/suggest_skill_group.py` to get AI-powered category suggestions:
```bash
python scripts/suggest_skill_group.py --skill "skill-name" --description "skill description"
```

The script will:
- Analyze skill domain and keywords
- Suggest best category with confidence score
- Detect new category opportunities (if 3+ related skills exist)
- Show refactor recommendations for overcrowded categories

Also check organization health periodically:
```bash
python scripts/suggest_skill_group.py --analyze-all
```

This prevents category sprawl and maintains optimal skill organization!

**⚠️ CRITICAL Step 6 - Skill Registration:**
Every new skill MUST be registered in two places:
- **SYSTEM.md** - Add `<skill>` block with name, description, and location
- **AGENTS.md** - Add trigger examples and update category list

Without registration, Claude cannot discover or activate the skill!

**⚠️ CRITICAL Step 7 - Sync to choose-skill:**
Run `scripts/sync_to_choose_skill.py` to update choose-skill's catalog:
```bash
python scripts/sync_to_choose_skill.py path/to/your/skill
```

This ensures choose-skill can recommend your new skill! Script will:
- Parse skill metadata and category
- Update choose-skill/references/skills-catalog.md
- Show diff and ask for confirmation

Skip ONLY if skill is internal/private and shouldn't be recommended.

## References

**Skill Creation Process:** `read .claude/skills/meta/create-skill/references/skill-creation-process.md`
- **Step 2 details:** How to use intelligent category placement
- **Step 6 details:** How to register skills in SYSTEM.md and AGENTS.md
- **Step 7 details:** How to sync to choose-skill catalog

**Intelligent Grouping:** `read .claude/skills/meta/create-skill/references/skill-grouping-intelligence.md`
- Domain keyword patterns and matching algorithm
- New category detection triggers
- Refactor opportunity patterns
- Confidence scoring and decision logic

**Optimization & Refactoring:**
- `read .claude/skills/meta/create-skill/references/optimization-report.md` - Complete optimization results (16/16 skills)
- `read .claude/skills/meta/create-skill/references/refactor-guide.md` - Step-by-step refactoring guide
- `read .claude/skills/meta/create-skill/references/refactor-plan.md` - Original refactoring strategy

**Categories & Organization:**
- `read .claude/global/SKILLS_CONTEXT.md` - Current skills structure (single source of truth)
- `read .claude/skills/meta/create-skill/references/categories-structure.md` - Category system design (deprecated, use SKILLS_CONTEXT.md)

**Automation Tools:**
- `scripts/suggest_skill_group.py` - Intelligent category suggestion and refactor detection ⚠️ NEW
- `scripts/init_skill.py` - Initialize new skill with template
- `scripts/quick_validate.py` - Validate skill structure
- `scripts/sync_to_choose_skill.py` - Sync skill to choose-skill catalog
- `scripts/package_skill.py` - Package skill as .zip
- `scripts/smart_refactor.py` - Auto-refactor skills to <200 lines (supports categories)
- `scripts/auto_refactor_skills.py` - Batch validation (supports categories)
- `scripts/migrate_to_categories.py` - Organize skills into categories

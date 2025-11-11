# Skills Categories Structure

## Proposed Structure

```
.claude/skills/
├── filament/                    # Filament 4.x Development (Laravel 12)
│   ├── filament-rules
│   ├── filament-resource-generator
│   ├── filament-form-debugger
│   └── image-management
│
├── fullstack/                   # Full-Stack Development
│   ├── backend-dev-guidelines
│   ├── frontend-dev-guidelines
│   ├── ux-designer
│   └── ui-styling
│
├── api/                         # API & Documentation
│   ├── api-design-principles
│   ├── api-cache-invalidation
│   └── api-documentation-writer
│
├── workflows/                   # Workflows & Automation
│   ├── database-backup
│   ├── systematic-debugging
│   ├── product-search-scoring
│   └── docs-seeker
│
└── meta/                        # Meta Skills (skill management)
    └── create-skill
```

## Category Descriptions

### filament/ (4 skills)
Laravel 12 + Filament 4.x specific skills for admin panel development.
- Schema namespace quirks
- Vietnamese UI
- Observer patterns
- Polymorphic image management

### fullstack/ (4 skills)
Full-stack development guidelines and design patterns.
- Node.js/Express/TypeScript backend
- React/TypeScript/Suspense frontend
- UI/UX design principles
- shadcn/ui + Tailwind styling

### api/ (3 skills)
API design, caching, and documentation.
- REST/GraphQL best practices
- Cache invalidation strategies
- Comprehensive API documentation

### workflows/ (4 skills)
Development workflows, debugging, and automation.
- Safe database migrations
- Systematic debugging framework
- Search optimization
- Documentation discovery

### meta/ (1 skill)
Skills for managing skills themselves.
- Skill creation, validation, packaging
- Optimization and refactoring tools

## Benefits

1. **Scalability** - Easy to add new skills without cluttering root
2. **Discovery** - Related skills grouped together
3. **Maintenance** - Clear ownership and context
4. **Navigation** - Easier to browse and find skills
5. **Documentation** - Category-level docs possible

## Migration Plan

1. Create category directories
2. Move skills to categories
3. Update all references:
   - SYSTEM.md `<available_skills>`
   - AGENTS.md triggers
   - Skill internal references
   - Validation scripts
4. Test all activations work
5. Update create-skill to support categories

## Path Updates

**Before:**
```
read .claude/skills/filament-rules/SKILL.md
```

**After:**
```
read .claude/skills/filament/filament-rules/SKILL.md
```

**SYSTEM.md:**
```xml
<skill>
<name>filament-rules</name>
<location>user/filament</location>
</skill>
```

## Backward Compatibility

Option 1: Hard migration (clean break)
- Move all skills immediately
- Update all references
- Break old paths temporarily

Option 2: Symlinks (gradual migration)
- Keep symlinks in root for backward compat
- Gradually update references
- Remove symlinks after migration complete

**Recommended:** Option 1 (clean, no confusion)

## Implementation Script

Create `scripts/migrate_to_categories.py` that:
1. Creates category directories
2. Moves skills to categories
3. Updates SYSTEM.md
4. Updates AGENTS.md
5. Updates skill internal references
6. Validates all paths work
7. Reports changes

## Testing

After migration:
```bash
# Validate structure
python .claude/skills/meta/create-skill/scripts/auto_refactor_skills.py \
  --skills-dir .claude/skills

# Test skill activation
"Tạo resource mới" -> filament/filament-resource-generator
"Bug không fix được" -> workflows/systematic-debugging
```

## Future Categories

As project grows, potential new categories:
- `testing/` - Testing frameworks and patterns
- `deployment/` - CI/CD, Docker, deployment
- `security/` - Security best practices
- `performance/` - Optimization techniques
- `mobile/` - Mobile development (if added)

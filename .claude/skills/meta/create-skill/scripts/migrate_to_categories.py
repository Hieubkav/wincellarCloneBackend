#!/usr/bin/env python3
"""
Migrate Skills to Categories - Organize skills into logical categories

Categories:
- filament/      (Filament 4.x specific)
- fullstack/     (Backend/Frontend/UX/UI)
- api/           (API design, cache, docs)
- workflows/     (Debugging, backup, search, docs-seeker)
- meta/          (create-skill)

Usage:
    python migrate_to_categories.py --skills-dir .claude/skills [--dry-run]
"""

import sys
import shutil
import re
from pathlib import Path
from typing import Dict, List


# Category mapping
CATEGORIES = {
    'filament': [
        'filament-rules',
        'filament-resource-generator',
        'filament-form-debugger',
        'image-management'
    ],
    'fullstack': [
        'backend-dev-guidelines',
        'frontend-dev-guidelines',
        'ux-designer',
        'ui-styling'
    ],
    'api': [
        'api-design-principles',
        'api-cache-invalidation',
        'api-documentation-writer'
    ],
    'workflows': [
        'database-backup',
        'systematic-debugging',
        'product-search-scoring',
        'docs-seeker'
    ],
    'meta': [
        'create-skill'
    ]
}


def get_all_skills(skills_dir: Path) -> List[str]:
    """Get all skill directories"""
    skills = []
    for item in skills_dir.iterdir():
        if item.is_dir() and (item / 'SKILL.md').exists():
            skills.append(item.name)
    return skills


def validate_categories() -> bool:
    """Validate all skills are categorized"""
    all_skills = []
    for skills in CATEGORIES.values():
        all_skills.extend(skills)
    
    # Check duplicates
    if len(all_skills) != len(set(all_skills)):
        print("❌ Error: Duplicate skills in categories")
        return False
    
    return True


def migrate_skills(skills_dir: Path, dry_run: bool = False) -> Dict:
    """Migrate skills to categories"""
    
    if not validate_categories():
        return None
    
    results = {
        'moved': [],
        'errors': [],
        'updated_refs': []
    }
    
    print(f"🚀 Migrating skills to categories...")
    print(f"   Mode: {'DRY RUN' if dry_run else 'ACTUAL MIGRATION'}\n")
    
    # Create category directories
    for category in CATEGORIES.keys():
        category_dir = skills_dir / category
        if not dry_run:
            category_dir.mkdir(exist_ok=True)
        print(f"✓ Category: {category}/")
    
    print()
    
    # Move skills
    for category, skills in CATEGORIES.items():
        for skill_name in skills:
            source = skills_dir / skill_name
            target = skills_dir / category / skill_name
            
            if not source.exists():
                print(f"⚠️  Skip: {skill_name} (not found)")
                results['errors'].append(f"{skill_name} not found")
                continue
            
            if target.exists():
                print(f"⚠️  Skip: {skill_name} (already in {category}/)")
                continue
            
            print(f"→ Moving {skill_name} to {category}/")
            
            if not dry_run:
                shutil.move(str(source), str(target))
            
            results['moved'].append({
                'skill': skill_name,
                'category': category,
                'old_path': f'.claude/skills/{skill_name}',
                'new_path': f'.claude/skills/{category}/{skill_name}'
            })
    
    return results


def update_system_md(skills_dir: Path, results: Dict, dry_run: bool = False) -> bool:
    """Update SYSTEM.md with new paths"""
    system_md = skills_dir.parent / 'global' / 'SYSTEM.md'
    
    if not system_md.exists():
        print(f"⚠️  SYSTEM.md not found: {system_md}")
        return False
    
    print(f"\n📝 Updating SYSTEM.md...")
    
    content = system_md.read_text(encoding='utf-8')
    original_content = content
    
    # Update location tags
    for move in results['moved']:
        skill_name = move['skill']
        category = move['category']
        
        # Pattern: <name>skill-name</name>\n<description>...</description>\n<location>user</location>
        pattern = f"(<name>{skill_name}</name>.*?<location>)user(</location>)"
        replacement = f"\\1user/{category}\\2"
        
        content = re.sub(pattern, replacement, content, flags=re.DOTALL)
    
    if content != original_content:
        if not dry_run:
            system_md.write_text(content, encoding='utf-8')
        print(f"✓ Updated {len(results['moved'])} skill locations in SYSTEM.md")
        return True
    else:
        print("⚠️  No changes needed in SYSTEM.md")
        return False


def update_skill_references(skills_dir: Path, results: Dict, dry_run: bool = False) -> int:
    """Update internal skill references"""
    print(f"\n📝 Updating skill internal references...")
    
    updated_count = 0
    
    for category in CATEGORIES.keys():
        category_dir = skills_dir / category
        if not category_dir.exists():
            continue
        
        for skill_dir in category_dir.iterdir():
            if not skill_dir.is_dir():
                continue
            
            skill_md = skill_dir / 'SKILL.md'
            if not skill_md.exists():
                continue
            
            content = skill_md.read_text(encoding='utf-8')
            original_content = content
            
            # Update references to other skills
            # Pattern: read .claude/skills/skill-name/
            for move in results['moved']:
                old_path = f".claude/skills/{move['skill']}/"
                new_path = f".claude/skills/{move['category']}/{move['skill']}/"
                content = content.replace(old_path, new_path)
            
            if content != original_content:
                if not dry_run:
                    skill_md.write_text(content, encoding='utf-8')
                print(f"  ✓ Updated {skill_dir.name}")
                updated_count += 1
    
    print(f"✓ Updated {updated_count} skills' internal references")
    return updated_count


def generate_report(results: Dict):
    """Generate migration report"""
    print(f"\n{'='*80}")
    print("Migration Summary")
    print(f"{'='*80}")
    
    if not results:
        print("❌ Migration failed")
        return
    
    print(f"Skills moved: {len(results['moved'])}")
    print(f"Errors: {len(results['errors'])}")
    
    if results['moved']:
        print(f"\n✅ Successfully migrated:")
        for move in results['moved']:
            print(f"   {move['skill']:35} → {move['category']}/")
    
    if results['errors']:
        print(f"\n⚠️  Errors:")
        for error in results['errors']:
            print(f"   - {error}")
    
    print(f"\n{'='*80}")
    print("Next Steps:")
    print(f"{'='*80}")
    print("1. Update AGENTS.md manually (trigger phrases)")
    print("2. Test skill activation with natural language")
    print("3. Run validation:")
    print("   python .claude/skills/meta/create-skill/scripts/auto_refactor_skills.py \\")
    print("     --skills-dir .claude/skills")
    print("4. Update any external documentation")


def main():
    dry_run = '--dry-run' in sys.argv
    auto_confirm = '--yes' in sys.argv or '-y' in sys.argv
    
    if len(sys.argv) < 3 or '--skills-dir' not in sys.argv:
        print("Usage: python migrate_to_categories.py --skills-dir .claude/skills [--dry-run] [--yes]")
        sys.exit(1)
    
    skills_dir_arg = sys.argv[sys.argv.index('--skills-dir') + 1]
    skills_dir = Path(skills_dir_arg).resolve()
    
    if not skills_dir.exists():
        print(f"❌ Skills directory not found: {skills_dir}")
        sys.exit(1)
    
    print(f"{'='*80}")
    print("Skills Category Migration")
    print(f"{'='*80}")
    print(f"Directory: {skills_dir}")
    print(f"Mode: {'🔍 DRY RUN (no changes)' if dry_run else '⚠️  ACTUAL MIGRATION'}")
    print(f"{'='*80}\n")
    
    if not dry_run and not auto_confirm:
        confirm = input("⚠️  This will reorganize all skills. Continue? (yes/no): ")
        if confirm.lower() != 'yes':
            print("Cancelled.")
            sys.exit(0)
    
    # Migrate
    results = migrate_skills(skills_dir, dry_run)
    
    if not results:
        sys.exit(1)
    
    # Update SYSTEM.md
    update_system_md(skills_dir, results, dry_run)
    
    # Update skill references
    update_skill_references(skills_dir, results, dry_run)
    
    # Report
    generate_report(results)
    
    if dry_run:
        print(f"\n💡 This was a DRY RUN. No files were changed.")
        print(f"   Run without --dry-run to apply changes.")
    else:
        print(f"\n✅ Migration complete!")


if __name__ == "__main__":
    main()

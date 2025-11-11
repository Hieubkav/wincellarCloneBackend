#!/usr/bin/env python3
"""
Sync choose-skill references with SKILLS_CONTEXT.md

This script auto-generates skills-catalog.md from SKILLS_CONTEXT.md to keep
choose-skill always up-to-date with the latest skills.

Usage:
    python sync_choose_skill.py

Author: Droid AI
Version: 1.0.0
Last Updated: 2025-11-11
"""

import os
import re
from pathlib import Path
from datetime import datetime


# Paths
# Current file: .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
# Go up 5 levels to repo root
SCRIPT_DIR = Path(__file__).parent  # scripts/
CHOOSE_SKILL_DIR = SCRIPT_DIR.parent  # choose-skill/
META_DIR = CHOOSE_SKILL_DIR.parent  # meta/
SKILLS_DIR = META_DIR.parent  # skills/
CLAUDE_DIR = SKILLS_DIR.parent  # .claude/
REPO_ROOT = CLAUDE_DIR.parent  # repo root/

SKILLS_CONTEXT_PATH = CLAUDE_DIR / "global/SKILLS_CONTEXT.md"
SKILLS_CATALOG_PATH = CHOOSE_SKILL_DIR / "references/skills-catalog.md"


def extract_skills_from_context(content: str) -> dict:
    """Extract skills information from SKILLS_CONTEXT.md"""
    
    # Extract total count
    total_match = re.search(r'\*\*Total Skills:\*\* (\d+) skills across (\d+) categories', content)
    total_skills = int(total_match.group(1)) if total_match else 0
    total_categories = int(total_match.group(2)) if total_match else 0
    
    # Extract last updated
    updated_match = re.search(r'\*\*Last Updated:\*\* (.+)', content)
    last_updated = updated_match.group(1).strip() if updated_match else "Unknown"
    
    # Extract category information using sections
    categories = {}
    
    # Find all category sections (### category/ (X skills))
    category_pattern = r'### ([^/]+)/ \((\d+) skills?\)\s+\*\*Description:\*\* ([^\n]+)\s+\*\*Skills:\*\*\s+([^*]+)'
    
    for match in re.finditer(category_pattern, content, re.MULTILINE):
        cat_name = match.group(1).strip()
        cat_count = int(match.group(2))
        cat_desc = match.group(3).strip()
        skills_text = match.group(4).strip()
        
        # Parse skills list
        skills = []
        for skill_line in skills_text.split('\n'):
            skill_line = skill_line.strip()
            if skill_line.startswith('- `') or skill_line.startswith('-'):
                # Extract skill name and description
                # Format: - `skill-name` - Description here
                skill_match = re.match(r'-\s+`([^`]+)`\s+-\s+(.+)', skill_line)
                if skill_match:
                    skill_name = skill_match.group(1)
                    skill_desc = skill_match.group(2)
                    skills.append({
                        'name': skill_name,
                        'description': skill_desc
                    })
        
        categories[cat_name] = {
            'count': cat_count,
            'description': cat_desc,
            'skills': skills
        }
    
    return {
        'total_skills': total_skills,
        'total_categories': total_categories,
        'last_updated': last_updated,
        'categories': categories
    }


def read_skill_details(skill_path: Path) -> dict:
    """Read additional details from a skill's SKILL.md file"""
    skill_md = skill_path / "SKILL.md"
    
    if not skill_md.exists():
        return {}
    
    content = skill_md.read_text(encoding='utf-8')
    
    details = {
        'when_to_use': [],
        'key_features': []
    }
    
    # Extract "When to Use" section
    when_match = re.search(r'## When [Tt]o [Uu]se.*?\n+(.*?)(?=\n##|\Z)', content, re.DOTALL)
    if when_match:
        when_text = when_match.group(1).strip()
        for line in when_text.split('\n'):
            line = line.strip()
            if line.startswith('-') or line.startswith('*'):
                details['when_to_use'].append(line[1:].strip())
    
    # Extract "Key Features" or similar sections
    features_match = re.search(r'## (?:Key Features|Features|Core Capabilities).*?\n+(.*?)(?=\n##|\Z)', content, re.DOTALL)
    if features_match:
        features_text = features_match.group(1).strip()
        for line in features_text.split('\n'):
            line = line.strip()
            if line.startswith('-') or line.startswith('*'):
                details['key_features'].append(line[1:].strip())
    
    return details


def generate_skills_catalog(data: dict) -> str:
    """Generate skills-catalog.md content"""
    
    output = []
    
    # Header
    output.append("# Skills Catalog - Complete Reference\n")
    output.append(f"**Total Skills:** {data['total_skills']} skills across {data['total_categories']} categories  ")
    output.append(f"**Last Updated:** {data['last_updated']}\n")
    output.append("> **Auto-generated** from SKILLS_CONTEXT.md via `sync_choose_skill.py`  ")
    output.append("> **Source of Truth:** `.claude/global/SKILLS_CONTEXT.md`\n")
    output.append("---\n")
    
    # Quick Navigation
    output.append("## Quick Navigation\n")
    for cat_name, cat_data in sorted(data['categories'].items()):
        anchor = cat_name.lower().replace(' ', '-')
        output.append(f"- [{cat_name.title()} ({cat_data['count']} skills)](#{anchor})")
    output.append("\n---\n")
    
    # Categories and Skills
    for cat_name, cat_data in sorted(data['categories'].items()):
        output.append(f"## {cat_name.title()} - {cat_data['description']}\n")
        output.append(f"**Total Skills:** {cat_data['count']}\n")
        
        for idx, skill in enumerate(cat_data['skills'], 1):
            skill_name = skill['name']
            skill_desc = skill['description']
            
            output.append(f"### {idx}. {skill_name}\n")
            output.append(f"**Path:** `.claude/skills/{cat_name}/{skill_name}/SKILL.md`\n")
            output.append(f"**Description:**  ")
            output.append(f"{skill_desc}\n")
            
            # Try to read additional details from the actual skill file
            skill_path = SKILLS_DIR / cat_name / skill_name
            if skill_path.exists():
                details = read_skill_details(skill_path)
                
                if details.get('when_to_use'):
                    output.append(f"**When to Use:**")
                    for item in details['when_to_use'][:5]:  # Limit to 5 items
                        output.append(f"- {item}")
                    output.append("")
                
                if details.get('key_features'):
                    output.append(f"**Key Features:**")
                    for item in details['key_features'][:5]:  # Limit to 5 items
                        output.append(f"- {item}")
                    output.append("")
            
            output.append("---\n")
        
        output.append("")
    
    # Footer
    output.append("## Notes\n")
    output.append("- This catalog is **auto-generated** from SKILLS_CONTEXT.md")
    output.append("- For the most up-to-date information, always check SKILLS_CONTEXT.md first")
    output.append("- To add/update skills: Modify SKILLS_CONTEXT.md, then run `sync_choose_skill.py`")
    output.append(f"- Last sync: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
    
    return '\n'.join(output)


def update_recommendation_patterns(data: dict, patterns_path: Path):
    """Update recommendation-patterns.md with new skill names"""
    
    if not patterns_path.exists():
        print(f"[!] Warning: {patterns_path} not found, skipping update")
        return
    
    content = patterns_path.read_text(encoding='utf-8')
    
    # Replace old skill names with new merged ones
    replacements = {
        'api-design-principles': 'api-design-patterns',
        'api-best-practices': 'api-design-patterns',
        'analyzing-database-indexes': 'database-performance',
        'analyzing-query-performance': 'database-performance',
        'generating-database-seed-data': 'database-data-generation',
        'generating-test-data': 'database-data-generation',
        'scanning-database-security': 'database-validation',
        'validating-database-integrity': 'database-validation',
        'generating-database-documentation': 'designing-database-schemas',
        'landing-page-guide': 'frontend-components',
    }
    
    updated = content
    changes_made = False
    
    for old_name, new_name in replacements.items():
        if old_name in updated:
            updated = updated.replace(f'`{old_name}`', f'`{new_name}`')
            updated = updated.replace(f'{old_name}', f'{new_name}')
            changes_made = True
            print(f"    [+] Replaced {old_name} -> {new_name}")
    
    if changes_made:
        patterns_path.write_text(updated, encoding='utf-8')
        print(f"[OK] Updated recommendation-patterns.md")
    else:
        print(f"[OK] No updates needed for recommendation-patterns.md")


def main():
    """Main sync function"""
    
    print("[*] Syncing choose-skill with SKILLS_CONTEXT.md...\n")
    
    # Check if SKILLS_CONTEXT.md exists
    if not SKILLS_CONTEXT_PATH.exists():
        print(f"[X] Error: SKILLS_CONTEXT.md not found at {SKILLS_CONTEXT_PATH}")
        return 1
    
    # Read SKILLS_CONTEXT.md
    print(f"[+] Reading {SKILLS_CONTEXT_PATH.name}...")
    content = SKILLS_CONTEXT_PATH.read_text(encoding='utf-8')
    
    # Extract skills data
    print("[+] Extracting skills information...")
    data = extract_skills_from_context(content)
    
    print(f"    Found: {data['total_skills']} skills across {data['total_categories']} categories")
    print(f"    Categories: {', '.join(sorted(data['categories'].keys()))}\n")
    
    # Generate skills-catalog.md
    print("[+] Generating skills-catalog.md...")
    catalog_content = generate_skills_catalog(data)
    
    # Write to file
    SKILLS_CATALOG_PATH.parent.mkdir(parents=True, exist_ok=True)
    SKILLS_CATALOG_PATH.write_text(catalog_content, encoding='utf-8')
    
    print(f"[OK] Generated {SKILLS_CATALOG_PATH.name}")
    print(f"     Total lines: {len(catalog_content.splitlines())}\n")
    
    # Update recommendation-patterns.md
    print("[+] Updating recommendation-patterns.md...")
    patterns_path = SKILLS_CATALOG_PATH.parent / "recommendation-patterns.md"
    update_recommendation_patterns(data, patterns_path)
    
    print("\n[*] Sync complete!")
    print(f"\n[INFO] Summary:")
    print(f"   - Total skills: {data['total_skills']}")
    print(f"   - Total categories: {data['total_categories']}")
    print(f"   - Last updated: {data['last_updated']}")
    print(f"\n[NEXT] Next steps:")
    print(f"   1. Review generated files")
    print(f"   2. Commit changes to git")
    print(f"   3. Re-run this script after any skills changes\n")
    
    return 0


if __name__ == "__main__":
    exit(main())

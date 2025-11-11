#!/usr/bin/env python3
r"""
Sync to choose-skill - Updates choose-skill catalog with new/updated skill

This script automatically updates choose-skill's skills-catalog.md with the
latest information from a skill, ensuring choose-skill can recommend it.

Usage:
    sync_to_choose_skill.py <skill-path>

Examples:
    sync_to_choose_skill.py ../../filament/my-new-skill
    sync_to_choose_skill.py ../../../skills/api/my-api-skill
    sync_to_choose_skill.py E:\path\to\skills\workflows\my-workflow

The script will:
1. Parse the skill's SKILL.md (name, description)
2. Detect category from path (filament/laravel/fullstack/etc.)
3. Update choose-skill/references/skills-catalog.md
4. Show diff and ask for confirmation
"""

import sys
import re
import io
from pathlib import Path
from datetime import datetime

# Fix Windows console encoding for emoji/unicode
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')


# Category mapping with descriptions
CATEGORIES = {
    'filament': {
        'name': 'Filament',
        'description': 'Filament 4.x (Laravel 12)',
        'header': '## Filament - Filament 4.x (Laravel 12)',
    },
    'laravel': {
        'name': 'Laravel',
        'description': 'Laravel Framework & Tools',
        'header': '## Laravel - Laravel Framework & Tools',
    },
    'fullstack': {
        'name': 'Fullstack',
        'description': 'Full-Stack Development',
        'header': '## Fullstack - Full-Stack Development',
    },
    'workflows': {
        'name': 'Workflows',
        'description': 'Development Workflows',
        'header': '## Workflows - Development Workflows',
    },
    'api': {
        'name': 'API',
        'description': 'API Design & Documentation',
        'header': '## API - API Design & Documentation',
    },
    'meta': {
        'name': 'Meta',
        'description': 'Skill Management',
        'header': '## Meta - Skill Management',
    },
    'optimize': {
        'name': 'Optimize',
        'description': 'Performance & SEO',
        'header': '## Optimize - Performance & SEO',
    },
    'marketing': {
        'name': 'Marketing',
        'description': 'Content & SEO Marketing',
        'header': '## Marketing - Content & SEO Marketing',
    },
    'database': {
        'name': 'Database',
        'description': 'Database Management & Optimization',
        'header': '## Database - Database Management & Optimization',
    },
}


def parse_skill_metadata(skill_path):
    """
    Parse SKILL.md to extract metadata.
    
    Returns:
        dict: {name, description, path, category, skill_name}
    """
    skill_md = skill_path / 'SKILL.md'
    
    if not skill_md.exists():
        print(f"❌ Error: SKILL.md not found at {skill_md}")
        return None
    
    try:
        content = skill_md.read_text(encoding='utf-8')
    except Exception as e:
        print(f"❌ Error reading SKILL.md: {e}")
        return None
    
    # Parse YAML frontmatter
    frontmatter_match = re.match(r'^---\s*\n(.*?)\n---', content, re.DOTALL)
    if not frontmatter_match:
        print("❌ Error: SKILL.md missing YAML frontmatter")
        return None
    
    frontmatter = frontmatter_match.group(1)
    
    # Extract name
    name_match = re.search(r'^name:\s*(.+)$', frontmatter, re.MULTILINE)
    if not name_match:
        print("❌ Error: 'name' field missing in YAML frontmatter")
        return None
    name = name_match.group(1).strip()
    
    # Extract description
    desc_match = re.search(r'^description:\s*(.+?)(?=\n\w+:|$)', frontmatter, re.MULTILINE | re.DOTALL)
    if not desc_match:
        print("❌ Error: 'description' field missing in YAML frontmatter")
        return None
    description = desc_match.group(1).strip()
    # Clean up description (remove newlines within description)
    description = re.sub(r'\s+', ' ', description)
    
    # Detect category from path
    parts = skill_path.parts
    category = None
    for part in parts:
        if part in CATEGORIES:
            category = part
            break
    
    if not category:
        print(f"❌ Error: Cannot detect category from path {skill_path}")
        print(f"   Valid categories: {', '.join(CATEGORIES.keys())}")
        return None
    
    # Get relative path from skills directory
    try:
        # Find skills directory in path
        skills_idx = None
        for i, part in enumerate(parts):
            if part == 'skills':
                skills_idx = i
                break
        
        if skills_idx is None:
            print("❌ Error: 'skills' directory not found in path")
            return None
        
        # Build relative path from skills/
        rel_parts = parts[skills_idx+1:]
        rel_path = '.claude/skills/' + '/'.join(rel_parts)
    except Exception as e:
        print(f"❌ Error building relative path: {e}")
        return None
    
    return {
        'name': name,
        'description': description,
        'path': rel_path,
        'category': category,
        'skill_name': skill_path.name,
    }


def extract_when_to_use(content):
    """
    Extract "When to Use" section from SKILL.md if available.
    
    Returns:
        list: List of use cases, or empty list
    """
    # Try to find "When to Use" or "When to Activate" section
    when_match = re.search(r'## When to (?:Use|Activate)\s*\n+(.*?)(?=\n##|\Z)', content, re.DOTALL)
    if not when_match:
        return []
    
    when_section = when_match.group(1).strip()
    
    # Extract bullet points
    use_cases = []
    for line in when_section.split('\n'):
        line = line.strip()
        if line.startswith('- ') or line.startswith('* '):
            use_case = line[2:].strip()
            # Remove quotes if present
            use_case = use_case.strip('"\'')
            use_cases.append(use_case)
    
    return use_cases


def extract_key_features(content):
    """
    Extract "Key Features" section from SKILL.md if available.
    
    Returns:
        list: List of features, or empty list
    """
    # Try to find sections that might contain features
    features = []
    
    # Look for "Key Features" or "Core Features" section
    features_match = re.search(r'## (?:Key|Core) Features\s*\n+(.*?)(?=\n##|\Z)', content, re.DOTALL)
    if features_match:
        features_section = features_match.group(1).strip()
        for line in features_section.split('\n'):
            line = line.strip()
            if line.startswith('- ') or line.startswith('* '):
                feature = line[2:].strip()
                features.append(feature)
    
    return features


def generate_catalog_entry(metadata, skill_md_content, number):
    """
    Generate catalog entry text for skills-catalog.md.
    
    Args:
        metadata: Dict from parse_skill_metadata
        skill_md_content: Full content of SKILL.md for extracting details
        number: Skill number in category
        
    Returns:
        str: Formatted catalog entry
    """
    use_cases = extract_when_to_use(skill_md_content)
    features = extract_key_features(skill_md_content)
    
    entry = f"### {number}. {metadata['name']}\n"
    entry += f"**Path:** `{metadata['path']}/SKILL.md`\n\n"
    entry += f"**Description:**  \n{metadata['description']}\n\n"
    
    if use_cases:
        entry += "**When to Use:**\n"
        for use_case in use_cases[:5]:  # Limit to 5 use cases
            entry += f"- {use_case}\n"
        entry += "\n"
    
    if features:
        entry += "**Key Features:**\n"
        for feature in features[:5]:  # Limit to 5 features
            entry += f"- {feature}\n"
        entry += "\n"
    
    entry += "---\n\n"
    
    return entry


def update_catalog(choose_skill_path, metadata, skill_md_content):
    """
    Update skills-catalog.md with new/updated skill entry.
    
    Returns:
        tuple: (success: bool, old_content: str, new_content: str)
    """
    catalog_path = choose_skill_path / 'references' / 'skills-catalog.md'
    
    if not catalog_path.exists():
        print(f"❌ Error: Catalog not found at {catalog_path}")
        return False, None, None
    
    try:
        old_content = catalog_path.read_text(encoding='utf-8')
    except Exception as e:
        print(f"❌ Error reading catalog: {e}")
        return False, None, None
    
    # Find the category section
    category_info = CATEGORIES[metadata['category']]
    category_header = category_info['header']
    
    # Find category section boundaries
    category_match = re.search(
        rf'^{re.escape(category_header)}\s*\n+(.*?)(?=\n## |\Z)',
        old_content,
        re.MULTILINE | re.DOTALL
    )
    
    if not category_match:
        print(f"❌ Error: Category section '{category_header}' not found in catalog")
        return False, None, None
    
    category_section = category_match.group(1)
    category_start = category_match.start(1)
    category_end = category_match.end(1)
    
    # Check if skill already exists in this category
    skill_pattern = rf'### \d+\. {re.escape(metadata["name"])}\s*\n'
    existing_match = re.search(skill_pattern, category_section)
    
    if existing_match:
        # Update existing entry
        print(f"ℹ️  Skill '{metadata['name']}' already exists in catalog, updating...")
        
        # Find the full entry (from ### to next ### or ---)
        entry_start = existing_match.start()
        entry_end_match = re.search(r'\n(?:###|---)', category_section[entry_start+1:])
        if entry_end_match:
            entry_end = entry_start + 1 + entry_end_match.start()
        else:
            entry_end = len(category_section)
        
        # Extract number from existing entry
        number_match = re.search(r'### (\d+)\.', category_section[entry_start:entry_end])
        number = int(number_match.group(1)) if number_match else 1
        
        # Generate new entry
        new_entry = generate_catalog_entry(metadata, skill_md_content, number)
        
        # Replace in category section
        new_category_section = (
            category_section[:entry_start] +
            new_entry +
            category_section[entry_end:]
        )
    else:
        # Add new entry
        print(f"✅ Adding new skill '{metadata['name']}' to catalog...")
        
        # Count existing entries to get next number
        existing_entries = re.findall(r'### (\d+)\.', category_section)
        next_number = max([int(n) for n in existing_entries], default=0) + 1
        
        # Generate new entry
        new_entry = generate_catalog_entry(metadata, skill_md_content, next_number)
        
        # Find insertion point (before "---" separator at end of category)
        # or at end if no separator
        separator_match = re.search(r'\n---\s*\n*$', category_section)
        if separator_match:
            insert_pos = separator_match.start()
            new_category_section = (
                category_section[:insert_pos] +
                "\n" + new_entry +
                category_section[insert_pos:]
            )
        else:
            new_category_section = category_section + "\n" + new_entry
    
    # Rebuild full content
    new_content = (
        old_content[:category_start] +
        new_category_section +
        old_content[category_end:]
    )
    
    # Update total count in header
    total_match = re.search(r'\*\*Total Skills:\*\* (\d+) skills', new_content)
    if total_match:
        # Count all ### entries in the document
        all_entries = re.findall(r'^### \d+\.', new_content, re.MULTILINE)
        new_total = len(all_entries)
        new_content = re.sub(
            r'\*\*Total Skills:\*\* \d+ skills',
            f'**Total Skills:** {new_total} skills',
            new_content
        )
    
    # Update "Last Updated" timestamp
    today = datetime.now().strftime('%Y-%m-%d')
    new_content = re.sub(
        r'\*\*Last Updated:\*\* \d{4}-\d{2}-\d{2}',
        f'**Last Updated:** {today}',
        new_content
    )
    
    return True, old_content, new_content


def show_diff(old_content, new_content):
    """Show a simple diff of what changed."""
    print("\n" + "="*60)
    print("CHANGES PREVIEW")
    print("="*60)
    
    old_lines = old_content.split('\n')
    new_lines = new_content.split('\n')
    
    # Find changed sections (simple approach)
    changes_shown = 0
    max_changes = 20  # Limit output
    
    for i, (old_line, new_line) in enumerate(zip(old_lines, new_lines)):
        if old_line != new_line and changes_shown < max_changes:
            print(f"Line {i+1}:")
            print(f"  - {old_line[:80]}")
            print(f"  + {new_line[:80]}")
            changes_shown += 1
    
    # Check for added lines
    if len(new_lines) > len(old_lines):
        print(f"\n+ Added {len(new_lines) - len(old_lines)} new lines")
    
    print("="*60)


def main():
    if len(sys.argv) < 2:
        print("Usage: sync_to_choose_skill.py <skill-path>")
        print("\nExamples:")
        print("  sync_to_choose_skill.py ../../filament/my-new-skill")
        print("  sync_to_choose_skill.py ../../../skills/api/my-api-skill")
        sys.exit(1)
    
    skill_path = Path(sys.argv[1]).resolve()
    
    if not skill_path.exists():
        print(f"❌ Error: Skill path does not exist: {skill_path}")
        sys.exit(1)
    
    if not skill_path.is_dir():
        print(f"❌ Error: Path is not a directory: {skill_path}")
        sys.exit(1)
    
    print(f"🔍 Parsing skill: {skill_path.name}")
    print(f"   Location: {skill_path}\n")
    
    # Parse skill metadata
    metadata = parse_skill_metadata(skill_path)
    if not metadata:
        sys.exit(1)
    
    print(f"✅ Skill parsed successfully:")
    print(f"   Name: {metadata['name']}")
    print(f"   Category: {metadata['category']}")
    print(f"   Path: {metadata['path']}")
    print(f"   Description: {metadata['description'][:80]}...")
    
    # Read full SKILL.md for extracting detailed info
    skill_md_content = (skill_path / 'SKILL.md').read_text(encoding='utf-8')
    
    # Find choose-skill path (should be in meta/choose-skill)
    # Navigate from current script location
    script_dir = Path(__file__).parent
    choose_skill_path = script_dir.parent.parent / 'choose-skill'
    
    if not choose_skill_path.exists():
        print(f"\n❌ Error: choose-skill not found at {choose_skill_path}")
        sys.exit(1)
    
    print(f"\n📝 Updating choose-skill catalog...")
    print(f"   Catalog: {choose_skill_path / 'references' / 'skills-catalog.md'}\n")
    
    # Update catalog
    success, old_content, new_content = update_catalog(
        choose_skill_path,
        metadata,
        skill_md_content
    )
    
    if not success:
        sys.exit(1)
    
    # Show diff
    show_diff(old_content, new_content)
    
    # Ask for confirmation
    print("\n❓ Apply these changes to choose-skill catalog? (y/n): ", end='')
    response = input().strip().lower()
    
    if response != 'y':
        print("❌ Cancelled. No changes made.")
        sys.exit(0)
    
    # Write changes
    catalog_path = choose_skill_path / 'references' / 'skills-catalog.md'
    try:
        catalog_path.write_text(new_content, encoding='utf-8')
        print(f"\n✅ Successfully synced to choose-skill catalog!")
        print(f"\nNext steps:")
        print(f"1. Review the changes in {catalog_path}")
        print(f"2. Continue to Step 7: Package the skill")
    except Exception as e:
        print(f"\n❌ Error writing catalog: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()

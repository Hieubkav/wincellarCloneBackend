#!/usr/bin/env python3
"""
Smart Batch Refactor - Tự động refactor tất cả skills về < 200 lines

Strategy:
1. Analyze SKILL.md structure
2. Auto-detect sections to extract (examples, detailed guide, troubleshooting)
3. Create references/ directory
4. Extract content intelligently
5. Update SKILL.md with references
6. Validate < 200 lines

Usage:
    python smart_refactor.py --skills-dir .claude/skills --target 200
"""

import sys
import re
from pathlib import Path
from typing import List, Tuple


def analyze_skill_structure(content: str) -> dict:
    """Analyze SKILL.md and identify extractable sections"""
    lines = content.split('\n')
    
    sections = []
    current_section = None
    section_start = 0
    
    for i, line in enumerate(lines):
        if line.startswith('## '):
            if current_section:
                sections.append({
                    'name': current_section,
                    'start': section_start,
                    'end': i,
                    'lines': i - section_start
                })
            current_section = line.replace('## ', '').strip()
            section_start = i
    
    # Add last section
    if current_section:
        sections.append({
            'name': current_section,
            'start': section_start,
            'end': len(lines),
            'lines': len(lines) - section_start
        })
    
    return {
        'total_lines': len(lines),
        'sections': sections
    }


def identify_extractable_sections(analysis: dict, target_lines: int = 200) -> List[str]:
    """Identify which sections should be extracted"""
    current_lines = analysis['total_lines']
    if current_lines <= target_lines:
        return []
    
    excess_lines = current_lines - target_lines
    extractable = []
    
    # Priority keywords for extraction
    extract_keywords = [
        'example', 'detailed', 'complete', 'comprehensive', 'advanced',
        'troubleshooting', 'reference', 'guide', 'pattern', 'implementation'
    ]
    
    # Sort sections by size (largest first)
    sorted_sections = sorted(
        analysis['sections'],
        key=lambda s: s['lines'],
        reverse=True
    )
    
    extracted_lines = 0
    for section in sorted_sections:
        if extracted_lines >= excess_lines:
            break
        
        section_name = section['name'].lower()
        
        # Check if section matches extract keywords
        should_extract = any(kw in section_name for kw in extract_keywords)
        
        # Also extract large sections (>50 lines)
        if section['lines'] > 50:
            should_extract = True
        
        # Don't extract essential sections
        essential_keywords = ['when to use', 'quick start', 'overview', 'core']
        is_essential = any(kw in section_name for kw in essential_keywords)
        
        if should_extract and not is_essential:
            extractable.append(section['name'])
            extracted_lines += section['lines']
    
    return extractable


def extract_sections(skill_path: Path, sections_to_extract: List[str]) -> bool:
    """Extract sections to references/ and update SKILL.md"""
    skill_md = skill_path / 'SKILL.md'
    content = skill_md.read_text(encoding='utf-8')
    lines = content.split('\n')
    
    # Create references directory
    references_dir = skill_path / 'references'
    references_dir.mkdir(exist_ok=True)
    
    # Extract each section
    remaining_lines = []
    extracted_content = {}
    current_section = None
    section_lines = []
    in_frontmatter = False
    frontmatter_done = False
    
    for line in lines:
        # Handle frontmatter
        if line.strip() == '---':
            if not frontmatter_done:
                in_frontmatter = not in_frontmatter
                if not in_frontmatter:
                    frontmatter_done = True
            remaining_lines.append(line)
            continue
        
        if in_frontmatter:
            remaining_lines.append(line)
            continue
        
        # Detect section headers
        if line.startswith('## '):
            # Save previous section
            if current_section:
                if current_section in sections_to_extract:
                    extracted_content[current_section] = section_lines
                else:
                    remaining_lines.extend(section_lines)
            
            current_section = line.replace('## ', '').strip()
            section_lines = [line]
        else:
            section_lines.append(line)
    
    # Handle last section
    if current_section:
        if current_section in sections_to_extract:
            extracted_content[current_section] = section_lines
        else:
            remaining_lines.extend(section_lines)
    
    # Write extracted sections to references/
    references_added = []
    for section_name, section_content in extracted_content.items():
        filename = section_name.lower().replace(' ', '-').replace('/', '-')
        filename = re.sub(r'[^a-z0-9-]', '', filename)
        ref_file = references_dir / f'{filename}.md'
        
        ref_file.write_text('\n'.join(section_content), encoding='utf-8')
        references_added.append((section_name, ref_file.name))
        print(f"  ✓ Extracted: {section_name} → references/{ref_file.name}")
    
    # Add references section to SKILL.md
    if references_added:
        remaining_lines.append('')
        remaining_lines.append('---')
        remaining_lines.append('')
        remaining_lines.append('## References')
        remaining_lines.append('')
        for section_name, filename in references_added:
            remaining_lines.append(f"**{section_name}:** `read .claude/skills/{skill_path.name}/references/{filename}`")
        remaining_lines.append('')
    
    # Write updated SKILL.md
    skill_md.write_text('\n'.join(remaining_lines), encoding='utf-8')
    
    return len(references_added) > 0


def refactor_skill(skill_path: Path, target_lines: int = 200) -> Tuple[bool, int, int]:
    """Refactor a single skill"""
    skill_md = skill_path / 'SKILL.md'
    
    if not skill_md.exists():
        return False, 0, 0
    
    content = skill_md.read_text(encoding='utf-8')
    original_lines = len(content.split('\n'))
    
    if original_lines <= target_lines:
        return False, original_lines, original_lines  # Already compliant
    
    print(f"\n📦 Refactoring {skill_path.name} ({original_lines} lines)...")
    
    # Analyze structure
    analysis = analyze_skill_structure(content)
    
    # Identify sections to extract
    sections = identify_extractable_sections(analysis, target_lines)
    
    if not sections:
        print(f"  ⚠️  No extractable sections found")
        return False, original_lines, original_lines
    
    print(f"  → Extracting {len(sections)} sections...")
    
    # Extract sections
    success = extract_sections(skill_path, sections)
    
    # Check result
    new_content = skill_md.read_text(encoding='utf-8')
    new_lines = len(new_content.split('\n'))
    
    saved = original_lines - new_lines
    print(f"  ✅ {original_lines} → {new_lines} lines (saved {saved})")
    
    if new_lines <= target_lines:
        print(f"  🎉 Under {target_lines} lines!")
    else:
        print(f"  ⚠️  Still {new_lines - target_lines} lines over limit")
    
    return True, original_lines, new_lines


def find_all_skills(base_dir):
    """Find all skills recursively (supports categories)"""
    skills = []
    for item in base_dir.rglob('SKILL.md'):
        skill_dir = item.parent
        # Skip if in references/scripts/assets
        if any(p in skill_dir.parts for p in ['references', 'scripts', 'assets', '__pycache__']):
            continue
        skills.append(skill_dir)
    return sorted(skills)


def main():
    if len(sys.argv) < 3:
        print("Usage: python smart_refactor.py --skills-dir .claude/skills [--target 200]")
        sys.exit(1)
    
    skills_dir = None
    target_lines = 200
    
    for i, arg in enumerate(sys.argv):
        if arg == '--skills-dir' and i + 1 < len(sys.argv):
            skills_dir = Path(sys.argv[i + 1]).resolve()
        elif arg == '--target' and i + 1 < len(sys.argv):
            target_lines = int(sys.argv[i + 1])
    
    if not skills_dir or not skills_dir.exists():
        print(f"❌ Skills directory not found: {skills_dir}")
        sys.exit(1)
    
    print(f"🚀 Smart Batch Refactor")
    print(f"   Target: < {target_lines} lines per skill")
    print(f"   Directory: {skills_dir}")
    
    # Find all skills (supports categories)
    skills = find_all_skills(skills_dir)
    
    results = []
    for skill_path in sorted(skills):
        refactored, before, after = refactor_skill(skill_path, target_lines)
        results.append({
            'name': skill_path.name,
            'refactored': refactored,
            'before': before,
            'after': after,
            'compliant': after <= target_lines
        })
    
    # Summary
    print(f"\n{'='*80}")
    print("Summary:")
    print(f"{'='*80}")
    
    total = len(results)
    refactored_count = sum(1 for r in results if r['refactored'])
    compliant_count = sum(1 for r in results if r['compliant'])
    
    print(f"Total skills: {total}")
    print(f"Refactored: {refactored_count}")
    print(f"✅ Compliant (<= {target_lines} lines): {compliant_count}/{total}")
    
    if compliant_count < total:
        print(f"\n⚠️  {total - compliant_count} skills still over limit:")
        for r in results:
            if not r['compliant']:
                print(f"   - {r['name']}: {r['after']} lines")
    else:
        print(f"\n🎉 All skills compliant!")
    
    sys.exit(0 if compliant_count == total else 1)


if __name__ == "__main__":
    main()

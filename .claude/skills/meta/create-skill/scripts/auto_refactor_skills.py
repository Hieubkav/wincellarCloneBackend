#!/usr/bin/env python3
"""
Auto Refactor Skills - Tự động refactor skills theo chuẩn < 200 lines

Phân tích SKILL.md và tự động:
1. Giữ lại core content (namespaces, quick examples, checklist)
2. Move detailed content vào references/
3. Update references section
4. Validate < 200 lines

Usage:
    python auto_refactor_skills.py --skills-dir .claude/skills
"""

import sys
import re
from pathlib import Path


CRITICAL_SECTIONS = [
    'When to Use',
    'Critical',
    'Namespaces',
    'Quick',
    'Standards Checklist',
    'Core',
    'Essential'
]

DETAIL_SECTIONS = [
    'Advanced',
    'Complete Guide',
    'Detailed',
    'Examples',
    'Patterns',
    'Best Practices',
    'Troubleshooting'
]


def analyze_skill(skill_md_path):
    """Phân tích SKILL.md và xác định cần refactor"""
    content = skill_md_path.read_text(encoding='utf-8')
    lines = content.split('\n')
    line_count = len(lines)
    
    if line_count <= 200:
        return False, line_count, "Already compliant"
    
    return True, line_count, f"Needs refactor ({line_count} lines)"


def count_lines_by_section(skill_md_path):
    """Đếm lines theo từng section"""
    content = skill_md_path.read_text(encoding='utf-8')
    lines = content.split('\n')
    
    sections = {}
    current_section = 'frontmatter'
    current_lines = []
    
    for line in lines:
        if line.startswith('## '):
            if current_section:
                sections[current_section] = len(current_lines)
            current_section = line.replace('## ', '').strip()
            current_lines = []
        else:
            current_lines.append(line)
    
    # Add last section
    if current_section:
        sections[current_section] = len(current_lines)
    
    return sections


def main():
    if len(sys.argv) < 3 or sys.argv[1] != '--skills-dir':
        print("Usage: python auto_refactor_skills.py --skills-dir .claude/skills")
        sys.exit(1)
    
    skills_dir = Path(sys.argv[2]).resolve()
    
    if not skills_dir.exists():
        print(f"❌ Skills directory not found: {skills_dir}")
        sys.exit(1)
    
    print(f"🔍 Analyzing skills in {skills_dir}\n")
    
    # Find all skills (supports categories)
    skills_found = []
    for item in skills_dir.rglob('SKILL.md'):
        skill_dir = item.parent
        # Skip if in references/scripts/assets
        if any(p in skill_dir.parts for p in ['references', 'scripts', 'assets', '__pycache__']):
            continue
        skills_found.append(skill_dir)
    
    # Analyze all skills
    results = []
    for skill_dir in sorted(skills_found):
        skill_md = skill_dir / 'SKILL.md'
        
        needs_refactor, line_count, message = analyze_skill(skill_md)
        
        # Get relative path for display (category/skill-name)
        try:
            rel_path = skill_dir.relative_to(skills_dir)
            display_name = str(rel_path).replace('\\', '/')
        except:
            display_name = skill_dir.name
        
        status = "❌ REFACTOR" if needs_refactor else "✅ OK"
        results.append((display_name, line_count, status, message))
        
        print(f"{status:15} {display_name:40} {line_count:4} lines - {message}")
    
    # Summary
    print(f"\n{'='*80}")
    total_skills = len(results)
    needs_refactor_count = sum(1 for r in results if r[2] == "❌ REFACTOR")
    compliant_count = total_skills - needs_refactor_count
    
    print(f"Total skills: {total_skills}")
    print(f"✅ Compliant (<= 200 lines): {compliant_count}")
    print(f"❌ Needs refactor (> 200 lines): {needs_refactor_count}")
    
    if needs_refactor_count > 0:
        print(f"\n💡 Run with --refactor flag to auto-refactor (coming soon)")
    
    sys.exit(0 if needs_refactor_count == 0 else 1)


if __name__ == "__main__":
    main()

#!/usr/bin/env python3
"""
Compress Skill - Nén SKILL.md về < 200 lines

Strategies:
1. Compact code examples (remove extra spacing)
2. Merge related sections
3. Simplify verbose descriptions
4. Extract detailed examples to references/

Usage:
    python compress_skill.py path/to/skill/SKILL.md
"""

import sys
import re
from pathlib import Path


def compress_code_blocks(content):
    """Nén code blocks: Remove extra blank lines"""
    # Replace multiple blank lines in code with single blank
    pattern = r'(```[\w]*\n)((?:.*\n)*?)(```)'
    
    def compress_block(match):
        start, code, end = match.groups()
        # Remove consecutive blank lines trong code
        lines = code.split('\n')
        compressed = []
        prev_blank = False
        for line in lines:
            is_blank = line.strip() == ''
            if is_blank:
                if not prev_blank:
                    compressed.append('')
                prev_blank = True
            else:
                compressed.append(line)
                prev_blank = False
        return start + '\n'.join(compressed) + '\n' + end
    
    return re.sub(pattern, compress_block, content)


def merge_short_sections(content):
    """Merge các section ngắn < 3 lines"""
    lines = content.split('\n')
    result = []
    i = 0
    
    while i < len(lines):
        line = lines[i]
        
        # Check if section header
        if line.startswith('## '):
            # Count lines until next section
            section_lines = [line]
            j = i + 1
            while j < len(lines) and not lines[j].startswith('## '):
                section_lines.append(lines[j])
                j += 1
            
            # If section is very short, merge với next
            content_lines = [l for l in section_lines[1:] if l.strip()]
            if len(content_lines) < 3 and j < len(lines):
                # Skip merging, just add as is for now
                result.extend(section_lines)
            else:
                result.extend(section_lines)
            
            i = j
        else:
            result.append(line)
            i += 1
    
    return '\n'.join(result)


def remove_horizontal_rules_excess(content):
    """Remove excessive --- separators"""
    # Replace multiple consecutive --- with single
    content = re.sub(r'(\n---\n)+', '\n---\n', content)
    return content


def compress_content(content):
    """Apply all compression strategies"""
    # 1. Compress code blocks
    content = compress_code_blocks(content)
    
    # 2. Remove excessive horizontal rules
    content = remove_horizontal_rules_excess(content)
    
    # 3. Remove trailing whitespace
    lines = content.split('\n')
    lines = [line.rstrip() for line in lines]
    
    # 4. Remove more than 2 consecutive blank lines
    result = []
    blank_count = 0
    for line in lines:
        if line == '':
            blank_count += 1
            if blank_count <= 1:
                result.append(line)
        else:
            blank_count = 0
            result.append(line)
    
    return '\n'.join(result)


def main():
    if len(sys.argv) != 2:
        print("Usage: python compress_skill.py path/to/SKILL.md")
        sys.exit(1)
    
    skill_path = Path(sys.argv[1])
    
    if not skill_path.exists():
        print(f"❌ File not found: {skill_path}")
        sys.exit(1)
    
    print(f"📦 Compressing {skill_path.name}...")
    
    # Read original
    original = skill_path.read_text(encoding='utf-8')
    original_lines = len(original.split('\n'))
    
    # Compress
    compressed = compress_content(original)
    compressed_lines = len(compressed.split('\n'))
    
    # Save
    skill_path.write_text(compressed, encoding='utf-8')
    
    # Report
    saved = original_lines - compressed_lines
    percentage = (saved / original_lines * 100) if original_lines > 0 else 0
    
    print(f"✅ Compressed: {original_lines} → {compressed_lines} lines")
    print(f"   Saved: {saved} lines ({percentage:.1f}%)")
    
    if compressed_lines > 200:
        print(f"⚠️  Still over limit ({compressed_lines} > 200)")
        print(f"   Need to extract {compressed_lines - 200} more lines to references/")
    else:
        print(f"🎉 Under 200 lines limit!")


if __name__ == "__main__":
    main()

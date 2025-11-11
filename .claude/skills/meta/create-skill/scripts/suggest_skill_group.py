#!/usr/bin/env python3
"""
Skill Group Intelligence - Suggests optimal category placement and refactoring opportunities

Analyzes skill domains, detects patterns, and suggests:
1. Best category for new skills
2. New category creation opportunities
3. Existing category refactoring needs

Usage:
    python suggest_skill_group.py --skill "skill-name" --description "skill description"
    python suggest_skill_group.py --analyze-all  # Check refactor opportunities
    python suggest_skill_group.py --skill-path path/to/skill  # Analyze existing skill

Examples:
    python suggest_skill_group.py --skill "stripe-payments" --description "Stripe payment integration"
    python suggest_skill_group.py --analyze-all
"""

import sys
import io
import re
import argparse
from pathlib import Path
from collections import defaultdict
from typing import Dict, List, Tuple, Optional

# Fix Windows console encoding
if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# Domain keyword patterns
DOMAIN_KEYWORDS = {
    'filament': ['filament', 'admin panel', 'resource', 'form builder', 'table builder', 'infolist'],
    'laravel': ['laravel', 'eloquent', 'artisan', 'blade', 'migration', 'route', 'middleware'],
    'fullstack': ['react', 'vue', 'angular', 'frontend', 'backend', 'typescript', 'ui', 'ux', 'component'],
    'workflows': ['workflow', 'automation', 'debugging', 'development', 'process', 'brainstorm', 'planning'],
    'api': ['api', 'rest', 'graphql', 'endpoint', 'documentation', 'swagger', 'openapi', 'cache invalidation'],
    'meta': ['skill', 'meta', 'tooling', 'automation', 'generator', 'framework'],
    'optimize': ['performance', 'optimization', 'speed', 'seo', 'core web vitals', 'lighthouse', 'page speed'],
    'marketing': ['marketing', 'content', 'seo content', 'copywriting', 'keyword', 'meta description'],
    'database': ['database', 'sql', 'postgresql', 'mysql', 'mongodb', 'query', 'schema', 'migration', 'orm', 'index', 'seed'],
}

# Technology stack keywords
TECH_KEYWORDS = {
    'frontend': ['react', 'vue', 'angular', 'svelte', 'next.js', 'typescript', 'javascript', 'css', 'tailwind', 'shadcn'],
    'backend': ['node.js', 'express', 'php', 'laravel', 'python', 'django', 'ruby', 'rails', 'java', 'spring'],
    'database': ['postgresql', 'mysql', 'mongodb', 'redis', 'elasticsearch', 'sqlite'],
    'devops': ['docker', 'kubernetes', 'ci/cd', 'github actions', 'jenkins', 'terraform', 'ansible'],
    'mobile': ['ios', 'android', 'react native', 'flutter', 'swift', 'kotlin'],
    'testing': ['jest', 'pytest', 'phpunit', 'cypress', 'selenium', 'playwright', 'test', 'tdd'],
    'security': ['authentication', 'authorization', 'oauth', 'jwt', 'encryption', 'security', 'vulnerability'],
}

# Optimal category sizes
OPTIMAL_SIZE = (3, 7)
WARNING_SIZE = 10
UNDERUTILIZED_SIZE = 2


class CategoryAnalyzer:
    def __init__(self, skills_dir: Path):
        self.skills_dir = skills_dir
        self.categories = self._load_categories()
        
    def _load_categories(self) -> Dict[str, List[str]]:
        """Load current category structure"""
        categories = {}
        
        if not self.skills_dir.exists():
            print(f"⚠️  Skills directory not found: {self.skills_dir}")
            return categories
        
        for cat_dir in self.skills_dir.iterdir():
            if cat_dir.is_dir() and not cat_dir.name.startswith('.'):
                skills = []
                for skill_dir in cat_dir.iterdir():
                    if skill_dir.is_dir() and (skill_dir / 'SKILL.md').exists():
                        skills.append(skill_dir.name)
                
                if skills:
                    categories[cat_dir.name] = skills
        
        return categories
    
    def suggest_category(self, skill_name: str, skill_description: str) -> Dict:
        """Suggest best category for a skill"""
        # Step 1: Extract domains
        domains = self._extract_domains(skill_description)
        
        # Step 2: Match against categories
        matches = self._match_categories(domains, skill_description)
        
        # Step 3: Check for new category opportunity
        new_cat_opportunity = self._check_new_category_opportunity(skill_name, domains)
        
        # Step 4: Build suggestion
        best_match = matches[0] if matches else None
        
        result = {
            'skill_name': skill_name,
            'suggested_category': best_match['category'] if best_match else 'workflows',
            'confidence': best_match['confidence'] if best_match else 0.3,
            'reasoning': best_match['reasoning'] if best_match else ['No clear match found, defaulting to workflows/'],
            'alternatives': matches[1:3] if len(matches) > 1 else [],
            'new_category_opportunity': new_cat_opportunity,
            'detected_domains': domains,
        }
        
        return result
    
    def _extract_domains(self, description: str) -> List[str]:
        """Extract domain keywords from description"""
        desc_lower = description.lower()
        domains = []
        
        # Check category keywords
        for category, keywords in DOMAIN_KEYWORDS.items():
            if any(kw in desc_lower for kw in keywords):
                domains.append(category)
        
        # Check tech keywords
        for tech, keywords in TECH_KEYWORDS.items():
            if any(kw in desc_lower for kw in keywords):
                domains.append(f"tech:{tech}")
        
        return list(set(domains))
    
    def _match_categories(self, domains: List[str], description: str) -> List[Dict]:
        """Match domains against existing categories"""
        matches = []
        
        for category in self.categories.keys():
            confidence = 0.0
            reasoning = []
            
            # Direct domain match
            if category in domains:
                confidence += 0.5
                reasoning.append(f"Direct match with {category}/ domain")
            
            # Keyword density in description
            category_keywords = DOMAIN_KEYWORDS.get(category, [])
            matched_keywords = [kw for kw in category_keywords if kw in description.lower()]
            if matched_keywords:
                keyword_score = len(matched_keywords) / len(category_keywords)
                confidence += keyword_score * 0.3
                reasoning.append(f"Matched keywords: {', '.join(matched_keywords[:3])}")
            
            # Existing similar skills
            similar_skills = self._find_similar_skills(category, description)
            if similar_skills:
                confidence += 0.2
                reasoning.append(f"Similar skills exist: {', '.join(similar_skills[:2])}")
            
            if confidence > 0:
                matches.append({
                    'category': category,
                    'confidence': min(confidence, 1.0),
                    'reasoning': reasoning,
                    'current_size': len(self.categories[category]),
                })
        
        # Sort by confidence
        matches.sort(key=lambda x: x['confidence'], reverse=True)
        return matches
    
    def _find_similar_skills(self, category: str, description: str) -> List[str]:
        """Find skills in category with similar descriptions"""
        similar = []
        desc_words = set(description.lower().split())
        
        for skill_name in self.categories.get(category, []):
            # Simple similarity: check if skill name words appear in description
            skill_words = set(skill_name.replace('-', ' ').split())
            if skill_words & desc_words:
                similar.append(skill_name)
        
        return similar[:3]
    
    def _check_new_category_opportunity(self, skill_name: str, domains: List[str]) -> Optional[Dict]:
        """Check if new category should be created"""
        # Look for scattered related skills
        scattered_skills = self._find_scattered_skills(domains)
        
        if len(scattered_skills) >= 2:  # Including new skill = 3 total
            return {
                'suggested_name': self._suggest_category_name(domains),
                'related_skills': scattered_skills,
                'reasoning': f"Found {len(scattered_skills)} related skills scattered across categories. Creating dedicated category would improve organization.",
            }
        
        return None
    
    def _find_scattered_skills(self, domains: List[str]) -> List[Tuple[str, str]]:
        """Find skills with similar domains scattered across categories"""
        scattered = []
        
        for category, skills in self.categories.items():
            for skill in skills:
                # Simple check: if skill name contains domain keywords
                for domain in domains:
                    if domain.startswith('tech:'):
                        tech = domain.split(':')[1]
                        if tech in skill.lower():
                            scattered.append((category, skill))
                            break
        
        return scattered
    
    def _suggest_category_name(self, domains: List[str]) -> str:
        """Suggest name for new category based on domains"""
        # Prioritize non-tech domains
        non_tech = [d for d in domains if not d.startswith('tech:')]
        if non_tech:
            return non_tech[0]
        
        # Use tech domain
        tech_domains = [d.split(':')[1] for d in domains if d.startswith('tech:')]
        return tech_domains[0] if tech_domains else 'general'
    
    def analyze_refactor_opportunities(self) -> List[Dict]:
        """Analyze all categories for refactor opportunities"""
        opportunities = []
        
        for category, skills in self.categories.items():
            skill_count = len(skills)
            
            # Overcrowded category
            if skill_count > WARNING_SIZE:
                clusters = self._detect_clusters(category, skills)
                opportunities.append({
                    'type': 'overcrowded',
                    'category': category,
                    'skill_count': skill_count,
                    'clusters': clusters,
                    'suggestion': f"Split {category}/ into {len(clusters)} categories" if len(clusters) > 1 else f"Review {category}/ organization",
                })
            
            # Underutilized category
            if skill_count <= UNDERUTILIZED_SIZE:
                opportunities.append({
                    'type': 'underutilized',
                    'category': category,
                    'skill_count': skill_count,
                    'suggestion': f"Consider merging {category}/ into related category or grow to {OPTIMAL_SIZE[0]}+ skills",
                })
        
        return opportunities
    
    def _detect_clusters(self, category: str, skills: List[str]) -> List[Dict]:
        """Detect clusters of related skills within a category"""
        # Simple clustering by prefix/common words
        clusters = defaultdict(list)
        
        for skill in skills:
            # Extract first significant word
            words = skill.split('-')
            prefix = words[0] if len(words) > 1 else skill
            clusters[prefix].append(skill)
        
        # Filter clusters with 3+ skills
        result = []
        for prefix, cluster_skills in clusters.items():
            if len(cluster_skills) >= 3:
                result.append({
                    'name': prefix,
                    'skills': cluster_skills,
                    'size': len(cluster_skills),
                })
        
        return result


def print_suggestion(result: Dict):
    """Pretty print suggestion result"""
    print("\n" + "="*60)
    print(f"🎯 Category Suggestion for: {result['skill_name']}")
    print("="*60)
    
    confidence = result['confidence']
    conf_emoji = "🟢" if confidence >= 0.8 else "🟡" if confidence >= 0.5 else "🔴"
    
    print(f"\n{conf_emoji} **Suggested Category:** {result['suggested_category']}/")
    print(f"**Confidence:** {confidence:.2f}/1.0")
    
    print("\n**Reasoning:**")
    for reason in result['reasoning']:
        print(f"  - {reason}")
    
    if result['alternatives']:
        print("\n**Alternatives:**")
        for alt in result['alternatives']:
            print(f"  - {alt['category']}/ (confidence: {alt['confidence']:.2f})")
            print(f"    Reasons: {', '.join(alt['reasoning'][:2])}")
    
    if result['new_category_opportunity']:
        opp = result['new_category_opportunity']
        print("\n" + "="*60)
        print("💡 **New Category Opportunity Detected!**")
        print("="*60)
        print(f"\n**Suggested Name:** {opp['suggested_name']}/")
        print(f"**Related Skills Found:** {len(opp['related_skills'])}")
        for cat, skill in opp['related_skills']:
            print(f"  - {cat}/{skill}")
        print(f"\n**Reasoning:** {opp['reasoning']}")
    
    print("\n" + "="*60)


def print_refactor_opportunities(opportunities: List[Dict]):
    """Pretty print refactor opportunities"""
    if not opportunities:
        print("\n✅ No refactor opportunities detected. All categories are well-organized!")
        return
    
    print("\n" + "="*60)
    print(f"💡 Refactor Opportunities Detected ({len(opportunities)})")
    print("="*60)
    
    for opp in opportunities:
        print(f"\n📁 **{opp['category']}/** ({opp['skill_count']} skills)")
        print(f"**Type:** {opp['type'].capitalize()}")
        print(f"**Suggestion:** {opp['suggestion']}")
        
        if opp['type'] == 'overcrowded' and opp['clusters']:
            print("\n**Detected Clusters:**")
            for cluster in opp['clusters']:
                print(f"  - {cluster['name']}* ({cluster['size']} skills): {', '.join(cluster['skills'][:3])}")


def main():
    parser = argparse.ArgumentParser(description='Skill Group Intelligence')
    parser.add_argument('--skill', help='Skill name to analyze')
    parser.add_argument('--description', help='Skill description')
    parser.add_argument('--skill-path', help='Path to existing skill directory')
    parser.add_argument('--analyze-all', action='store_true', help='Analyze all categories for refactor opportunities')
    parser.add_argument('--skills-dir', default='.claude/skills', help='Path to skills directory')
    
    args = parser.parse_args()
    
    # Resolve skills directory
    skills_dir = Path(args.skills_dir)
    if not skills_dir.is_absolute():
        # Try from script location (script is in .claude/skills/meta/create-skill/scripts/)
        script_dir = Path(__file__).parent.parent.parent  # Go up to .claude/skills/
        if args.skills_dir == '.claude/skills':
            skills_dir = script_dir
        else:
            skills_dir = script_dir / args.skills_dir.replace('.claude/skills/', '')
    
    analyzer = CategoryAnalyzer(skills_dir)
    
    if args.analyze_all:
        print("🔍 Analyzing all categories for refactor opportunities...")
        opportunities = analyzer.analyze_refactor_opportunities()
        print_refactor_opportunities(opportunities)
    
    elif args.skill_path:
        # Analyze existing skill
        skill_path = Path(args.skill_path)
        skill_md = skill_path / 'SKILL.md'
        
        if not skill_md.exists():
            print(f"❌ SKILL.md not found: {skill_md}")
            return 1
        
        # Parse SKILL.md
        content = skill_md.read_text(encoding='utf-8')
        match = re.search(r'^description:\s*(.+)$', content, re.MULTILINE)
        description = match.group(1) if match else ""
        
        result = analyzer.suggest_category(skill_path.name, description)
        print_suggestion(result)
    
    elif args.skill and args.description:
        result = analyzer.suggest_category(args.skill, args.description)
        print_suggestion(result)
    
    else:
        parser.print_help()
        return 1
    
    return 0


if __name__ == '__main__':
    sys.exit(main())

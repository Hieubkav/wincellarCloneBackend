# Skill Grouping Intelligence

## Overview

Intelligent system for analyzing skill domains and suggesting optimal category placement with automatic detection of refactoring opportunities.

## Current Categories (9 total)

```
filament/    - Filament 4.x (Laravel 12) - 4 skills
laravel/     - Laravel Framework & Tools - 3 skills
fullstack/   - Full-Stack Development - 4 skills
workflows/   - Development Workflows - 7 skills
api/         - API Design & Documentation - 3 skills
meta/        - Skill Management - 2 skills
optimize/    - Performance & SEO - 2 skills
marketing/   - Content & SEO Marketing - 1 skill
database/    - Database Management & Optimization - 12 skills
```

**Total:** 38 skills across 9 categories

## Grouping Principles

### 1. Domain Cohesion
Skills in same category should share:
- **Primary technology** (e.g., Laravel, React, Database)
- **Problem domain** (e.g., performance, security, testing)
- **Workflow stage** (e.g., development, deployment, monitoring)

### 2. Size Balance
- **Optimal:** 3-7 skills per category
- **Warning:** >10 skills (consider splitting)
- **Underutilized:** 1-2 skills (consider merging)

### 3. Naming Clarity
Category names should be:
- **Specific:** Clear scope (not "general" or "misc")
- **Discoverable:** Match user mental models
- **Scalable:** Allow growth without renaming

## Detection Patterns

### Pattern A: Orphan Skills
**Trigger:** New skill doesn't fit existing categories
**Analysis:**
- Check if 2+ related skills exist scattered
- Evaluate domain independence
- Assess future growth potential

**Example:**
```
Existing: docker-compose, kubernetes, terraform (scattered in workflows/)
New: aws-deployment
Suggestion: Create devops/ category, migrate 4 skills
```

### Pattern B: Overcrowded Category
**Trigger:** Category has >10 skills
**Analysis:**
- Identify sub-domains within category
- Check for natural clusters (3+ skills)
- Evaluate split feasibility

**Example:**
```
database/ (12 skills):
  - Cluster 1: schema design (4 skills)
  - Cluster 2: query optimization (4 skills)
  - Cluster 3: data generation (4 skills)
Suggestion: Split into database-design/, database-performance/, database-testing/
```

### Pattern C: Technology Evolution
**Trigger:** New technology stack emerges
**Analysis:**
- Count skills for new tech
- Check if tech is long-term
- Evaluate category necessity

**Example:**
```
Existing: 1 GraphQL skill in api/
New: graphql-subscriptions, graphql-federation
Growth: Expecting 5+ GraphQL skills
Suggestion: Create graphql/ category when count >= 3
```

### Pattern D: Cross-Cutting Concerns
**Trigger:** Skill applies to multiple categories
**Analysis:**
- Determine primary domain
- Consider meta/ for truly cross-cutting
- Evaluate if new concern category needed

**Example:**
```
New: security-scanner (applies to backend, frontend, API)
Analysis: Security is cross-cutting concern
Suggestion: Create security/ category for security-focused skills
```

## Decision Algorithm

```python
def suggest_category(skill_name, skill_description):
    # Step 1: Parse domain keywords
    domains = extract_domains(skill_description)
    
    # Step 2: Match against existing categories
    matches = match_categories(domains, existing_categories)
    
    # Step 3: Evaluate match strength
    if best_match.confidence > 0.8:
        return best_match.category
    
    # Step 4: Check for orphan pattern
    related_skills = find_related_skills(domains)
    if len(related_skills) >= 2:
        return suggest_new_category(skill_name, related_skills)
    
    # Step 5: Default to closest match or workflows/
    return best_match.category or "workflows"

def check_refactor_opportunities():
    for category in categories:
        # Check overcrowded
        if category.skill_count > 10:
            suggest_split(category)
        
        # Check underutilized
        if category.skill_count == 1:
            suggest_merge(category)
        
        # Check clustering
        clusters = detect_clusters(category.skills)
        if len(clusters) >= 2 and all(c.size >= 3 for c in clusters):
            suggest_split_by_clusters(category, clusters)
```

## Domain Keywords

### Technology Stack
- **Frontend:** React, Vue, Angular, TypeScript, CSS, UI, UX, components
- **Backend:** Node.js, Express, PHP, Laravel, Python, Django, API, REST, GraphQL
- **Database:** SQL, PostgreSQL, MySQL, MongoDB, schema, query, migration, ORM
- **DevOps:** Docker, Kubernetes, CI/CD, deployment, infrastructure, cloud
- **Mobile:** iOS, Android, React Native, Flutter, mobile

### Problem Domain
- **Performance:** optimization, speed, caching, CDN, lazy loading
- **Security:** authentication, authorization, encryption, vulnerability, OWASP
- **Testing:** unit test, integration test, E2E, TDD, BDD, coverage
- **Monitoring:** logging, metrics, alerts, observability, APM
- **Documentation:** API docs, guides, tutorials, comments

### Workflow Stage
- **Development:** coding, debugging, refactoring, IDE, editor
- **Design:** architecture, patterns, diagrams, UML, planning
- **Deployment:** release, rollback, versioning, environments
- **Maintenance:** bug fixing, updates, patches, legacy code

## Confidence Scoring

```
High confidence (0.8-1.0):
- Multiple exact keyword matches
- Existing category has similar skills
- Clear technology/domain alignment

Medium confidence (0.5-0.8):
- Some keyword matches
- Category scope is flexible
- Partial domain overlap

Low confidence (0.0-0.5):
- Few keyword matches
- Multiple possible categories
- Cross-cutting concerns
```

## Suggestion Format

```markdown
🎯 **Category Suggestion: {category_name}/**

**Confidence:** {score}/1.0
**Reasoning:**
- {reason_1}
- {reason_2}
- {reason_3}

**Alternative:** {alternative_category} (confidence: {alt_score})

---

💡 **Refactor Opportunity Detected:**

**Pattern:** {pattern_name}
**Impact:** {number} skills affected
**Suggestion:**
- {action_1}
- {action_2}

**Preview:**
{before_after_structure}
```

## Examples

### Example 1: Clear Match
```
Skill: tailwind-components
Description: "Reusable Tailwind CSS components with variants and animations"

Analysis:
- Keywords: CSS, components, UI
- Match: fullstack/ (ui-styling exists)
- Confidence: 0.95

Suggestion: fullstack/tailwind-components
Reasoning: Perfect fit with existing ui-styling skill
```

### Example 2: New Category
```
Skill: stripe-integration
Description: "Stripe payment processing with webhooks and subscription management"

Analysis:
- Keywords: payment, subscription, webhooks
- Related skills: paypal-integration, payment-gateway (if exist)
- No existing category
- Confidence: 0.7 (create new) / 0.6 (api/)

Suggestion: Create payments/ category
Reasoning: 
- Payment processing is distinct domain
- Potential for 5+ payment-related skills
- Doesn't fit cleanly into api/ or workflows/
```

### Example 3: Refactor Trigger
```
Skill: redis-optimization
Description: "Redis caching strategies and performance optimization"

Analysis:
- Current: database/ (12 skills, overcrowded)
- Cluster detection: 4 caching-related skills
- Keywords: caching, performance, Redis

Suggestion: database/redis-optimization
+ Refactor opportunity: Split database/ into:
  - database-design/ (schema, migrations)
  - database-performance/ (caching, optimization, indexes)
  - database-testing/ (seed data, test data)
```

## Integration with create-skill

Auto-trigger suggestions when:
1. **New skill initialization** - Suggest category during creation
2. **Manual invocation** - Run `scripts/suggest_skill_group.py`
3. **Periodic review** - Check refactor opportunities quarterly

## References

- `scripts/suggest_skill_group.py` - Implementation
- `.claude/global/SKILLS_CONTEXT.md` - Current structure (single source of truth)
- `../choose-skill/references/skills-catalog.md` - Full skill details and examples

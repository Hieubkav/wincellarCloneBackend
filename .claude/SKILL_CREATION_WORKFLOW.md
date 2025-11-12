# 🚀 Workflow Tạo Skill Mới - Step-by-Step

**Mục đích:** Tạo một skill mới theo design pattern của `ux-designer`, chuẩn chỉnh từng bước.  
**Thời gian:** 2-4 giờ cho skill hoàn chỉnh  
**Output:** 1 folder skill với 6-8 files, sẵn sàng production  

---

## Phase 1: Research & Planning (30 phút)

### Step 1.1: Xác Định Domain & Scope
**Task:** Viết 1 trang về domain skill của bạn

**Dùng template này:**
```markdown
## Domain Analysis: [Skill Name]

### 1. Định Nghĩa Lĩnh Vực
- Skill giải quyết vấn đề gì?
- Khi nào developer cần skill này?
- Lĩnh vực kỹ thuật (security, performance, maintainability, etc)?

### 2. Trigger Keywords (brainstorm 20+)
[List 20+ từ khoá mà user có thể mention]
Examples:
- code review
- security audit
- performance analysis
- bug detection
- refactoring
- testing strategy
- [continue...]

### 3. Anti-Patterns (brainstorm 15+)
[Common mistakes trong domain này]
Examples:
- Reviewing code style instead of logic
- Rejecting without explaining
- Suggesting solutions without context
- [continue...]

### 4. Core Principles (3-5)
[Những giá trị nền tảng của domain]
Example:
- Security first (not later)
- Collaboration over criticism
- [continue...]

### 5. Target Users
- AI developers using Claude Code
- Teams working on [domain]
- Developers at level: [junior/mid/senior]

### 6. Success Criteria
- When do users consider skill "successful"?
- How will users validate results?
```

**Example: Code Review Domain**
```markdown
## Domain Analysis: Code Review Excellence

### 1. Định Nghĩa Lĩnh Vực
- Hướng dẫn code review theo best practices
- Giúp tìm bugs, security issues, performance problems
- Dạy developer cách review collaboration-focused

### 2. Trigger Keywords
- code review, CR feedback, pull request review
- security audit, vulnerability scan, bug detection
- performance optimization, N+1 queries, slow database
- testing strategy, test coverage, integration test
- refactoring, code smell, technical debt
- maintainability, readability, documentation
- [continue 15+ more]

### 3. Anti-Patterns
- ❌ Nitpicking code style
- ❌ Rejecting without explanation
- ❌ Suggesting solutions without context
- ❌ Not considering project constraints
- ❌ Making it personal ("bad code")
- ❌ Ignoring non-functional requirements
- ❌ One-pass review (no iteration)
- ❌ Treating security as optional

### 4. Core Principles
- Security First: Identify vulnerabilities early
- Collaboration: Build team, not break team
- Education: Review = mentoring opportunity
- Pragmatism: Consider trade-offs and constraints

### 5. Target Users
- Junior developers learning best practices
- Teams doing peer code review
- CI/CD automation engineers

### 6. Success Criteria
- Reviewers provide actionable feedback
- Developers understand WHY suggestions matter
- Security issues caught before merge
- Code quality improves over time
```

---

### Step 1.2: Thống Kê Keyword Coverage
**Task:** Cross-check keywords với use cases thực tế

```markdown
## Keyword → Use Case Mapping

| Keyword | Use Case | Example |
|---------|----------|---------|
| "code review" | User asks for CR guidance | "Can you review this PR?" |
| "security audit" | User asks for vuln scan | "Check for security issues" |
| "performance" | User asks for perf optimization | "This code is slow" |
| "bug detection" | User asks for bug finding | "Find bugs in this" |
| "refactoring" | User asks for refactor guidance | "How should I refactor this?" |
| [continue...] | | |
```

**Target:** 10+ keywords mapping to real use cases

---

## Phase 2: Create Core File (SKILL.md) - 1.5 giờ

### Step 2.1: Create Skill Directory Structure
```bash
# Create folder: .claude/skills/[category]/[skill-name]/
# Example: .claude/skills/workflows/code-review-excellence/

mkdir -p .claude/skills/workflows/code-review-excellence
touch .claude/skills/workflows/code-review-excellence/SKILL.md
touch .claude/skills/workflows/code-review-excellence/README.md
```

---

### Step 2.2: Write Frontmatter (5 phút)
**File:** `SKILL.md`

```yaml
---
name: [Skill Name]
description: Expert [domain] guidance focusing on [3 main areas]. 
Use when [trigger keywords]. Always [critical behavior], never [anti-behavior]. 
version: 1.0.0
---
```

**Example: Code Review**
```yaml
---
name: Code Review Excellence
description: Expert code review guidance focusing on security, performance, 
and maintainability. Use when reviewing code, analyzing quality issues, finding 
bugs, or mentoring developers. Always explain why a suggestion matters, never 
reject outright without explanation.
version: 1.0.0
---
```

**Checklist:**
- [ ] Name is clear and action-oriented
- [ ] Description includes 10+ trigger keywords
- [ ] Description specifies CRITICAL behavior (Always/Never)
- [ ] Domain focus is clear (3 main areas)

---

### Step 2.3: Write CRITICAL Protocol (10 phút)
**Section:** Core Philosophy

**Template:**
```markdown
## Core Philosophy

### CRITICAL: [Domain] Decision Protocol
- **ALWAYS [primary mandate]** (user requirement)
- **NEVER [anti-pattern]** (prevent bad behavior)
- [Supporting guideline 1]
- [Supporting guideline 2]
- [Supporting guideline 3]
```

**Example: Code Review**
```markdown
## Core Philosophy

### CRITICAL: Collaborative Review Protocol
- **ALWAYS EXPLAIN** why a suggestion matters (prevent "just say no")
- **NEVER REJECT** without alternative suggestion (prevent dismissal)
- Consider project constraints and technical debt tradeoffs
- Build team competency through review (not just enforce standards)
- Frame as learning opportunity, not criticism
```

**Checklist:**
- [ ] 1 primary mandate (ALWAYS)
- [ ] 1 primary prevention (NEVER)
- [ ] 3 supporting guidelines
- [ ] Clear, actionable, memorable

---

### Step 2.4: Write Principles (30 phút)
**Section:** Foundational Principles

**Template:**
```markdown
### [Principle Name] 
[Why it matters - philosophical grounding]
- [How to implement 1]
- [How to implement 2]  
- [How to implement 3]
```

**Example: Code Review**
```markdown
### Security First
[Why] Vulnerabilities caught in review are cheaper to fix than in production
- Identify input/output validation issues early
- Check authentication and authorization logic
- Verify data handling and encryption use
- Review API security assumptions

### Performance Consciousness  
[Why] Small performance issues compound across codebase
- Identify algorithmic complexity issues (O(n²) loops)
- Flag N+1 database query patterns
- Suggest caching for expensive operations
- Consider memory usage in data structures

### Maintainability Focus
[Why] Code is read 10x more than written
- Check naming clarity and self-documentation
- Identify complex sections needing tests
- Suggest simplification before adding features
- Verify error handling comprehensiveness

### Collaborative Tone
[Why] Review builds team, not breaks team
- Explain tradeoffs in your suggestions
- Acknowledge good solutions and design decisions
- Ask questions instead of making statements ("Why did you choose X?" vs "You should use Y")
- Suggest improvements, not demands
```

**Checklist:**
- [ ] 3-5 principles (not more)
- [ ] Each principle has Why + How
- [ ] Principles are NOT generic (domain-specific)
- [ ] Can guide decision-making

---

### Step 2.5: Write Anti-Patterns (20 phút)
**Section:** Common Patterns to Avoid

**Template:**
```markdown
## Common Patterns to Avoid

### ❌ Don't:
- [Specific anti-pattern 1 with example]
- [Specific anti-pattern 2 with example]
- [Specific anti-pattern 3 with example]
- [Specific anti-pattern 4 with example]
- [Specific anti-pattern 5 with example]
- [Specific anti-pattern 6 with example]
- [Specific anti-pattern 7 with example]
- [Specific anti-pattern 8 with example]

### ✅ Do:
- [Best practice 1 corresponding to anti-pattern 1]
- [Best practice 2 corresponding to anti-pattern 2]
- [Best practice 3 corresponding to anti-pattern 3]
- [Best practice 4 corresponding to anti-pattern 4]
- [Best practice 5 corresponding to anti-pattern 5]
- [Best practice 6 corresponding to anti-pattern 6]
- [Best practice 7 corresponding to anti-pattern 7]
- [Best practice 8 corresponding to anti-pattern 8]
```

**Example: Code Review**
```markdown
## Common Patterns to Avoid

### ❌ Don't:
- Nitpick code style instead of logic quality (e.g., variable naming)
- Reject code without explaining why it's problematic
- Suggest solutions without understanding project constraints
- Treat security as optional or "we can fix it later"
- Make suggestions personal ("bad code" instead of "confusing logic")
- Ignore non-functional requirements like scalability
- Do single-pass review without iteration/discussion
- Approve code you don't understand

### ✅ Do:
- Focus on logic, security, performance, maintainability
- Always explain the impact of your suggestion (why it matters)
- Acknowledge constraints and suggest tradeoff analysis
- Treat security as first-class concern (check first)
- Frame feedback as learning ("This pattern might cause X")
- Consider NFRs: scalability, reliability, monitoring
- Iterate through discussion, ask clarifying questions
- Understand before approving, ask questions if unclear
```

**Checklist:**
- [ ] 8 Don't items with specific examples (not generic)
- [ ] 8 Do items matching Don't items (parallel structure)
- [ ] Each is derived from principles above
- [ ] Binary format (easy to remember)

---

### Step 2.6: Write Process & Testing (25 phút)
**Section:** Process & Testing

**Template:**
```markdown
## [Domain] Process & Testing

### [Domain] Workflow

1. **[Phase 1 Name]:**
   - [Context question 1]
   - [Context question 2]
   - [Context question 3]

2. **[Phase 2 Name]:**
   - [Scan/analyze for issue category 1]
   - [Scan/analyze for issue category 2]
   - [Scan/analyze for issue category 3]

3. **[Phase 3 Name]:**
   - [Assessment approach 1]
   - [Assessment approach 2]
   - [Assessment approach 3]

4. **[Phase 4 Name]:**
   - [Suggestion type 1]
   - [Suggestion type 2]
   - [Suggestion type 3]

### Testing Checklist

**[Category 1]:**
- [ ] [Item 1]
- [ ] [Item 2]
- [ ] [Item 3]
- [ ] [Item 4]

**[Category 2]:**
- [ ] [Item 1]
- [ ] [Item 2]
- [ ] [Item 3]
- [ ] [Item 4]

**[Category 3]:**
- [ ] [Item 1]
- [ ] [Item 2]
- [ ] [Item 3]
- [ ] [Item 4]
```

**Example: Code Review**
```markdown
## Code Review Process & Testing

### Code Review Workflow

1. **Understand Context:**
   - What is this PR trying to accomplish?
   - What's the code complexity level?
   - What's the team's skill distribution?
   - Are there technical constraints?

2. **Security Scan:**
   - Identify input/output validation gaps
   - Check authentication/authorization logic
   - Verify data handling and encryption
   - Review external API calls

3. **Performance Assessment:**
   - Identify algorithmic complexity issues
   - Flag N+1 queries or inefficient loops
   - Suggest caching opportunities
   - Consider memory usage

4. **Quality & Maintainability:**
   - Check test coverage for critical paths
   - Evaluate naming clarity
   - Assess code organization
   - Verify error handling

### Testing Checklist

**Security Review:**
- [ ] Input validation for all user inputs
- [ ] Authentication checks in protected routes
- [ ] Authorization logic is correct
- [ ] Secrets/API keys not exposed

**Performance Review:**
- [ ] No O(n²) algorithms for large datasets
- [ ] Database queries are optimized (no N+1)
- [ ] Caching used appropriately
- [ ] Memory leaks prevented

**Maintainability Review:**
- [ ] Code is readable without comments explaining "why"
- [ ] Functions do one thing (single responsibility)
- [ ] Error handling is comprehensive
- [ ] Tests cover happy path + edge cases

**Collaboration Review:**
- [ ] All suggestions have explanations
- [ ] Trade-offs acknowledged
- [ ] Constraints considered
- [ ] Tone is constructive
```

**Checklist:**
- [ ] 4-step workflow (Understand → Scan → Assess → Suggest)
- [ ] Each step has 3-4 concrete sub-tasks
- [ ] 3 test categories with 4 items each
- [ ] All items are measurable

---

### Step 2.7: Add References & Wrap Up (10 phút)
**End of SKILL.md**

```markdown
## Version History

- v1.0.0 (2025-11-12): Initial release with [main features]

## References

For additional context, see:
- **[Domain] Patterns:** `read .claude/skills/[category]/[skill-name]/SECURITY-PATTERNS.md`
- **[Domain] Tools:** `read .claude/skills/[category]/[skill-name]/TOOLS-REFERENCE.md`
- **[Domain] Examples:** `read .claude/skills/[category]/[skill-name]/references/examples.md`
```

**Example: Code Review**
```markdown
## Version History

- v1.0.0 (2025-11-12): Initial release with collaborative review protocol, 
  security-first approach, and comprehensive testing checklist

## References

For additional context, see:
- **Security Patterns:** `read .claude/skills/workflows/code-review-excellence/SECURITY-PATTERNS.md`
- **Performance Patterns:** `read .claude/skills/workflows/code-review-excellence/PERFORMANCE-PATTERNS.md`
- **Common Examples:** `read .claude/skills/workflows/code-review-excellence/references/examples.md`
```

---

### Step 2.8: Final Check - SKILL.md
**Target:** 150-200 dòng
```
Frontmatter:          5 dòng
CRITICAL Protocol:   10 dòng
Principles (3-5):    60-80 dòng
Process + Testing:   40-50 dòng
Anti-patterns:       20 dòng
Version + Refs:      10 dòng
─────────────────────────
Total:              155-175 dòng ✅
```

---

## Phase 3: Create Progressive Files (1 giờ)

### Step 3.1: Create SECURITY-PATTERNS.md (code-review example)
**Kích thước:** 400-500 dòng

**Structure:**
```markdown
# Security Review Patterns

## Authentication & Authorization
### Pattern 1: JWT Token Validation
### Pattern 2: Role-Based Access Control
### Pattern 3: API Key Management

## Input Validation
### Pattern 1: User Input Sanitization
### Pattern 2: Data Type Validation
### Pattern 3: Boundary Checking

## Data Protection
### Pattern 1: Encryption at Rest
### Pattern 2: Encryption in Transit
### Pattern 3: Secret Management

## Common Vulnerabilities
### OWASP Top 10 Checklist
- [ ] SQL Injection
- [ ] Broken Authentication
- [ ] Sensitive Data Exposure
- [ ] XML External Entities (XXE)
- [ ] Broken Access Control
- [ ] Security Misconfiguration
- [ ] Cross-Site Scripting (XSS)
- [ ] Insecure Deserialization
- [ ] Using Components with Known Vulnerabilities
- [ ] Insufficient Logging & Monitoring

## Code Examples
[Real examples of vulnerable code + fixes]

## Testing Tools
- OWASP ZAP
- Burp Suite
- [domain-specific tools]
```

---

### Step 3.2: Create PERFORMANCE-PATTERNS.md
**Kích thước:** 400-500 dòng

**Structure:**
```markdown
# Performance Review Patterns

## Algorithmic Complexity
### O(n) Linear Search vs O(log n) Binary Search
### Identifying O(n²) Loops
### Optimization Strategies

## Database Query Optimization
### N+1 Query Problem
### Index Usage
### Query Caching

## Memory Management
### Memory Leaks in [language]
### Large Data Structure Handling
### Garbage Collection Tuning

## Performance Testing
### Load Testing Tools
### Profiling Tools
### Benchmarking

## Code Examples
[Real examples of slow code + optimizations]

## Best Practices Summary
✅ Do:
- Profile before optimizing
- Use caching appropriately
- [continue...]

❌ Don't:
- Premature optimization
- Over-cache
- [continue...]
```

---

### Step 3.3: Create Testing & Validation
**Create:** `TESTING-STRATEGY.md` (300-400 dòng)

```markdown
# Testing & Code Review Strategy

## Unit Testing Best Practices
- Arrange-Act-Assert pattern
- Test coverage metrics
- Mocking and stubbing

## Integration Testing
- Database interactions
- API calls
- Message queues

## End-to-End Testing
- User workflows
- Critical paths
- Error scenarios

## Code Coverage Analysis
- Coverage metrics (line, branch, path)
- Coverage tools
- Coverage targets by project type

## Automated Review Tools
- Linting (ESLint, Pylint, etc.)
- Type checking (TypeScript, mypy)
- Security scanning (SonarQube, Snyk)
- Performance profiling

## Manual Review Checklist
- [ ] Core logic reviewed
- [ ] Edge cases considered
- [ ] Error handling verified
```

---

## Phase 4: Create User Documentation (README.md) - 30 phút

### Step 4.1: Create README.md Structure
**File:** `README.md`

**Template:**
```markdown
# [Skill Name] Skill

Expert [domain] guidance skill for Claude Code.

## Overview

[2-3 sentences about what skill does]

This skill provides comprehensive [domain] guidance based on [philosophy], 
emphasizing:
- [Focus 1]
- [Focus 2]
- [Focus 3]

## Structure

\`\`\`
[skill-name]/
├── SKILL.md                      # Main skill file
├── [DOMAIN]-PATTERNS.md          # Detailed patterns
├── [DOMAIN2]-PATTERNS.md         # Additional patterns
├── TESTING-STRATEGY.md           # Testing reference
└── references/
    ├── examples.md
    ├── common-mistakes.md
    └── tools-and-resources.md
\`\`\`

## When Claude Uses This Skill

Claude automatically uses this skill when you:
- [Trigger 1]
- [Trigger 2]
- [Trigger 3]
- [Trigger 4]
- [Trigger 5]

## Key Principles

### 1. [Principle 1]
[Explanation]

### 2. [Principle 2]
[Explanation]

### 3. [Principle 3]
[Explanation]

## Supporting Files

### [DOMAIN]-PATTERNS.md
[Description of what's in this file, when it's loaded]

**Claude loads this when:** You ask about [specific domain]

### TESTING-STRATEGY.md
[Description]

**Claude loads this when:** You ask about testing

## Example Usage

### Scenario 1: [Common Use Case]

**You ask:**
\`\`\`
[Example user query]
\`\`\`

**Claude responds:**
\`\`\`
[Example Claude response using skill]
\`\`\`

### Scenario 2: [Another Use Case]

[Repeat above]

## Testing the Skill

After installation, test by asking [domain]-related questions:

\`\`\`
"[Sample question 1]"
"[Sample question 2]"
"[Sample question 3]"
\`\`\`

Claude will automatically use the [Skill Name] skill based on context.

## Version History

- **v1.0.0** (2025-11-12): Initial release
  - [Feature 1]
  - [Feature 2]
  - [Feature 3]

## Customization

This is a personal skill. You can customize:

1. **Protocol**: Edit CRITICAL protocol section
2. **Principles**: Update core principles
3. **Triggers**: Adjust when skill activates
4. **Tools**: Update references for your tech stack

## Troubleshooting

### Claude doesn't use the skill

Check that trigger keywords match your questions. Current keywords:
- [List 10+ keywords]

### Skill feels incomplete

Load supporting files explicitly:
- "Show me [domain] patterns"
- "What are testing best practices?"

## Related Skills

Consider pairing with:
- [Related skill 1]
- [Related skill 2]
```

---

### Step 4.2: Fill in Example Scenarios (15 phút)
**Create 3+ realistic scenarios**

**Example: Code Review**
```markdown
## Example Usage

### Scenario 1: Reviewing a Pull Request

**You ask:**
\`\`\`
Please review this authentication code before I merge.
\`\`\`

**Claude responds:**
\`\`\`
I'll review this following our security-first, collaborative approach.
Before I dive in, I have a few questions:

1. What's the authentication method? (JWT, OAuth, session-based?)
2. Is this for a new feature or refactoring existing auth?
3. Are there specific security concerns you're worried about?

I'll check for:
- Input validation and sanitization
- Proper token handling and validation
- Error messages don't leak sensitive info
- Testing coverage for edge cases

Let me start with the security scan...
\`\`\`

### Scenario 2: Analyzing Performance Issues

**You ask:**
\`\`\`
This database query is slow. How can I optimize it?
\`\`\`

**Claude responds:**
\`\`\`
Let me help you optimize this. Here's my review process:

1. Identify the issue (N+1? missing index? algorithmic?)
2. Explain the performance impact
3. Suggest alternatives with trade-offs
4. Help with implementation

First, let me analyze the query:
[Shows analysis]

Trade-offs to consider:
- Caching vs freshness
- Denormalization vs complexity
- [continue...]
\`\`\`

### Scenario 3: Guiding Code Organization

**You ask:**
\`\`\`
How should I structure this new feature?
\`\`\`

**Claude responds:**
\`\`\`
I'll help you structure this for maintainability. Let me understand:

1. What problem does this feature solve?
2. What's the complexity level?
3. Are there security considerations?

I'll suggest an architecture that:
- Separates concerns clearly
- Makes testing straightforward
- Allows future modifications
- Follows team conventions

Here's my suggestion with trade-offs...
\`\`\`
```

---

### Step 4.3: Final README Checks
```markdown
✅ Checklist:
- [ ] Overview clearly explains what skill does
- [ ] Structure diagram matches your files
- [ ] Trigger conditions are realistic
- [ ] 3+ example scenarios with Q&A
- [ ] Customization guide for your project
- [ ] Troubleshooting covers common issues
- [ ] Version history documents this version
- [ ] Related skills mentioned (if applicable)
```

---

## Phase 5: Create Reference Files (30 phút)

### Step 5.1: Create `references/examples.md`
```markdown
# [Skill] Examples

## Example 1: [Real-world scenario]
[Code or case study]
[Analysis using skill]
[Output/recommendation]

## Example 2: [Another scenario]
[Code or case study]
[Analysis using skill]
[Output/recommendation]

## Example 3: [Edge case]
[Code or case study]
[Analysis using skill]
[Output/recommendation]

## Example 4: [Common mistake]
[Code showing mistake]
[Why it's wrong]
[How to fix it]

## Lessons Learned
[Synthesis of patterns from examples]
```

---

### Step 5.2: Create `references/tools-and-resources.md`
```markdown
# Tools & Resources

## Tools by Category

### [Category 1]
- [Tool 1]: [What it does], [Link]
- [Tool 2]: [What it does], [Link]

### [Category 2]
- [Tool 1]: [What it does], [Link]
- [Tool 2]: [What it does], [Link]

## Learning Resources

### Books
- [Book 1]: [Why it's relevant]
- [Book 2]: [Why it's relevant]

### Online Courses
- [Course 1]: [What you'll learn]
- [Course 2]: [What you'll learn]

### Standards & Guidelines
- [Standard 1]: [Link]
- [Standard 2]: [Link]
```

---

### Step 5.3: Create `references/common-mistakes.md`
```markdown
# Common Mistakes & How to Avoid Them

## Mistake 1: [Specific mistake]
### Why it happens:
[Psychological/practical reason]

### Impact:
[Consequences]

### How to avoid:
[Prevention strategy]

### Example:
[Code example]

## Mistake 2: [Another mistake]
[Repeat structure]

## Mistake 3: [Another mistake]
[Repeat structure]

## Prevention Checklist
- [ ] [Check 1]
- [ ] [Check 2]
- [ ] [Check 3]
```

---

## Phase 6: Final Validation (30 phút)

### Step 6.1: File Structure Checklist
```bash
.claude/skills/[category]/[skill-name]/
├── SKILL.md                    (150-200 dòng)    ✅ DONE
├── README.md                   (200-300 dòng)    ✅ DONE
├── [DOMAIN]-PATTERNS.md        (400-500 dòng)    ✅ DONE
├── [DOMAIN2]-PATTERNS.md       (400-500 dòng)    ✅ DONE
├── TESTING-STRATEGY.md         (300-400 dòng)    ✅ DONE
└── references/
    ├── examples.md             (200-300 dòng)    ✅ DONE
    ├── common-mistakes.md      (200-300 dòng)    ✅ DONE
    └── tools-and-resources.md  (100-200 dòng)    ✅ DONE
```

---

### Step 6.2: Content Quality Checklist

**SKILL.md Validation:**
- [ ] Frontmatter has 10+ trigger keywords
- [ ] CRITICAL protocol at top (3-5 rules)
- [ ] 3-5 core principles (Why + How each)
- [ ] 4-step workflow with sub-tasks
- [ ] 3 test categories × 4 items each
- [ ] Anti-patterns section (8 Don't + 8 Do)
- [ ] All tied together coherently (not random)
- [ ] Total 150-200 dòng

**Progressive Files Validation:**
- [ ] Each file covers 1 major domain concern
- [ ] Structure: Concept → Pattern → Code → Example → Validation
- [ ] 400-500 dòng each (substantial but focused)
- [ ] Cross-references to other files
- [ ] Has real code examples (not abstract)

**README.md Validation:**
- [ ] Clearly explains what skill does
- [ ] Structure diagram matches files
- [ ] 10+ realistic trigger conditions
- [ ] 3+ example scenarios with Q&A
- [ ] Customization guide provided
- [ ] Troubleshooting section included
- [ ] Version history documented
- [ ] 200-300 dòng

**References/ Validation:**
- [ ] examples.md has 4-5 real scenarios
- [ ] common-mistakes.md has 5-8 mistakes + prevention
- [ ] tools-and-resources.md curated for domain
- [ ] All are supporting, not core (can skip)

---

### Step 6.3: Test with Real Queries
**Setup:** Ask Claude 5-10 questions matching your trigger keywords

```
Test Query 1: [Trigger keyword 1] + [domain question]
Expected: Claude uses SKILL.md, follows protocol

Test Query 2: [Trigger keyword 2] + [detailed question]
Expected: Claude loads progressive file

Test Query 3: [Opposite behavior question]
Expected: Claude references anti-patterns

Test Query 4: [Edge case question]
Expected: Claude shows workflow, explains trade-offs

Test Query 5: [Tool/implementation question]
Expected: Claude references supporting files
```

---

### Step 6.4: Final Refinement (15 phút)
**Polish:**
- [ ] Fix typos and grammar
- [ ] Ensure consistent formatting
- [ ] Verify all code examples work
- [ ] Check all links/references
- [ ] Ensure trigger keywords are realistic
- [ ] Validate version number

---

## Phase 7: Documentation & Handoff (15 phút)

### Step 7.1: Create Installation Guide
```markdown
# Installation Guide: [Skill Name]

## Prerequisites
- Claude Code environment set up
- `.claude/skills/` directory exists

## Installation Steps

1. **Copy folder:**
   \`\`\`bash
   cp -r [skill-name] ~/.claude/skills/[category]/
   \`\`\`

2. **Verify structure:**
   \`\`\`bash
   ls -la .claude/skills/[category]/[skill-name]/
   \`\`\`

3. **Test activation:**
   Ask Claude: "Can you [domain action]?"
   Expected: Claude loads SKILL.md and responds

## Customization

Edit `SKILL.md`:
- Line XX: Update trigger keywords
- Line YY: Adjust protocol for your team
- Line ZZ: Update tools for your stack

## Updates

When updating:
1. Update version in SKILL.md frontmatter
2. Document changes in Version History
3. Test with real queries
4. Commit changes

## Support

If skill doesn't activate:
- Check keyword triggers match your question
- Verify file paths are correct
- Read README.md troubleshooting section
```

---

### Step 7.2: Create CHANGELOG
```markdown
# Changelog

## [Unreleased]

## [1.0.0] - 2025-11-12
### Added
- Initial release with core philosophy
- Security-first protocol for [domain]
- Progressive disclosure files for [domain1] and [domain2]
- Comprehensive testing strategy
- Reference materials and examples

### Features
- CRITICAL collaborative protocol
- 4-step workflow
- 3 testing categories
- Anti-patterns guide

## [Future Versions]
- [ ] Add API documentation patterns
- [ ] Add tool integration guides
- [ ] Expand reference examples
```

---

## 🎯 Total Time Breakdown

| Phase | Task | Time | Output |
|-------|------|------|--------|
| 1 | Research & Planning | 30 min | Domain analysis, keywords |
| 2 | SKILL.md (core file) | 90 min | 170-200 dòng core |
| 3 | Progressive files (3) | 60 min | 1200-1500 dòng total |
| 4 | README.md | 30 min | User documentation |
| 5 | Reference files (3) | 30 min | Supporting materials |
| 6 | Validation & Testing | 30 min | Quality check |
| 7 | Documentation | 15 min | Installation guide |
| | **TOTAL** | **4.5 hours** | **Complete skill** |

---

## 📋 Production Checklist

Before publishing skill:

### Code Quality
- [ ] No typos or grammar errors
- [ ] Consistent formatting throughout
- [ ] All code examples tested and working
- [ ] All cross-references valid

### Completeness
- [ ] SKILL.md has CRITICAL protocol
- [ ] SKILL.md has 3-5 principles
- [ ] SKILL.md has 4-step workflow
- [ ] SKILL.md has testing checklist
- [ ] SKILL.md has anti-patterns
- [ ] 2-3 progressive files (500 dòng each)
- [ ] 3 reference files (100-300 dòng each)
- [ ] README.md with examples

### Testability
- [ ] 10+ trigger keywords defined
- [ ] 3+ test queries validated
- [ ] All progressive files load correctly
- [ ] Examples are realistic

### Maintainability
- [ ] Version number documented
- [ ] Customization guide provided
- [ ] Installation instructions clear
- [ ] Related skills identified

### Production Ready
- [ ] Can be shared with team
- [ ] Documentation is complete
- [ ] No hardcoded project-specific values
- [ ] Pattern can be replicated for other skills

---

## 🚀 Next Steps After Creating Skill

1. **Test with team** → Get feedback
2. **Iterate** → Refine based on real usage
3. **Document learnings** → Update anti-patterns section
4. **Version bump** → Release v1.1.0 with improvements
5. **Share pattern** → Help others create similar skills
6. **Generalize** → Extract reusable patterns for new skills

---

**Workflow Version:** 1.0.0  
**Last Updated:** 2025-11-12  
**Based on:** UX Designer Skill Pattern Analysis  
**Status:** Production-ready template


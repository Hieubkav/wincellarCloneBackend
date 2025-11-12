# Phân Tích Design Pattern Skill - UX Designer

## 📌 Tóm Tắt Điều Hành

Skill `ux-designer` là **mẫu vàng** để xây dựng các skill hiệu quả bởi vì:

| Yếu Tố | Giải Pháp |
|--------|----------|
| **Quá tải thông tin** | Phân tách core (170 dòng) + progressive (600+ dòng) |
| **Claude độc quyền quyết định** | CRITICAL Protocol: "ALWAYS ASK before decisions" |
| **Generic advice** | Philosophy section riêng biệt dành cho domain-specific values |
| **Không biết khi nào dùng skill** | Descriptive metadata + trigger keywords rõ ràng |
| **Thiếu validation** | Testing Checklist + Process Workflow tích hợp |
| **Khó tái sử dụng** | Anti-patterns + Common Patterns rõ ràng |

---

## 🏗️ Cấu Trúc Tệp: Kiến Trúc Tinh Tế

### Level 1: Metadata & Quick Trigger
```yaml
---
name: UX Designer
description: Expert UI/UX design guidance... [15+ trigger keywords]
version: 1.0.0
---
```
**Best Practice:** Metadata phải có 10+ keyword để Claude tự động kích hoạt

---

### Level 2: Core Philosophy (SKILL.md, 170 dòng)
**Cấu trúc ba tầng:**

#### 🔴 Tầng 1: CRITICAL Protocol (3-5 dòng)
```markdown
### CRITICAL: Design Decision Protocol
- **ALWAYS ASK** before making any design decisions
- Never implement design changes until explicitly instructed
```
**Best Practice Giải Thích:**
- **CRITICAL** prefix → Claude hiểu đây là rule binding (non-negotiable)
- Đặt ngay đầu Philosophy section → mọi quyết định sau phải follow
- Prevents: Claude tự ý thay đổi design mà không xin phép

#### 🟡 Tầng 2: Foundational Principles (50-80 dòng)
```markdown
### Stand Out From Generic Patterns
- Avoid Generic Training Dataset Patterns (3 items)
- Draw Inspiration From (4 sources)
- Visual Interest Strategies (5 techniques)

### Core Design Philosophy
1. Simplicity Through Reduction
   - Identify, eliminate, reach minimum
   
2. Material Honesty
   - Digital properties, interaction physics
   
3. Obsessive Detail
   - Quality emerges from 100s decisions

[... 4 more principles]
```

**Best Practice Giải Thích:**
- **Phân biệt Philosophy vs Practice** rõ ràng
  - "Stand Out" = Why (giá trị)
  - "Material Honesty" = How (implementation)
- **Hierarchical structure** (Main principle → Sub-principles → Details)
- **Domain-specific NOT generic** (không copy ChatGPT default)
- **Actionable** (mỗi principle có thể guide quyết định)

#### 🟢 Tầng 3: Accessibility Standards + Process (60 dòng)
```markdown
## Accessibility Standards
**Core Requirements:** [5-7 non-negotiable items]
**Implementation Details:** [Concrete code patterns]

## Design Process & Testing
### Design Workflow
1. Understand Context
2. Explore Options
3. Implement Iteratively
4. Validate

### Testing Checklist
- Visual Testing: [3-5 items]
- Accessibility Testing: [3-5 items]
- Cross-Device Testing: [3-5 items]
```

**Best Practice Giải Thích:**
- **Accessibility early, not late** → Không phải afterthought
- **Workflow 4 bước** → Enforce collaboration (Understand/Explore = ask, Implement/Validate = do)
- **Checklist rõ ràng** → Dễ kiểm tra, dễ validate

---

### Level 3: Common Patterns to Avoid (20 dòng)
```markdown
## Common Patterns to Avoid

❌ **Don't:**
- Use generic SaaS blue without considering alternatives
- Default to shadows and gradients
- Copy Apple's design language
- [... 5 more anti-patterns]

✅ **Do:**
- Ask before making design decisions
- Suggest unique, contextually appropriate color pairs
- [... 5 more best practices]
```

**Best Practice Giải Thích:**
- **Binary format** (Don't/Do) → Easy to remember, high contrast
- **Guards against regression** → Prevent common mistakes
- **Saves tokens** → Quick reference, no explanation needed
- **Anti-patterns từ domain-specific failures** (not generic)

---

### Level 4: References & Progressive Disclosure
```
SKILL.md (170 dòng)
    ↓
Mentions: "For additional context, see: RESPONSIVE-DESIGN.md"
    ↓
Claude loads RESPONSIVE-DESIGN.md (584 dòng) only if user asks about responsive
    ↓
Claude loads ACCESSIBILITY.md (828 dòng) only if user asks about a11y
    ↓
Loads references/ (deep-dive files) only if extremely specific questions
```

**Best Practice - Progressive Disclosure Pattern:**

| File | Kích Thước | Khi Nào Load | Nội Dung |
|------|-----------|-------------|---------|
| SKILL.md | 170 dòng | Luôn luôn | Philosophy + Protocol + Process + Anti-patterns |
| RESPONSIVE-DESIGN.md | 584 dòng | User hỏi "mobile", "responsive", "breakpoint" | Detailed patterns: mobile-first, breakpoints, images, typography, layouts |
| ACCESSIBILITY.md | 828 dòng | User hỏi "accessibility", "WCAG", "a11y" | POUR principles, semantic HTML, ARIA, contrast, testing |
| references/visual-design-standards.md | ? | Hỏi cụ thể về color/typography | Deep-dive visual theory |
| references/interaction-design.md | ? | Hỏi cụ thể về animation/interaction | Animation patterns, microinteractions |

**Best Practice:**
- Core file ≤ 200 dòng (digestible)
- Progressive files 500-1000 dòng (comprehensive)
- Total knowledge base > 2000 dòng (complete reference)
- Load only when relevant (token efficiency)

---

### Level 5: README.md - User Documentation
```markdown
# UX Designer Skill
├── Overview
│   └── What skill does, who uses it
├── Structure
│   └── Visual diagram of files
├── When Claude Uses This Skill
│   └── 15+ trigger conditions
├── Key Principles
│   └── Condensed version of SKILL.md
├── Supporting Files
│   └── When each file loads + what it contains
├── Example Usage
│   └── 3+ realistic scenarios with Q&A
├── Testing the Skill
│   └── How to verify it works
├── Version History
│   └── Change log
├── Customization
│   └── How to adapt for your project
└── Troubleshooting
    └── Common issues + solutions
```

**Best Practice Giải Thích:**
- **README = Contract với user** (not just Claude)
- **Enables validation** → "Tôi expect Claude sẽ hỏi trước thay đổi design"
- **Facilitates customization** → "Tôi có thể thay đổi color preferences section"
- **Documents evolution** → Version history giúp tracking

---

## 🔬 Chi Tiết Best Practice Trong UX Designer Skill

### ✅ Best Practice 1: Metadata Keywords (Trigger Activation)
**Location:** Line 3 in SKILL.md
```yaml
description: Expert UI/UX design guidance for building unique, accessible, 
and user-centered interfaces. Use when designing interfaces, making visual 
design decisions, choosing colors/typography, implementing responsive layouts, 
or when user mentions design, UI, UX, styling, or visual appearance.
```

**Keywords đếm được:**
- designing interfaces ✓
- visual design decisions ✓
- choosing colors ✓
- typography ✓
- responsive layouts ✓
- design ✓
- UI ✓
- UX ✓
- styling ✓
- visual appearance ✓

**Best Practice:**
- Tối thiểu 10+ keywords
- Mỗi keyword = 1 use case thực tế
- Include semantic variations (UI/UX, styling/visual, design/designing)
- **Hiệu quả:** Claude sẽ tự động load skill khi user hỏi bất kỳ 1 trong 15+ điều này

---

### ✅ Best Practice 2: CRITICAL Protocol (Enforce Guardrails)
**Location:** Line 8-12 in SKILL.md
```markdown
## Core Philosophy

**CRITICAL: Design Decision Protocol**
- **ALWAYS ASK** before making any design decisions (colors, fonts, sizes, layouts)
- Never implement design changes until explicitly instructed
- The guidelines below are practical guidance for when design decisions are approved
- Present alternatives and trade-offs, not single "correct" solutions
```

**Best Practice Giải Thích:**
- **CRITICAL keyword** → Claude nhận ra đây là top-priority
- **ALL CAPS** trong rule → Visual emphasis cho Claude
- **4 points** → Rõ ràng quyết định AI có thể/không thể làm
  - ❌ KHÔNG: Implement design changes unilaterally
  - ✅ CÓ: Ask first
  - ✅ CÓ: Present alternatives
  - ✅ CÓ: Explain trade-offs
- **Benefit:** Prevents rogue Claude from redesigning UI mà không xin phép

---

### ✅ Best Practice 3: Domain-Specific Philosophy
**Location:** Lines 16-37 in SKILL.md (Stand Out From Generic)
```markdown
### Stand Out From Generic Patterns

**Avoid Generic Training Dataset Patterns:**
- Don't default to "Claude style" designs (excessive bauhaus, liquid glass, apple-like)
- Don't use generic SaaS aesthetics that look machine-generated
- Don't rely only on solid colors - suggest photography, patterns, textures
- Think beyond typical patterns - you can step off the written path
```

**Best Practice Giải Thích:**
- **Domain-specific NOT generic** → Differentiates from ChatGPT
- **Addresses AI bias** → "Don't default to Claude style" là self-aware
- **Provides escape hatch** → "you can step off the written path"
- **Actionable guidance** → Konkret examples (bauhaus, liquid glass, SaaS aesthetic)

**Why This Matters:**
- AI models tend to replicate training data patterns
- UX Designer skill explicitly guards against this
- Applicable to ANY skill: identify + avoid domain's "generic AI patterns"

---

### ✅ Best Practice 4: Philosophy + Principles + Implementation
**Structure Pattern:**
```
Level 1: Philosophy (Why)
    ↓
Level 2: Principles (What)
    ↓
Level 3: Implementation (How)
```

**Example from SKILL.md:**
```markdown
### Core Design Philosophy

1. **Simplicity Through Reduction** ← Principle
   - Identify the essential purpose and eliminate distractions ← Why
   - Begin with complexity, then deliberately remove until reaching simplest ← How
   - Every element must justify its existence ← Validation

2. **Material Honesty** ← Principle
   - Digital materials have unique properties - embrace them ← Why
   - Buttons should feel pressable, cards should feel substantial ← How
   - Animations should reflect real-world physics ← Implementation detail
   - **Prefer flat minimal design with no depth** ← Hard rule (derived from philosophy)
```

**Best Practice:**
- Each principle = (Why + How + Validation)
- Hard rules (no shadows, gradients) derived from philosophy
- Makes implementation predictable: user asks → Claude references principle → answer is consistent

---

### ✅ Best Practice 5: Workflow Enforcement
**Location:** Lines 84-107 in SKILL.md
```markdown
## Design Process & Testing

### Design Workflow

1. **Understand Context:**
   - What problem are we solving?
   - Who are the users?
   - What are success criteria?

2. **Explore Options:**
   - Present 2-3 alternative approaches
   - Explain trade-offs of each option
   - Ask which direction resonates

3. **Implement Iteratively:**
   - Start with structure and hierarchy
   - Add visual polish progressively
   - Test at each stage

4. **Validate:**
   - Use playwright MCP to test visual changes
   - Check across different screen sizes
   - Verify accessibility
```

**Best Practice Giải Thích:**
- **4-step workflow** = collaboration enforcement
  - Step 1-2 = Ask (Understand + Explore)
  - Step 3-4 = Do (Implement + Validate)
- **Each step có sub-questions** → Prevents skipping
- **Mentions validation tools** (playwright MCP) → Concrete, not vague
- **Prevents waterfall** → Iterative approach (implement → test → feedback loop)

**Why This Works:**
- Without workflow, Claude might skip straight to implementation
- Workflow ensures collaboration at each step
- Makes testing part of process, not afterthought

---

### ✅ Best Practice 6: Testing Checklist (Measurable)
**Location:** Lines 108-126 in SKILL.md
```markdown
### Testing Checklist

**Visual Testing:**
- Use playwright MCP when available for automated testing
- Check responsive behavior at common breakpoints
- Verify touch targets on mobile
- Test with different content lengths

**Accessibility Testing:**
- Test keyboard navigation
- Verify screen reader compatibility
- Check color contrast ratios
- Ensure focus states are visible

**Cross-Device Testing:**
- Test on actual devices, not just emulators
- Check different browsers
- Verify touch interactions on mobile
- Test landscape and portrait orientations
```

**Best Practice:**
- 3 categories (Visual, Accessibility, Cross-device) = comprehensive
- Each category có 4-5 specific items = measurable
- Includes "Test on actual devices" = practical constraint
- **Benefit:** After implementation, Claude knows exactly what to check

---

### ✅ Best Practice 7: Anti-Patterns with Binary Format
**Location:** Lines 128-148 in SKILL.md
```markdown
## Common Patterns to Avoid

❌ **Don't:**
- Use generic SaaS blue (#3B82F6) without considering alternatives
- Default to shadows and gradients for depth
- Copy Apple's design language
- Use glass morphism effects
- Make design decisions without asking
- Implement typography without considering the font version
- Use animations that delay user actions
- Create cluttered interfaces with competing elements

✅ **Do:**
- Ask before making design decisions
- Suggest unique, contextually appropriate color pairs
- Use flat, minimal design
- Consider unconventional typography choices
- Provide immediate feedback for interactions
- Create generous white space
- Test with real devices
- Validate accessibility
```

**Best Practice:**
- **Binary** (Don't/Do) format → Easy to parse, high contrast
- **Parallel structure** → Same number of items on both sides
- **Specific examples** → "generic SaaS blue (#3B82F6)" not "avoid blue"
- **Domain-specific failures** → These are actual UX mistakes, not generic
- **Derived from philosophy** → Each "Don't" relates to principles above
  - "Don't copy Apple" ← Avoid Generic Patterns philosophy
  - "Don't use shadows/gradients" ← Prefer flat minimal design principle
  - "Don't make decisions without asking" ← CRITICAL protocol

**Why This Pattern Works:**
- Saves tokens (quick reference)
- Guards against regression (prevents common mistakes)
- Makes implementation predictable

---

### ✅ Best Practice 8: Progressive Disclosure Architecture
**How RESPONSIVE-DESIGN.md is Structured:**

```markdown
# Responsive Design Reference (584 lines total)

## Mobile-First Approach
- Why Mobile-First (3 reasons)

## Breakpoint Strategy
### Standard Breakpoints
- CSS media queries
### Tailwind Responsive Classes
- Examples

## Responsive Images
### Using srcset
### Next.js Image Component

## Responsive Typography
### Fluid Typography with Tailwind
### Fluid Typography with CSS Clamp

## Responsive Layouts
### CSS Grid Pattern
### Flexbox Pattern

## Touch-Friendly Interfaces
### Touch Target Sizing
### Touch Gestures

## Navigation Patterns
### Mobile Menu Pattern
### Sticky Navigation

## Responsive Forms
### Form Layout Pattern

## Responsive Content Hiding
### Show/Hide Based on Screen Size

## Performance Optimization
### Lazy Loading
### Responsive Video

## Testing Responsive Designs
### Browser DevTools
### Real Device Testing
### Playwright Testing

## Common Responsive Patterns
### Card Grid
### Hero Section

## Accessibility Considerations
### Focus Management on Mobile
### Skip Links

## Best Practices Summary
✅ Do: [8 items]
❌ Don't: [8 items]
```

**Best Practice Pattern - Progressive File Structure:**
1. **Problem statement** (Why mobile-first?)
2. **Solution patterns** (How to implement)
3. **Code examples** (Concrete Tailwind, CSS)
4. **Alternative approaches** (srcset vs Next.js Image)
5. **Performance considerations** (Lazy loading, optimization)
6. **Testing strategies** (How to validate)
7. **Common patterns** (Reusable components)
8. **Accessibility** (Never separate, always integrated)
9. **Best practices checklist** (Summary with ✅/❌)

**Why This Structure:**
- **User doesn't read linear** → Can jump to relevant section
- **Theory + practice** → Not just "what" but "why" and "how"
- **Progressive deepening** → Start simple, add complexity
- **Code examples in context** → Not separate documentation
- **Testing integrated** → Not afterthought

---

## 📐 Design Pattern Generalization Matrix

### Thiết Kế Skill Mới: Áp Dụng Pattern này cho `code-review-excellence`

| Component | UX Designer | Code Review | Generic Pattern |
|-----------|------------|-------------|-----------------|
| **Metadata Keywords** | 15+ (design, UI, UX, etc.) | 12+ (review, quality, security, etc.) | Domain-specific trigger words (10+) |
| **CRITICAL Protocol** | "Always Ask before decisions" | "Always Explain why" | 1 non-negotiable rule at top |
| **Philosophy Section** | Stand Out + 5 Principles | Collaborative + 4 Values | Domain philosophy (3-5 major concepts) |
| **Anti-Patterns** | 8 Don't + 8 Do items | Common code smells + best practices | Binary format with domain specifics |
| **Process Workflow** | 4-step (Understand→Explore→Implement→Validate) | 4-step (Understand→Scan→Assess→Suggest) | 4-step collaborative workflow |
| **Testing Checklist** | 3 categories × 4 items | 4 categories × 4 items | Category-based validation |
| **Progressive Files** | RESPONSIVE-DESIGN, ACCESSIBILITY | SECURITY-PATTERNS, PERFORMANCE-PATTERNS | 2-3 domain-specific deep dives |
| **Core File Size** | 170 dòng | ~170 dòng | Aim for 150-200 lines |
| **Progressive Size** | 500-800 dòng | 500-800 dòng | Each progressive file |
| **Trigger Condition** | Visual/styling/design mentions | Code quality/review mentions | Domain-specific keywords |

---

## 🎯 Best Practice Principles Applied

### 1. **Metadata-Driven Activation**
✅ Done by: Descriptive with 10+ keywords
❌ Anti-pattern: Generic skill names

### 2. **Protocol-First Philosophy**
✅ Done by: CRITICAL section at top
❌ Anti-pattern: Rules scattered throughout

### 3. **Principle-Based Decision Making**
✅ Done by: 5 core principles guide all decisions
❌ Anti-pattern: Arbitrary guidelines

### 4. **Guard Against Regression**
✅ Done by: Anti-patterns section with specific examples
❌ Anti-pattern: Only positive guidance

### 5. **Collaborative Workflow**
✅ Done by: 4-step process with "ask at each stage"
❌ Anti-pattern: Single pass implementation

### 6. **Measurable Validation**
✅ Done by: Testing checklist with specific categories
❌ Anti-pattern: Vague "test thoroughly" advice

### 7. **Progressive Information Disclosure**
✅ Done by: Core (170 lines) + Progressive (600+ lines) + References
❌ Anti-pattern: Dump everything in one file (2000+ lines)

### 8. **User-Centric Documentation**
✅ Done by: Comprehensive README.md for humans
❌ Anti-pattern: Only Claude-facing documentation

---

## 💎 UX Designer's Unique Innovations

### Innovation 1: "Stand Out From Generic Patterns"
**Problem:** AI models replicate training data
**Solution:** Explicitly avoid "Claude style", SaaS defaults, Apple copy
**Benefit:** Makes skill output more original

### Innovation 2: "Material Honesty"
**Problem:** Digital design often copies physical without purpose
**Solution:** Lean into digital properties (digital can do things physical can't)
**Benefit:** Philosophical grounding for why certain aesthetics (flat, minimal)

### Innovation 3: Accessibility NOT Optional
**Problem:** A11y often seen as constraint
**Solution:** Integrated into core standards, testing, patterns
**Benefit:** Accessibility is first-class, not afterthought

### Innovation 4: CRITICAL Protocol
**Problem:** AI can bypass user preferences
**Solution:** Explicit "ALWAYS ASK" at top, prevents unilateral decisions
**Benefit:** User retains control over design direction

---

## 📋 Summary: Best Practice Checklist

✅ **Metadata & Activation**
- [ ] 10+ trigger keywords in description
- [ ] Keywords are realistic use cases
- [ ] Skill name clearly indicates domain

✅ **Core Philosophy (SKILL.md)**
- [ ] CRITICAL protocol at top (3-5 points)
- [ ] 3-5 core principles (each has Why/How)
- [ ] 3-5 foundational concepts
- [ ] Accessibility standards integrated
- [ ] 4-step workflow (not single pass)
- [ ] 3+ category testing checklist
- [ ] Anti-patterns section (8+ specific items)
- [ ] Total: 150-200 dòng

✅ **Progressive Disclosure Files**
- [ ] 2-3 detailed reference files (500-800 dòng each)
- [ ] Each covers 1 major concern
- [ ] Structure: Concept → Implementation → Examples → Testing
- [ ] Cross-references to others

✅ **User Documentation (README.md)**
- [ ] Overview + Structure diagram
- [ ] When/how Claude uses skill (trigger conditions)
- [ ] Key principles summary
- [ ] 3+ example usage scenarios
- [ ] Customization guide
- [ ] Troubleshooting
- [ ] Version history

✅ **Reusability**
- [ ] Can be adapted to specific project
- [ ] Pattern can be replicated for other skills
- [ ] No hardcoded project-specific values

---

**Ngày phân tích:** 2025-11-12  
**Nguồn:** UX Designer Skill (Filament Portfolio)  
**Độ trưởng thành:** Production-ready  
**Pattern sử dụng cho:** Tạo code-review-excellence, database-performance, API-design-patterns, và các skill khác


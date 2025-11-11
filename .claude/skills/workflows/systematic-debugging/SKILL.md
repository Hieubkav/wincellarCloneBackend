---
name: Systematic Debugging
description: Four-phase debugging framework ensuring root cause investigation before fixes. Never jump to solutions. USE WHEN encountering bugs, test failures, unexpected behavior, or when fixes fail repeatedly.
---

# Systematic Debugging

## The Iron Law

```
NO FIXES WITHOUT ROOT CAUSE INVESTIGATION
```

**Why:** Random fixes waste time, create new bugs, mask real issues.

## When to Use

ANY technical issue: bugs, test failures, performance problems, build issues.

**ESPECIALLY when:**
- Under time pressure
- "Quick fix" seems obvious
- Tried multiple fixes already
- Don't fully understand issue

## Four Phases (MUST complete in order)

### Phase 1: Root Cause Investigation

**BEFORE any fix:**

1. **Read errors completely** - Don't skip stack traces
2. **Reproduce consistently** - Exact steps, reliable trigger
3. **Check recent changes** - Git diff, dependencies, config
4. **Gather evidence** - Add diagnostic logging at component boundaries
5. **Trace data flow** - Where does bad value originate?

**Multi-component systems:**
```bash
# Add logging at EACH layer
echo "=== Layer 1: Input ===" && log_input
echo "=== Layer 2: Processing ===" && log_process
echo "=== Layer 3: Output ===" && log_output
# Run ONCE to see WHERE it breaks
```

### Phase 2: Pattern Analysis

1. **Find working examples** - Similar code that works
2. **Compare references** - Read completely, don't skim
3. **Identify differences** - Every difference matters
4. **Understand dependencies** - Config, environment, assumptions

### Phase 3: Hypothesis & Testing

1. **Form hypothesis** - "I think X causes Y because Z"
2. **Test minimally** - Smallest change, one variable
3. **Verify** - Worked? → Phase 4. Didn't work? → New hypothesis
4. **When uncertain** - Say "I don't understand X"

### Phase 4: Implementation

1. **Create failing test** - Automated if possible
2. **Implement single fix** - Address root cause only
3. **Verify** - Tests pass, issue resolved, no new breakage

**If fix doesn't work:**
- Fixes tried < 3: Return to Phase 1
- **Fixes tried ≥ 3: STOP - Question architecture**
  - Pattern might be fundamentally wrong
  - Discuss with human before more fixes

## Red Flags - STOP & Follow Process

Catch yourself thinking:
- "Quick fix now, investigate later"
- "Just try X and see"
- "Skip the test, manually verify"
- "Probably X, let me fix"
- "One more fix" (after 2+)
- "Each fix reveals new problems"

**ALL → Return to Phase 1**

## Common Excuses

| Excuse | Reality |
|--------|---------|
| "Too simple for process" | Simple bugs have root causes too |
| "Emergency, no time" | Systematic is FASTER than guessing |
| "Just try first" | First fix sets bad pattern |
| "Test after confirming" | Untested fixes don't stick |
| "Multiple fixes save time" | Can't isolate, causes new bugs |

## Quick Reference

| Phase | Activities | Success |
|-------|-----------|---------|
| 1. Root Cause | Read, reproduce, check, gather evidence | Understand WHAT & WHY |
| 2. Pattern | Find working, compare | Identify differences |
| 3. Hypothesis | Theory, test minimally | Confirmed or new |
| 4. Implementation | Test, fix, verify | Resolved, tests pass |

## Real Impact

- Systematic: 15-30 min to fix
- Random: 2-3 hours thrashing
- First-time success: 95% vs 40%

---

**Complete guide:** `read .claude/skills/workflows/systematic-debugging/SKILL.md` (original 296 lines)

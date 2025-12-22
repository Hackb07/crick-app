---
category: ai-governance
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@ai:feedback"
---

# AI Feedback Loop

**Learn from Mistakes, Never Repeat Them**

---

## Core Principle

WHEN starting ANY task THEN check `MISTAKES_LOG.md` for past errors.

WHEN making a mistake THEN log it immediately for future prevention.

---

## Workflow

### 1. Pre-Task Check
```
BEFORE writing code THEN:
  1. Read MISTAKES_LOG.md
  2. Scan for relevant past errors
  3. Apply preventive measures
```

### 2. During Task
```
IF error occurs THEN:
  1. Identify root cause
  2. Check if similar error exists in log
  3. If new → prepare log entry
```

### 3. Post-Task Logging
```
WHEN mistake confirmed THEN:
  1. Add entry to MISTAKES_LOG.md
  2. Include:
     - Date (YYYY-MM-DD)
     - Context (What task was being done)
     - Mistake (What went wrong)
     - Root Cause (Why it happened)
     - Prevention (How to avoid in future)
     - Rule Reference (Which rule was violated)
```

---

## Mistake Categories

### 1. Logic Errors
- Off-by-one errors
- Null/undefined handling
- Edge case misses
- Incorrect assumptions

### 2. Architecture Violations
- Circular dependencies
- Tight coupling
- Breaking existing contracts
- Missing separation of concerns

### 3. Security Oversights
- Missing input validation
- XSS vulnerabilities
- Hardcoded secrets
- Insufficient error handling

### 4. Performance Issues
- Memory leaks
- N+1 queries
- Unnecessary allocations
- Blocking operations

### 5. AI-Specific Errors
- Hallucinated APIs
- Deprecated patterns
- Incorrect library usage
- Misunderstood requirements

---

## Log Entry Format

```markdown
## [YYYY-MM-DD] - [Category] - [Brief Description]

**Context**: [What task was being performed]

**Mistake**: [What went wrong - be specific]

**Root Cause**: [Why it happened - analysis]

**Impact**: [What broke or could have broken]

**Prevention**: [Concrete steps to avoid this in future]

**Rule Violated**: [@rule:reference]

**Status**: ✅ Fixed / ⚠️ Monitoring / ❌ Recurring

---
```

---

## Prevention Strategies

### Active Scanning
```
WHEN reading MISTAKES_LOG THEN:
  - Filter by current context (Web vs Game vs System)
  - Prioritize recent mistakes (last 30 days)
  - Flag recurring patterns (Status: ❌ Recurring)
```

### Pattern Recognition
```
IF mistake_count >= 3 for same_root_cause THEN:
  - Escalate to CRITICAL
  - Create automation rule
  - Update UNIFIED_RULES.md
```

### Continuous Improvement
```
MONTHLY review MISTAKES_LOG:
  - Identify top 5 recurring issues
  - Create preventive automation
  - Update rule documentation
```

---

## Integration with Existing Rules

### With @core:workflow
```
BEFORE code (Step 1) THEN:
  1. Read MISTAKES_LOG.md
  2. Understand requirements
  3. Identify constraints
  ...
```

### With @ai:hallucination
```
WHEN AI generates code THEN:
  1. Check MISTAKES_LOG for hallucinated APIs
  2. Verify all function calls exist
  3. Cross-reference with documentation
```

### With @test:regression
```
WHEN bug found THEN:
  1. Write failing test
  2. Fix bug
  3. Log to MISTAKES_LOG.md
  4. Commit test + fix + log entry
```

---

## Example Log Entry

```markdown
## 2025-12-04 - Logic Error - Snake History Double Update

**Context**: Optimizing Snake game physics for zero-allocation performance.

**Mistake**: In Particle.update(), I added `this.pos.addMut(this.vel.mult(dt))` 
but then also added manual component updates `this.pos.x += this.vel.x * dt`, 
causing position to update twice per frame.

**Root Cause**: Incomplete refactoring. Started with mutable method, then 
switched to manual updates but forgot to remove the first line.

**Impact**: Particles moved 2x faster than intended, breaking visual effects.

**Prevention**: 
1. ALWAYS remove old code when refactoring
2. Test visual output after physics changes
3. Use single update pattern (either mutable OR manual, not both)

**Rule Violated**: @core:clean (DRY - Don't Repeat Yourself)

**Status**: ✅ Fixed

---
```

---

## Automation Hooks

### Pre-Commit
```bash
# Check if MISTAKES_LOG.md has been updated in last 7 days
node automation/check-feedback-loop.js
```

### CI/CD Gate
```bash
# Verify no recurring mistakes (Status: ❌ Recurring) exist
node automation/validate-mistake-resolution.js
```

---

## Metrics

Track effectiveness:
- **Total Mistakes Logged**: Count
- **Recurring Mistakes**: Count (Status: ❌)
- **Prevention Rate**: (Fixed / Total) × 100%
- **Time to Resolution**: Days between log and fix

---

## Enforcement

- **P1 Priority**: ALWAYS check log before starting work
- **Mandatory Logging**: ANY mistake must be logged
- **Review Cadence**: Weekly for active projects, Monthly for stable projects

---

**Related Rules**:
- `@ai:hallucination` - Prevent AI-generated errors
- `@test:regression` - Prevent bug recurrence
- `@core:workflow` - Structured thinking process

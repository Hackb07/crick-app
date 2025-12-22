---
category: testing
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@test:regression"
source: "Consolidated from RULE 12 (DRCA-S)"
---

# Regression Prevention

**Fix It Once. Verify Forever.**

---

## Core Principle

WHEN a bug is found THEN write a test that reproduces it.

IF test passes THEN fix the bug.

---

## Workflow

1.  **Reproduce**: Create a failing test case (Red).
2.  **Fix**: Patch the code (Green).
3.  **Refactor**: Clean up (Refactor).
4.  **Commit**: Include test with fix.

---

## Root Cause Analysis (RCA)

- **Rule**: For critical bugs, document RCA.
- **Question**: Why wasn't this caught by existing tests?

---

## Enforcement

- **Review**: Reject bug fixes without regression tests.

---

**Related Rules**:
- `@test:quality` - Test quality
- `@ops:incident` - Incident response

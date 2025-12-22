---
category: testing
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@test:coverage"
source: "Consolidated from Test Rules"
---

# Coverage Requirements

**Measure What Matters. Don't Game the Metrics.**

---

## Core Principle

WHEN writing code THEN cover it with tests.

IF coverage drops THEN block merge.

---

## Thresholds

### 1. Line Coverage
- **Target**: > 80% global.
- **Critical**: 100% for Core/Auth/Payment modules.

### 2. Branch Coverage
- **Target**: > 70%.
- **Goal**: Test all `if/else` paths.

### 3. Mutation Score
- **Target**: > 60% (if available).
- **Goal**: Ensure tests actually fail when code breaks.

---

## Enforcement

- **CI**: `jest --coverage`, `phpunit --coverage`.
- **Gate**: Fail build if thresholds missed.

---

**Related Rules**:
- `@test:pyramid` - Test strategy
- `@test:quality` - Test quality

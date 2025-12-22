---
category: testing
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@test:quality"
source: "Consolidated from Test Rules"
---

# Test Quality

**Tests Are Code. Treat Them With Respect.**

---

## Core Principle

WHEN writing tests THEN ensure they are readable, reliable, and independent.

IF a test is flaky THEN fix it or delete it.

---

## Quality Standards

### 1. Naming
- **Pattern**: `it_should_expected_behavior_when_condition`.
- **Example**: `it_should_calculate_total_when_items_added`.

### 2. Independence
- **Rule**: Tests must not depend on execution order.
- **Action**: Clean DB between tests (`RefreshDatabase`).

### 3. Assertions
- **Rule**: One logical assertion per test.
- **Anti-Pattern**: Testing 10 things in one function.

### 4. Mocks
- **Rule**: Mock external boundaries only.
- **Anti-Pattern**: Mocking the class under test.

---

## Enforcement

- **Review**: Reject tests with `sleep()`.
- **CI**: Flaky test detector.

---

**Related Rules**:
- `@test:pyramid` - Test strategy
- `@core:clean` - Clean code

---
category: testing
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@test:pyramid"
source: "Consolidated from RULE 18 (TP-S) & test-pyramid.mdc"
---

# Test Pyramid

**70% Unit. 20% Integration. 10% E2E.**

---

## Core Principle

WHEN writing tests THEN follow the 70/20/10 distribution.

IF logic is complex THEN unit test it. IF flow crosses boundaries THEN integration test it.

---

## The Pyramid

### 1. Unit Tests (70%)
- **Scope**: Single function, class, or component.
- **Speed**: Fast (< 10ms).
- **Mocks**: Mock all external dependencies (DB, API, File System).
- **Goal**: Verify logic, edge cases, error handling.
- **Tool**: PHPUnit, Jest.

### 2. Integration Tests (20%)
- **Scope**: Interaction between 2+ modules (e.g., Service + DB).
- **Speed**: Medium (< 500ms).
- **Mocks**: Real DB (test container), mocked external APIs.
- **Goal**: Verify data flow, transactions, queries.

### 3. E2E Tests (10%)
- **Scope**: Full user journey (Browser → API → DB).
- **Speed**: Slow (> 1s).
- **Mocks**: Minimal. Test the real system.
- **Goal**: Verify critical user paths (Login, Checkout).
- **Tool**: Cypress, Playwright.

---

## Test Quality Standards

### AAA Pattern
All tests must follow **Arrange-Act-Assert**:
```php
// Arrange
$calculator = new Calculator();
$a = 5; $b = 10;

// Act
$result = $calculator->add($a, $b);

// Assert
$this->assertEquals(15, $result);
```

### No Flakiness
- **Rule**: Tests must be deterministic.
- **Anti-Pattern**: `sleep(1)`, relying on random execution order.

### Coverage Targets
- **Unit**: > 80% line coverage.
- **Critical Paths**: 100% branch coverage.

---

## Enforcement

- **CI/CD**: `test-runner.js` enforces coverage thresholds.
- **PR Gate**: Block merge if coverage drops.
- **Review**: Reject PRs without tests.

---

**Related Rules**:
- `@test:coverage` - Coverage details
- `@quality:standards` - Code quality

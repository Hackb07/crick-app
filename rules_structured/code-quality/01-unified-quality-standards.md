---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:standards"
source: "Consolidated from RULE 5 (UQC-S)"
---

# Unified Quality Standards

**Measurable Quality. Strict Metrics. No Spaghetti.**

---

## Core Principle

WHEN writing code THEN adhere to strict complexity and size limits.

IF code exceeds limits THEN refactor immediately.

---

## Complexity Metrics

### 1. Cyclomatic Complexity
- **Limit**: Max **10** per function.
- **Action**: If > 10, split function.

### 2. Function Size
- **Limit**: Max **50** lines.
- **Ideal**: 10-20 lines.
- **Action**: Extract methods.

### 3. Class Size
- **Limit**: Max **300** lines.
- **Action**: Extract classes (SRP).

### 4. Nesting Depth
- **Limit**: Max **3** levels.
- **Action**: Return early, guard clauses.

---

## Code Style

### 1. Formatting
- **PHP**: PSR-12.
- **JS/TS**: Prettier standard.
- **HTML/CSS**: Consistent indentation (2 or 4 spaces).

### 2. Comments
- **Rule**: Explain WHY, not WHAT.
- **Anti-Pattern**: `// Increment i`
- **Required**: PHPDoc/JSDoc for public APIs.

### 3. Dead Code
- **Rule**: Delete commented-out code.
- **Rule**: Remove unused imports/variables.

---

## Enforcement

- **Linting**: ESLint, PHPCS.
- **Static Analysis**: PHPStan (Level 8), SonarQube.
- **CI Gate**: Block merge if quality gate fails.

---

**Related Rules**:
- `@core:clean` - Clean code principles
- `@quality:naming` - Naming conventions

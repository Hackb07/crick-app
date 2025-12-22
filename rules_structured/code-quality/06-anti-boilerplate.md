---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:boilerplate"
source: "Consolidated from RULE 13 (ABP-S)"
---

# Anti-Boilerplate

**Don't Repeat Yourself. Generate, Don't Type.**

---

## Core Principle

WHEN code is repetitive THEN automate it.

IF boilerplate > logic THEN refactor.

---

## Reduction Strategies

### 1. Generators
- **Rule**: Use CLI tools to scaffold files.
- **Tool**: `artisan make:model`, `nest generate`.

### 2. Base Classes
- **Rule**: Abstract common setup into Base classes.
- **Example**: `BaseController` handles standard responses.

### 3. Traits / Mixins
- **Rule**: Use composition for shared behavior.
- **Example**: `HasUuid`, `Timestampable`.

---

## Enforcement

- **Review**: Flag copy-pasted setup code.
- **Metrics**: High duplication % blocks merge.

---

**Related Rules**:
- `@core:clean` - DRY principle
- `@arch:structure` - Project structure

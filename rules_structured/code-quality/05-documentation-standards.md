---
category: code-quality
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@quality:docs"
source: "Consolidated from RULE 14 (DOC-S) & documentation-standards.mdc"
---

# Documentation Standards

**Code Tells What. Docs Tell Why.**

---

## Core Principle

WHEN writing code THEN document the *intent* and *constraints*.

IF code is complex THEN explain the *why*.

---

## 5-Level Documentation Hierarchy

### 1. Code (Self-Documenting)
- **Rule**: Variable/Function names must reveal intent.
- **Goal**: Read code like prose.
- **See**: `@quality:naming`

### 2. Inline Comments (The "Why")
- **Rule**: Only explain non-obvious logic, hacks, or business rules.
- **Anti-Pattern**: `// Increment i`
- **Good**: `// Retry 3 times because API X is flaky`

### 3. Function/Class Docs (PHPDoc/JSDoc)
- **Rule**: Required for all public APIs/Interfaces.
- **Content**: Parameters, Return types, Thrown exceptions.
```php
/**
 * Calculates tax based on region rules.
 *
 * @param float $amount
 * @param string $regionCode
 * @throws InvalidRegionException
 * @return float
 */
public function calculateTax(...)
```

### 4. Module README (The "How")
- **Rule**: Every module/service needs a `README.md`.
- **Content**: Purpose, Dependencies, Setup, Usage Examples.

### 5. Architecture Decision Records (ADR)
- **Rule**: Document major decisions (Framework choice, DB schema).
- **Format**: Context, Decision, Consequences.
- **Location**: `/docs/adr/`

---

## API Documentation

**Rule**: All APIs must have OpenAPI (Swagger) specs.
- **Source of Truth**: The code (via annotations) or the spec file.
- **Requirement**: Examples for every endpoint.

---

## Enforcement

- **CI**: Check for missing PHPDoc on public methods.
- **Review**: Reject PRs with "magic" logic and no comments.
- **Automation**: `docs-generator.js` can scaffold docs.

---

**Related Rules**:
- `@quality:naming` - Naming conventions
- `@arch:intent` - AIS documentation

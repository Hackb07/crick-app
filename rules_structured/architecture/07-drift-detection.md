---
category: architecture
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@arch:drift"
source: "Consolidated from RULE 32 (ABD-S)"
---

# Architecture Drift Detection

**Code Must Match Design. Reality Must Match Intent.**

---

## Core Principle

WHEN code diverges from architecture THEN it is a violation.

IF drift is detected THEN block merge or require AIS update.

---

## Detection Mechanisms

### 1. Static Analysis
- **Check**: Imports violating layer boundaries.
- **Tool**: `dependency-cruiser` / `deptrac`.
- **Rule**: No skipping layers (e.g., Controller -> Repository).

### 2. Structural Validation
- **Check**: Files outside standard folders.
- **Rule**: All code must be in `/src` and follow module structure.
- **See**: `@arch:structure`.

### 3. Schema Drift
- **Check**: DB schema vs Migration files vs Entity definitions.
- **Rule**: All three must stay in sync.

---

## Reconciliation Process

1.  **Detect**: CI reports violation.
2.  **Decide**:
    *   **Fix Code**: Refactor to match architecture.
    *   **Update Arch**: If the drift is intentional, update the AIS (`@arch:intent`).
3.  **Enforce**: PR cannot merge until resolved.

---

## Enforcement

- **CI**: `consistency-auditor.js` runs on every PR.
- **Nightly**: Full scan of entire repo.

---

**Related Rules**:
- `@arch:intent` - The source of truth
- `@arch:boundary` - Boundary rules

---
category: architecture
priority: P2
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@arch:lifecycle"
source: "Consolidated from RULE 33 (MLS-S)"
---

# Module Lifecycle

**Birth. Growth. Maturity. Death.**

---

## Core Principle

WHEN a module changes state THEN document it.

IF a module is deprecated THEN enforce warning usage.

---

## Lifecycle Stages

### 1. Experimental (Alpha)
- **Status**: `experimental`
- **Use**: Prototyping, non-critical features.
- **Rules**: Can break API contracts. No SLAs.

### 2. Stable (GA)
- **Status**: `stable`
- **Use**: Production features.
- **Rules**: Strict API versioning. Full testing required.

### 3. Deprecated
- **Status**: `deprecated`
- **Use**: Phasing out.
- **Rules**: Log warnings on usage. No new features. Planned removal date.

### 4. End of Life (EOL)
- **Status**: `eol`
- **Use**: Dead code.
- **Rules**: Must be deleted from codebase.

---

## Promotion Criteria

**Experimental → Stable**:
- [ ] 80% Test Coverage
- [ ] Documentation Complete
- [ ] Security Review Passed
- [ ] Performance Budget Met

---

## Enforcement

- **Metadata**: `module.json` or `package.json` must state status.
- **CI**: Block usage of EOL modules. Warn on Deprecated.

---

**Related Rules**:
- `@arch:api` - API versioning
- `@workflow:release` - Release strategy

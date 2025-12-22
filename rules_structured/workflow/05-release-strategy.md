---
category: workflow
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@workflow:release"
source: "Consolidated from RULE 34 (RSS-S)"
---

# Release Strategy

**Ship Safely. Rollback Fast.**

---

## Core Principle

WHEN releasing THEN use a safe deployment strategy.

IF release fails THEN rollback automatically.

---

## Strategies

### 1. Blue/Green
- **Method**: Deploy to idle environment (Green), switch traffic.
- **Pros**: Instant rollback, zero downtime.

### 2. Canary
- **Method**: Roll out to 1% -> 10% -> 100% of users.
- **Pros**: Limits blast radius.

### 3. Feature Flags
- **Method**: Deploy code behind a toggle.
- **Pros**: Decouple deploy from release.

---

## Versioning

**Rule**: Semantic Versioning (`MAJOR.MINOR.PATCH`).
- **Major**: Breaking change.
- **Minor**: New feature (backward compatible).
- **Patch**: Bug fix.

---

## Enforcement

- **CI/CD**: Automated version bumping.
- **Ops**: Release gates.

---

**Related Rules**:
- `@workflow:cicd` - Pipeline
- `@arch:api` - API versioning

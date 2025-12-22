---
category: operations
priority: P2
type: ruleset
applies_to: [backend]
always_apply: true
shorthand: "@ops:hardening"
source: "Consolidated from RULE 45 (OPS-S)"
---

# Operational Hardening

**Production Ready. Battle Tested.**

---

## Core Principle

WHEN deploying code THEN ensure it is resilient and secure.

IF system fails THEN it should fail gracefully.

---

## Hardening Checklist

### 1. Timeouts
- **Rule**: All external calls must have timeouts.
- **Default**: 5 seconds.

### 2. Retries
- **Rule**: Implement exponential backoff for transient errors.
- **Limit**: Max 3 retries.

### 3. Rate Limiting
- **Rule**: Protect all public APIs.
- **Strategy**: Token bucket / Leaky bucket.

### 4. Circuit Breakers
- **Rule**: Stop calling failing services to prevent cascading failure.

---

## Enforcement

- **Review**: Check for missing timeouts.
- **Testing**: Chaos engineering (simulate failures).

---

**Related Rules**:
- `@sec:baseline` - Security baseline
- `@quality:errors` - Error handling

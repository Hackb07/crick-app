---
category: operations
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@ops:incident"
source: "Consolidated from RULE 12 (DRCA-S)"
---

# Incident Response

**Calm. Structured. Blameless.**

---

## Core Principle

WHEN production breaks THEN follow the incident protocol.

IF incident is over THEN conduct a blameless post-mortem.

---

## Protocol

1.  **Detect**: Alert triggers.
2.  **Acknowledge**: On-call engineer responds.
3.  **Mitigate**: Stop the bleeding (Rollback, Feature Flag).
4.  **Resolve**: Fix the root cause.

---

## Post-Mortem (RCA)

**Template**:
- **Summary**: What happened?
- **Timeline**: When did it happen?
- **Root Cause**: Why did it happen? (5 Whys)
- **Action Items**: How to prevent recurrence?

**Rule**: Blameless. Focus on process, not people.

---

## Enforcement

- **Process**: Required for all Sev-1/Sev-2 incidents.

---

**Related Rules**:
- `@test:regression` - Regression tests
- `@ops:observability` - Metrics

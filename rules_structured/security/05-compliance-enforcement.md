---
category: security
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@sec:compliance"
source: "Consolidated from RULE 44 (CES-S)"
---

# Compliance Enforcement

**Audit Everything. Prove Compliance.**

---

## Core Principle

WHEN a critical action occurs THEN log an immutable audit record.

IF compliance check fails THEN block the action.

---

## Audit Trails

### 1. What to Log
- **Who**: User ID / IP Address.
- **What**: Action (Create, Update, Delete).
- **When**: UTC Timestamp.
- **Where**: Resource ID.
- **Why**: Reason (if applicable).

### 2. Immutability
- **Rule**: Audit logs must be append-only.
- **Storage**: Separate audit table or service.

---

## Regulatory Standards

### 1. GDPR (Europe)
- **Requirement**: Consent management, Data portability.

### 2. SOC2 (Security)
- **Requirement**: Change management logs, Access reviews.

### 3. PCI-DSS (Payments)
- **Requirement**: Never store CVV. Encrypt PAN.

---

## Enforcement

- **Middleware**: Auto-logging of state changes.
- **CI**: Check for compliance violations (e.g., logging credit cards).
- **Review**: Compliance Officer sign-off for sensitive features.

---

**Related Rules**:
- `@sec:privacy` - Data privacy
- `@ops:observability` - Logging

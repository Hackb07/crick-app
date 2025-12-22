---
category: ai-governance
priority: P2
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:trust"
source: "Consolidated from RULE 51 (ATCS-S)"
---

# AI Trust Calibration

**Trust But Verify. Calibrate Autonomy.**

---

## Core Principle

WHEN task risk increases THEN AI autonomy decreases.

IF task is critical THEN require human sign-off.

---

## Trust Levels

### Level 1: Low Risk (High Autonomy)
- **Tasks**: Documentation, Unit Tests, CSS.
- **Action**: AI can generate and commit (if tests pass).

### Level 2: Medium Risk (Verified Autonomy)
- **Tasks**: Feature Logic, API Endpoints.
- **Action**: AI generates, Human reviews PR.

### Level 3: High Risk (Low Autonomy)
- **Tasks**: Auth, Payments, Core Architecture.
- **Action**: AI suggests, Human implements/verifies line-by-line.

---

## Enforcement

- **User**: Assess risk level before prompting.
- **System**: `.cursorrules` warns on high-risk files (e.g., `auth.php`).

---

**Related Rules**:
- `@ai:safety` - Safety rails
- `@sec:baseline` - Security baseline

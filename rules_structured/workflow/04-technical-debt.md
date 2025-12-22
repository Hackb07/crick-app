---
category: workflow
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@workflow:debt"
source: "Consolidated from RULE 11 (TDL-S)"
---

# Technical Debt Management

**Debt Must Be Paid. Track It or Drown.**

---

## Core Principle

WHEN taking shortcuts THEN document it as debt.

IF debt expires THEN pay it immediately.

---

## Debt Ledger

**Location**: `/docs/debt/ledger.md`

**Format**:
- **Item**: Description of the hack/shortcut.
- **Cost**: Impact (High/Med/Low).
- **Deadline**: When must it be fixed?
- **Owner**: Who is responsible?

---

## Rules

1.  **No Hidden Debt**: `// TODO` comments must be tracked in the ledger.
2.  **Debt Cap**: Max 10 active high-priority debt items.
3.  **Refactoring Sprints**: Dedicate 20% time to paying debt.

---

## Enforcement

- **Review**: Reject PRs with excessive TODOs without ledger entries.
- **Process**: Monthly debt review meeting.

---

**Related Rules**:
- `@core:clean` - Clean code
- `@core:mindset` - Senior mindset

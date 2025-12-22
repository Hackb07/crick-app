---
category: ai-governance
priority: P1
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:multi-agent"
source: "Consolidated from RULE 28 (MAC-S)"
---

# Multi-Agent Collaboration

**Primary. Validator. Fixer. The Hierarchy of Agents.**

---

## Core Principle

WHEN complex tasks arise THEN split between specialized agents.

IF agents disagree THEN escalate or default to Validator.

---

## Agent Roles

### 1. Primary (The Builder)
- **Role**: Generates initial solution.
- **Focus**: Speed, creativity, implementation.

### 2. Validator (The Critic)
- **Role**: Reviews Primary's output.
- **Focus**: Security, correctness, adherence to rules.
- **Authority**: Can reject Primary's work.

### 3. Fixer (The Polisher)
- **Role**: Applies fixes requested by Validator.
- **Focus**: Refactoring, bug fixing.

---

## Workflow

1.  **Primary** drafts code.
2.  **Validator** checks against `@sec:baseline` and `@arch:intent`.
3.  **If Pass**: Output code.
4.  **If Fail**: Send to **Fixer** with error report.
5.  **Loop**: Max 2 iterations.

---

## Enforcement

- **Protocol**: Define agent handoffs in prompt.
- **Limit**: Stop infinite loops (max 2 retries).

---

**Related Rules**:
- `@ai:review` - Semantic review
- `@core:workflow` - Coding workflow

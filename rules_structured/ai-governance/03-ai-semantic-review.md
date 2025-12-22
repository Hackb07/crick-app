---
category: ai-governance
priority: P1
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:review"
source: "Consolidated from RULE 24 (AISR-S)"
---

# AI Semantic Review

**Code Must Make Sense. Intent Must Be Preserved.**

---

## Core Principle

WHEN AI generates code THEN validate semantic correctness, not just syntax.

IF code compiles but fails logic THEN it is a hallucination.

---

## Review Checklist

### 1. Intent Preservation
- **Check**: Does the code actually solve the user's request?
- **Anti-Pattern**: User asks for "Login", AI builds "Registration".

### 2. Context Awareness
- **Check**: Does the code respect existing patterns?
- **Anti-Pattern**: Using `snake_case` in a `camelCase` project.

### 3. Logic Validation
- **Check**: Are edge cases handled?
- **Check**: Are loops terminating?

---

## The "Critic" Role

**Rule**: Every AI generation step must be followed by a self-critique.
1.  **Generate**: Draft code.
2.  **Critique**: Check against rules.
3.  **Refine**: Fix issues.
4.  **Finalize**: Output.

---

## Enforcement

- **Process**: AI Agent must run "Critic" step before outputting.
- **Human**: Developer reviews AI PRs with extra scrutiny on logic.

---

**Related Rules**:
- `@ai:hallucination` - Prevention
- `@core:mindset` - Senior mindset

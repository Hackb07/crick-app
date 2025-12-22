---
category: workflow
priority: P3
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@workflow:review"
source: "Consolidated from Code Review Rules"
---

# Code Review Standards

**Quality Gate. Knowledge Sharing.**

---

## Core Principle

WHEN reviewing code THEN look for logic, security, and style.

IF code is hard to understand THEN reject it.

---

## Checklist

### 1. Functionality
- [ ] Does it meet requirements?
- [ ] Are edge cases handled?

### 2. Quality
- [ ] Is it readable? (`@core:clean`)
- [ ] Is it tested? (`@test:coverage`)
- [ ] Is it documented? (`@quality:docs`)

### 3. Security
- [ ] Input validation? (`@sec:baseline`)
- [ ] Auth checks? (`@sec:auth`)

---

## Etiquette

- **Rule**: Critique the code, not the person.
- **Rule**: Explain *why* a change is requested.
- **Rule**: Approve if "good enough", don't block for nitpicks.

---

## Enforcement

- **GitHub/GitLab**: Require 1 approval to merge.
- **CI**: All checks must pass before review.

---

**Related Rules**:
- `@core:mindset` - Senior mindset
- `@workflow:git` - Git workflow

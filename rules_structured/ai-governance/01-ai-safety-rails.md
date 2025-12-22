---
category: ai-governance
priority: P1
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:safety"
source: "Consolidated from RULE 25 (AISR2-S) & temperature-policy.mdc"
---

# AI Safety Rails

**Deterministic Output. Controlled Temperature. Safe Generation.**

---

## Core Principle

WHEN generating code THEN adhere to strict temperature and safety profiles.

IF task is critical THEN use deterministic settings.

---

## Temperature Policy

### 1. Critical Logic (0.0 - 0.1)
**Use for**: Auth, Payments, Cryptography, Core Architecture.
- **Temperature**: `0.0` - `0.1`
- **Goal**: Determinism, exactness.
- **Constraint**: No creativity. Strict adherence to specs.

### 2. Standard Logic (0.1 - 0.2)
**Use for**: Business logic, API endpoints, Data processing.
- **Temperature**: `0.1` - `0.2`
- **Goal**: Reliability with minor flexibility for phrasing.
- **Constraint**: Low variance.

### 3. Frontend/UI (0.2)
**Use for**: CSS, HTML structure, Component layout.
- **Temperature**: `0.2` (Strict Limit)
- **Goal**: Aesthetic coherence, responsive design patterns.
- **Constraint**: Must match design system tokens.

### 4. Ideation/Docs (0.5 - 0.7)
**Use for**: Brainstorming, Explanations, Documentation.
- **Temperature**: `0.5` - `0.7`
- **Goal**: Clarity, engagement, creative solutions.
- **Constraint**: Must remain factual.

---

## Global Rules

- **Production Safety**: Any code that can break production must not exceed temperature `0.1`.
- **UI Limit**: UI-only code must not exceed temperature `0.2`.
- **Creativity Limit**: Creative tasks are the *only* time temperature may exceed `0.3`.
- **Production Limit**: Never generate production code with temperature > `0.2`.

---

## Safety Constraints

### 1. No Dangerous Code
- **Rule**: Never generate code that disables security controls.
- **Block**: `csrf_exempt`, `allow_all_origins`, `eval()`, `exec()`.

### 2. No Hardcoded Secrets
- **Rule**: Never generate API keys, passwords, or tokens in code.
- **Action**: Use environment variables (`process.env`, `$_ENV`).

### 3. No Hallucinated APIs
- **Rule**: Only use APIs that exist in the codebase or standard libraries.
- **Action**: Verify method signatures before use.

---

## Enforcement

- **System Prompt**: Temperature settings injected via `.cursorrules`.
- **Post-Gen Scan**: `security-precheck.js` scans for violations.
- **Review**: Human must verify critical logic.

---

**Related Rules**:
- `@sec:baseline` - Security rules
- `@ai:hallucination` - Prevention strategies

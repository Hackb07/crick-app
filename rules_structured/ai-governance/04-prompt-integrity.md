---
category: ai-governance
priority: P1
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:prompt"
source: "Consolidated from RULE 21 (AIPI-S)"
---

# Prompt Integrity

**Garbage In, Garbage Out. Version Your Prompts.**

---

## Core Principle

WHEN interacting with AI THEN use structured, versioned prompts.

IF prompt is ambiguous THEN clarify before generating.

---

## Prompt Protocol

### 1. Structure
- **Role**: "You are a Senior Engineer..."
- **Context**: "Project is PHP 8.2, DDD..."
- **Task**: "Create a User entity..."
- **Constraints**: "No setters, use private properties..."
- **Output**: "Return only the PHP code..."

### 2. Versioning
- **Rule**: Store system prompts in git (`.cursorrules`, `.prompts/`).
- **Rule**: Track changes to prompts like code.

### 3. Context Management
- **Rule**: Don't dump entire codebase into context.
- **Action**: Select relevant files only (`@file`).

---

## Enforcement

- **System**: `.cursorrules` enforces the base prompt.
- **Review**: Check if AI output matches prompt constraints.

---

**Related Rules**:
- `@core:ai` - AI behavior
- `@ai:safety` - Safety rails

---
category: core
priority: P1
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@core:ai"
---

# AI Behavior Guidelines

**File-by-File Changes + Preserve Existing Code**

---

## Core Principle

WHEN AI generates code THEN make surgical, file-by-file changes.

IF existing code works THEN preserve it unless explicitly changing.

---

## Rules

### 1. File-by-File Changes

WHEN making edits THEN:
- Change ONE file at a time (unless files are tightly coupled)
- Show ONLY the changed sections
- DO NOT regenerate entire files unnecessarily
- Use `replace_file_content` for single contiguous edits
- Use `multi_replace_file_content` for multiple non-contiguous edits

### 2. No Apologies or Summaries

WHEN responding THEN:
- DO NOT apologize for mistakes
- DO NOT write "Here's the updated code" or similar preamble
- Just make the change and explain WHY (if non-obvious)
- Focus on technical rationale, not politeness

### 3. Preserve Existing Code

WHEN editing THEN:
- IF code works AND not explicitly changing THEN keep it
- DO NOT refactor unrelated code
- DO NOT "improve" code not in scope
- DO NOT change formatting unless requested
- Respect existing patterns and conventions

### 4. Single Chunk Edits

WHEN using edit tools THEN:
- Make edits as atomic as possible
- Group related changes in single chunk
- DO NOT split logically connected changes
- DO NOT make parallel edits to same file

### 5. Exact Matching

WHEN replacing content THEN:
- Match EXACT whitespace (spaces, tabs, newlines)
- Match EXACT character sequence
- Include leading/trailing whitespace in TargetContent
- Verify line numbers match viewed content

### 6. Minimal Diffs

WHEN making changes THEN:
- Change ONLY what's necessary
- Keep diffs small and focused
- Easier to review = faster approval
- Smaller changes = fewer bugs

---

## Workflow

BEFORE making changes:
1. View the file to understand current state
2. Identify EXACT section to change
3. Verify line numbers and content
4. Make surgical edit
5. Explain rationale (if non-obvious)

AFTER making changes:
- DO NOT ask "Does this look good?"
- DO NOT ask "Should I continue?"
- Just complete the task and state what was done

---

## Anti-Patterns

- ❌ Regenerating entire files for small changes
- ❌ Apologizing repeatedly
- ❌ Asking permission for obvious next steps
- ❌ Refactoring unrelated code
- ❌ Changing formatting unnecessarily
- ❌ Making parallel edits to same file
- ❌ Whitespace mismatches in TargetContent

---

## Examples

### ✅ Good

```
WHEN fixing bug in login function THEN:
- View login.php
- Replace ONLY the buggy section
- Explain fix: "Changed password comparison to use hash_equals() to prevent timing attacks"
```

### ❌ Bad

```
WHEN fixing bug in login function THEN:
- Regenerate entire login.php
- Refactor unrelated functions
- Change indentation style
- Add comments everywhere
- Say "I apologize for the confusion. Here's the updated code..."
```

---

## Temperature Policy Integration

WHEN generating code THEN use appropriate temperature:

| Context | Temperature | Rationale |
|---------|-------------|-----------|
| Auth/payments | 0.05 | Deterministic, security-critical |
| Backend logic | 0.1 | Consistent, predictable |
| Frontend UI | 0.2 | Some creativity acceptable |
| Brainstorming | 0.5-0.7 | Exploratory, creative |

See `@ai:safety` for full temperature policy.

---

## Enforcement

- Always apply
- No exceptions
- AI must follow these guidelines for ALL code generation

---

**Related Rules**:
- `@ai:safety` - Temperature policy
- `@ai:hallucination` - AAIA requirements
- `@core:workflow` - Structured thinking process

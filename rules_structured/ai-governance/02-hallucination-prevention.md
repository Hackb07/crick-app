---
category: ai-governance
priority: P1
type: ruleset
applies_to: [ai]
always_apply: true
shorthand: "@ai:hallucination"
source: "Consolidated from RULE 21 (AIPI-S) & RULE 22 (AIMB-S)"
---

# Hallucination Prevention

**Verify Before Generating. Cite Sources. No Guessing.**

---

## Core Principle

WHEN generating code THEN verify existence of dependencies, APIs, and files.

IF unsure THEN ask user or read file. DO NOT GUESS.

---

## Prevention Strategies

### 1. Context Loading
**Rule**: Never assume file contents.
- **Action**: Read the file (`read_file`) before editing it.
- **Action**: Read the definition (`go_to_definition`) before calling a function.

### 2. Dependency Verification
**Rule**: Do not import libraries that are not in `package.json` or `composer.json`.
- **Check**: Is it installed?
- **Check**: Is the version compatible?

### 3. API Signature Check
**Rule**: Do not invent method parameters.
- **Action**: Check the function signature.
- **Anti-Pattern**: Calling `user.update(id, data)` when it is `user.update(data, id)`.

### 4. File Path Reality
**Rule**: Do not invent file paths.
- **Action**: Use `list_dir` or `find_file` to confirm paths.
- **Anti-Pattern**: `import ... from './utils/helper'` when `helper.js` doesn't exist.

---

## The "Critic" Pattern

**Before Outputting Code**:
1.  **Plan**: What am I about to write?
2.  **Critique**: Does this function exist? Did I read it?
3.  **Verify**: If I didn't read it, stop and read it.
4.  **Generate**: Only then, write the code.

---

## Enforcement

- **Runtime**: AI must use tools to verify facts.
- **Review**: Reviewers check for "magic" methods that don't exist.
- **Automation**: `preflight-analyzer.js` checks for broken imports.

---

**Related Rules**:
- `@ai:safety` - Safety rails
- `@core:ai` - AI behavior

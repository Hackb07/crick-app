---
title: "Learning & Mistake Management"
version: "1.0.0"
priority: "P1"
tags: ["core", "learning", "quality"]
---

# 🧠 Learning & Mistake Management

**Purpose**: To institutionalize the "Learning Loop" across all projects. This rule defines how to track, analyze, and prevent mistakes.

---

## 1. The "Mistakes Log" Philosophy

Every project MUST have a `MISTAKES_LOG.md` in its root directory. This file is not a "Wall of Shame" but a **"Knowledge Base of Avoidable Errors"**.

### Why?
AI Agents and Developers often repeat the same context-specific mistakes (e.g., "Forget to update the service wrapper" or "Use wrong color token"). A persistent log ensures that **New Tasks start with Old Wisdom**.

---

## 2. Mistake Log Structure

Copy this template to `MISTAKES_LOG.md` at the start of every project:

```markdown
---
title: "Project Mistakes Log"
version: "1.0.0"
status: "active"
---

# 📄 Mistakes Log

**Usage**: Read this file BEFORE every task.

## [YYYY-MM-DD] - [Category] - [Title]

**Context**: [What was being done]
**Mistake**: [Specific error]
**Root Cause**: [Why it happened]
**Impact**: [Consequences]
**Prevention**: [Actionable Step]
**Status**: ✅ Fixed / ⚠️ Recurring
```

---

## 3. The Correction Workflow

When a mistake occurs:

1.  **Stop**: Do not blindly attempt to fix it multiple times.
2.  **Analyze**: Why did it happen? (e.g., "I assumed the file path existed").
3.  **Log**: Add an entry to `MISTAKES_LOG.md`.
4.  **Update Config**: If the mistake was due to a missing rule, **Update the Rule System**.
    *   *Example*: "I used the wrong CSS class." -> *Action*: "Update `design/tokens.md` to be clearer."

---

## 4. Categories of Mistakes

1.  **Architecture Violation**: Breaking the project structure (e.g., Monolithic files).
2.  **Logic Error**: Code bugs (e.g., PHP Fatal Errors, JS ReferenceErrors).
3.  **Design Violation**: Brand inconsistency, non-responsive layouts.
4.  **Process Violation**: Skipping tests, ignoring the Preflight Checklist.

---

## 5. Review Cadence

*   **Before Task**: Read the last 5 entries of `MISTAKES_LOG.md`.
*   **Weekly**: Review recurring mistakes and create automation (scripts/tests) to prevent them.

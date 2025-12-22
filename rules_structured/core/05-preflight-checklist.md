---
title: "Universal Preflight Checklist"
version: "1.0.0"
priority: "P1"
tags: ["workflow", "quality", "safety"]
---

# 🛑 Universal Preflight Checklist

**Purpose**: This checklist functions as a mandatory "Gatekeeper" before any code generation or implementation begins. Following this ensures compliance with architecture, design, and code quality standards across ANY project.

**Usage**: Review this checklist **BEFORE** writing a single line of code.

---

## 1. 🏗️ Architecture & Structure Check
*   **MVC Check**: Are you putting business logic in a View/Template file?
    *   **Rule**: Logic belongs in `Services` or `Controllers`. Views are for display only.
*   **File Size Limit**: Will this file likely exceed 300 lines?
    *   **Rule**: If yes, plan for modularization (CSS to external file, JS to external file) *before* you start.
*   **Path Verification**: Are you using helper functions (e.g., `assetUrl()`) correctly?
    *   **Rule**: Verify the target path exists. Don't guess.

## 2. 🎨 Design & Identity Check
*   **Context Check**: What is the visual context? (e.g., Admin vs Public, Dark Mode vs Light Mode)
    *   **Rule**: Use the correct Design Tokens. Do NOT mix themes (e.g., don't use Public Green in Blue Admin).
*   **Responsive Check**: Will this layout break on mobile (375px)?
    *   **Rule**: Mobile-First design is mandatory.
*   **Component Reuse**: Am I creating a new button/card style?
    *   **Rule**: Check existing CSS or Component Library first. DRY (Don't Repeat Yourself).

## 3. 🛡️ Logic & Safety Check
*   **Method Verification**: Am I calling a function/method?
    *   **Rule**: Verify the method exists in the target class/service. Don't assume.
*   **Variable Scoping**: Am I assuming a variable exists?
    *   **Rule**: Check global scope or imports. Use `isset()` or null coalescing (`??`) for safety.
*   **Deprecation Check**: Am I using old/deprecated patterns?
    *   **Rule**: Use the latest standard defined in `@rules_structured`.

## 4. 📝 Rule Compliance Check
*   **Read MISTAKES_LOG.md**: Have I reviewed the project's specific "Mistakes Log"?
    *   **Rule**: "Learning from history" is required to prevent regression.
*   **Domain Rules**: Have I checked the specific rule file for this task?
    *   **Example**: If working on Forms, check `08-form-design-patterns.md`.

---

## 5. 🚀 Execution Protocol
1.  **Plan**: Outline the change.
2.  **Verify**: Check this list.
3.  **Execute**: Write the code.
4.  **Review**: Self-correct against this list.

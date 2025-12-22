# 🦅 Kavin45$ Universal Rule Engine

**Version**: 2.3.0
**Status**: Production Ready
**Portability**: 100% (Universal Drop-in)

---

## 🎯 What is this?

This folder (`rules_structured`) is a **self-contained Rule Engine**.
You can copy-paste this entire folder into **ANY project** (PHP, Node, Python, etc.) to instantly add:

1.  **Strict Engineering Standards** (Security, Architecture, Code Quality)
2.  **Automated Validation** (8+ scripts to check your code)
3.  **AI Governance** (Rules to keep AI safe and smart)
4.  **Design System** (Guidelines for premium UI/UX)

---

## 🚀 How to Reuse in New Projects

### Step 1: Copy the Engine
Copy the entire `rules_structured` folder to your new project's root:
```
my-new-project/
├── src/
├── .git/
└── rules_structured/   <-- PASTE HERE
```

### Step 2: Initialize (1 Minute)
Run these commands to verify it works:
```bash
# Enter the automation directory
cd rules_structured/automation

# Run a security check on your new project
node check-security.js ../../
```

### Step 3: Use with AI
When prompting AI, just point it to the rules:
```
@[rules_structured/UNIFIED_RULES.md]
@[rules_structured/PRE_FLIGHT.md]
Task: Build a login form...
```

---

## 📂 What's Inside?

| Folder | Purpose |
|--------|---------|
| `automation/` | **The Brain**: Scripts that validate your code automatically. |
| `core/` | **The Heart**: Fundamental principles (DRY, SOLID, KISS). |
| `sec/` | **The Shield**: Security rules (OWASP, Auth, Secrets). |
| `arch/` | **The Structure**: Architecture patterns (MVC, Boundaries). |
| `design/` | **The Look**: UI/UX, responsiveness, and accessibility. |
| `ai/` | **The Safety**: Rules for using AI responsibility. |

---

## ✨ Features

- **Portable**: No hardcoded paths. Works relative to where you place it.
- **Language Agnostic**: Automation scripts work on JS, TS, PHP, HTML, CSS, etc.
- **Zero Config**: Just run the scripts.
- **Self-Documenting**: Contains its own guides and "How-to".

---

## 🔗 Quick Links

- [Complete Guide](GUIDE.md) - **Start Here**
- [Unified Rules](UNIFIED_RULES.md) - The master rule file for AI
- [Automation](automation/README.md) - How to run the checks

---

**Managed by**: Kavin45$ Engineering
**License**: Internal / Proprietary

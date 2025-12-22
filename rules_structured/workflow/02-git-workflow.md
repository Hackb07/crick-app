---
category: workflow
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@workflow:git"
source: "Project Standards & Execution Handbook"
---

# Git Workflow Standards

**Clean History. Semantic Commits. Structured Branches.**

---

## Core Principle

WHEN committing code THEN use semantic naming.

IF branch name is random THEN reject.

---

## Branch Naming

**Rule**: Use `type/description-kebab-case`.

**Types**:
- `feat/` - New feature (e.g., `feat/login-endpoint`)
- `fix/` - Bug fix (e.g., `fix/user-role-validation`)
- `refactor/` - Code restructuring (e.g., `refactor/auth-service`)
- `docs/` - Documentation updates (e.g., `docs/api-specs`)
- `chore/` - Maintenance/Config (e.g., `chore/update-deps`)

**Anti-Patterns**:
- ❌ `dev` (too generic)
- ❌ `login` (missing type)
- ❌ `fix-bug` (redundant)

---

## Commit Messages

**Rule**: Conventional Commits format.

```text
<type>(<scope>): <subject>

<body>

<footer>
```

**Examples**:
- ✅ `feat(auth): add jwt token generation`
- ✅ `fix(api): handle null user in response`
- ✅ `docs(readme): update setup instructions`

---

## Workflow Rules

1.  **Main Branch Protection**: No direct commits to `main`/`master`.
2.  **Pull Requests**: Required for all changes.
3.  **Squash & Merge**: Preferred to keep history clean.
4.  **Delete Branch**: After merge.

---

## Enforcement

- **Pre-Commit**: `commitlint` checks message format.
- **CI**: Block PRs with invalid branch names.

---

**Related Rules**:
- `@arch:structure` - Project structure
- `@core:workflow` - Coding workflow

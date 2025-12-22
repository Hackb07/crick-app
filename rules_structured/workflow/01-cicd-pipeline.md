---
category: workflow
priority: P2
type: ruleset
applies_to: [all]
always_apply: true
shorthand: "@workflow:cicd"
source: "Consolidated from RULE 19 (PIPE-S) & RULE 20 (CICD-S)"
---

# CI/CD Pipeline

**Automate Everything. Trust the Pipeline.**

---

## Core Principle

WHEN code is pushed THEN run the pipeline.

IF pipeline fails THEN reject the change.

---

## Pipeline Stages

### 1. Build
- **Action**: Compile code, install dependencies.
- **Goal**: Ensure it builds.

### 2. Test
- **Action**: Run Unit, Integration, E2E tests.
- **Rule**: `@test:pyramid`.

### 3. Lint & Analyze
- **Action**: Run Linter, Static Analysis, Security Scan.
- **Rule**: `@quality:standards`, `@sec:baseline`.

### 4. Deploy (Staging)
- **Action**: Deploy to staging environment.
- **Goal**: Manual verification / QA.

### 5. Deploy (Production)
- **Action**: Deploy to prod (Blue/Green or Canary).
- **Gate**: Manual approval or auto-promote if metrics healthy.

---

## Enforcement

- **GitHub Actions / GitLab CI**: Configuration file (`.github/workflows/main.yml`).
- **Rule**: Branch protection requires passing checks.

---

**Related Rules**:
- `@test:coverage` - Coverage gates
- `@workflow:git` - Git workflow

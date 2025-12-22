---
title: "Kavin45$ Rules Index"
version: "2.2.0"
status: "active"
---

# 🎯 Kavin45$ - Rules Index

**Complete catalog of engineering rules**

> **NOTE**: The primary executable rule set is **[UNIFIED_RULES.md](../UNIFIED_RULES.md)**. The files listed below provide the detailed breakdown.

---

## 📋 Categories

| Category | Files | Priority | Shorthand |
|----------|-------|----------|-----------|
| **Core** | 6 | P1 | `@core` |
| **Architecture** | 9 | P1 | `@arch` |
| **Security** | 5 | P1 | `@sec` |
| **Design** | 10 | P1 | `@design` |
| **Code Quality** | 6 | P2 | `@quality` |
| **Testing** | 4 | P2 | `@test` |
| **AI Governance** | 7 | P1 | `@ai` |
| **Operations** | 4 | P2 | `@ops` |
| **Workflow** | 5 | P3 | `@flow` |

---

## 📁 Core Rules (P1 - Always Apply)

**Location**: `core/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-senior-engineer-mindset.md` | `@core:mindset` | Tier-based delivery |
| `02-ai-behavior.md` | `@core:ai` | AI interaction guidelines |
| `03-clean-code-principles.md` | `@core:clean` | DRY, KISS, SOLID |
| `04-coding-workflow.md` | `@core:workflow` | Structured thinking |
| `05-preflight-checklist.md` | `@core:preflight` | Mandatory checklist |
| `06-learning-and-mistakes.md` | `@core:learn` | Mistake tracking |

---

## 📁 Architecture Rules (P1 - Always Apply)

**Location**: `architecture/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-architectural-intent-ais.md` | `@arch:intent` | AIS requirements |
| `02-boundary-enforcement.md` | `@arch:boundary` | Module boundaries |
| `03-nfr-requirements.md` | `@arch:nfr` | Non-functional requirements |
| `04-api-contract-design.md` | `@arch:api` | API contracts |
| `05-domain-modeling.md` | `@arch:domain` | Domain-driven design |
| `06-dependency-injection.md` | `@arch:di` | DI patterns |
| `07-drift-detection.md` | `@arch:drift` | Drift monitoring |
| `08-module-lifecycle.md` | `@arch:lifecycle` | Module lifecycle |
| `09-project-structure.md` | `@arch:structure` | Folder structure |

---

## 📁 Security Rules (P1 - Always Apply)

**Location**: `security/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-security-baseline.md` | `@sec:baseline` | 10 security checklists |
| `02-data-privacy.md` | `@sec:privacy` | PII/PCI handling |
| `03-secrets-management.md` | `@sec:secrets` | Key management |
| `04-auth-authz.md` | `@sec:auth` | Authentication |
| `05-compliance-enforcement.md` | `@sec:compliance` | Compliance |

---

## 📁 Design Rules (P1 - Always Apply)

**Location**: `design/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-ui-ux-principles.md` | `@design:principles` | UI/UX Core |
| `02-responsive-and-adaptive.md` | `@design:responsive` | Mobile/Desktop |
| `03-pwa-standards.md` | `@design:pwa` | PWA Rules |
| `04-design-to-code-workflow.md` | `@design:workflow` | Handoff |
| `05-landing-page-opt.md` | `@design:landing` | Conversion |
| `06-component-library-standards.md` | `@design:components` | Component API |
| `07-animation-and-motion.md` | `@design:animation` | Motion Design |
| `08-form-design-patterns.md` | `@design:forms` | Form UX |
| `09-cross-platform-consistency.md` | `@design:platform` | Cross-Device |
| `10-design-tokens-system.md` | `@design:tokens` | Design Tokens |
| `11-ai-interface-design.md` | `@design:ai` | AI Interfaces |
| `12-data-visualization.md` | `@design:dataviz` | Charts & Data |
| `13-content-design.md` | `@design:content` | Micro-copy & Error messages |

---

## 📁 Code Quality Rules (P2)

**Location**: `code-quality/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-unified-quality-standards.md` | `@quality:standards` | Quality metrics |
| `02-naming-conventions.md` | `@quality:naming` | Naming rules |
| `03-error-handling.md` | `@quality:errors` | Error patterns |
| `04-logging-observability.md` | `@quality:logging` | Logging |
| `05-documentation-standards.md` | `@quality:docs` | Documentation |
| `06-anti-boilerplate.md` | `@quality:boilerplate` | Reduce repetition |
| `07-algorithm-complexity.md` | `@quality:algorithms` | Algorithm efficiency |

---

## 📁 Testing Rules (P2)

**Location**: `testing/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-test-pyramid.md` | `@test:pyramid` | 70/20/10 strategy |
| `02-coverage-requirements.md` | `@test:coverage` | ≥80% coverage |
| `03-test-quality.md` | `@test:quality` | Test quality |
| `04-regression-prevention.md` | `@test:regression` | Bug prevention |

---

## 📁 AI Governance Rules (P1)

**Location**: `ai-governance/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-ai-safety-rails.md` | `@ai:safety` | Temperature policy |
| `02-hallucination-prevention.md` | `@ai:hallucination` | AAIA requirements |
| `03-ai-semantic-review.md` | `@ai:review` | Semantic validation |
| `04-prompt-integrity.md` | `@ai:prompt` | Prompt versioning |
| `05-multi-agent-collaboration.md` | `@ai:multi` | Agent coordination |
| `06-ai-feedback-loop.md` | `@ai:feedback` | Learning loop |
| `07-ai-trust-calibration.md` | `@ai:trust` | Trust levels |
| `08-file-corruption-prevention.md` | `@ai:corruption` | Prevention of file corruption |

---

## 📁 Operations Rules (P2)

**Location**: `operations/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-operational-hardening.md` | `@ops:hardening` | Production readiness |
| `02-performance-budget.md` | `@ops:perf` | Performance targets |
| `03-observability-telemetry.md` | `@ops:observability` | Metrics & traces |
| `04-incident-response.md` | `@ops:incident` | Incident handling |
| `05-animation-performance.md` | `@ops:animation` | Animation guidelines |

---

## 📁 Workflow Rules (P3)

**Location**: `workflow/`

| File | Shorthand | Purpose |
|------|-----------|---------|
| `01-cicd-pipeline.md` | `@flow:cicd` | CI/CD gates |
| `02-git-workflow.md` | `@flow:git` | Branching strategy |
| `03-code-review.md` | `@flow:review` | Review checklist |
| `04-technical-debt.md` | `@flow:debt` | Debt tracking |
| `05-release-strategy.md` | `@flow:release` | Release process |

---

## 🔧 Automation

**Location**: `automation/`

Real, working scripts for rule enforcement. See `automation/README.md` for details.

**Available Scripts**:
- `check-security.js` - Scans for SQL injection, XSS, hardcoded secrets
- `check-naming.js` - Validates naming conventions (PascalCase, camelCase)
- `run-all.js` - Master script that runs all checks
- `package.json` - NPM configuration

**Usage**:
```bash
cd automation
node run-all.js "path/to/your/project"
```

**Exit Codes**:
- `0` - All checks passed
- `1` - Critical failures (blocks commit)
- `2` - Warnings (review recommended)

---

## 🚀 Usage

### Shorthand Codes

```bash
@core               # All core rules
@arch               # All architecture rules
@sec                # All security rules
@design             # All design/UIUX rules
@quality            # All quality rules
@test               # All testing rules
@ai                 # All AI governance rules
@ops                # All operations rules
@flow               # All workflow rules
```

---

## 📊 Priority Matrix

| Priority | When | Categories |
|----------|------|------------|
| **P1** | Always | Core, Architecture, Security, Design, AI |
| **P2** | Context | Quality, Testing, Operations |
| **P3** | On-demand | Workflow |

---

## ✅ Developer Checklist

### Before Coding
- [ ] **Check PRE_FLIGHT.md** (AI mandatory checklist)
- [ ] **Check MISTAKES_LOG.md** (Learn from past errors)
- [ ] Determine scope (ATOMIC/COMPONENT/SYSTEM)
- [ ] Review security baseline (`@sec:baseline`)

### While Coding
- [ ] Follow clean code principles (`@core:clean`)
- [ ] Apply naming conventions (`@quality:naming`)
- [ ] Add error handling (`@quality:errors`)
- [ ] avoid magic numbers (use CONFIG)

### After Coding
- [ ] Write tests (≥80% coverage)
- [ ] Run security precheck (`node check-security.js .`)
- [ ] Update documentation
- [ ] Log mistakes to MISTAKES_LOG.md

---

## 🎓 Key Principles (Cheat Sheet)

### Clean Code
- **DRY**: Don't Repeat Yourself
- **KISS**: Keep It Simple, Stupid
- **SOLID**: Single responsibility, Open/closed, etc.

### Security (10 Checklists)
1. CSRF protection | 2. XSS prevention | 3. SQL injection prevention
4. Session security | 5. Password hashing | 6. Input validation
7. Security headers | 8. File upload security | 9. Error handling
10. Secrets management

### Testing Strategy
- **70%** Unit tests
- **20%** Integration tests
- **10%** E2E tests

---

**Version**: 2.3.0
**Total Rules**: 67 files (across 10 categories)
**Automation**: 8 working scripts
**Status**: ✅ Active & Enforced
**Last Updated**: 2025-12-08

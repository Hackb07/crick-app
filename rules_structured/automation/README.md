# 🔧 Automation Scripts

**Purpose**: Real, working scripts that enforce rules automatically.

---

## 📋 Available Scripts

| Script | Purpose | Category | Usage |
|--------|---------|----------|-------|
| `check-security.js` | SQL injection, XSS, secrets | @sec | `node check-security.js <path>` |
| `check-architecture.js` | Boundaries, coupling, AIS | @arch | `node check-architecture.js <path>` |
| `check-ai-governance.js` | AAIA, hallucinations | @ai | `node check-ai-governance.js <path>` |
| `check-code-quality.js` | Complexity, duplication | @quality | `node check-code-quality.js <path>` |
| `check-naming.js` | PascalCase, camelCase | @quality | `node check-naming.js <path>` |
| `check-ui-design.js` | Accessibility, semantics | @design | `node check-ui-design.js <path>` |
| `check-testing.js` | Coverage, test quality | @test | `node check-testing.js <path>` |
| `run-all.js` | Runs all checks | ALL | `node run-all.js <path>` |

---

## 🚀 Quick Start

### 1. Run All Checks
```bash
node run-all.js "path/to/your/project"
```

### 2. Run Specific Category
```bash
# Security only
node check-security.js "path/to/project"

# Architecture only
node check-architecture.js "path/to/project"

# UI/UX only
node check-ui-design.js "path/to/project"
```

### 3. Run by Priority
```bash
# Critical checks only (Security, Architecture, AI)
node check-security.js .
node check-architecture.js .
node check-ai-governance.js .

# Quality checks (Code Quality, Naming, UI, Testing)
node check-code-quality.js .
node check-naming.js .
node check-ui-design.js .
node check-testing.js .
```

---

## 📋 What Each Checker Does

### Critical Checks (P1 - Always Run)

**Security (@sec)**
- SQL injection detection
- XSS vulnerabilities
- Hardcoded secrets
- Weak cryptography
- Command injection

**Architecture (@arch)**
- Circular dependencies
- Tight coupling
- Missing dependency injection
- God classes (>20 methods)
- Cross-layer violations
- Missing AIS documentation

**AI Governance (@ai)**
- Missing AAIA documentation
- Hallucinated APIs
- Missing error handling in AI code
- Deprecated patterns
- Missing input validation
- High-temperature code in critical areas

### Quality Checks (P2 - Context-Dependent)

**Code Quality (@quality)**
- Cyclomatic complexity (>10)
- Function size (>50 lines)
- Deep nesting (>3 levels)
- Code duplication
- Magic numbers

**Naming (@quality)**
- PascalCase for classes
- camelCase for functions/variables
- UPPER_SNAKE_CASE for constants
- Generic names (data, temp, obj)
- Single-letter variables

**UI/UX Design (@design)**
- Missing alt text on images
- Non-semantic HTML
- Missing ARIA labels
- Missing viewport meta tag
- Hardcoded colors (use CSS variables)
- Missing form labels
- Missing heading hierarchy

**Testing (@test)**
- Missing test files
- No assertions in tests
- Commented tests
- Focused tests (.only)
- Flaky tests (sleep/setTimeout)
- Test coverage estimation

---

## 🎯 Integration

### Git Pre-Commit Hook
```bash
#!/bin/sh
node automation/run-all.js .
```

### CI/CD (GitHub Actions)
```yaml
- name: Run Rule Checks
  run: node automation/run-all.js .
```

---

**Status**: ✅ Active  
**Version**: 1.0.0  
**Last Updated**: 2025-12-08

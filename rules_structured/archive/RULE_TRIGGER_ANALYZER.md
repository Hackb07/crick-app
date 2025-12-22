---
title: "Rule Trigger Analyzer"
version: "1.0.0"
purpose: "Shows which rules activate for any prompt"
---

# 🎯 Rule Trigger Analyzer

**Purpose**: Understand which rules the AI applies for different types of prompts.

---

## 📋 How to Use

**Give me any prompt, and I'll show you**:
1. Which rules are triggered
2. Why they're triggered
3. What the AI will check
4. Estimated lines of rules read

---

## 🔍 Example Prompts

### Example 1: "Fix the login bug"

**Triggered Rules**:
```
STEP 0: Check MISTAKES_LOG.md (Line 70)
  - Scan for past login-related errors
  - Lines Read: ~50

STEP 1: Identify Context (Line 21-32)
  - Context: WEB (login is web-based)
  - Priority: Statelessness, Security
  - Lines Read: 12

STEP 2: Determine Scope (core/01-senior-engineer-mindset.md)
  - Scope: ATOMIC (single bug fix)
  - Quality: ENTERPRISE (always)
  - Lines Read: 40

STEP 3: Core Rules (Line 48-76)
  - @core:clean - DRY/KISS/SOLID
  - @core:workflow - Structured thinking
  - Lines Read: 29

STEP 4: Security Check (Line 128-147)
  - @sec:baseline - SQL injection check
  - @sec:auth - Session validation
  - Lines Read: 20

STEP 5: Code Quality (Line 161-203)
  - @quality:naming - Variable names
  - @quality:errors - Error handling
  - Lines Read: 43

STEP 6: Testing (Line 208-240)
  - @test:regression - Write test for bug
  - Lines Read: 33

TOTAL RULES READ: ~227 lines (out of 437)
RULES SKIPPED: CI/CD, Git Workflow, Performance (not relevant)
```

---

### Example 2: "Create a user registration API"

**Triggered Rules**:
```
STEP 0: Check MISTAKES_LOG.md
  - Scan for API/auth errors
  - Lines Read: ~50

STEP 1: Identify Context
  - Context: WEB/API
  - Priority: Statelessness, Security
  - Lines Read: 12

STEP 2: Determine Scope
  - Scope: COMPONENT (new feature)
  - Quality: ENTERPRISE
  - Lines Read: 40

STEP 3: Core Rules
  - @core:clean - DRY/KISS/SOLID
  - @core:workflow - Plan before code
  - Lines Read: 29

STEP 4: Architecture (Line 80-123)
  - @arch:intent - Document API contract
  - @arch:boundary - Separate concerns
  - Lines Read: 44

STEP 5: Security (Line 128-156) ⚠️ CRITICAL
  - @sec:baseline - ALL 10 checklists
  - @sec:auth - Password hashing, MFA
  - @sec:privacy - PII handling
  - Lines Read: 29

STEP 6: Code Quality
  - @quality:naming - RESTful naming
  - @quality:errors - Validation errors
  - Lines Read: 43

STEP 7: Testing
  - @test:pyramid - 70/20/10 distribution
  - @test:coverage - ≥80% required
  - Lines Read: 33

STEP 8: API Design (architecture/04-api-contract-design.md)
  - Versioning (/api/v1/)
  - Request/Response schemas
  - Lines Read: ~100

TOTAL RULES READ: ~380 lines
RULES SKIPPED: Performance (not critical for API)
```

---

### Example 3: "Add a pause button to the game"

**Triggered Rules**:
```
STEP 0: Check MISTAKES_LOG.md
  - Scan for game-related errors
  - Lines Read: ~50

STEP 1: Identify Context
  - Context: GAME/REAL_TIME
  - Priority: Zero GC, Performance
  - Lines Read: 12

STEP 2: Determine Scope
  - Scope: COMPONENT (UI feature)
  - Quality: ENTERPRISE
  - Lines Read: 40

STEP 3: Core Rules
  - @core:clean - DRY/KISS/SOLID
  - Lines Read: 29

STEP 4: Performance (Line 282-292) ⚠️ CRITICAL
  - Frame Time: < 16ms (60fps)
  - GC Pause: < 1ms
  - Zero allocation in hot loops
  - Lines Read: 11

STEP 5: Code Quality
  - @quality:naming - camelCase methods
  - Lines Read: 43

STEP 6: Architecture
  - @arch:boundary - Separate UI from game logic
  - Lines Read: 10

TOTAL RULES READ: ~195 lines
RULES SKIPPED: Security (no user input), Testing (UI component)
```

---

### Example 4: "Refactor the entire authentication system"

**Triggered Rules**:
```
STEP 0: Check MISTAKES_LOG.md
  - Scan for auth/refactor errors
  - Lines Read: ~50

STEP 1: Identify Context
  - Context: WEB/API
  - Priority: Security, Scalability
  - Lines Read: 12

STEP 2: Determine Scope
  - Scope: SYSTEM (architecture change)
  - Quality: ENTERPRISE
  - Lines Read: 40

STEP 3: Core Rules
  - @core:clean - DRY/KISS/SOLID
  - @core:workflow - Plan architecture first
  - Lines Read: 29

STEP 4: Architecture (Line 80-123) ⚠️ CRITICAL
  - @arch:intent - Full AIS required
  - @arch:boundary - Define module boundaries
  - @arch:drift - Prevent drift
  - Lines Read: 44

STEP 5: Security (Line 128-156) ⚠️ CRITICAL
  - @sec:baseline - ALL 10 checklists
  - @sec:auth - Zero Trust, MFA
  - @sec:compliance - Audit trails
  - Lines Read: 29

STEP 6: Code Quality
  - @quality:standards - Complexity ≤ 10
  - @quality:naming - Domain vocabulary
  - Lines Read: 43

STEP 7: Testing (Line 208-240) ⚠️ CRITICAL
  - @test:pyramid - 70/20/10
  - @test:coverage - 100% for auth
  - Lines Read: 33

STEP 8: Operations (Line 280-312)
  - @ops:hardening - Production readiness
  - @ops:observability - Logging
  - Lines Read: 33

STEP 9: Workflow (Line 317-368)
  - @flow:cicd - CI/CD gates
  - @flow:review - Code review checklist
  - Lines Read: 52

STEP 10: Deep Dive Files
  - architecture/01-architectural-intent-ais.md (~200 lines)
  - security/04-auth-authz.md (~150 lines)
  - testing/01-test-pyramid.md (~100 lines)

TOTAL RULES READ: ~675 lines (UNIFIED + detailed files)
RULES SKIPPED: None (full system refactor)
```

---

## 📊 Rule Activation Matrix

| Prompt Type | Scope | Rules Read | Critical Rules |
|-------------|-------|------------|----------------|
| **Bug Fix** | ATOMIC | ~227 lines | Security, Testing |
| **New Feature** | COMPONENT | ~380 lines | Security, Architecture |
| **UI Component** | COMPONENT | ~195 lines | Performance |
| **System Refactor** | SYSTEM | ~675 lines | All |

---

## 🎯 Try It Yourself

**Give me any prompt, and I'll show you**:
1. Exact rules triggered
2. Line numbers in UNIFIED_RULES.md
3. Additional files opened (if any)
4. Total lines read
5. Why each rule was triggered

**Example prompts to try**:
- "Add email validation"
- "Create a leaderboard API"
- "Optimize the rendering loop"
- "Add user profile page"
- "Fix memory leak in game"

---

**Created**: 2025-12-04  
**Usage**: Interactive - paste any prompt below

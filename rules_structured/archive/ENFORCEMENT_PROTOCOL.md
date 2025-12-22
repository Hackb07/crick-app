---
title: "MANDATORY ENFORCEMENT PROTOCOL"
version: "1.0.0"
status: "CRITICAL"
---

# 🚨 MANDATORY ENFORCEMENT PROTOCOL

**PROBLEM**: AI reads UNIFIED_RULES.md but doesn't enforce them automatically.

**SOLUTION**: This protocol FORCES enforcement.

---

## ⚠️ FOR AI ASSISTANTS - READ THIS FIRST

When user references `@[UNIFIED_RULES.md]` or any rule file:

### STEP 1: ACKNOWLEDGE ENFORCEMENT (MANDATORY)
```
I will enforce these rules:
- @core (clean code, workflow)
- @sec (security baseline)
- @arch (boundaries, AIS)
- @quality (naming, errors)
- @test (coverage ≥80%)

I will validate my code with automation before submitting.
```

### STEP 2: COMPLETE PRE_FLIGHT.md (MANDATORY)
Before coding, complete the checklist in PRE_FLIGHT.md:
- [ ] Check MISTAKES_LOG.md
- [ ] Identify applicable rules
- [ ] Plan approach
- [ ] Commit to quality

### STEP 3: CODE WITH RULES (MANDATORY)
While coding:
- ✅ Follow @core:clean (DRY, KISS, SOLID)
- ✅ Apply @sec:baseline (SQL injection, XSS, secrets)
- ✅ Apply @quality:naming (PascalCase, camelCase)
- ✅ Add @quality:errors (proper error handling)
- ✅ Document with @arch:intent (AIS if needed)

### STEP 4: SELF-VALIDATE (MANDATORY)
After coding, tell user:
```
✅ Code complete. Please run validation:

cd automation
node run-all.js .

This will check:
- Security violations
- Architecture issues
- Code quality
- Naming conventions
- UI/UX standards
- Testing coverage
```

### STEP 5: FIX VIOLATIONS (MANDATORY)
If automation finds issues:
- User will share the output
- AI MUST fix ALL critical issues (exit code 1)
- AI SHOULD fix warnings (exit code 2)
- Re-validate until clean

---

## 📋 FOR USERS - ENFORCEMENT WORKFLOW

### When Asking AI to Code

**BAD** (No enforcement):
```
@[UNIFIED_RULES.md]

Create a login form
```

**GOOD** (Enforced):
```
@[UNIFIED_RULES.md]
@[PRE_FLIGHT.md]

ENFORCE all rules. Complete PRE_FLIGHT first.

Create a login form

After coding, I will run automation to validate.
```

### After AI Codes

**ALWAYS run automation**:
```bash
cd automation
node run-all.js .
```

**If issues found**:
```
AI found these violations:
[paste automation output]

Fix ALL critical issues.
```

---

## 🔒 ENFORCEMENT LEVELS

### Level 1: Critical (MUST FIX)
- ❌ Security violations (SQL injection, XSS, secrets)
- ❌ Architecture violations (tight coupling, circular deps)
- ❌ AI governance violations (missing AAIA, no validation)

**Exit code**: 1 (blocks commit)

### Level 2: High (SHOULD FIX)
- ⚠️ Code quality issues (complexity >10, function >50 lines)
- ⚠️ Naming violations (bad names, wrong case)
- ⚠️ UI/UX issues (missing alt text, no ARIA)

**Exit code**: 2 (warning)

### Level 3: Low (REVIEW)
- ℹ️ Magic numbers
- ℹ️ Hardcoded colors
- ℹ️ Non-responsive units

**Exit code**: 2 (warning)

---

## 🎯 ENFORCEMENT CHECKLIST

### Before Every AI Request

- [ ] Reference `@[UNIFIED_RULES.md]`
- [ ] Reference `@[PRE_FLIGHT.md]`
- [ ] Add: "ENFORCE all rules"
- [ ] Add: "I will validate with automation"

### After AI Codes

- [ ] Run `node run-all.js .`
- [ ] Review output
- [ ] If violations found, ask AI to fix
- [ ] Re-run until clean
- [ ] Commit only when automation passes

---

## 🚨 COMMON VIOLATIONS & FIXES

### Violation 1: SQL Injection
**Automation finds**:
```
❌ CRITICAL: Potential SQL injection
   file.php:42
   $query = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

**Fix**:
```php
// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_GET['id']]);
```

### Violation 2: Missing Error Handling
**Automation finds**:
```
⚠️ HIGH: Missing error handling
   service.js:15
   function processData() { ... }
```

**Fix**:
```javascript
function processData() {
  try {
    // ... code
  } catch (error) {
    logger.error('processData failed', error);
    throw new ProcessingError('Failed to process data', error);
  }
}
```

### Violation 3: Bad Naming
**Automation finds**:
```
⚠️ MEDIUM: Generic variable name
   controller.php:23
   $data = fetchUsers();
```

**Fix**:
```php
$users = fetchUsers();
```

---

## 💡 WHY THIS MATTERS

### Without Enforcement
1. AI reads rules ✅
2. AI codes ❌ (may violate rules)
3. You don't know violations exist ❌
4. Code has security/quality issues ❌

### With Enforcement
1. AI reads rules ✅
2. AI commits to enforcement ✅
3. AI codes following rules ✅
4. Automation validates ✅
5. AI fixes violations ✅
6. Clean, secure code ✅

---

## 🔧 AUTOMATION COMMANDS

### Quick Validation
```bash
cd automation
node run-all.js .
```

### Specific Checks
```bash
# Security only (fastest for critical issues)
node check-security.js .

# Architecture only
node check-architecture.js .

# All quality checks
node check-code-quality.js .
node check-naming.js .
node check-ui-design.js .
node check-testing.js .
```

---

## 📊 ENFORCEMENT METRICS

Track your enforcement success:

**Week 1**:
- Tasks: 10
- Violations found: 45
- Violations fixed: 45
- Clean commits: 10/10

**Goal**: 100% clean commits

---

## 🎯 TEMPLATE FOR AI REQUESTS

Copy-paste this template:

```
@[UNIFIED_RULES.md]
@[PRE_FLIGHT.md]

MANDATORY ENFORCEMENT:
1. Complete PRE_FLIGHT checklist
2. Follow ALL applicable rules
3. Self-validate before submitting
4. I will run automation to verify

TASK:
[Your actual request]

VALIDATION:
After you code, I will run:
cd automation && node run-all.js .
```

---

## 🚨 CRITICAL REMINDER

**UNIFIED_RULES.md is NOT self-enforcing!**

You MUST:
1. ✅ Tell AI to enforce (not just read)
2. ✅ Run automation after AI codes
3. ✅ Fix violations before committing
4. ✅ Use PRE_FLIGHT.md checklist

**Otherwise, rules are just documentation (junk).**

---

## 📞 QUICK REFERENCE

### Every AI Request
```
@[UNIFIED_RULES.md] @[PRE_FLIGHT.md]
ENFORCE all rules.
[Your request]
```

### After AI Codes
```bash
cd automation
node run-all.js .
```

### If Violations Found
```
Fix these violations:
[paste output]
```

---

**Status**: ✅ MANDATORY PROTOCOL  
**Enforcement**: REQUIRED  
**Validation**: AUTOMATED  
**Success Rate**: 100% (when followed)

# ⚡ QUICK ENFORCEMENT GUIDE

**Copy-paste these templates for instant enforcement**

---

## 📋 TEMPLATE 1: For AI Requests

```
@[UNIFIED_RULES.md]
@[PRE_FLIGHT.md]

MANDATORY ENFORCEMENT:
- Complete PRE_FLIGHT checklist
- Follow ALL applicable rules (@core, @sec, @arch, @quality)
- I will validate with automation after you code

TASK:
[Your actual request here]

VALIDATION:
After coding, I will run: cd automation && node run-all.js .
```

---

## 📋 TEMPLATE 2: After AI Codes

```bash
# Run this EVERY time AI codes
cd automation
node run-all.js .
```

---

## 📋 TEMPLATE 3: If Violations Found

```
Automation found violations. Fix ALL critical issues:

[Paste automation output here]
```

---

## 🎯 3-STEP WORKFLOW

### Step 1: Request (Use Template 1)
```
@[UNIFIED_RULES.md] @[PRE_FLIGHT.md]
ENFORCE all rules.
Create a login form.
```

### Step 2: Validate (Use Template 2)
```bash
cd automation
node run-all.js .
```

### Step 3: Fix (Use Template 3 if needed)
```
Fix these violations:
❌ CRITICAL: SQL injection at login.php:42
```

---

## 🚨 CRITICAL REMINDERS

1. **UNIFIED_RULES.md is NOT self-enforcing**
2. **Always tell AI to ENFORCE (not just read)**
3. **Always run automation after AI codes**
4. **Always fix violations before committing**

---

## 📊 WHAT AUTOMATION CHECKS

✅ Security (SQL injection, XSS, secrets)  
✅ Architecture (coupling, boundaries, AIS)  
✅ AI Governance (AAIA, hallucinations)  
✅ Code Quality (complexity, duplication)  
✅ Naming (PascalCase, camelCase)  
✅ UI/UX (accessibility, semantics)  
✅ Testing (coverage, quality)

---

## 💡 WHY THIS MATTERS

**Without Enforcement**:
- AI reads rules ✅
- AI may violate rules ❌
- You don't know ❌
- Security issues ❌

**With Enforcement**:
- AI commits to rules ✅
- Automation validates ✅
- Violations caught ✅
- Clean, secure code ✅

---

**Print this and keep it visible!**

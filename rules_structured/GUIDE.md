# 📖 COMPLETE GUIDE - Everything You Need

**One file with everything. Read sections as needed.**

---

## 🚀 QUICK START (5 Minutes)

### Step 1: Test Automation
```bash
cd automation
node check-security.js .
```

### Step 2: Use This Template With AI
```
@[UNIFIED_RULES.md]
@[PRE_FLIGHT.md]

ENFORCE all rules. I will validate with automation.

TASK: [Your request]
```

### Step 3: After AI Codes
```bash
cd automation
node run-all.js .
```

**That's it!** Use this workflow every time.

---

## 📁 FILE STRUCTURE (Simplified)

```
rules_structured/
├── README.md              ← Overview
├── GUIDE.md              ← This file (everything you need)
├── UNIFIED_RULES.md      ← The rules (read with AI)
├── INDEX.md              ← Rules catalog (reference)
├── QUICK_REF.md          ← Cheat sheet
├── PRE_FLIGHT.md         ← AI checklist
│
├── automation/           ← 7 working scripts
│   ├── check-security.js
│   ├── check-architecture.js
│   ├── check-ai-governance.js
│   ├── check-code-quality.js
│   ├── check-naming.js
│   ├── check-ui-design.js
│   ├── check-testing.js
│   └── run-all.js
│
└── [rule folders]/       ← 67 rule files (don't read directly)
```

**Only 6 files in root!** Everything else is in folders.

---

## 🎯 WHAT EACH FILE DOES

| File | Purpose | When to Use |
|------|---------|-------------|
| **README.md** | Overview | First time |
| **GUIDE.md** | This file - complete guide | Daily reference |
| **UNIFIED_RULES.md** | The actual rules | With AI (use @[...]) |
| **INDEX.md** | Rules catalog | Finding specific rules |
| **QUICK_REF.md** | One-page cheat sheet | Quick lookup |
| **PRE_FLIGHT.md** | AI checklist | With AI (use @[...]) |

---

## 📋 DAILY WORKFLOW

### Every Time You Code With AI

**1. Copy-Paste This Template**:
```
@[UNIFIED_RULES.md]
@[PRE_FLIGHT.md]

MANDATORY ENFORCEMENT:
- Complete PRE_FLIGHT checklist
- Follow ALL applicable rules (@core, @sec, @arch, @quality)
- I will validate with automation

TASK:
[Your actual request here]

VALIDATION:
After coding, I will run: cd automation && node run-all.js .
```

**2. After AI Codes, Run Automation**:
```bash
cd automation
node run-all.js .
```

**3. If Violations Found**:
```
Fix these violations:
[Paste automation output]
```

**4. Log Mistakes**:
Add to `../MISTAKES_LOG.md` (project root)

---

## 🔧 AUTOMATION COMMANDS

### Run All Checks
```bash
cd automation
node run-all.js .
```

### Run Specific Check
```bash
node check-security.js .       # Security only (fastest)
node check-architecture.js .   # Architecture only
node check-ai-governance.js .  # AI governance only
node check-code-quality.js .   # Code quality only
node check-naming.js .         # Naming only
node check-ui-design.js .      # UI/UX only
node check-testing.js .        # Testing only
```

### Check Specific File
```bash
node check-security.js path/to/file.php
node check-naming.js path/to/file.js
```

---

## 📊 WHAT AUTOMATION CHECKS

### Critical (P1 - Must Fix)
✅ **Security** (`check-security.js`)
- SQL injection
- XSS vulnerabilities
- Hardcoded secrets
- Weak cryptography
- Command injection

✅ **Architecture** (`check-architecture.js`)
- Circular dependencies
- Tight coupling
- Missing dependency injection
- God classes (>20 methods)
- Cross-layer violations
- Missing AIS documentation

✅ **AI Governance** (`check-ai-governance.js`)
- Missing AAIA documentation
- Hallucinated APIs
- Missing error handling
- Deprecated patterns
- Missing input validation

### Quality (P2 - Should Fix)
✅ **Code Quality** (`check-code-quality.js`)
- Complexity >10
- Function size >50 lines
- Deep nesting >3
- Code duplication
- Magic numbers

✅ **Naming** (`check-naming.js`)
- PascalCase for classes
- camelCase for functions
- UPPER_SNAKE_CASE for constants
- Generic names (data, temp, obj)

✅ **UI/UX** (`check-ui-design.js`)
- Missing alt text
- Non-semantic HTML
- Missing ARIA labels
- Missing viewport
- Hardcoded colors
- Missing form labels

✅ **Testing** (`check-testing.js`)
- Missing test files
- No assertions
- Commented tests
- Focused tests (.only)
- Flaky tests

---

## 🚨 CRITICAL RULES

### UNIFIED_RULES.md is NOT Self-Enforcing!

**Wrong Way** ❌:
```
@[UNIFIED_RULES.md]
Create a login form
```
**Problem**: AI reads but doesn't enforce

**Right Way** ✅:
```
@[UNIFIED_RULES.md] @[PRE_FLIGHT.md]
ENFORCE all rules. I will validate with automation.
Create a login form
```
**Result**: AI enforces + you validate

---

## 📅 WEEKLY/MONTHLY TASKS

### Weekly (15 minutes)
1. Review `MISTAKES_LOG.md`
2. Run full validation: `node run-all.js .`
3. Fix accumulated issues
4. Look for patterns

### Monthly (30 minutes)
1. Full project scan
2. Generate reports
3. Review trends
4. Plan refactoring

### Quarterly (2 hours)
1. Manual Operations review (performance, observability)
2. Manual Workflow review (CI/CD, git strategy)
3. Update rules if needed
4. Team sync

---

## 💡 COMMON ISSUES & SOLUTIONS

### "Too many issues found"
**Solution**: Fix critical first (exit code 1), then high (exit code 2), warnings can wait

### "AI still violates rules"
**Solution**: Always use enforcement template, always run automation, paste violations to AI

### "Don't know which rules apply"
**Solution**: Use PRE_FLIGHT.md checklist, start with @core, @sec, @arch (always apply)

### "Too many files to read"
**Solution**: Read UNIFIED_RULES.md only (12KB), don't read all 67 files, use automation (0 tokens)

---

## 📊 TOKEN EFFICIENCY

### The Problem
- 67 rule files × 200 lines = ~50,000 tokens

### The Solution
- **UNIFIED_RULES.md**: ~3,000 tokens (compressed)
- **Automation**: 0 tokens (runs locally)
- **Average task**: ~3,100 tokens total

**97% reduction!**

---

## 🎯 RULE CATEGORIES

### What Each Folder Contains

| Folder | Files | Enforced By | Coverage |
|--------|-------|-------------|----------|
| `ai-governance/` | 7 | check-ai-governance.js | 100% |
| `architecture/` | 9 | check-architecture.js | 100% |
| `security/` | 5 | check-security.js | 100% |
| `code-quality/` | 6 | check-code-quality.js + check-naming.js | 100% |
| `design/` | 16 | check-ui-design.js | 100% |
| `testing/` | 4 | check-testing.js | 100% |
| `core/` | 6 | PRE_FLIGHT.md + All checkers | 100% |
| `operations/` | 4 | Manual review | 0% |
| `workflow/` | 5 | Manual review | 0% |

**Total**: 78% automated (all critical categories)

---

## ✅ QUICK CHECKLIST

### Today
- [ ] Test automation: `node check-security.js .`
- [ ] Use enforcement template with AI
- [ ] Run automation after AI codes
- [ ] Fix 1-2 critical issues

### This Week
- [ ] Use template daily
- [ ] Run automation before commits
- [ ] Log mistakes in MISTAKES_LOG.md
- [ ] Weekly review

### Ongoing
- [ ] Always enforce rules with AI
- [ ] Always validate with automation
- [ ] Always fix critical issues
- [ ] Weekly/monthly/quarterly reviews

---

## 📞 NEED HELP?

### Quick Answers
- **"How to enforce?"** → Use template above (section: Daily Workflow)
- **"What to check?"** → Run `node run-all.js .`
- **"How to fix?"** → Paste automation output to AI
- **"Token usage?"** → Use UNIFIED_RULES.md only (~3K tokens)

### Files to Bookmark
1. **GUIDE.md** (this file) - Everything you need
2. **UNIFIED_RULES.md** - Use with AI
3. **PRE_FLIGHT.md** - Use with AI
4. **MISTAKES_LOG.md** (project root) - Track errors

---

## 🎉 YOU'RE READY!

### Your Next Action (5 minutes)
```bash
cd automation
node check-security.js .
```

Then use the template above with AI!

---

**Status**: ✅ SIMPLIFIED
**Root Files**: 6 (down from 17)
**Coverage**: 78% automated
**Token Cost**: ~3K per task

**Everything you need is in this one file!** 🚀

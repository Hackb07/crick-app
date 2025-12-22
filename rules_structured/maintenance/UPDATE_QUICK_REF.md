---
title: "Rules Update System - Quick Reference"
version: "1.0.0"
---

# 🔄 How to Keep Rules Updated

## 📊 Update System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    UPDATE TRIGGER SOURCES                    │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1. MISTAKES_LOG.md  →  Pattern Detection (3+ same errors)  │
│  2. Project Learning →  Post-project review                  │
│  3. Security Advisory→  CVE/OWASP updates                    │
│  4. Team Feedback    →  Clarification requests               │
│  5. New Technology   →  Framework/tool adoption              │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                      UPDATE PROCESS                          │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Step 1: Identify need (from triggers above)                │
│  Step 2: Update specific rule file                          │
│  Step 3: Update UNIFIED_RULES.md (if needed)                │
│  Step 4: Log in RULES_CHANGELOG.md                          │
│  Step 5: Bump version number                                │
│  Step 6: Test with AI assistant                             │
│  Step 7: Sync to active projects                            │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    VERSION CONTROL                           │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  MAJOR (X.0.0) → Breaking changes, restructure              │
│  MINOR (0.X.0) → New rules, categories                      │
│  PATCH (0.0.X) → Fixes, clarifications                      │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## ⏰ Update Schedule

| Frequency | Activity | Time Required | Action |
|-----------|----------|---------------|--------|
| **Weekly** | Review MISTAKES_LOG.md | 5 min | Check for patterns |
| **As Needed** | Update rules | 10-30 min | When triggers occur |
| **Monthly** | Sync to projects | 30 min | Update all active projects |
| **Quarterly** | Full audit | 2 hours | Comprehensive review |

---

## 🎯 Quick Update Examples

### Example 1: Recurring Mistake
```
TRIGGER: Same error 3+ times in MISTAKES_LOG.md
ACTION:  Update rule → Log change → Bump PATCH
TIME:    10 minutes
```

### Example 2: New Technology
```
TRIGGER: Adopted React for new project
ACTION:  Create new rule file → Update index → Bump MINOR
TIME:    30 minutes
```

### Example 3: Security Advisory
```
TRIGGER: New CVE discovered
ACTION:  Update security rule → Log [SECURITY] → Notify projects
TIME:    15 minutes (URGENT)
```

### Example 4: Clarification Request
```
TRIGGER: Team says "Rule X is unclear"
ACTION:  Add examples → Update docs → Bump PATCH
TIME:    10 minutes
```

---

## 📝 Files to Update

| File | Always | If Needed | Never |
|------|--------|-----------|-------|
| **Specific rule file** | ✅ | | |
| **RULES_CHANGELOG.md** | ✅ | | |
| **Version numbers** | ✅ | | |
| **UNIFIED_RULES.md** | | ✅ | |
| **INDEX.md** | | ✅ | |
| **README.md** | | ✅ | |

---

## 🚨 Priority Levels

### 🔴 URGENT (Update Immediately)
- Security vulnerabilities
- Critical bugs
- Production issues

### 🟡 HIGH (Update This Week)
- Recurring mistakes (3+ times)
- Team blockers
- New technology adoption

### 🟢 NORMAL (Update Monthly)
- Clarifications
- Minor improvements
- Documentation updates

### ⚪ LOW (Update Quarterly)
- Typo fixes
- Formatting
- Nice-to-have additions

---

## 📋 Update Checklist

```markdown
Before updating:
- [ ] Read MISTAKES_LOG.md
- [ ] Identify trigger
- [ ] Determine priority

During update:
- [ ] Update specific rule file
- [ ] Update UNIFIED_RULES.md (if needed)
- [ ] Update INDEX.md (if structure changed)
- [ ] Log in RULES_CHANGELOG.md
- [ ] Bump version number
- [ ] Test with AI assistant

After update:
- [ ] Commit changes
- [ ] Sync to active projects (if needed)
- [ ] Notify team (if breaking change)
```

---

## 🔗 Related Files

- **UPDATE_GUIDE.md** - Detailed step-by-step guide
- **RULES_CHANGELOG.md** - Complete version history
- **MISTAKES_LOG.md** - Source of improvement triggers
- **UNIFIED_RULES.md** - Main executable ruleset

---

## 💡 Pro Tips

1. ✅ **Small, frequent updates** > Large, infrequent ones
2. ✅ **Always explain WHY** in changelog
3. ✅ **Test with AI** before committing
4. ✅ **Keep examples current** with latest syntax
5. ✅ **Archive old versions** yearly

---

**Last Updated**: 2025-12-04 23:49 IST  
**Version**: 1.0.0

---
title: "Rules Update Guide"
version: "1.0.0"
purpose: "Step-by-step guide for updating rules"
---

# 🔄 Rules Update Guide

**Quick guide for keeping your rules system current and effective.**

---

## 🚀 Quick Update Process

### **5-Minute Update** (Weekly)
```bash
1. Read MISTAKES_LOG.md
2. Check for patterns (3+ same errors)
3. If found → Update relevant rule
4. Log in RULES_CHANGELOG.md
5. Bump version
```

### **30-Minute Review** (Monthly)
```bash
1. Review all active projects
2. Collect team feedback
3. Check for new security advisories
4. Update rules as needed
5. Sync to all projects
```

### **2-Hour Audit** (Quarterly)
```bash
1. Comprehensive review of all 43 rule files
2. Remove obsolete rules
3. Add new best practices
4. Update examples
5. Major version bump if needed
```

---

## 📝 Update Scenarios

### **Scenario 1: Recurring Mistake**
**Trigger**: Same error in MISTAKES_LOG.md 3+ times

**Example**:
```markdown
# In MISTAKES_LOG.md you see:
- 2025-11-20: Forgot CSRF token
- 2025-11-25: Forgot CSRF token  
- 2025-12-01: Forgot CSRF token
```

**Action**:
```bash
1. Open: rules_structured/security/01-security-baseline.md
2. Add/strengthen: CSRF rule with checklist
3. Update: UNIFIED_RULES.md (if needed)
4. Log in: RULES_CHANGELOG.md
   
   ## [2.2.1] - 2025-12-04
   ### Fixed
   - Strengthened CSRF token requirement with mandatory checklist
   **Trigger**: Recurring mistake (3 occurrences)
   
5. Bump version: 2.2.0 → 2.2.1
```

---

### **Scenario 2: New Technology**
**Trigger**: Adopting new framework (e.g., React, Vue, Next.js)

**Action**:
```bash
1. Create: rules_structured/architecture/10-react-patterns.md
2. Add to: INDEX.md
3. Update: UNIFIED_RULES.md (add React-specific rules)
4. Log in: RULES_CHANGELOG.md
   
   ## [2.3.0] - 2025-12-04
   ### Added
   - New file: architecture/10-react-patterns.md
   - React component best practices
   - Hooks usage guidelines
   **Trigger**: Adopted React for new projects
   
5. Bump version: 2.2.1 → 2.3.0 (MINOR - new rules)
```

---

### **Scenario 3: Security Advisory**
**Trigger**: New CVE or OWASP update

**Example**: New XSS attack vector discovered

**Action**:
```bash
1. Open: rules_structured/security/01-security-baseline.md
2. Update: XSS prevention section
3. Update: UNIFIED_RULES.md
4. Log in: RULES_CHANGELOG.md
   
   ## [2.2.2] - 2025-12-04
   ### Security
   - Updated XSS prevention for new DOM-based attack vector
   - Added Content-Security-Policy strict-dynamic directive
   **Trigger**: CVE-2025-XXXX
   **Impact**: HIGH - All web projects must update
   
5. Bump version: 2.2.1 → 2.2.2
6. NOTIFY: All active projects immediately
```

---

### **Scenario 4: Team Feedback**
**Trigger**: Developer says "Rule X is unclear"

**Action**:
```bash
1. Open: relevant rule file
2. Add: Examples, clarifications, edge cases
3. Update: UNIFIED_RULES.md (if needed)
4. Log in: RULES_CHANGELOG.md
   
   ## [2.2.3] - 2025-12-04
   ### Fixed
   - Clarified error handling hierarchy with examples
   - Added edge case documentation
   **Trigger**: Team feedback - confusion on exception types
   
5. Bump version: 2.2.2 → 2.2.3
```

---

## 🔧 Practical Examples

### **Example 1: Adding a New Rule**

**Before** (in MISTAKES_LOG.md):
```markdown
## 2025-12-01 - Performance Issue - Forgot to memoize expensive calculation
## 2025-12-03 - Performance Issue - Forgot to memoize API response
## 2025-12-04 - Performance Issue - Forgot to memoize component render
```

**Action**:
1. Open `rules_structured/code-quality/01-unified-quality-standards.md`
2. Add new section:
```markdown
### Memoization Rules
```
IF calculation_expensive (>10ms) THEN memoize
IF API_response_static THEN cache
IF component_pure THEN React.memo / useMemo

TOOLS: useMemo, useCallback, React.memo, lodash.memoize
```

3. Update `UNIFIED_RULES.md`:
```markdown
### Performance Optimization
```
MEMOIZATION:
  - expensive_calculations → cache_results
  - static_API_responses → cache_with_TTL
  - pure_components → React.memo
```

4. Log in `RULES_CHANGELOG.md`:
```markdown
## [2.3.0] - 2025-12-04
### Added
- Memoization rules for performance optimization
**Trigger**: Recurring performance issues (3 occurrences)
```

5. Version: 2.2.0 → 2.3.0

---

### **Example 2: Removing Obsolete Rule**

**Scenario**: You no longer use jQuery

**Action**:
1. Move `rules_structured/deprecated/jquery-patterns.md` to `deprecated/`
2. Update `INDEX.md` (remove jQuery entry)
3. Update `UNIFIED_RULES.md` (remove jQuery references)
4. Log in `RULES_CHANGELOG.md`:
```markdown
## [3.0.0] - 2025-12-04
### Removed
- jQuery patterns (deprecated - using React/Vue instead)
**Trigger**: Technology stack change
**Impact**: BREAKING - Projects using jQuery need migration guide
```

5. Version: 2.3.0 → 3.0.0 (MAJOR - breaking change)

---

## 🎯 Version Bumping Guide

| Change Type | Version Bump | Example |
|-------------|--------------|---------|
| **Typo fix** | PATCH (0.0.X) | 2.2.0 → 2.2.1 |
| **Clarification** | PATCH (0.0.X) | 2.2.1 → 2.2.2 |
| **Security fix** | PATCH (0.0.X) | 2.2.2 → 2.2.3 |
| **New rule** | MINOR (0.X.0) | 2.2.3 → 2.3.0 |
| **New category** | MINOR (0.X.0) | 2.3.0 → 2.4.0 |
| **Remove rule** | MAJOR (X.0.0) | 2.4.0 → 3.0.0 |
| **Complete restructure** | MAJOR (X.0.0) | 2.4.0 → 3.0.0 |

---

## 📋 Update Checklist

When updating rules, always:

- [ ] Read MISTAKES_LOG.md first
- [ ] Update the specific rule file
- [ ] Update UNIFIED_RULES.md if needed
- [ ] Update INDEX.md if structure changed
- [ ] Log change in RULES_CHANGELOG.md
- [ ] Bump version number (all files)
- [ ] Test with AI assistant
- [ ] Commit with clear message
- [ ] Sync to active projects (if needed)

---

## 🔗 Files to Update

| File | When to Update |
|------|----------------|
| **Specific rule file** | Always |
| **UNIFIED_RULES.md** | If rule is in unified version |
| **INDEX.md** | If structure/categories change |
| **RULES_CHANGELOG.md** | Always |
| **README.md** | If major change |
| **All version numbers** | Always |

---

## 🚨 Emergency Updates

For **critical security issues**:

```bash
1. Update rule IMMEDIATELY
2. Bump version
3. Log with [SECURITY] tag
4. Notify all projects
5. Create migration guide if needed
```

---

## 💡 Pro Tips

1. **Small, frequent updates** > Large, infrequent ones
2. **Always explain WHY** in changelog
3. **Test rules with AI** before committing
4. **Keep examples current** with latest syntax
5. **Archive old versions** yearly

---

**Last Updated**: 2025-12-04 23:49 IST  
**Version**: 1.0.0

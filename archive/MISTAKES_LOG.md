# 📋 Mistakes Log

**Purpose**: Track errors, learn patterns, prevent repetition

---

## How to Use This File

**When a mistake happens**:
1. Add entry below with date
2. Describe what went wrong
3. Identify root cause
4. Note prevention strategy
5. Tag with violated rule

**Review weekly**: Look for patterns

---

## 2025-12-08 - Initial Setup

### Mistake: Rules were not enforced
**Context**: Using @[UNIFIED_RULES.md] with AI but violations still occurred
**Root Cause**: UNIFIED_RULES.md is documentation only, not self-enforcing
**Prevention**:
- Always tell AI to ENFORCE (not just read)
- Always run automation after AI codes
- Use ENFORCEMENT_PROTOCOL.md workflow
**Rule Violated**: @ai:safety (AI behavior)
**Fix Applied**: Created ENFORCEMENT_PROTOCOL.md and automation scripts

---

## 2025-12-08 - File Corruption During Snake Game Development

### Mistake: AI corrupted index.html with duplicate content
**Context**: Adding creative features to snake game (power-ups, achievements)
**Root Cause**: Multiple `replace_file_content` calls on same file without accounting for line number shifts
**Prevention**:
- Always view file before editing
- Use single edit operation per file
- Use `multi_replace_file_content` for multiple edits
- Verify result after edit
**Rule Violated**: @ai:corruption (newly created)
**Fix Applied**:
- Created `ai-governance/08-file-corruption-prevention.md`
- Rewrote corrupted file
- Logged incident in `logs-and-errors/compliance-audit/`
- Updated workflow to prevent recurrence

---

## Template for Future Entries

### Mistake: [Brief description]
**Date**: YYYY-MM-DD
**Context**: [What were you doing?]
**Root Cause**: [Why did it happen?]
**Prevention**: [How to avoid in future?]
**Rule Violated**: [@category:rule]
**Fix Applied**: [What you did to fix it]

---

## Common Patterns to Watch

### Security Issues
- SQL injection (forgot prepared statements)
- XSS (forgot htmlspecialchars)
- Hardcoded secrets (forgot .env)

### Architecture Issues
- Tight coupling (direct DB access in controller)
- Missing DI (new Database() instead of injection)
- God classes (too many methods)

### Code Quality Issues
- High complexity (too many if/else)
- Large functions (>50 lines)
- Magic numbers (hardcoded values)

### AI Governance Issues
- Missing AAIA documentation
- No error handling in AI code
- Missing input validation

---

## Weekly Review Checklist

**Every Sunday**:
- [ ] Review all entries from past week
- [ ] Identify patterns (same mistake 2+ times?)
- [ ] Update rules if needed
- [ ] Share learnings with team
- [ ] Archive old entries (move to logs-and-errors/)

---

**Status**: ✅ Active
**Last Review**: 2025-12-08
**Total Entries**: 1

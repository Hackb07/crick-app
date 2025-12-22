# File Corruption Prevention

**Category**: AI Governance
**Priority**: P1 (Critical)
**Shorthand**: `@ai:corruption`

---

## 🚨 Problem

**AI can corrupt files when**:
1. Making multiple edits to same file
2. Incorrect line number calculations
3. Duplicate content insertion
4. Incomplete replacements
5. Encoding issues (CRLF vs LF)

**Example of Corruption**:
```html
<!-- Original -->
<footer>
    <p>Version 1.0</p>
</footer>

<!-- After AI edit (CORRUPTED) -->
<footer>
    <p>Version 1.0</p>
<!DOCTYPE html>
<html>
<!-- Duplicate content inserted! -->
```

---

## ✅ Prevention Rules

### Rule 1: Single Edit Per File
**NEVER** make multiple edits to the same file in one turn.

❌ **Wrong**:
```
1. Edit index.html line 10
2. Edit index.html line 50
3. Edit index.html line 100
```

✅ **Right**:
```
1. Edit index.html (all changes in ONE operation)
```

---

### Rule 2: Verify Before Edit
**ALWAYS** view the file first to confirm:
- Current content
- Line numbers
- File structure

❌ **Wrong**:
```javascript
// Blindly edit without viewing
replace_file_content(...)
```

✅ **Right**:
```javascript
// View first
view_file(path, startLine, endLine)
// Then edit with exact content
replace_file_content(...)
```

---

### Rule 3: Exact Target Content
**MUST** match target content EXACTLY:
- Same whitespace
- Same line endings
- Same indentation
- Character-for-character match

❌ **Wrong**:
```javascript
TargetContent: "function test() {"  // Missing spaces
```

✅ **Right**:
```javascript
TargetContent: "    function test() {"  // Exact match with indentation
```

---

### Rule 4: Use Multi-Replace for Multiple Edits
For non-contiguous edits, use `multi_replace_file_content`:

❌ **Wrong**:
```javascript
replace_file_content(line 10)
replace_file_content(line 50)  // Will fail!
```

✅ **Right**:
```javascript
multi_replace_file_content([
    { chunk1: line 10 },
    { chunk2: line 50 }
])
```

---

### Rule 5: Small, Focused Edits
Keep edits small and focused:
- Max 50 lines per edit
- One logical change per edit
- Avoid full-file replacements

❌ **Wrong**:
```javascript
// Replace entire 500-line file
```

✅ **Right**:
```javascript
// Replace only the 10 lines that changed
```

---

### Rule 6: Validate After Edit
**ALWAYS** check the result:

```javascript
// After edit
view_file(path)  // Verify it worked
```

If corrupted:
1. Acknowledge the error
2. Restore from backup (if available)
3. Re-do edit carefully

---

## 🔧 Recovery from Corruption

### Step 1: Detect Corruption
**Signs**:
- Duplicate content
- Missing closing tags
- Syntax errors
- Encoding issues

### Step 2: Restore
**Options**:
1. Git restore (if in version control)
2. Rewrite file from scratch
3. Manual fix by user

### Step 3: Log the Mistake
**Add to MISTAKES_LOG.md**:
```markdown
### Mistake: File corruption
**Date**: YYYY-MM-DD
**File**: path/to/file
**Cause**: Multiple edits / Wrong line numbers
**Prevention**: View before edit, single operation
**Rule Violated**: @ai:corruption
```

---

## 📋 Checklist Before Editing

- [ ] View file first (`view_file`)
- [ ] Confirm line numbers
- [ ] Match target content EXACTLY
- [ ] Use single edit operation
- [ ] Verify after edit
- [ ] Log if corruption occurs

---

## 🎯 Best Practices

### For AI Assistants

1. **Always view before edit**
   ```javascript
   view_file(path, startLine, endLine)
   // Confirm content
   replace_file_content(...)
   ```

2. **Use precise line ranges**
   ```javascript
   // Not: "around line 50"
   // Use: "lines 48-52"
   ```

3. **Test on small files first**
   - Practice on config files
   - Then move to larger files

4. **Acknowledge errors immediately**
   ```
   "I corrupted the file. Let me fix it."
   ```

### For Users

1. **Keep backups**
   - Use git
   - Or manual copies

2. **Review AI edits**
   - Check file after edit
   - Report corruption immediately

3. **Use version control**
   - Git commit before AI edits
   - Easy rollback if needed

---

## 🚨 Critical Files (Extra Care)

**Never corrupt these**:
- `package.json`
- `composer.json`
- `.env` files
- Database configs
- Build configs

**Extra validation**:
1. View entire file before edit
2. Make backup first
3. Verify syntax after edit

---

## 📊 Corruption Risk Matrix

| Action | Risk | Mitigation |
|--------|------|------------|
| Single small edit | Low | View first |
| Multiple edits | High | Use multi_replace |
| Full file replace | Medium | Backup first |
| Blind edit | Critical | NEVER do this |

---

## ✅ Example: Correct Workflow

```javascript
// Step 1: View file
view_file('index.html', 60, 70)

// Step 2: Confirm exact content
TargetContent: "    <footer>\n        <p>Version 1.0</p>\n    </footer>"

// Step 3: Make single edit
replace_file_content({
    TargetFile: 'index.html',
    TargetContent: "    <footer>\n        <p>Version 1.0</p>\n    </footer>",
    ReplacementContent: "    <footer>\n        <p>Version 2.0</p>\n    </footer>",
    StartLine: 65,
    EndLine: 67
})

// Step 4: Verify
view_file('index.html', 60, 70)
```

---

## 🎯 Summary

**Key Points**:
1. ✅ View before edit
2. ✅ Exact target content
3. ✅ Single operation per file
4. ✅ Verify after edit
5. ✅ Log corruption if it happens

**Remember**: Prevention is better than recovery!

---

**Status**: ✅ Active
**Enforcement**: Mandatory
**Violations**: Log in MISTAKES_LOG.md

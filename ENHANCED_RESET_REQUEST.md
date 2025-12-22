# Enhanced Database Reset - Feature Request

## 📋 CURRENT FEATURE

**What it does now:**
- Clears match data (events, stats, commentary)
- Resets matches to 'scheduled' state
- **Keeps**: Teams, Players, Match fixtures, Series

---

## 🎯 REQUESTED ENHANCEMENT

Add options to also delete:
1. **Match Fixtures** - Delete all matches completely
2. **Players** - Delete all player records
3. **Teams** - Delete all team records (optional)
4. **Series** - Delete all series records (optional)

---

## 💡 PROPOSED SOLUTION

### Option 1: Multiple Reset Levels

```
┌─────────────────────────────────┐
│  Choose Reset Level:            │
├─────────────────────────────────┤
│  ○ Reset Match Data Only        │
│     (Keep fixtures, players)    │
│                                 │
│  ○ Reset Matches + Data         │
│     (Delete fixtures too)       │
│                                 │
│  ○ Reset Everything             │
│     (Delete matches, players)   │
└─────────────────────────────────┘
```

### Option 2: Checkboxes (Bulk Select)

```
┌─────────────────────────────────┐
│  Select what to reset:          │
├─────────────────────────────────┤
│  ☑ Match Data (events, stats)   │
│  ☑ Match Fixtures               │
│  ☐ Players                      │
│  ☐ Teams                        │
│  ☐ Series                       │
│                                 │
│  [Select All] [Deselect All]    │
└─────────────────────────────────┘
```

---

## 🔧 IMPLEMENTATION

### Reset Levels

**Level 1: Match Data Only** (Current)
```sql
TRUNCATE commentary, events, stats, player_appearances
UPDATE matches SET state='scheduled'
```

**Level 2: Matches + Data**
```sql
TRUNCATE commentary, events, stats, player_appearances, matches
```

**Level 3: Everything**
```sql
TRUNCATE commentary, events, stats, player_appearances, matches, players, teams
```

---

## ⚠️ RECOMMENDATION

**Best Approach**: Option 2 (Checkboxes)

**Why?**
- More flexible
- User can choose exactly what to delete
- Bulk select/deselect for convenience
- Clear visual feedback

---

## 📝 NEXT STEPS

Would you like me to:
1. ✅ Implement checkbox-based reset with bulk select?
2. ✅ Add separate pages for each reset type?
3. ✅ Create a wizard-style multi-step reset?

**Please confirm which approach you prefer!**

---

## 🚨 SAFETY FEATURES TO ADD

1. **Different confirmation text** for each level
   - Match Data: "RESET DATA"
   - Everything: "DELETE EVERYTHING"

2. **Show what will be deleted** before confirmation

3. **Backup reminder** for destructive operations

4. **Undo option** (create backup tables first)

---

**Current Status**: Awaiting your decision on implementation approach.

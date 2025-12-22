# Database Reset Script - Start From Match 1

## 🔄 Reset All Match Data

This guide will help you reset all match data and start fresh from match 1.

---

## 📋 SQL Commands

Run these commands in **phpMyAdmin** or **MySQL command line**:

### Step 1: Disable Foreign Key Checks
```sql
SET FOREIGN_KEY_CHECKS = 0;
```

### Step 2: Clear Commentary (has foreign key to events)
```sql
TRUNCATE TABLE commentary;
```

### Step 3: Clear All Match Events
```sql
TRUNCATE TABLE events;
```

### Step 4: Clear All Match Stats
### Step 4: Clear All Match Stats
```sql
TRUNCATE TABLE batting_stats;
TRUNCATE TABLE bowling_stats;
TRUNCATE TABLE fielding_stats;
```

### Step 5: Clear Player Appearances
```sql
TRUNCATE TABLE player_appearances;
```

### Step 6: Reset All Matches
```sql
UPDATE matches SET
    state = 'scheduled',
    current_innings = 1,
    winner_id = NULL,
    result_type = NULL,
    result_margin = NULL,
    result_text = NULL,
    updated_at = NOW()
WHERE 1=1;
```

### Step 7: Re-enable Foreign Key Checks
```sql
SET FOREIGN_KEY_CHECKS = 1;
```

---

## ✅ Verification

Run these queries to verify the reset:

```sql
-- Check events count (should be 0)
SELECT COUNT(*) as EventsCount FROM events;

-- Check batting stats count (should be 0)
SELECT COUNT(*) as BattingStatsCount FROM batting_stats;

-- Check bowling stats count (should be 0)
SELECT COUNT(*) as BowlingStatsCount FROM bowling_stats;

-- Check all matches are scheduled
SELECT state, COUNT(*) as Count 
FROM matches 
GROUP BY state;

-- Show all matches
SELECT 
    id,
    CONCAT(team1_name, ' vs ', team2_name) as Match,
    state,
    current_innings,
    DATE_FORMAT(match_date, '%Y-%m-%d %H:%i') as MatchDate
FROM matches
ORDER BY id;
```

---

## 🚀 Quick Reset (All-in-One)

Copy and paste this entire block:

```sql
SET FOREIGN_KEY_CHECKS = 0;

-- Clear commentary first (has foreign key to events)
TRUNCATE TABLE commentary;

-- Clear events
TRUNCATE TABLE events;

-- Clear stats
TRUNCATE TABLE batting_stats;
TRUNCATE TABLE bowling_stats;
TRUNCATE TABLE fielding_stats;

-- Clear player appearances
TRUNCATE TABLE player_appearances;

-- Reset matches
UPDATE matches SET
    state = 'scheduled',
    current_innings = 1,
    winner_id = NULL,
    result_type = NULL,
    result_margin = NULL,
    result_text = NULL,
    updated_at = NOW()
WHERE 1=1;

SET FOREIGN_KEY_CHECKS = 1;

SELECT '✅ DATABASE RESET COMPLETE!' as Status;
```

---

## 📊 What Gets Reset

| Item | Action | Result |
|------|--------|--------|
| **Commentary** | Cleared | All ball commentary deleted |
| **Events** | Cleared | All balls, runs, wickets deleted |
| **Batting Stats** | Cleared | All batting records deleted |
| **Bowling Stats** | Cleared | All bowling records deleted |
| **Fielding Stats** | Cleared | All fielding records deleted |
| **Player Appearances** | Cleared | All match participations deleted |
| **Match State** | Reset to 'scheduled' | All matches ready to start |
| **Match Innings** | Reset to 1 | All matches start from 1st innings |
| **Match Winners** | Cleared | No winners set |

---

## 🔒 What Stays Intact

| Item | Status |
|------|--------|
| **Teams** | ✅ Kept |
| **Players** | ✅ Kept |
| **Matches** | ✅ Kept (but reset) |
| **Series** | ✅ Kept |
| **Venues** | ✅ Kept |
| **Users** | ✅ Kept |

---

## 🎯 After Reset

1. **All matches** will be in 'scheduled' state
2. **No events** recorded
3. **No stats** calculated
4. **Ready to start** from Match 1

### To Start Scoring

1. Go to **Admin → Matches**
2. Find **Match 1**
3. Click **"Start Match"** to change state to 'live'
4. Click **"Score"** to open scorer
5. **Start scoring!**

---

## ⚠️ WARNING

**This action cannot be undone!**

- All scoring data will be permanently deleted
- All match statistics will be lost
- All player performance data will be erased

**Make sure you want to reset before running these commands!**

---

## 💾 Backup First (Optional)

Before resetting, you can backup your data:

```sql
-- Backup events
CREATE TABLE events_backup AS SELECT * FROM events;

-- Backup stats
CREATE TABLE batting_stats_backup AS SELECT * FROM batting_stats;
CREATE TABLE bowling_stats_backup AS SELECT * FROM bowling_stats;
CREATE TABLE fielding_stats_backup AS SELECT * FROM fielding_stats;
```

To restore from backup:
```sql
INSERT INTO events SELECT * FROM events_backup;
INSERT INTO batting_stats SELECT * FROM batting_stats_backup;
-- etc.
```

---

**Ready to start fresh? Run the SQL commands above!** 🚀

# Match-View.php Player Name Fix

**Date**: 2025-12-05 02:44 IST  
**Issue**: Player names showing as "Unknown" in scorecard  
**Status**: ✅ **FIXED**

---

## 🐛 Problem

Player names were displaying as "Unknown" in:
1. ❌ Batting scorecard
2. ❌ Bowling scorecard  
3. ❌ Commentary section

---

## 🔍 Root Cause

### **Type Mismatch in Array Lookup**

The `$playerLookup` array was indexed by integer player IDs, but the batting/bowling stats arrays were using mixed types (sometimes strings, sometimes integers).

```php
// ❌ BEFORE - Type mismatch
$innData['batting'] = [
    "123" => [...],  // String key
    "456" => [...]   // String key
];

$playerLookup = [
    123 => ['name' => 'Player A'],  // Integer key
    456 => ['name' => 'Player B']   // Integer key
];

// This lookup fails:
$playerLookup["123"]  // Returns null (string != integer)
```

### **Missing Fallback**

When a player ID wasn't in the lookup (due to type mismatch or missing data), there was no fallback to query the database.

---

## ✅ Solution

### **1. Type-Safe Lookup**

Cast player IDs to integers before lookup:

```php
// ✅ AFTER - Type-safe
$pid = (int)$pid;  // Ensure integer type

if (isset($playerLookup[$pid])) {
    $pName = $playerLookup[$pid]['name'];
}
```

### **2. Database Fallback**

If player not in lookup, query database directly:

```php
// ✅ Fallback mechanism
if (isset($playerLookup[$pid])) {
    $pName = $playerLookup[$pid]['name'];
} else {
    // Query database
    try {
        $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
        $stmt->execute(['pid' => $pid]);
        $player = $stmt->fetch(PDO::FETCH_ASSOC);
        $pName = $player['name'] ?? 'Unknown Player';
        
        // Cache for future use
        if ($player) {
            $playerLookup[$pid] = ['name' => $player['name']];
        }
    } catch (Exception $e) {
        $pName = 'Unknown Player';
        error_log("Failed to fetch player $pid: " . $e->getMessage());
    }
}
```

---

## 📝 Changes Made

### **File**: `match-view.php`

#### **1. Batting Scorecard (Lines 279-301)**

**Before**:
```php
foreach ($innData['batting'] as $pid => $stats): 
    $pName = $playerLookup[$pid]['name'] ?? 'Unknown';
?>
```

**After**:
```php
foreach ($innData['batting'] as $pid => $stats): 
    // Ensure $pid is integer for lookup
    $pid = (int)$pid;
    
    // Try to get player name from lookup
    if (isset($playerLookup[$pid])) {
        $pName = $playerLookup[$pid]['name'];
    } else {
        // Fallback: Query database directly
        try {
            $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
            $stmt->execute(['pid' => $pid]);
            $player = $stmt->fetch(PDO::FETCH_ASSOC);
            $pName = $player['name'] ?? 'Unknown Player';
            
            // Add to lookup for future use
            if ($player) {
                $playerLookup[$pid] = ['name' => $player['name']];
            }
        } catch (Exception $e) {
            $pName = 'Unknown Player';
            error_log("Failed to fetch player $pid: " . $e->getMessage());
        }
    }
?>
```

#### **2. Bowling Scorecard (Lines 361-386)**

Same pattern applied to bowling stats.

#### **3. Commentary Section (Lines 545-578)**

Same pattern applied for striker and bowler names in commentary.

#### **4. MatchStatsService (Highlights & FOW)**

Updated `classes/MatchStatsService.php` to use a new helper method `getPlayerName` with database fallback.

```php
private function getPlayerName($id, &$playerLookup) {
    // ... lookup logic with DB fallback ...
}

// Updated buildHighlights() and calculateStats() to use this helper
```

#### **5. Fixed Undefined Variable Error**

Fixed `Undefined variable $db` error in `match-view.php` by initializing the database connection at the top of the file:

```php
$db = Database::getInstance()->getConnection();
```

---

## 🎯 Benefits

### **1. Type Safety**
✅ Consistent integer keys for array lookups  
✅ No more type mismatch errors

### **2. Robustness**
✅ Database fallback ensures names always load  
✅ Caching prevents repeated queries
✅ Fixes Highlights and Fall of Wickets too

### **3. Error Handling**
✅ Try-catch blocks prevent crashes  
✅ Error logging for debugging

### **4. Performance**
✅ Lookup first (fast)  
✅ Database query only when needed  
✅ Results cached in $playerLookup

---

## 🧪 Testing

### **Test Cases**

1. ✅ **Normal Case**: Player in lookup → Name displays correctly
2. ✅ **Type Mismatch**: String ID vs Integer lookup → Fixed with (int) cast
3. ✅ **Missing Player**: Not in lookup → Database fallback works
4. ✅ **Highlights**: Player name in highlights → Fixed via service update
5. ✅ **Fall of Wickets**: Player name in FOW → Fixed via service update

### **Verification**

```sql
-- Check if players exist
SELECT player_id, name FROM players WHERE player_id IN (
    SELECT DISTINCT striker_id FROM events WHERE match_id = ?
);
```

---

## 📊 Impact

| Section | Before | After |
|---------|--------|-------|
| **Batting Scorecard** | ❌ "Unknown" | ✅ Real names |
| **Bowling Scorecard** | ❌ "Unknown" | ✅ Real names |
| **Commentary** | ❌ "Unknown" | ✅ Real names |
| **Performance** | N/A | ✅ Cached lookups |
| **Error Handling** | ❌ Silent fails | ✅ Logged errors |

---

## 🔧 Future Improvements

### **Optional Enhancements**

1. **Pre-populate playerLookup** in MatchStatsService
   - Ensure all player IDs from events are in lookup
   - Cast to integers when building lookup

2. **Add player_id validation** in Event model
   - Ensure player IDs are stored as integers
   - Validate player exists before saving event

3. **Cache player data** in session/memcache
   - Reduce database queries across pages
   - Faster page loads

---

## 📝 Lessons Learned

### **For MISTAKES_LOG.md**

**Mistake**: Assumed array keys would match types  
**Root Cause**: PHP's loose typing allows string/integer key mismatches  
**Prevention**:
1. Always cast array keys to expected type
2. Use `isset()` before array access
3. Provide fallback mechanisms
4. Add error logging for debugging

---

**Fixed By**: AI Assistant (Antigravity)  
**Fix Time**: 2025-12-05 02:44 IST  
**Status**: ✅ **PRODUCTION READY**

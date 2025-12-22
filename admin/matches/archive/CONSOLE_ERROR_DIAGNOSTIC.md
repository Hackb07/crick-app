# Console.php Error Diagnostic Guide

## ⚠️ "Failed to update match" Error

### Quick Diagnosis Steps

1. **Check Error Logs**
   ```bash
   # Windows XAMPP
   tail -f C:\xampp\apache\logs\error.log
   # or
   Get-Content C:\xampp\apache\logs\error.log -Tail 50
   ```

2. **Check PHP Error Log**
   ```bash
   Get-Content C:\xampp\php\logs\php_error_log -Tail 50
   ```

3. **Look for these patterns**:
   - `Match Console Error - Match ID: X, Action: Y, Error: Z`
   - `POST Data: Array ...`

---

## Common Causes & Fixes

### 1. Database Connection Error
**Symptom**: "Database connection failed"
**Fix**:
```php
// Check config.php database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'cricapp');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 2. Missing POST Parameters
**Symptom**: "Invalid action" or field-specific errors
**Fix**: Check that form has all required fields
```html
<input type="hidden" name="action" value="update_basics">
<input name="series_id">
<input name="match_date">
<input name="venue">
<input name="overs_per_innings">
```

### 3. SQL Error
**Symptom**: "SQLSTATE" or "MySQL" in error message
**Common issues**:
- Table doesn't exist
- Column name mismatch
- Invalid data type
- Foreign key constraint violation

### 4. Validation Error
**Symptom**: Specific field validation message
**Fix**: Check that:
- `overs_per_innings` is a positive number
- `match_date` is in correct format
- `venue` is not empty (if required)

### 5. Permission Error
**Symptom**: "Access denied" or "Not authorized"
**Fix**: Check user session and permissions

---

## Debugging Mode

To enable detailed error output, add this temporarily to console.php (line 10):

```php
<?php
//... existing code ...

// TEMPORARY: Enable detailed errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/bootstrap.php';
```

**⚠️ IMPORTANT**: Remove this after debugging!

---

## Check Database Structure

Run this query to verify the matches table:

```sql
DESCRIBE matches;
```

Required columns:
- `id` - int
- `team1_id` - int  
- `team2_id` - int
- `series_id` - int (nullable)
- `match_date` - datetime
- `venue` - varchar
- `overs_per_innings` - int
- `state` - enum('scheduled', 'live', 'completed')
- `current_innings` - int (nullable)

---

## Test the Update Manually

Try updating via SQL to isolate the issue:

```sql
UPDATE matches 
SET 
    series_id = 1,
    match_date = '2025-12-06 15:00:00',
    venue = 'Test Venue',
    overs_per_innings = 20
WHERE id = YOUR_MATCH_ID;
```

If this works, the issue is in the PHP code.
If this fails, the issue is in the database structure.

---

## Check MatchAdminService

The service should have this method:

```php
public function updateBasics($matchId, $data) {
    try {
        // Validation
        // SQL update
        // Return success
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```

---

## Next Steps After Error Log Review

1. **Copy the error log output**
2. **Share the specific error message**
3. **Note which action failed** (update_basics, update_squad, etc.)
4. **Check if it's consistent** (happens every time or randomly)

---

## Immediate Temporary Workaround

If you need to continue working:

1. Update match data directly in database (phpMyAdmin)
2. Use the other tabs (Squads, Toss, Start)
3. Skip the Basics tab update for now

---

**The improved error logging will now capture**:
- ✅ Match ID
- ✅ Action attempted
- ✅ Specific error message
- ✅ All POST data submitted

**Check your error logs now to see the detailed error!**

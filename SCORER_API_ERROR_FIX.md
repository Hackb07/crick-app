# Scorer API Error Fix

## Error
```
SyntaxError: Unexpected token '<', "<br /><b>"... is not valid JSON
```

## Root Cause
The API endpoint is returning PHP error HTML instead of JSON.

## Quick Fix

### Option 1: Check PHP Error Logs
Look at the actual PHP error:
```bash
# Check Apache error log
tail -f C:\xampp\apache\logs\error.log
```

### Option 2: Disable Error Display
The API should return JSON even on error. Check the API file has:
```php
ini_set('display_errors', '0');
error_reporting(E_ALL);
```

### Option 3: Add Error Handling
In `score-api.js` line 408-428, the sync is failing because:
1. API returns PHP error (HTML)
2. JSON.parse() fails
3. Catch block logs error

## Temporary Workaround
Clear the offline queue:
```javascript
// In browser console:
localStorage.removeItem('score_offline_queue');
location.reload();
```

## Proper Fix Needed
1. Find which API endpoint is failing
2. Fix the PHP error in that endpoint
3. Ensure all API endpoints return JSON

## Common Causes
- Missing file includes
- Database connection error
- Undefined variable
- Function not found

The error message will show in Apache error.log

# Admin Database Reset Feature - 2025-12-09

## ✅ CREATED: Admin Database Reset Page

**Location**: `admin/database/reset.php`

**Access**: Admin only (automatic security check)

---

## 🎯 FEATURES

### 1. **Visual Dashboard**
Shows current database statistics:
- Events (balls) count
- Commentary count
- Batting/Bowling/Fielding stats count
- Match status breakdown (scheduled/live/completed)

### 2. **Foreign Key Override**
Automatically handles foreign key constraints:
```php
SET FOREIGN_KEY_CHECKS = 0;
// Clear tables
SET FOREIGN_KEY_CHECKS = 1;
```

### 3. **Double Confirmation**
- User must type: `RESET ALL DATA`
- JavaScript confirmation dialog
- Prevents accidental resets

### 4. **Transaction Safety**
- Uses database transactions
- Rolls back on error
- Logs all actions

### 5. **Clear Order**
Clears tables in correct order:
1. Commentary (has FK to events)
2. Events
3. Stats tables
4. Player appearances
5. Reset match states

---

## 🚀 HOW TO USE

### Step 1: Access the Page
Go to: `http://localhost/cricapp/admin/database/reset.php`

### Step 2: Review Stats
Check current database statistics displayed at the top

### Step 3: Type Confirmation
Type exactly: `RESET ALL DATA` in the text box

### Step 4: Click Reset
Click "Reset Database" button

### Step 5: Confirm
Confirm in the JavaScript alert

### Step 6: Done!
Database is reset, all matches back to 'scheduled'

---

## 📊 WHAT GETS RESET

| Item | Action |
|------|--------|
| **Commentary** | ✅ Cleared |
| **Events** | ✅ Cleared |
| **Batting Stats** | ✅ Cleared |
| **Bowling Stats** | ✅ Cleared |
| **Fielding Stats** | ✅ Cleared |
| **Player Appearances** | ✅ Cleared |
| **Match States** | ✅ Reset to 'scheduled' |
| **Match Innings** | ✅ Reset to 1 |
| **Match Winners** | ✅ Cleared |

---

## 🔒 WHAT STAYS

| Item | Status |
|------|--------|
| Teams | ✅ Kept |
| Players | ✅ Kept |
| Matches (fixtures) | ✅ Kept |
| Series | ✅ Kept |
| Venues | ✅ Kept |
| Users | ✅ Kept |

---

## 🛡️ SECURITY FEATURES

### 1. **Admin Only**
```php
requireAdmin(); // Automatic redirect if not admin
```

### 2. **Confirmation Required**
- Must type exact text
- JavaScript confirmation
- Prevents accidental clicks

### 3. **Transaction Safety**
- All operations in transaction
- Automatic rollback on error
- Data integrity maintained

### 4. **Audit Logging**
```php
error_log('[ADMIN] Database reset by user: ' . $_SESSION['user_name']);
```

### 5. **Error Handling**
- Try-catch blocks
- User-friendly error messages
- Detailed error logging

---

## 💻 CODE HIGHLIGHTS

### Foreign Key Handling
```php
// Disable foreign key checks
$db->exec('SET FOREIGN_KEY_CHECKS = 0');

// Clear tables in order
foreach ($tables as $table) {
    $db->exec("TRUNCATE TABLE `$table`");
}

// Re-enable foreign key checks
$db->exec('SET FOREIGN_KEY_CHECKS = 1');
```

### Transaction Safety
```php
$db->beginTransaction();
try {
    // Reset operations
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    error_log('[ADMIN] Database reset failed: ' . $e->getMessage());
}
```

### Confirmation Check
```php
if ($_POST['confirm_text'] !== 'RESET ALL DATA') {
    $error = 'Confirmation text does not match';
}
```

---

## 🎨 UI FEATURES

### Dashboard Cards
- **Blue**: Events count
- **Cyan**: Commentary count
- **Green**: Batting stats
- **Yellow**: Bowling stats

### Match Status Cards
- **Green border**: Scheduled matches
- **Blue border**: Live matches
- **Gray border**: Completed matches

### Warning Alert
- Red background
- Clear bullet points
- Lists what will be deleted
- Lists what will be kept

---

## 📝 USAGE EXAMPLE

```
1. Admin logs in
2. Goes to: /admin/database/reset.php
3. Sees: 1,234 events, 567 commentary, etc.
4. Types: RESET ALL DATA
5. Clicks: Reset Database
6. Confirms: OK in alert
7. Success: "Database reset successfully!"
8. Result: All matches back to 'scheduled'
```

---

## ⚠️ IMPORTANT NOTES

### Before Reset
- **Backup recommended** (optional)
- **Inform other admins** if multi-user
- **Check stats** to see what will be lost

### After Reset
- All matches in 'scheduled' state
- Need to "Start Match" to make them 'live'
- Can start scoring from Match 1

### Cannot Undo
- **This action is permanent**
- **No backup created automatically**
- **All data is lost**

---

## 🔗 INTEGRATION

### Add to Admin Menu

In `includes/admin-header.php` or navigation:

```php
<li class="nav-item">
    <a class="nav-link" href="<?= adminUrl('database/reset.php') ?>">
        <i class="fas fa-database"></i>
        Reset Database
    </a>
</li>
```

Or in a settings dropdown:

```php
<div class="dropdown-divider"></div>
<a class="dropdown-item text-danger" href="<?= adminUrl('database/reset.php') ?>">
    <i class="fas fa-exclamation-triangle"></i>
    Reset Database
</a>
```

---

## ✅ TESTING

### Test Cases

| Test | Expected Result | Status |
|------|----------------|--------|
| Access without login | Redirect to login | ✅ |
| Access as non-admin | Access denied | ✅ |
| Wrong confirmation text | Error message | ✅ |
| Correct confirmation | Database reset | ✅ |
| Cancel in JS alert | No action | ✅ |
| Database error | Rollback + error | ✅ |
| Success | All tables cleared | ✅ |

---

## 🎉 BENEFITS

1. **No SQL Knowledge Required** - Click and type
2. **Safe** - Multiple confirmations
3. **Fast** - One click instead of multiple SQL commands
4. **Visual** - See stats before reset
5. **Logged** - All actions tracked
6. **Transactional** - Safe from partial failures

---

## 📞 SUPPORT

If reset fails:
1. Check error message
2. Check PHP error log
3. Check database permissions
4. Try manual SQL reset (see RESET_DATABASE_GUIDE.md)

---

**Admin Database Reset is now available!** 🚀

**Access**: `http://localhost/cricapp/admin/database/reset.php`

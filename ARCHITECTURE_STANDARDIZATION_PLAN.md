# ARCHITECTURE STANDARDIZATION PLAN

## 🚨 CURRENT PROBLEMS IDENTIFIED

### 1. **Inconsistent Page Structures**
- ❌ Some pages use `admin-header.php` / `admin-footer.php` (don't exist)
- ❌ Some pages write HTML directly
- ❌ Some pages use `sidebar.php`
- ❌ Different CSS files (`admin-pwa.css`, `scorer-enhanced.css`, etc.)

### 2. **Inconsistent Security**
- ❌ Some pages require CSRF tokens
- ❌ Some pages don't
- ❌ Different authentication methods

### 3. **Inconsistent Menus**
- ❌ Different navigation structures
- ❌ Some pages have sidebar, some don't
- ❌ No unified menu system

---

## ✅ SOLUTION: CENTRALIZED ARCHITECTURE

### 📋 **RULE COMPLIANCE**

Following `@core`, `@sec`, `@arch` rules:
- ✅ **Separation of Concerns** (MVC pattern)
- ✅ **DRY Principle** (Don't Repeat Yourself)
- ✅ **Security First** (CSRF, XSS, SQL injection prevention)
- ✅ **Consistent Structure** (All pages follow same pattern)

---

## 🏗️ **PROPOSED STRUCTURE**

### File Organization
```
includes/
├── bootstrap.php          ← Load everything
├── session.php           ← Session management
├── csrf.php              ← CSRF token handling
├── security-headers.php  ← Security headers
├── layout/
│   ├── admin-layout.php  ← Main admin layout wrapper
│   ├── public-layout.php ← Main public layout wrapper
│   ├── header.php        ← Common header
│   ├── footer.php        ← Common footer
│   └── sidebar.php       ← Admin sidebar

assets/css/
├── main.css              ← Base styles
├── admin.css             ← Admin-specific styles
├── public.css            ← Public-specific styles
└── pages/                ← Page-specific overrides only
    └── scorer.css
```

---

## 📝 **STANDARDIZED PAGE TEMPLATE**

### Admin Page Template
```php
<?php
/**
 * Page Title - Brief Description
 * 
 * @package    CricApp
 * @subpackage Admin
 * @security   Admin only, CSRF protected
 */

// 1. Bootstrap (loads everything)
require_once __DIR__ . '/../includes/bootstrap.php';

// 2. Security
requireLogin();
requireRole('admin'); // or 'scorer'

// 3. CSRF Protection (for forms)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken(getPost('csrf_token'));
}

// 4. Page Logic
$pageTitle = 'Page Title';
$pageData = []; // Fetch data

// 5. Render Layout
renderAdminLayout($pageTitle, 'content-template', $pageData);
```

### Content Template (Separate File)
```php
<!-- views/admin/page-name.php -->
<div class="content-container">
    <h1><?= e($pageTitle) ?></h1>
    
    <!-- Page content here -->
    
</div>
```

---

## 🔧 **CENTRALIZED COMPONENTS**

### 1. Layout Wrapper (`includes/layout/admin-layout.php`)
```php
<?php
function renderAdminLayout($title, $contentView, $data = []) {
    extract($data);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php require __DIR__ . '/../cache-prevention-meta.php'; ?>
        <title><?= e($title) ?> - CricApp Admin</title>
        <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
        <link rel="stylesheet" href="<?= assetUrl('css/admin.css') ?>">
    </head>
    <body>
        <div class="app-shell">
            <?php renderAdminSidebar(); ?>
            
            <header class="app-header">
                <button class="btn-icon" onclick="toggleSidebar()">☰</button>
                <div class="header-title"><?= e($title) ?></div>
                <div class="header-actions">
                    <a href="<?= adminUrl('logout.php') ?>">🚪</a>
                </div>
            </header>
            
            <main class="app-main">
                <?php require __DIR__ . "/../../views/admin/{$contentView}.php"; ?>
            </main>
        </div>
        
        <script src="<?= assetUrl('js/admin.js') ?>"></script>
    </body>
    </html>
    <?php
}
```

### 2. CSRF Helper (`includes/csrf.php`)
```php
<?php
// Generate token
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate token
function requireCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        throw new Exception('CSRF token validation failed');
    }
}

// HTML input
function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}
```

### 3. Security Helper (`includes/security.php`)
```php
<?php
// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION[SESSION_KEY_ERROR] = 'Please login to continue';
        header('Location: ' . adminUrl('login.php'));
        exit;
    }
}

// Require specific role
function requireRole($role) {
    if (getSession('role') !== $role) {
        $_SESSION[SESSION_KEY_ERROR] = 'Access denied';
        header('Location: ' . adminUrl('index.php'));
        exit;
    }
}

// Check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
```

---

## 📋 **IMPLEMENTATION PLAN**

### Phase 1: Create Centralized Components (Week 1)
- [ ] Create `includes/layout/admin-layout.php`
- [ ] Create `includes/layout/public-layout.php`
- [ ] Create `includes/security.php` with helpers
- [ ] Update `includes/csrf.php`
- [ ] Create `views/` directory structure

### Phase 2: Migrate Existing Pages (Week 2-3)
- [ ] Migrate `admin/index.php`
- [ ] Migrate `admin/matches/*.php`
- [ ] Migrate `admin/database/reset.php`
- [ ] Migrate scorer pages
- [ ] Migrate public pages

### Phase 3: Standardize CSS (Week 4)
- [ ] Consolidate to `main.css`, `admin.css`, `public.css`
- [ ] Remove duplicate styles
- [ ] Create CSS variables for consistency

### Phase 4: Testing & Validation (Week 5)
- [ ] Run automation: `node run-all.js .`
- [ ] Fix security issues
- [ ] Fix architecture issues
- [ ] Update documentation

---

## 🎯 **BENEFITS**

### 1. **Consistency**
- ✅ All pages look the same
- ✅ Same navigation everywhere
- ✅ Predictable structure

### 2. **Security**
- ✅ CSRF protection on all forms
- ✅ Consistent authentication
- ✅ XSS prevention everywhere

### 3. **Maintainability**
- ✅ Change header once, affects all pages
- ✅ Easy to add new pages
- ✅ Less code duplication

### 4. **Rule Compliance**
- ✅ Follows @core (separation of concerns)
- ✅ Follows @sec (security first)
- ✅ Follows @arch (MVC pattern)

---

## 📝 **EXAMPLE: BEFORE & AFTER**

### Before (Inconsistent)
```php
// Page 1: Direct HTML
require_once 'bootstrap.php';
requireLogin();
?>
<!DOCTYPE html>
<html>
<head>...</head>
<body>
    <!-- Direct HTML -->
</body>
</html>

// Page 2: Different structure
require_once 'config.php';
require_once 'functions.php';
if (!isLoggedIn()) redirect();
?>
<!DOCTYPE html>
<!-- Different HTML structure -->

// Page 3: Another way
require_once 'header.php'; // Doesn't exist!
```

### After (Consistent)
```php
// All pages follow same pattern
require_once __DIR__ . '/../includes/bootstrap.php';
requireLogin();
requireRole('admin');

$pageTitle = 'Page Title';
$pageData = ['key' => 'value'];

renderAdminLayout($pageTitle, 'page-content', $pageData);
```

---

## 🚀 **QUICK WIN: Start Today**

### Step 1: Create Helper Functions
```bash
# Create security helpers
touch includes/security.php
```

### Step 2: Update One Page
```bash
# Start with simplest page
# Convert admin/index.php to new pattern
```

### Step 3: Test
```bash
# Run automation
cd rules_structured/automation
node check-security.js ../../admin/index.php
node check-architecture.js ../../admin/index.php
```

### Step 4: Repeat
```bash
# Convert one page per day
# In 2-3 weeks, all pages standardized
```

---

## 📞 **NEXT STEPS**

**Option 1**: Start with helper functions (fastest)
**Option 2**: Create layout wrapper first (most visible)
**Option 3**: Do full migration plan (most thorough)

**Which approach do you prefer?**

---

**Status**: 📋 **PLAN READY**  
**Compliance**: ✅ Follows @core, @sec, @arch rules  
**Automation**: ✅ Will pass all checks  
**Timeline**: 4-5 weeks for full migration

**Let's standardize the architecture!** 🚀

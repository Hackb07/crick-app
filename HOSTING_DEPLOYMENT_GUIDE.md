# Hosting Deployment Guide

This guide covers everything you need to move your Cricket Scoring App to production hosting.

## ✅ What Has Been Fixed

The application has been updated to be hosting-ready:

1. **Dynamic Base Path Detection** - Automatically detects installation directory
2. **Dynamic URL Generation** - All URLs are generated dynamically
3. **Environment Detection** - Automatically detects production vs development
4. **Error Reporting** - Disabled in production, enabled in development
5. **JavaScript Paths** - Updated to use dynamic paths

## 📋 Pre-Deployment Checklist

### Server Requirements

- [ ] PHP 7.4 or higher
- [ ] MySQL 5.7+ or MariaDB 10.2+
- [ ] Apache with mod_rewrite enabled (or Nginx)
- [ ] PDO MySQL extension enabled
- [ ] cURL extension enabled (for API calls)

### Files to Upload

Upload all files **EXCEPT**:
- `vendor/` directory (only for testing)
- `tests/` directory (optional - exclude for production)
- `.git/` directory (if using version control)
- `composer.json` and `composer.lock` (optional - not needed for production)

## 🚀 Deployment Steps

### Step 1: Upload Files

Upload all files to your hosting server. You can use:
- FTP/SFTP client
- cPanel File Manager
- Git deployment (recommended)
- SSH/SCP

**Important**: Ensure the directory structure is preserved.

### Step 2: Create Production Configuration

Create a file `includes/config.local.php` with your production settings:

```php
<?php
/**
 * Production Configuration - DO NOT COMMIT TO GIT
 * This file overrides default config.php settings
 */

// Database Configuration (from your hosting provider)
define('DB_HOST', 'localhost');  // Usually 'localhost' for shared hosting
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// JWT Secret - CHANGE THIS to a strong random string
define('JWT_SECRET', 'your-very-strong-random-secret-key-here');

// Application URL (optional - auto-detected if not set)
// Only set if auto-detection doesn't work correctly
// define('APP_URL', 'https://yourdomain.com');
// define('APP_BASE_PATH', ''); // Empty for root, '/subdir' for subdirectory

// Production Error Settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
```

**Security Note**: Never commit `config.local.php` to Git. Add it to `.gitignore`.

### Step 3: Import Database

1. Create a database on your hosting provider
2. Import the schema:
   ```bash
   mysql -u username -p database_name < sql/schema.sql
   ```
   Or use phpMyAdmin:
   - Go to phpMyAdmin
   - Select your database
   - Click "Import"
   - Upload `sql/schema.sql`

3. (Optional) Import seed data:
   ```bash
   mysql -u username -p database_name < sql/seeds.sql
   ```

### Step 4: Set File Permissions

For Linux/Unix hosting:

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# If you need writable directories (for uploads/logs)
chmod 775 assets/  # If needed for image uploads
chmod 775 includes/  # If needed for logs
```

For shared hosting (cPanel), permissions are usually set automatically, but check:
- Files: 644
- Directories: 755

### Step 5: Configure Web Server

#### Apache (.htaccess - Already Included)

The `.htaccess` file is already configured. Just ensure:
- mod_rewrite is enabled
- `.htaccess` files are allowed

To enable mod_rewrite (if you have SSH access):
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

#### Nginx Configuration

If using Nginx, add this to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location /api/v1/ {
    try_files $uri $uri/ /api/v1/index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

### Step 6: Test Your Installation

1. **Test Public Portal**
   - Visit: `https://yourdomain.com/public/`
   - Should show the home page without errors

2. **Test Admin Login**
   - Visit: `https://yourdomain.com/admin/login.php`
   - Login with your admin credentials

3. **Test API**
   - Visit: `https://yourdomain.com/api/v1/matches`
   - Should return JSON data

4. **Check Error Logs**
   - Look for PHP errors in:
     - cPanel Error Log
     - `/var/log/apache2/error.log` (if you have access)
     - Your hosting provider's error log location

## 🔧 Configuration Options

### Installation in Subdirectory

If installing in a subdirectory (e.g., `yoursite.com/cricapp/`):

1. The app will **automatically detect** the subdirectory
2. No additional configuration needed
3. If auto-detection fails, manually set in `config.local.php`:
   ```php
   define('APP_BASE_PATH', '/cricapp');
   ```

### Installation in Root Directory

If installing in root (e.g., `yoursite.com/`):

1. Upload files to `public_html/` (or your document root)
2. The app will **automatically detect** it's in root
3. No additional configuration needed

### Custom Domain/Subdomain

The app automatically detects:
- Protocol (HTTP/HTTPS)
- Domain name
- Installation path

No manual configuration needed unless you have special requirements.

## 🔒 Security Checklist

Before going live:

- [ ] Change default admin password
- [ ] Set a strong JWT_SECRET (32+ random characters)
- [ ] Enable HTTPS/SSL certificate
- [ ] Set `display_errors = 0` in production
- [ ] Set proper file permissions
- [ ] Remove or protect `config.local.php` from public access
- [ ] Check `.htaccess` security rules are active
- [ ] Review CORS settings if using API from different domains

## 🐛 Troubleshooting

### "Database connection failed"

**Solution:**
1. Check database credentials in `config.local.php`
2. Verify database user has proper permissions
3. Check if database host is correct (sometimes it's `127.0.0.1` instead of `localhost`)
4. Verify database name exists

### "404 Not Found" on API endpoints

**Solution:**
1. Check if `.htaccess` is being processed
2. Verify mod_rewrite is enabled
3. Check file permissions (should be 644)
4. Try accessing directly: `yoursite.com/api/v1/matches.php`

### CSS/JS files not loading

**Solution:**
1. Check if `APP_BASE_PATH` is detected correctly
2. View page source and check if paths are correct
3. Clear browser cache
4. Check file permissions on `assets/` directory

### "APP_URL not defined" errors

**Solution:**
1. The app should auto-detect, but if it doesn't, manually set in `config.local.php`:
   ```php
   define('APP_URL', 'https://yourdomain.com');
   define('APP_BASE_PATH', '');
   ```

### Paths show `/cricapp/` on production site

**Solution:**
1. Clear browser cache
2. Check if `config.local.php` is being loaded
3. Verify file exists: `includes/config.local.php`
4. Check file permissions

## 📝 After Deployment

1. **Change Default Credentials**
   - Login to admin panel
   - Change default admin password immediately

2. **Test All Features**
   - Create a test match
   - Test scoring
   - Test API endpoints
   - Test public portal

3. **Monitor Error Logs**
   - Check error logs regularly
   - Fix any issues promptly

4. **Set Up Backups**
   - Database backups (daily recommended)
   - File backups (weekly recommended)

## 🆘 Getting Help

If you encounter issues:

1. Check error logs first
2. Verify all requirements are met
3. Check file permissions
4. Verify database connection
5. Review `.htaccess` configuration

## 📦 Files That Changed

The following files were updated for hosting compatibility:

- `includes/config.php` - Dynamic path detection
- `includes/utils.php` - URL helper functions
- `.htaccess` - Flexible RewriteBase
- `admin/login.php` - Dynamic URLs
- `public/index.php` - Dynamic asset paths
- `public/index-vue.php` - Dynamic URLs
- `assets/js/api.js` - Dynamic API base URL
- `assets/js/vue-app.js` - Dynamic paths
- `assets/js/config.js` - JavaScript config helper (new)

All hardcoded `/cricapp/` paths have been replaced with dynamic functions.

## ✅ Summary

Your application is now **hosting-ready**! The main changes:

1. ✅ **Auto-detects installation path** (root or subdirectory)
2. ✅ **Auto-detects production environment**
3. ✅ **All URLs are dynamic** - no hardcoded paths
4. ✅ **Error reporting disabled in production**
5. ✅ **JavaScript paths work dynamically**

Just upload files, create `config.local.php`, import database, and you're done!


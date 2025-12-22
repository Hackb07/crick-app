# Deployment Guide - Core PHP Website

This application is a **pure PHP core website** with no external production dependencies.

## Production Deployment

### What You Need

1. **PHP 7.4+** with PDO MySQL extension
2. **MySQL 5.7+** or MariaDB 10.2+
3. **Apache** with mod_rewrite (or Nginx with rewrite rules)

### What You DON'T Need

- ❌ **Composer** - Not required for production
- ❌ **Vendor directory** - Can be excluded from deployment
- ❌ **External libraries** - All code is self-contained

## Deployment Steps

### 1. Upload Files

Upload all files **EXCEPT** the `vendor/` directory:

```
cricapp/
├── api/
├── admin/
├── public/
├── classes/
├── includes/
├── assets/
├── sql/
├── cron/
├── Spec/
├── tests/          # Optional - exclude for production
└── vendor/          # EXCLUDE - only for testing
```

### 2. Configure Database

Update `includes/config.php` or create `includes/config.local.php`:

```php
<?php
define('DB_HOST', 'your_host');
define('DB_NAME', 'cricapp');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('JWT_SECRET', 'your-secret-key');
```

### 3. Import Database Schema

```bash
mysql -u username -p cricapp < sql/schema.sql
mysql -u username -p cricapp < sql/seeds.sql
```

### 4. Set Permissions

```bash
chmod -R 755 cricapp/
chmod -R 775 cricapp/assets/  # If uploads needed
```

### 5. Configure Web Server

#### Apache (.htaccess already included)

Ensure mod_rewrite is enabled:

```bash
a2enmod rewrite
service apache2 restart
```

#### Nginx

Add rewrite rules to your server block:

```nginx
location /cricapp {
    try_files $uri $uri/ /cricapp/index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

## Core PHP Architecture

### Bootstrap System

All entry points use `includes/bootstrap.php`:

```php
<?php
// Load core PHP bootstrap (no vendor/ dependencies)
require_once __DIR__ . '/../includes/bootstrap.php';
```

The bootstrap file automatically loads:
- Custom autoloader
- Configuration
- Database connection
- Utility functions
- Middleware

### Custom Autoloader

Classes are automatically loaded from:
- `includes/` - Core infrastructure
- `classes/` - Model classes

No manual `require_once` needed for classes!

### File Structure

```
includes/
├── autoloader.php    # Custom autoloader (replaces Composer)
├── bootstrap.php     # Single entry point
├── config.php        # Configuration
├── db.php           # Database singleton
├── utils.php        # Utility functions
└── middleware.php   # Auth middleware

classes/
├── Database.php     # DatabaseModel base class
├── User.php
├── Match.php
├── Event.php
└── ... (all other models)
```

## Development vs Production

### Development

If you want to run tests:

```bash
composer install  # Installs PHPUnit
vendor/bin/phpunit
```

### Production

```bash
# Exclude vendor/ from deployment
rsync -av --exclude 'vendor/' --exclude 'tests/' ./ user@server:/var/www/cricapp/
```

Or use `.gitignore` to exclude `vendor/` from version control.

## Verification

After deployment, verify:

1. ✅ Visit `http://yoursite.com/cricapp/` - Should show public portal
2. ✅ Visit `http://yoursite.com/cricapp/admin/` - Should show login page
3. ✅ Check API: `curl http://yoursite.com/cricapp/api/v1/matches` - Should return JSON

## Troubleshooting

### "Class not found" errors

Ensure `includes/autoloader.php` is loaded via `includes/bootstrap.php`.

### Database connection errors

Check `includes/config.php` or `includes/config.local.php` credentials.

### 404 errors on API endpoints

Verify `.htaccess` is working and mod_rewrite is enabled.

### Autoloader not working

Check that `spl_autoload_register` is called before class instantiation.


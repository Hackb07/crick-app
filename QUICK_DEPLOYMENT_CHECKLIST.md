# Quick Deployment Checklist

## ✅ Pre-Deployment

- [ ] All files uploaded to hosting server
- [ ] Database created on hosting provider
- [ ] Database credentials obtained

## ⚙️ Configuration

1. **Create `includes/config.local.php`**:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');

// JWT Secret - CHANGE THIS!
define('JWT_SECRET', 'your-strong-random-secret-key-here');
```

2. **Import Database**:
   - Import `sql/schema.sql`
   - (Optional) Import `sql/seeds.sql`

3. **Set File Permissions**:
   - Directories: 755
   - Files: 644

## ✅ Post-Deployment

- [ ] Visit your site - should load without errors
- [ ] Test admin login - change default password
- [ ] Test API endpoint - `/api/v1/matches`
- [ ] Check error logs for any issues

## 🎯 That's It!

The application will **automatically detect**:
- Installation path (root or subdirectory)
- Production vs development environment
- HTTPS vs HTTP
- Base URLs

No manual path configuration needed!

## 📖 Full Guide

See `HOSTING_DEPLOYMENT_GUIDE.md` for detailed instructions.


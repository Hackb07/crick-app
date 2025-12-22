<?php
/**
 * Local Configuration File
 * 
 * Database and application configuration for your local environment.
 * This file is excluded from version control.
 */

// Database Configuration (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cricapp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// JWT Configuration
// ⚠️ CHANGE THIS SECRET KEY IN PRODUCTION!
define('JWT_SECRET', 'cricapp-secret-key-change-this-in-production-' . hash('sha256', __DIR__));
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 3600); // 1 hour
define('JWT_REFRESH_EXPIRY', 86400); // 24 hours

// Base Path Configuration (Optional - auto-detected if not set)
// Uncomment and set if auto-detection doesn't work:
define('APP_BASE_PATH_MANUAL', '/cricapp');  // For subdirectory: /cricapp
// define('APP_BASE_PATH_MANUAL', '');          // For root installation: ''

// Debug Mode (set to false in production)
define('DEBUG_MODE', true);

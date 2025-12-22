<?php
/**
 * Bootstrap File - Single Entry Point for All Includes
 * 
 * Include this file at the start of every PHP script
 */

// Define APP_INIT to prevent direct access to config
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Load configuration
require_once __DIR__ . '/config.php';

// Load autoloader
require_once __DIR__ . '/autoloader.php';

// Load database
// Database is autoloaded and initialized on demand
// require_once __DIR__ . '/db.php';

// Load session management (must be loaded before utilities that use session)
require_once __DIR__ . '/session.php';

// Load utilities
require_once __DIR__ . '/utils.php';

// Load action logger helper
require_once __DIR__ . '/action-logger-helper.php';

// Load match score helper
require_once __DIR__ . '/match-score-helper.php';

// Load security headers (must be loaded early, before any output)
require_once __DIR__ . '/security-headers.php';

// Load cricket match helper functions
require_once __DIR__ . '/cricket-match-helpers.php';

// Load error handling helpers
require_once __DIR__ . '/error-handler.php';

// Load development configuration (disables caching on localhost)
require_once __DIR__ . '/dev-config.php';

// Load security helpers (authentication, authorization)
require_once __DIR__ . '/security.php';

// Load layout helpers (admin and public layouts)
require_once __DIR__ . '/layout/admin-layout.php';

// Load admin sidebar
require_once __DIR__ . '/../admin/includes/sidebar.php';

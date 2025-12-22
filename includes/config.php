<?php
/**
 * Base Configuration File
 * 
 * This file contains base configuration constants.
 * Local overrides should be in config.local.php
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    die('Direct access not allowed');
}

// Auto-detect base path
function detectBasePath() {
    // Method 1: Check if APP_BASE_PATH is manually set in config.local.php
    if (defined('APP_BASE_PATH_MANUAL')) {
        return APP_BASE_PATH_MANUAL;
    }
    
    // Method 2: Use SCRIPT_NAME to detect base path (most reliable)
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Get the directory of the script
    $scriptDir = dirname($scriptName);
    
    // Normalize path separators
    $scriptDir = str_replace('\\', '/', $scriptDir);
    
    // Remove leading slash for processing
    $scriptDir = ltrim($scriptDir, '/');
    
    // If script is directly in root (like /index.php), scriptDir will be '.' or empty
    if ($scriptDir === '' || $scriptDir === '.') {
        // Check REQUEST_URI to see if we're in a subdirectory
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $requestUri = strtok($requestUri, '?'); // Remove query string
        $requestUri = str_replace('\\', '/', $requestUri);
        
        // Look for known app directories in the path
        $knownDirs = ['public', 'admin', 'api'];
        foreach ($knownDirs as $dir) {
            $pos = strpos($requestUri, '/' . $dir);
            if ($pos !== false && $pos > 0) {
                // Extract base path up to the known directory
                $basePath = substr($requestUri, 0, $pos);
                return $basePath;
            }
        }
        
        // If no known directories found, check if SCRIPT_NAME contains subdirectory
        $scriptParts = explode('/', trim($scriptName, '/'));
        if (count($scriptParts) > 1) {
            // Script is in a subdirectory, extract it
            array_pop($scriptParts); // Remove filename
            $basePath = '/' . implode('/', $scriptParts);
            return $basePath;
        }
        
        return '';
    }
    
    // Script is in a subdirectory
    // Extract base path by going up from script directory
    $parts = explode('/', $scriptDir);
    
    // Remove the last part (public, admin, api, etc.)
    if (count($parts) > 1) {
        array_pop($parts);
        $basePath = '/' . implode('/', $parts);
        return $basePath;
    }
    
    // If script is directly in a subdirectory (like /cricapp/index.php)
    return '/' . $scriptDir;
}

// Load local config FIRST (before defining APP_BASE_PATH)
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// Application Configuration
define('APP_NAME', 'Cricket Scoring App');
define('APP_VERSION', '1.0');
define('APP_BASE_PATH', detectBasePath());

// Auto-detect APP_URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = APP_BASE_PATH;
define('APP_URL', $protocol . '://' . $host . $basePath);

// Database Configuration (defaults - override in config.local.php)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'cricapp');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// JWT Configuration (defaults - override in config.local.php)
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'change-this-secret-key-in-production');
}
if (!defined('JWT_ALGORITHM')) {
    define('JWT_ALGORITHM', 'HS256');
}
if (!defined('JWT_EXPIRY')) {
    define('JWT_EXPIRY', 3600); // 1 hour
}
if (!defined('JWT_REFRESH_EXPIRY')) {
    define('JWT_REFRESH_EXPIRY', 86400); // 24 hours
}

// Rate Limiting Configuration
if (!defined('RATE_LIMIT_ANONYMOUS_PER_MIN')) {
    define('RATE_LIMIT_ANONYMOUS_PER_MIN', 10);
}
if (!defined('RATE_LIMIT_ANONYMOUS_PER_HOUR')) {
    define('RATE_LIMIT_ANONYMOUS_PER_HOUR', 100);
}
if (!defined('RATE_LIMIT_AUTH_PER_MIN')) {
    define('RATE_LIMIT_AUTH_PER_MIN', 100);
}
if (!defined('RATE_LIMIT_AUTH_PER_HOUR')) {
    define('RATE_LIMIT_AUTH_PER_HOUR', 1000);
}

// POTM Configuration
if (!defined('POTM_OVERS_NORM')) {
    define('POTM_OVERS_NORM', 6.0);
}
if (!defined('POTM_WEIGHT_RUNS')) {
    define('POTM_WEIGHT_RUNS', 0.6);
}
if (!defined('POTM_WEIGHT_WICKETS')) {
    define('POTM_WEIGHT_WICKETS', 0.8);
}
if (!defined('POTM_WEIGHT_FIELDING')) {
    define('POTM_WEIGHT_FIELDING', 0.3);
}

// Statistics Recompute Configuration
if (!defined('STATS_RECOMPUTE_CHUNK_SIZE')) {
    define('STATS_RECOMPUTE_CHUNK_SIZE', 100);
}
if (!defined('STATS_RECOMPUTE_HEARTBEAT_INTERVAL')) {
    define('STATS_RECOMPUTE_HEARTBEAT_INTERVAL', 10); // seconds
}
if (!defined('STATS_RECOMPUTE_STALE_THRESHOLD')) {
    define('STATS_RECOMPUTE_STALE_THRESHOLD', 60); // seconds
}

// Error Reporting
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('UTC');


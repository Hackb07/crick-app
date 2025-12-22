<?php
/**
 * Development Configuration
 * Disables caching for easier development
 * 
 * @package CricApp
 * @version 1.0.0
 */

// ============================================
// DISABLE ALL CACHING FOR DEVELOPMENT
// ============================================

/**
 * Disable browser caching
 * Forces browser to always fetch fresh content
 */
function disableCaching() {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

/**
 * Disable PHP OpCache for this request
 * Ensures latest PHP code is always executed
 */
function disableOpCache() {
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate(__FILE__, true);
    }
}

/**
 * Get asset URL without cache-busting
 * Simple URL for development
 * 
 * @param string $path Asset path
 * @return string Full asset URL
 */
function assetUrlNoCaching($path) {
    return assetUrl($path);
}

// ============================================
// AUTO-APPLY IN DEVELOPMENT MODE
// ============================================

/**
 * Check if we're in development mode
 * Based on environment or domain
 */
function isDevelopmentMode() {
    // Check if running on localhost
    $isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? '', [
        'localhost',
        '127.0.0.1',
        '::1'
    ]);
    
    // Check if ENVIRONMENT constant is set to 'development'
    $isDev = defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    
    return $isLocalhost || $isDev;
}

// Auto-disable caching in development
if (isDevelopmentMode()) {
    disableCaching();
}

<?php
/**
 * Security Headers Middleware
 * 
 * Implements security headers for all HTTP responses
 * Call this early in bootstrap.php or at the start of each page
 */

/**
 * Set cache prevention headers for all dynamic pages
 * CRITICAL for cricket scoring app - scores must always be fresh
 */
function setCachePreventionHeaders() {
    // Aggressive cache prevention - critical for real-time cricket scores
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('ETag: "' . hash('sha256', time() . rand()) . '"');
}

/**
 * Set security headers for all responses
 */
function setSecurityHeaders() {
    // CRITICAL: Prevent caching for cricket scoring app
    // Scores must always be fresh - no cached data allowed
    setCachePreventionHeaders();
    
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection (legacy browsers)
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy - only send referrer for same-origin requests
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    // Adjusted to allow Google Fonts for Inter font family
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // 'unsafe-inline' needed for inline scripts, consider removing in production
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com", // Allow Google Fonts stylesheets
        "img-src 'self' data: https:",
        "font-src 'self' data: https://fonts.gstatic.com", // Allow Google Fonts font files
        "connect-src 'self'",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "upgrade-insecure-requests" // Upgrade HTTP to HTTPS (only if using HTTPS)
    ];
    
    // Only add upgrade-insecure-requests if using HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    if ($isHttps) {
        // Keep upgrade-insecure-requests
    } else {
        // Remove upgrade-insecure-requests for HTTP
        $csp = array_filter($csp, function($directive) {
            return $directive !== 'upgrade-insecure-requests';
        });
    }
    
    header('Content-Security-Policy: ' . implode('; ', $csp));
    
    // Strict Transport Security (HSTS) - only for HTTPS
    if ($isHttps) {
        // 1 year HSTS with includeSubDomains
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    // Permissions Policy (formerly Feature Policy)
    $permissionsPolicy = [
        'geolocation=()',
        'microphone=()',
        'camera=()',
        'payment=()',
        'usb=()'
    ];
    header('Permissions-Policy: ' . implode(', ', $permissionsPolicy));
}

/**
 * Set CORS headers for API endpoints
 * 
 * @param array $allowedOrigins Allowed origins (default: same origin only)
 * @param array $allowedMethods Allowed HTTP methods
 * @param array $allowedHeaders Allowed headers
 */
function setCorsHeaders($allowedOrigins = [], $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'], $allowedHeaders = ['Content-Type', 'Authorization']) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // If no allowed origins specified, only allow same origin
    if (empty($allowedOrigins)) {
        $allowedOrigins = [APP_URL];
    }
    
    // Check if origin is allowed
    if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Allow browser caching for static/completed content
 * Overrides the default no-cache headers
 * 
 * @param int $maxAge Cache duration in seconds (default 1 hour)
 */
function allowBrowserCaching($maxAge = 3600) {
    header_remove('Pragma');
    header_remove('Expires');
    
    header("Cache-Control: public, max-age=$maxAge");
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");
    
    // Remove ETag as it's generated randomly in setCachePreventionHeaders
    header_remove('ETag');
}

// Auto-set security headers if not already set
if (!headers_sent()) {
    setSecurityHeaders();
}


<?php
/**
 * CSRF Protection
 */

/**
 * Generate CSRF token
 * 
 * @return string Token
 */
function generateCsrfToken() {
    if (!hasSession('csrf_token')) {
        setSession('csrf_token', bin2hex(random_bytes(32)));
    }
    
    return getSession('csrf_token');
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool Is valid
 */
function validateCsrfToken($token) {
    $sessionToken = getSession('csrf_token');
    
    if (empty($sessionToken)) {
        return false;
    }
    
    return hash_equals($sessionToken, $token);
}

/**
 * Get CSRF token input field
 * 
 * @return string HTML input field
 */
function csrfInput() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Require CSRF token validation for POST/PUT/DELETE requests
 * 
 * @param string $token CSRF token from request
 * @return bool True if valid, false otherwise
 * @throws Exception If token is invalid
 */
function requireCsrfToken($token = null) {
    // Get token from request
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }
    
    if (empty($token)) {
        throw new Exception('CSRF token is required');
    }
    
    if (!validateCsrfToken($token)) {
        throw new Exception('Invalid CSRF token');
    }
    
    return true;
}

/**
 * Validate CSRF token for API requests (returns JSON error on failure)
 * 
 * @param string $token CSRF token from request
 * @return bool True if valid
 */
function requireCsrfTokenApi($token = null) {
    try {
        requireCsrfToken($token);
        return true;
    } catch (Exception $e) {
        jsonResponse([
            'success' => false,
            'error' => 'CSRF token validation failed',
            'message' => $e->getMessage()
        ], 403);
        return false;
    }
}


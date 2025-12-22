<?php
/**
 * Authentication API
 * 
 * Endpoints: /api/v1/auth/login, /api/v1/auth/refresh, /api/v1/auth/logout
 */

// Include error wrapper first
require_once __DIR__ . '/../../api-error-wrapper.php';

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Wrap in try-catch for error handling
try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Priority 1: Check query parameter first
    if (isset($_GET['path'])) {
        $path = $_GET['path'];
    } else {
        // Priority 2: Use helper
        $path = getApiPath(); 
    }
} catch (Exception $e) {
    error_log('API Error in auth.php (initialization): ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
}

// Route handling
if ($method === 'POST' && $path === '/login') {
    try {
        // Rate limiting
        $identifier = getClientIdentifier();
        applyRateLimit($identifier, '/auth/login', 'POST');
        
        $data = getJsonBody();
        
        if (!$data || empty($data['username']) || empty($data['password'])) {
            jsonResponse(['success' => false, 'error' => 'Username and password required'], 400);
        }
        
        $userModel = new User();
        $user = $userModel->authenticate($data['username'], $data['password']);
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'Invalid credentials'], 401);
        }
        
        // Generate JWT token
        $payload = JWT::createPayload($user['user_id'], $user['role'], JWT_EXPIRY);
        $token = JWT::encode($payload);
        
        // Generate refresh token
        $refreshPayload = JWT::createPayload($user['user_id'], $user['role'], JWT_REFRESH_EXPIRY);
        $refreshToken = JWT::encode($refreshPayload);
        
        jsonResponse([
            'success' => true,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $user
        ]);
    } catch (Exception $e) {
        error_log('API Error in auth.php (login): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
    }
    
} elseif ($method === 'POST' && $path === '/refresh') {
    // Rate limiting
    $user = requireAuth();
    $identifier = getClientIdentifier($user);
    applyRateLimit($identifier, '/auth/refresh', 'POST');
    
    // Generate new token
    $payload = JWT::createPayload($user['user_id'], $user['role'], JWT_EXPIRY);
    $token = JWT::encode($payload);
    
    jsonResponse([
        'success' => true,
        'token' => $token
    ]);
    
} elseif ($method === 'POST' && $path === '/logout') {
    // Rate limiting
    $user = requireAuth();
    $identifier = getClientIdentifier($user);
    applyRateLimit($identifier, '/auth/logout', 'POST');
    
    // Client-side token discard (stateless JWT)
    jsonResponse(['success' => true, 'message' => 'Logged out']);
    
} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}


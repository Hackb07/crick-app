<?php
/**
 * API Middleware
 * 
 * Authentication, authorization, and rate limiting
 */

/**
 * Get authenticated user from JWT token
 * 
 * @return array|null User data or null
 */
function getAuthenticatedUser() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (empty($authHeader)) {
        return null;
    }
    
    // Extract token from "Bearer <token>"
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        return null;
    }
    
    $payload = JWT::decode($token);
    
    if (!$payload) {
        return null;
    }
    
    $userModel = new User();
    return $userModel->getById($payload['user_id']);
}

/**
 * Require authentication
 * 
 * @return array User data
 */
function requireAuth() {
    $user = getAuthenticatedUser();
    
    if (!$user) {
        jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
    }
    
    return $user;
}

/**
 * Require specific role(s)
 * 
 * @param string|array $requiredRole Required role(s)
 * @return array User data
 */
function requireRole($requiredRole) {
    $user = requireAuth();
    
    // Check if User class exists before calling static method
    if (class_exists('User')) {
        if (!User::hasRole($user, $requiredRole)) {
            jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
        }
    } else {
        // Fallback: check role directly
        if (!isset($user['role'])) {
            jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
        }
        
        $hasRole = is_array($requiredRole) 
            ? in_array($user['role'], $requiredRole)
            : $user['role'] === $requiredRole;
            
        if (!$hasRole) {
            jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
        }
    }
    
    return $user;
}

/**
 * Apply rate limiting
 * 
 * @param string $identifier User ID or IP address
 * @param string $endpoint API endpoint
 * @param string $method HTTP method
 */
function applyRateLimit($identifier, $endpoint, $method = 'GET') {
    $rateLimiter = new RateLimiter();
    $result = $rateLimiter->check($identifier, $endpoint, $method);
    
    if (!$result['allowed']) {
        header('X-RateLimit-Limit: ' . $result['limit']);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . $result['reset']);
        jsonResponse([
            'success' => false,
            'error' => 'Rate limit exceeded',
            'reset_at' => $result['reset']
        ], 429);
    }
    
    // Set rate limit headers
    header('X-RateLimit-Limit: ' . $result['limit']);
    header('X-RateLimit-Remaining: ' . $result['remaining']);
    header('X-RateLimit-Reset: ' . $result['reset']);
}

/**
 * Get client identifier (user ID or IP)
 * 
 * @param array|null $user Authenticated user
 * @return string Identifier
 */
function getClientIdentifier($user = null) {
    if ($user && isset($user['user_id'])) {
        return (string)$user['user_id'];
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}


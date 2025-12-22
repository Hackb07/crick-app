<?php
/**
 * Users API
 * 
 * Endpoints: /api/v1/users
 */

// Include error wrapper first
require_once __DIR__ . '/../../api-error-wrapper.php';

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/validation.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = getApiPath(); // Use helper function with PATH_INFO fallback
$pathParts = explode('/', trim($path, '/'));

// Require admin role for all endpoints
$user = requireRole('admin');

// Rate limiting
$identifier = getClientIdentifier($user);
applyRateLimit($identifier, '/users', $method);

$userModel = new User();

// GET /api/v1/users - List users
if ($method === 'GET' && empty($path)) {
    $role = getQuery('role');
    $isActive = getQuery('is_active');
    
    $filters = [];
    if ($role) {
        $filters['role'] = $role;
    }
    if ($isActive !== null) {
        $filters['is_active'] = (int)$isActive;
    }
    
    $users = $userModel->getAll($filters);
    
    jsonResponse(['success' => true, 'data' => $users]);
    
// GET /api/v1/users/{id} - Get user
} elseif ($method === 'GET' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    $userId = (int)$pathParts[0];
    $userData = $userModel->getById($userId);
    
    if (!$userData) {
        jsonResponse(['success' => false, 'error' => 'User not found'], 404);
    }
    
    jsonResponse(['success' => true, 'data' => $userData]);
    
// POST /api/v1/users - Create user
} elseif ($method === 'POST' && empty($path)) {
    $data = getJsonBody();
    
    $errors = validateRequired($data, ['username', 'password', 'role']);
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 400);
    }
    
    // Validate role
    if (!in_array($data['role'], ['admin', 'scorer', 'user'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid role'], 400);
    }
    
    // Check if username exists
    if ($userModel->getByUsername($data['username'])) {
        jsonResponse(['success' => false, 'error' => 'Username already exists'], 400);
    }
    
    $userId = $userModel->create($data);
    
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'Failed to create user'], 500);
    }
    
    $userData = $userModel->getById($userId);
    jsonResponse(['success' => true, 'data' => $userData], 201);
    
// PUT /api/v1/users/{id} - Update user
} elseif ($method === 'PUT' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    $userId = (int)$pathParts[0];
    $data = getJsonBody();
    
    // Validate role if provided
    if (isset($data['role']) && !in_array($data['role'], ['admin', 'scorer', 'user'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid role'], 400);
    }
    
    // Prevent deleting own account
    if (function_exists('getUserId') && getUserId() == $userId && isset($data['is_active']) && $data['is_active'] == 0) {
        jsonResponse(['success' => false, 'error' => 'Cannot deactivate own account'], 400);
    }
    
    $result = $userModel->update($userId, $data);
    
    if (!$result) {
        jsonResponse(['success' => false, 'error' => 'Failed to update user'], 500);
    }
    
    $userData = $userModel->getById($userId);
    jsonResponse(['success' => true, 'data' => $userData]);
    
// DELETE /api/v1/users/{id} - Delete user
} elseif ($method === 'DELETE' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    $userId = (int)$pathParts[0];
    
    // Prevent deleting yourself
    if ($userId == $user['user_id']) {
        jsonResponse(['success' => false, 'error' => 'Cannot delete your own account'], 400);
    }
    
    $result = $userModel->delete($userId);
    
    if (!$result) {
        jsonResponse(['success' => false, 'error' => 'Failed to delete user or account protection active'], 500);
    }
    
    jsonResponse(['success' => true]);
    
} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}


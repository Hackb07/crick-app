<?php
/**
 * Players API
 * 
 * Endpoints: /api/v1/players
 */

// Include error wrapper first
require_once __DIR__ . '/../../api-error-wrapper.php';

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/validation.php';

header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Use Container for dependency injection
$container = Container::getInstance();
$playerModel = $container->get('Player');

// Initialize request variables
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getApiPath();
    $pathParts = explode('/', trim($path, '/'));

    // Rate limiting
    $user = getAuthenticatedUser();
    $identifier = getClientIdentifier($user);
    applyRateLimit($identifier, '/players', $method);
} catch (Exception $e) {
    handleApiError($e, 500, ['endpoint' => '/api/v1/players', 'action' => 'initialization']);
}

// GET /api/v1/players - List players
if ($method === 'GET' && empty($path)) {
    withErrorHandling(function() use ($playerModel) {
        $players = $playerModel->getAll();
        jsonResponse(['success' => true, 'data' => $players]);
    }, ['endpoint' => '/api/v1/players', 'action' => 'GET list']);
    
// GET /api/v1/players/{id} - Get player
} elseif ($method === 'GET' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    withErrorHandling(function() use ($playerModel, $pathParts) {
        $playerId = (int)$pathParts[0];
        $player = $playerModel->getById($playerId);
        
        if (!$player) {
            throw new NotFoundException('Player not found');
        }
        
        jsonResponse(['success' => true, 'data' => $player]);
    }, ['endpoint' => '/api/v1/players', 'action' => 'GET by id']);
    
// POST /api/v1/players - Create player
} elseif ($method === 'POST' && empty($path)) {
    withErrorHandling(function() use ($playerModel) {
        $user = requireRole(['admin', 'scorer']);
        
        $data = getJsonBody();
        
        $errors = validateRequired($data, ['name']);
        if (!empty($errors)) {
            handleValidationError($errors);
        }
        
        $playerId = $playerModel->create($data);
        
        if (!$playerId) {
            throw new Exception('Failed to create player');
        }
        
        $player = $playerModel->getById($playerId);
        jsonResponse(['success' => true, 'data' => $player], 201);
    }, ['endpoint' => '/api/v1/players', 'action' => 'POST create']);
    
// PUT /api/v1/players/{id} - Update player
} elseif ($method === 'PUT' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    withErrorHandling(function() use ($playerModel, $pathParts) {
        $user = requireRole(['admin', 'scorer']);
        
        $playerId = (int)$pathParts[0];
        $data = getJsonBody();
        
        $result = $playerModel->update($playerId, $data);
        
        if (!$result) {
            throw new Exception('Failed to update player');
        }
        
        $player = $playerModel->getById($playerId);
        jsonResponse(['success' => true, 'data' => $player]);
    }, ['endpoint' => '/api/v1/players', 'action' => 'PUT update']);
    
// DELETE /api/v1/players/{id} - Delete player
} elseif ($method === 'DELETE' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    withErrorHandling(function() use ($playerModel, $pathParts) {
        $user = requireRole(['admin']);
        
        $playerId = (int)$pathParts[0];
        $result = $playerModel->delete($playerId);
        
        if (!$result) {
            throw new Exception('Failed to delete player');
        }
        
        jsonResponse(['success' => true]);
    }, ['endpoint' => '/api/v1/players', 'action' => 'DELETE']);
    
} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}


<?php
/**
 * Admin API
 * 
 * Admin-only endpoints
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
applyRateLimit($identifier, '/admin', $method);

$db = Database::getInstance()->getConnection();

// GET /api/v1/admin/logs - Get admin action logs
if ($method === 'GET' && $path === '/logs') {
    $limit = (int)getQuery('limit', 50);
    
    $sql = "SELECT al.*, u.username as admin_username
            FROM admin_action_logs al
            LEFT JOIN users u ON al.admin_id = u.user_id
            ORDER BY al.timestamp DESC
            LIMIT :limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $logs = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $logs]);
    
// POST /api/v1/admin/potm/override - Override POTM decision
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[0] === 'potm' && $pathParts[1] === 'override') {
    $data = getJsonBody();
    
    $errors = validateRequired($data, ['match_id', 'player_id']);
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 400);
    }
    
    $potm = new POTM();
    $result = $potm->saveDecision(
        $data['match_id'],
        $data['player_id'], // computed
        $data['player_id'], // final (override)
        $data['reason'] ?? 'Admin override',
        $user['user_id']
    );
    
    if (!$result) {
        jsonResponse(['success' => false, 'error' => 'Failed to override POTM'], 500);
    }
    
    jsonResponse(['success' => true]);
    
// POST /api/v1/admin/pots/override - Override POTS points
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[0] === 'pots' && $pathParts[1] === 'override') {
    $data = getJsonBody();
    
    $errors = validateRequired($data, ['series_id', 'player_id', 'points']);
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 400);
    }
    
    $sql = "INSERT INTO pots_aggregate (series_id, player_id, total_points, updated_at) 
            VALUES (:series_id, :player_id, :total_points, NOW())
            ON DUPLICATE KEY UPDATE total_points = :total_points, updated_at = NOW()";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        'series_id' => $data['series_id'],
        'player_id' => $data['player_id'],
        'total_points' => $data['points']
    ]);
    
    if (!$result) {
        jsonResponse(['success' => false, 'error' => 'Failed to override POTS'], 500);
    }
    
    jsonResponse(['success' => true]);
    
} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}


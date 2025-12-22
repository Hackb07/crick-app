<?php
/**
 * Teams API
 * 
 * Endpoints: /api/v1/teams
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance()->getConnection();

// Helper to get path info if rate limiting logic needs it (omitted for brevity)

// GET /api/v1/teams - List all teams
if ($method === 'GET') {
    try {
        $teamModel = new Team($db);
        $teams = $teamModel->getAll();
        jsonResponse(['success' => true, 'data' => $teams]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }

// POST /api/v1/teams - Create team
} elseif ($method === 'POST') {
    try {
        requireRole(['admin']);
        $data = getJsonBody();
        
        $teamModel = new Team($db);
        $teamId = $teamModel->create($data);

        if (!$teamId) {
            throw new Exception('Failed to create team');
        }

        jsonResponse(['success' => true, 'id' => $teamId]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

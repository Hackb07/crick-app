<?php
/**
 * Options API
 * Returns lists of teams, series, etc. for dropdowns
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

try {
    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method not allowed');
    }

    $db = Database::getInstance()->getConnection();
    $type = getQuery('type');
    $data = [];

    if ($type === 'teams' || $type === 'all') {
        $teamModel = new Team($db);
        $data['teams'] = $teamModel->getAll();
    }

    if ($type === 'series' || $type === 'all') {
        $seriesModel = new Series($db);
        $data['series'] = $seriesModel->getAll();
    }
    
    if ($type === 'venues' || $type === 'all') {
         $matchModel = new MatchModel($db);
         $data['venues'] = $matchModel->getUniqueVenues();
    }

    jsonResponse(['success' => true, 'data' => $type === 'all' ? $data : ($data[$type] ?? [])]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}

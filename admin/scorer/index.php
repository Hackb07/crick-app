<?php
/**
 * Scorer Dashboard - Light Version
 * Mobile-optimized dashboard for scorers
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

// Require scorer login
if (!isLoggedIn() || getSession('role') !== 'scorer') {
    header('Location: ' . adminUrl('scorer-login.php'));
    exit;
}

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLive();
$allMatches = $matchModel->getAll();
$recentMatches = array_slice($allMatches, 0, 10);

// Render using standard layout with header and sidebar
renderAdminLayout('Scorer Dashboard', 'scorer/index', [
    'liveMatches' => $liveMatches,
    'recentMatches' => $recentMatches
], [
    'sidebar' => true,
    'header' => true,
    'bodyClass' => 'scorer-dashboard',
    'activeMenu' => 'scorer'
]);

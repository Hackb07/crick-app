<?php
/**
 * Admin Dashboard
 * 
 * Main admin dashboard showing statistics and recent matches
 * 
 * @package    CricApp
 * @subpackage Admin
 * @security   Login required
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

// Security: Require login (any authenticated user can access dashboard)
requireLogin();

// Get data
$matchModel = new MatchModel();
$liveMatches = $matchModel->getLive();
$allMatches = $matchModel->getAll();
$recentMatches = array_slice($allMatches, 0, 5);

// Calculate stats
$totalMatches = count($allMatches);
$liveCount = count($liveMatches);
$completedCount = count(array_filter($allMatches, function($m) { 
    return $m['state'] === 'completed'; 
}));

$playerModel = new Player();
$totalPlayers = count($playerModel->getAll());

$teamModel = new Team();
$totalTeams = count($teamModel->getAll());

// Get user info
$username = getCurrentUsername() ?? 'Admin';
$userRole = getSession('role', 'admin');

// Render
renderAdminLayout('Dashboard', 'dashboard', [
    'username' => $username,
    'userRole' => $userRole,
    'totalMatches' => $totalMatches,
    'liveCount' => $liveCount,
    'completedCount' => $completedCount,
    'totalPlayers' => $totalPlayers,
    'totalTeams' => $totalTeams,
    'recentMatches' => $recentMatches
], [
    'activeMenu' => 'dashboard'
]);

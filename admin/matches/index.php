<?php
/**
 * Matches Listing - Admin Panel
 * 
 * Display all matches with filtering options
 * 
 * @package    CricApp
 * @subpackage Admin\Matches
 * @security   Login required
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

// Security
requireLogin();

// Get filter
$state = getQuery('state', '');
$filters = [];
if ($state) {
    $filters['state'] = $state;
}

// Fetch matches
$matches = [];
$error = '';

try {
    $matchModel = new MatchModel();
    $matches = $matchModel->getAll($filters);
} catch (PDOException $e) {
    error_log('Matches Index: Database error - ' . $e->getMessage());
    $error = 'Database error occurred. Please try again.';
} catch (Exception $e) {
    error_log('Matches Index: Error fetching matches - ' . $e->getMessage());
    $error = 'Error loading matches. Please try again.';
}

// Render
renderAdminLayout('Matches', 'matches-list', [
    'matches' => $matches,
    'state' => $state,
    'error' => $error
], [
    'activeMenu' => 'matches'
]);

<?php
/**
 * View Team - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$teamId = (int)getQuery('id', 0);
if (!$teamId) {
    header('Location: ' . adminUrl('teams/'));
    exit;
}

$teamModel = new Team();
$team = $teamModel->getById($teamId);

if (!$team) {
    header('Location: ' . adminUrl('teams/'));
    exit;
}

// Render
renderAdminLayout($team['name'], 'teams/view', [
    'team' => $team
], [
    'activeMenu' => 'teams',
    'headerActions' => [
        [
            'url' => adminUrl('teams/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

<?php
/**
 * Delete Team - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';

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

// Check dependencies before showing delete form
$dependencies = $teamModel->checkDependencies($teamId);
$canDelete = $dependencies['can_delete'];

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm')) {
    try {
        $result = $teamModel->delete($teamId);
        
        if ($result) {
            header('Location: ' . adminUrl('teams/') . '?deleted=1');
            exit;
        } else {
            $error = 'Failed to delete team';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Re-check dependencies to show current state
        $dependencies = $teamModel->checkDependencies($teamId);
        $canDelete = $dependencies['can_delete'];
    }
}

// Render
renderAdminLayout('Delete Team', 'teams/delete', [
    'team' => $team,
    'dependencies' => $dependencies,
    'canDelete' => $canDelete,
    'error' => $error
], [
    'activeMenu' => 'teams',
    'headerActions' => [
        [
            'url' => adminUrl('teams/view.php?id=' . $teamId),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

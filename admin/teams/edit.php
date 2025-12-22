<?php
/**
 * Edit Team - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken();

        $data = [
            'name' => trim(getPost('name', '')),
            'short_name' => trim(getPost('short_name', '')),
            'logo_url' => getPost('logo')
        ];
        
        if (empty($data['name'])) {
            $error = 'Team name is required';
        } else {
            $result = $teamModel->update($teamId, $data);
            
            if ($result) {
                header('Location: ' . adminUrl('teams/view.php?id=' . $teamId));
                exit;
            } else {
                $error = 'Failed to update team';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Edit Team', 'teams/edit', [
    'team' => $team,
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

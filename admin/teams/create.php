<?php
/**
 * Create Team - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
            $teamModel = new Team();
            $teamId = $teamModel->create($data);
            
            if ($teamId) {
                // Log action
                logAction('create', 'team', $teamId, ['name' => $data['name']]);
                
                header('Location: ' . adminUrl('teams/view.php?id=' . $teamId));
                exit;
            } else {
                $error = 'Failed to create team';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Create Team', 'teams/create', [
        'error' => $error
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

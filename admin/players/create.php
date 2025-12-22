<?php
/**
 * Create Player - Admin Panel
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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken();

        $data = [
            'name' => trim(getPost('name', '')),
            'date_of_birth' => getPost('date_of_birth') ? getPost('date_of_birth') : null,
            'batting_hand' => getPost('batting_hand'),
            'bowling_style' => getPost('bowling_style'),
            'profile_image' => getPost('profile_image')
        ];
        
        if (empty($data['name'])) {
            $error = 'Player name is required';
        } else {
            $playerModel = new Player();
            $playerId = $playerModel->create($data);
            
            if ($playerId) {
                // Log action
                logAction('create', 'player', $playerId, ['name' => $data['name']]);
                
                header('Location: ' . adminUrl('players/view.php?id=' . $playerId));
                exit;
            } else {
                $error = 'Failed to create player';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Create Player', 'players/create', [
        'error' => $error,
        'success' => $success
    ], [
    'activeMenu' => 'players',
    'headerActions' => [
        [
            'url' => adminUrl('players/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

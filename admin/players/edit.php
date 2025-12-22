<?php
/**
 * Edit Player - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$playerId = (int)getQuery('id', 0);
if (!$playerId) {
    header('Location: ' . adminUrl('players/'));
    exit;
}

$playerModel = new Player();
$player = $playerModel->getById($playerId);

if (!$player) {
    header('Location: ' . adminUrl('players/'));
    exit;
}

$error = '';

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
            $result = $playerModel->update($playerId, $data);
            
            if ($result) {
                // Log action
                logAction('update', 'player', $playerId, ['changes' => $data]);
                
                header('Location: ' . adminUrl('players/view.php?id=' . $playerId));
                exit;
            } else {
                $error = 'Failed to update player';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Edit Player', 'players/edit', [
    'player' => $player,
    'error' => $error
], [
    'activeMenu' => 'players',
    'headerActions' => [
        [
            'url' => adminUrl('players/view.php?id=' . $playerId),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

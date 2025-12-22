<?php
/**
 * View Player - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

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

// Render
renderAdminLayout('Player Profile', 'players/view', [
    'player' => $player
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

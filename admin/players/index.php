<?php
/**
 * Players Listing - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$playerModel = new Player();
$players = $playerModel->getAll();

// Render
renderAdminLayout('Players', 'players/index', [
    'players' => $players
], [
    'activeMenu' => 'players',
    'headerActions' => [
        [
            'url' => adminUrl('players/create.php'),
            'label' => '+',
            'class' => 'btn-icon',
            'aria-label' => 'Add Player'
        ]
    ]
]);

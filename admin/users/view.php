<?php
/**
 * View User - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$userId = (int)getQuery('id', 0);
if (!$userId) {
    header('Location: ' . adminUrl('users/'));
    exit;
}

$userModel = new User();
$user = $userModel->getById($userId);

if (!$user) {
    header('Location: ' . adminUrl('users/'));
    exit;
}

// Render
renderAdminLayout('User Profile', 'users/view', [
    'user' => $user
], [
    'activeMenu' => 'users',
    'headerActions' => [
        [
            'url' => adminUrl('users/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

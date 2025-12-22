<?php
/**
 * Users List - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$userModel = new User();

// Get filter parameters
$role = getQuery('role', '');
$isActive = getQuery('is_active') ? (int)getQuery('is_active') : null;

$filters = [];
if ($role) {
    $filters['role'] = $role;
}
if ($isActive !== null) {
    $filters['is_active'] = $isActive;
}

$users = $userModel->getAll($filters);

// Render
renderAdminLayout('Users', 'users/index', [
    'users' => $users,
    'role' => $role,
    'isActive' => $isActive
], [
    'activeMenu' => 'users',
    'headerActions' => [
        [
            'url' => adminUrl('users/create.php'),
            'label' => '+',
            'class' => 'btn-icon',
            'aria-label' => 'Add User'
        ]
    ]
]);

<?php
/**
 * Create User - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim(getPost('username', '')),
        'email' => trim(getPost('email', '')),
        'password' => getPost('password', ''),
        'role' => getPost('role', 'user'),
        'full_name' => trim(getPost('full_name', '')),
        'is_active' => getPost('is_active') ? 1 : 0
    ];
    
    // Validation
    if (empty($data['username'])) {
        $error = 'Username is required';
    } elseif (empty($data['password'])) {
        $error = 'Password is required';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($data['role'], ['admin', 'scorer', 'user'])) {
        $error = 'Invalid role';
    } else {
        $userModel = new User();
        
        // Check if username exists
        if ($userModel->getByUsername($data['username'])) {
            $error = 'Username already exists';
        } else {
            $userId = $userModel->create($data);
            
            if ($userId) {
                header('Location: ' . adminUrl('users/view.php?id=' . $userId));
                exit;
            } else {
                $error = 'Failed to create user';
            }
        }
    }
}

// Render
renderAdminLayout('Create User', 'users/create', [
        'error' => $error,
        'success' => $success
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

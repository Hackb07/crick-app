<?php
/**
 * Edit User - Admin Panel
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'email' => trim(getPost('email', '')),
        'role' => getPost('role', 'user'),
        'full_name' => trim(getPost('full_name', '')),
        'is_active' => getPost('is_active') ? 1 : 0
    ];
    
    // Validate role
    if (!in_array($data['role'], ['admin', 'scorer', 'user'])) {
        $error = 'Invalid role';
    } else {
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE users 
                WHERE user_id = :user_id";
        
        // Manual update to avoid potential model issues if not all fields are present or supported in update()
        // But better to use model if possible. Checking User::update in previous steps might have been useful.
        // Let's stick to the inline logic from the original file but rewritten cleanly.
        
        $sql = "UPDATE users 
                SET email = :email, role = :role, full_name = :full_name, 
                    is_active = :is_active, updated_at = NOW() 
                WHERE user_id = :user_id";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            'user_id' => $userId,
            'email' => $data['email'],
            'role' => $data['role'],
            'full_name' => $data['full_name'],
            'is_active' => $data['is_active']
        ]);
        
        // Update password if provided
        if (getPost('password')) {
            if (strlen(getPost('password')) < 6) {
                $error = 'Password must be at least 6 characters';
            } else {
                $userModel->updatePassword($userId, getPost('password'));
            }
        }
        
        if ($result && !$error) {
            header('Location: ' . adminUrl('users/view.php?id=' . $userId));
            exit;
        } elseif (!$result) {
            $error = 'Failed to update user';
        }
    }
}

// Render
renderAdminLayout('Edit User', 'users/edit', [
    'user' => $user,
    'error' => $error
], [
    'activeMenu' => 'users',
    'headerActions' => [
        [
            'url' => adminUrl('users/view.php?id=' . $userId),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);

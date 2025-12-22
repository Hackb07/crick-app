<?php
/**
 * Create admin user script
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/User.php';

echo "Creating admin user...\n\n";

try {
    $userModel = new User();
    
    // Check if admin exists
    $existing = $userModel->getByUsername('admin');
    
    if ($existing) {
        echo "⚠ Admin user already exists. Updating password...\n";
        $userModel->update($existing['user_id'], [
            'password' => 'admin123'
        ]);
        echo "✓ Admin password updated!\n";
    } else {
        echo "Creating new admin user...\n";
        $result = $userModel->create([
            'username' => 'admin',
            'email' => 'admin@cricapp.local',
            'password' => 'admin123',
            'role' => 'admin',
            'full_name' => 'Administrator'
        ]);
        
        if ($result['success']) {
            echo "✓ Admin user created successfully!\n";
        } else {
            throw new Exception("Failed to create admin user");
        }
    }
    
    // Verify
    $admin = $userModel->authenticate('admin', 'admin123');
    if ($admin) {
        echo "\n✅ Admin user verified!\n";
        echo "  Username: " . $admin['username'] . "\n";
        echo "  Role: " . $admin['role'] . "\n";
        echo "\nLogin credentials:\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}





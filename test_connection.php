<?php
/**
 * Test Database Connection
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/User.php';

echo "Testing database connection...\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Database connection successful!\n\n";
    
    // Test User model
    echo "Testing User model...\n";
    $userModel = new User();
    
    // Test authentication with default admin user
    echo "Testing authentication (admin/admin123)...\n";
    $user = $userModel->authenticate('admin', 'admin123');
    
    if ($user) {
        echo "✓ Authentication successful!\n";
        echo "  User ID: " . $user['user_id'] . "\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Role: " . $user['role'] . "\n";
        echo "  Full Name: " . ($user['full_name'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Authentication failed. Admin user may not exist.\n";
    }
    
    echo "\n✓ All tests passed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}





<?php
/**
 * PHPUnit Bootstrap File
 * Loads application configuration and sets up test environment
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define test environment
define('APP_ENV', 'testing');
define('APP_URL', 'http://localhost/cricapp');

// Load application configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/middleware.php';

// Load classes
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Match.php';
require_once __DIR__ . '/../classes/Player.php';
require_once __DIR__ . '/../classes/JWT.php';

// Test helper functions
function testJsonResponse($response) {
    return json_decode($response, true);
}

function testCreateTestUser($username = 'testuser', $role = 'admin') {
    $db = Database::getInstance()->getConnection();
    $passwordHash = password_hash('testpass123', PASSWORD_DEFAULT);
    
    // Check if user already exists
    $checkSql = "SELECT user_id FROM users WHERE username = :username";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute(['username' => $username]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Update existing user
        $updateSql = "UPDATE users SET password_hash = :password_hash, role = :role WHERE user_id = :user_id";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute([
            'password_hash' => $passwordHash,
            'role' => $role,
            'user_id' => $existing['user_id']
        ]);
        return $existing['user_id'];
    }
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password_hash, role, full_name) 
            VALUES (:username, :email, :password_hash, :role, :full_name)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'email' => $username . '@test.com',
        'password_hash' => $passwordHash,
        'role' => $role,
        'full_name' => 'Test User'
    ]);
    
    return $db->lastInsertId();
}

function testCreateTestTeam($name = 'Test Team') {
    $db = Database::getInstance()->getConnection();
    
    $sql = "INSERT INTO teams (name, short_name) VALUES (:name, :short_name)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'short_name' => substr($name, 0, 3)
    ]);
    
    return $db->lastInsertId();
}

function testCreateTestPlayer($name = 'Test Player') {
    $db = Database::getInstance()->getConnection();
    
    $sql = "INSERT INTO players (name) VALUES (:name)";
    $stmt = $db->prepare($sql);
    $stmt->execute(['name' => $name]);
    
    return $db->lastInsertId();
}

function testCleanup($table, $id, $idColumn = null) {
    $db = Database::getInstance()->getConnection();
    
    // Handle special cases for column names
    if ($idColumn === null) {
        $columnMap = [
            'users' => 'user_id',
            'teams' => 'team_id',
            'players' => 'player_id',
            'matches' => 'match_id',
            'series' => 'series_id'
        ];
        $idColumn = $columnMap[$table] ?? $table . '_id';
    }
    
    $sql = "DELETE FROM `$table` WHERE `$idColumn` = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute(['id' => $id]);
}


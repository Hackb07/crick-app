<?php
/**
 * Logout Page - Public Portal
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Destroy session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

destroySession();

// Redirect to home
header('Location: ' . publicUrl('index.php'));
exit;



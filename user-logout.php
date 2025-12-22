<?php
/**
 * User Logout
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header('Location: user-login.php');
exit;

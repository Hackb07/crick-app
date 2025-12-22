<?php
/**
 * Admin/Scorer Logout
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

// Check role before destroying session
$role = getSession('role');
$isScorer = ($role === 'scorer');

destroySession();

// Redirect based on role
if ($isScorer) {
    header('Location: ' . adminUrl('scorer-login.php'));
} else {
    header('Location: ' . adminUrl('login.php'));
}
exit;

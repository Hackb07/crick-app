<?php
/**
 * Security Helper Functions
 * 
 * Centralized security functions for authentication and authorization
 * 
 * @package    CricApp
 * @subpackage Includes
 * @security   Core security functions
 */

/**
 * Require specific user role
 * Redirects to dashboard if user doesn't have required role
 * 
 * @param string $role Required role (admin, scorer, etc.)
 * @return void
 */
function requireRole(string $role): void
{
    if (getSession('role') !== $role) {
        $_SESSION[SESSION_KEY_ERROR] = 'Access denied. Required role: ' . $role;
        header('Location: ' . adminUrl('index.php'));
        exit;
    }
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check
 * @return bool True if user has the role
 */
function hasRole(string $role): bool
{
    return getSession('role') === $role;
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin
 */
function isAdmin(): bool
{
    return hasRole('admin');
}

/**
 * Check if user is scorer
 * 
 * @return bool True if user is scorer
 */
function isScorer(): bool
{
    return hasRole('scorer');
}

/**
 * Get current username
 * 
 * @return string|null Username or null if not logged in
 */
function getCurrentUsername(): ?string
{
    return getSession('username');
}

/**
 * Get current user ID (alias for getUserId from session.php)
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId(): ?int
{
    return getUserId();
}

/**
 * Logout current user
 * Destroys session and redirects to login
 * 
 * @return void
 */
function logout(): void
{
    session_destroy();
    header('Location: ' . adminUrl('login.php'));
    exit;
}


<?php


if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');

    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
         $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if ($isHttps) {
        ini_set('session.cookie_secure', 1);
    }

    session_name('CRICAPP_SESSION');
    session_start();
}

/* ===== Session Helpers ===== */

function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

function getSession($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

function hasSession($key) {
    return isset($_SESSION[$key]);
}

function unsetSession($key) {
    unset($_SESSION[$key]);
}

function destroySession() {
    session_destroy();
}

function isLoggedIn() {
    return hasSession('user_id');
}

function getUserId() {
    return getSession('user_id');
}

function requireLogin() {
    if (!isLoggedIn()) {
        require_once __DIR__ . '/utils.php';
        header('Location: ' . adminUrl('login.php'));
        exit;
    }
    return getUserId();
}

function requireScorer() {
    if (!isLoggedIn() || getSession('role') !== 'scorer') {
        require_once __DIR__ . '/utils.php';
        header('Location: ' . adminUrl('scorer-login.php'));
        exit;
    }
    return getUserId();
}

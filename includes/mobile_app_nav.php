<?php
/**
 * Mobile App-Style Navigation
 * Include this in all admin pages for consistent mobile app interface
 */

if (!isset($user)) {
    $user = $_SESSION['user'] ?? null;
}

if (!$user) {
    return;
}

$currentPath = $_SERVER['REQUEST_URI'];
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Determine active menu item
$activeClass = function($path) use ($currentPath, $currentDir) {
    $checkPath = $path;
    if ($path === '/cricapp/admin/' && $currentDir === 'admin' && basename($_SERVER['PHP_SELF']) === 'index.php') {
        return 'active';
    }
    if (strpos($currentPath, $path) !== false && $path !== '/cricapp/admin/') {
        return 'active';
    }
    return '';
};
?>

<!-- Mobile App Container -->
<div class="app-container">
    <!-- App Header -->
    <header class="app-header">
        <div class="app-header-left">
            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
                ☰
            </button>
            <h1 class="app-header-title"><?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?></h1>
        </div>
        <div class="app-header-actions">
            <span style="font-size: 0.875rem; display: none;" class="d-md-inline"><?= htmlspecialchars($user['username']) ?></span>
            <a href="/cricapp/admin/logout.php" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Logout</a>
        </div>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar/Drawer Menu -->
    <aside class="app-sidebar" id="app-sidebar">
        <div class="sidebar-header">
            <h3>Menu</h3>
            <button class="sidebar-close" aria-label="Close menu">×</button>
        </div>
        
        <nav>
            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/" class="sidebar-menu-link <?= $activeClass('/cricapp/admin/') ?>">
                        <span class="sidebar-menu-icon">🏠</span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/matches/" class="sidebar-menu-link <?= $activeClass('/matches') ?>">
                        <span class="sidebar-menu-icon">⚽</span>
                        <span class="sidebar-menu-text">Matches</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/players/" class="sidebar-menu-link <?= $activeClass('/players') ?>">
                        <span class="sidebar-menu-icon">👤</span>
                        <span class="sidebar-menu-text">Players</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/teams/" class="sidebar-menu-link <?= $activeClass('/teams') ?>">
                        <span class="sidebar-menu-icon">👥</span>
                        <span class="sidebar-menu-text">Teams</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/series/" class="sidebar-menu-link <?= $activeClass('/series') ?>">
                        <span class="sidebar-menu-icon">📅</span>
                        <span class="sidebar-menu-text">Series</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/stats/" class="sidebar-menu-link <?= $activeClass('/stats') ?>">
                        <span class="sidebar-menu-icon">📊</span>
                        <span class="sidebar-menu-text">Statistics</span>
                    </a>
                </li>
                <?php if ($user['role'] === 'admin'): ?>
                <li class="sidebar-menu-item">
                    <a href="/cricapp/admin/settings/" class="sidebar-menu-link <?= $activeClass('/settings') ?>">
                        <span class="sidebar-menu-icon">⚙️</span>
                        <span class="sidebar-menu-text">Settings</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="sidebar-user-role"><?= htmlspecialchars(ucfirst($user['role'])) ?></div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="app-main">
        <!-- Content will be inserted here by pages -->

<!-- Bottom Navigation - Mobile Only -->
<nav class="app-bottom-nav">
    <div class="app-bottom-nav-items">
        <a href="/cricapp/admin/" class="app-bottom-nav-item <?= $activeClass('/cricapp/admin/') ?>">
            <span class="app-bottom-nav-icon">🏠</span>
            <span class="app-bottom-nav-label">Home</span>
        </a>
        <a href="/cricapp/admin/matches/" class="app-bottom-nav-item <?= $activeClass('/matches') ?>">
            <span class="app-bottom-nav-icon">⚽</span>
            <span class="app-bottom-nav-label">Matches</span>
        </a>
        <a href="/cricapp/admin/players/" class="app-bottom-nav-item <?= $activeClass('/players') ?>">
            <span class="app-bottom-nav-icon">👤</span>
            <span class="app-bottom-nav-label">Players</span>
        </a>
        <a href="/cricapp/admin/stats/" class="app-bottom-nav-item <?= $activeClass('/stats') ?>">
            <span class="app-bottom-nav-icon">📊</span>
            <span class="app-bottom-nav-label">Stats</span>
        </a>
        <a href="/cricapp/admin/settings/" class="app-bottom-nav-item <?= $activeClass('/settings') ?>">
            <span class="app-bottom-nav-icon">⚙️</span>
            <span class="app-bottom-nav-label">Settings</span>
        </a>
    </div>
</nav>


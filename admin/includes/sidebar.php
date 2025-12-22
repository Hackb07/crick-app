<?php
/**
 * Admin Sidebar Navigation
 * 
 * Shared sidebar menu for all admin pages
 * 
 * @param string $activePage The active page identifier (e.g., 'dashboard', 'matches', 'players', etc.)
 */
function renderAdminSidebar($activePage = '') {
    // Determine active class for each menu item
    $isDashboard = ($activePage === 'dashboard' || $activePage === 'index');
    $isMatches = ($activePage === 'matches');
    $isPlayers = ($activePage === 'players');
    $isTeams = ($activePage === 'teams');
    $isSeries = ($activePage === 'series');
    $isUsers = ($activePage === 'users');
    $isLogs = ($activePage === 'logs');
    $isStats = ($activePage === 'stats' || $activePage === 'statistics');
    ?>
    <aside class="admin-sidebar" id="sidebar">
        <div class="admin-sidebar-header">
            <a href="<?= adminUrl('index.php') ?>" class="admin-sidebar-logo">🏏 CricApp</a>
        </div>
        <nav class="admin-sidebar-nav">
            <a href="<?= adminUrl('index.php') ?>" class="admin-nav-item <?= $isDashboard ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">📊</span>
                Dashboard
            </a>
            <a href="<?= adminUrl('matches/') ?>" class="admin-nav-item <?= $isMatches ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">🏏</span>
                Matches
            </a>
            <a href="<?= adminUrl('players/') ?>" class="admin-nav-item <?= $isPlayers ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">👤</span>
                Players
            </a>
            <a href="<?= adminUrl('teams/') ?>" class="admin-nav-item <?= $isTeams ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">👥</span>
                Teams
            </a>
            <a href="<?= adminUrl('series/') ?>" class="admin-nav-item <?= $isSeries ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">🏆</span>
                Series
            </a>
            <a href="<?= adminUrl('users/') ?>" class="admin-nav-item <?= $isUsers ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">👥</span>
                Users
            </a>
            <a href="<?= adminUrl('logs/') ?>" class="admin-nav-item <?= $isLogs ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">📋</span>
                Action Logs
            </a>
            <a href="<?= adminUrl('stats/') ?>" class="admin-nav-item <?= $isStats ? 'active' : '' ?>">
                <span class="admin-nav-item-icon">📈</span>
                Statistics
            </a>
            <a href="<?= adminUrl('logout.php') ?>" class="admin-nav-item">
                <span class="admin-nav-item-icon">🚪</span>
                Logout
            </a>
        </nav>
    </aside>
    <?php
}
?>


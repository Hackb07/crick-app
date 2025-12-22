<?php
/**
 * Admin Navigation Helper
 * Include this in all admin pages for consistent navigation
 */

if (!isset($user)) {
    $user = $_SESSION['user'] ?? null;
}

if (!$user) {
    return;
}

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="admin-nav">
    <div class="container">
        <a href="/cricapp/admin/" <?= ($currentDir === 'admin' && $currentPage === 'index.php') ? 'class="active"' : '' ?>>Dashboard</a>
        <a href="/cricapp/admin/matches/" <?= ($currentDir === 'matches') ? 'class="active"' : '' ?>>Matches</a>
        <a href="/cricapp/admin/players/" <?= ($currentDir === 'players') ? 'class="active"' : '' ?>>Players</a>
        <a href="/cricapp/admin/teams/" <?= ($currentDir === 'teams') ? 'class="active"' : '' ?>>Teams</a>
        <a href="/cricapp/admin/series/" <?= ($currentDir === 'series') ? 'class="active"' : '' ?>>Series</a>
        <a href="/cricapp/admin/stats/" <?= ($currentDir === 'stats') ? 'class="active"' : '' ?>>Statistics</a>
        <?php if ($user['role'] === 'admin'): ?>
            <a href="/cricapp/admin/settings/" <?= ($currentDir === 'settings') ? 'class="active"' : '' ?>>Settings</a>
        <?php endif; ?>
    </div>
</nav>





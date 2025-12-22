<?php
/**
 * Public Header
 * Common header for all public pages
 */
?>
<header class="public-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?= publicUrl('index.php') ?>">
                    🏏 <span>CricApp</span>
                </a>
            </div>
            <nav class="main-nav">
                <a href="<?= publicUrl('index.php') ?>">Home</a>
                <a href="<?= publicUrl('matches.php') ?>">Matches</a>
                <a href="<?= publicUrl('series.php') ?>">Series</a>
                <a href="<?= publicUrl('stats.php') ?>">Stats</a>
            </nav>
            <div class="header-actions">
                <a href="<?= adminUrl('login.php') ?>" class="btn btn-primary">Admin Login</a>
            </div>
        </div>
    </div>
</header>

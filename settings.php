<?php
/**
 * Settings/More Menu Page - Public Portal
 * 
 * Features:
 * - Green header
 * - Settings menu with options
 * - About section
 * - Help/Support links
 * - Logout option if logged in
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/session.php';

$loggedIn = isLoggedIn();
$user = null;
if ($loggedIn) {
    $userId = getUserId();
    $userModel = new User();
    $user = $userModel->getById($userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        /* Green Header */
        .green-header {
            background: var(--cricket-green);
            color: white;
            padding: var(--spacing-md) 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }
        
        .green-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .green-header-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        /* Settings Menu */
        .settings-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .settings-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--border-color);
        }
        
        .settings-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-md);
            background: var(--bg-primary);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-sm);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--text-primary);
        }
        
        .settings-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .settings-item-content {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex: 1;
        }
        
        .settings-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .settings-item-info {
            flex: 1;
        }
        
        .settings-item-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .settings-item-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .settings-item-arrow {
            color: var(--text-secondary);
            font-size: 1.25rem;
        }
        
        .logout-item {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .logout-item .settings-item-title,
        .logout-item .settings-item-arrow {
            color: white;
        }
        
        .logout-item .settings-item-subtitle {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .logout-item:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        }
    </style>
</head>
<body>
    <!-- Green Header -->
    <header class="green-header">
        <div class="container green-header-content">
            <a href="<?= publicUrl('index.php') ?>" class="green-header-logo">🏏 CricApp</a>
        </div>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: var(--spacing-sm);">⚙️ Settings</h1>
            <?php if ($loggedIn && $user): ?>
                <p style="color: var(--text-secondary); font-size: 1rem;">
                    Logged in as <strong><?= e($user['username']) ?></strong>
                    <?php if ($user['full_name']): ?>
                        (<?= e($user['full_name']) ?>)
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Account Section -->
        <div class="settings-section">
            <h2 class="settings-section-title">Account</h2>
            
            <?php if ($loggedIn): ?>
                <a href="<?= publicUrl('profile.php') ?>" class="settings-item">
                    <div class="settings-item-content">
                        <div class="settings-item-icon">👤</div>
                        <div class="settings-item-info">
                            <div class="settings-item-title">Profile</div>
                            <div class="settings-item-subtitle">View and edit your profile</div>
                        </div>
                    </div>
                    <div class="settings-item-arrow">→</div>
                </a>
            <?php else: ?>
                <a href="<?= publicUrl('login.php') ?>" class="settings-item">
                    <div class="settings-item-content">
                        <div class="settings-item-icon">🔐</div>
                        <div class="settings-item-info">
                            <div class="settings-item-title">Login / Sign Up</div>
                            <div class="settings-item-subtitle">Create an account or login</div>
                        </div>
                    </div>
                    <div class="settings-item-arrow">→</div>
                </a>
            <?php endif; ?>
            
            <?php if ($loggedIn): ?>
                <a href="<?= publicUrl('logout.php') ?>" class="settings-item logout-item">
                    <div class="settings-item-content">
                        <div class="settings-item-icon">🚪</div>
                        <div class="settings-item-info">
                            <div class="settings-item-title">Logout</div>
                            <div class="settings-item-subtitle">Sign out of your account</div>
                        </div>
                    </div>
                    <div class="settings-item-arrow">→</div>
                </a>
            <?php endif; ?>
        </div>

        <!-- App Section -->
        <div class="settings-section">
            <h2 class="settings-section-title">App</h2>
            
            <a href="<?= publicUrl('teams-ranking.php') ?>" class="settings-item">
                <div class="settings-item-content">
                    <div class="settings-item-icon">🏆</div>
                    <div class="settings-item-info">
                        <div class="settings-item-title">Teams Ranking</div>
                        <div class="settings-item-subtitle">View team rankings</div>
                    </div>
                </div>
                <div class="settings-item-arrow">→</div>
            </a>
            
            <a href="<?= publicUrl('points-table.php') ?>" class="settings-item">
                <div class="settings-item-content">
                    <div class="settings-item-icon">📊</div>
                    <div class="settings-item-info">
                        <div class="settings-item-title">Points Table</div>
                        <div class="settings-item-subtitle">View points table for series</div>
                    </div>
                </div>
                <div class="settings-item-arrow">→</div>
            </a>
        </div>

        <!-- About Section -->
        <div class="settings-section">
            <h2 class="settings-section-title">About</h2>
            
            <div class="settings-item" style="cursor: default;">
                <div class="settings-item-content">
                    <div class="settings-item-icon">ℹ️</div>
                    <div class="settings-item-info">
                        <div class="settings-item-title">About CricApp</div>
                        <div class="settings-item-subtitle">Cricket Scoring Application v1.0</div>
                    </div>
                </div>
            </div>
            
            <div class="settings-item" style="cursor: default;">
                <div class="settings-item-content">
                    <div class="settings-item-icon">📧</div>
                    <div class="settings-item-info">
                        <div class="settings-item-title">Contact</div>
                        <div class="settings-item-subtitle">support@cricapp.com</div>
                    </div>
                </div>
            </div>
            
            <div class="settings-item" style="cursor: default;">
                <div class="settings-item-content">
                    <div class="settings-item-icon">📱</div>
                    <div class="settings-item-info">
                        <div class="settings-item-title">Version</div>
                        <div class="settings-item-subtitle">1.0.0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="<?= publicUrl('index.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                <span>Home</span>
            </a>
            <a href="<?= publicUrl('matches.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                <span>Matches</span>
            </a>
            <a href="<?= publicUrl('live.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                <span>Live</span>
            </a>
            <a href="<?= publicUrl('leaderboard.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                <span>Leaderboard</span>
            </a>
        </div>
    </nav>
</body>
</html>



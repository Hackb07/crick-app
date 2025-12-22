<?php
/**
 * PWA Admin Layout Template
 * 
 * Usage:
 *   $pageTitle = "Dashboard";
 *   $showBottomNav = true;
 *   $activeNav = "dashboard";
 *   include __DIR__ . '/includes/layout-pwa.php';
 * 
 * Variables:
 *   - $pageTitle: Page title (required)
 *   - $showBottomNav: Show bottom navigation on mobile (default: false)
 *   - $activeNav: Active nav item key (for highlighting)
 *   - $headerActions: HTML for header actions (optional)
 *   - $topbarActions: HTML for desktop topbar actions (optional)
 *   - $sidebarNav: Custom sidebar navigation array (optional)
 */

// Default values
$pageTitle = $pageTitle ?? 'Admin';
$showBottomNav = $showBottomNav ?? false;
$activeNav = $activeNav ?? '';
$headerActions = $headerActions ?? '';
$topbarActions = $topbarActions ?? '';

// Default navigation items
$defaultNavItems = [
    'dashboard' => [
        'icon' => '📊',
        'label' => 'Dashboard',
        'url' => adminUrl('index.php')
    ],
    'matches' => [
        'icon' => '🏏',
        'label' => 'Matches',
        'url' => adminUrl('matches/')
    ],
    'players' => [
        'icon' => '👤',
        'label' => 'Players',
        'url' => adminUrl('players/')
    ],
    'teams' => [
        'icon' => '👥',
        'label' => 'Teams',
        'url' => adminUrl('teams/')
    ],
    'series' => [
        'icon' => '🏆',
        'label' => 'Series',
        'url' => adminUrl('series/')
    ],
    'users' => [
        'icon' => '👥',
        'label' => 'Users',
        'url' => adminUrl('users/')
    ],
    'logs' => [
        'icon' => '📋',
        'label' => 'Logs',
        'url' => adminUrl('logs/')
    ],
    'stats' => [
        'icon' => '📈',
        'label' => 'Stats',
        'url' => adminUrl('stats/')
    ],
    'settings' => [
        'icon' => '⚙️',
        'label' => 'Settings',
        'url' => adminUrl('settings/')
    ]
];

$sidebarNav = $sidebarNav ?? $defaultNavItems;

// Bottom nav items (subset for mobile)
$bottomNavItems = [
    'dashboard' => ['icon' => '📊', 'label' => 'Dashboard', 'url' => adminUrl('index.php')],
    'matches' => ['icon' => '🏏', 'label' => 'Matches', 'url' => adminUrl('matches/')],
    'users' => ['icon' => '👥', 'label' => 'Users', 'url' => adminUrl('users/')],
    'settings' => ['icon' => '⚙️', 'label' => 'Settings', 'url' => adminUrl('settings/')]
];

// Get current user info
$currentUser = getSession('username', 'Admin');
$currentRole = getSession('role', 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1e7e34">
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title><?= e($pageTitle) ?> - CricApp Admin</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <script>
        // Register service worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('SW registered:', reg))
                    .catch((err) => console.log('SW registration failed:', err));
            });
        }
        
        // Toggle filters on mobile
        function toggleFilters() {
            const content = document.querySelector('.filters-content');
            if (content) {
                content.classList.toggle('open');
            }
        }
    </script>
</head>
<body>
    <div class="app-shell">
        <!-- Mobile Header -->
        <header class="app-header">
            <button class="app-header-btn" onclick="toggleSidebar()" aria-label="Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <h1 class="app-header-title"><?= e($pageTitle) ?></h1>
            <div class="app-header-actions">
                <?= $headerActions ?>
                <a href="<?= adminUrl('logout.php') ?>" class="app-header-btn" aria-label="Logout">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Desktop Sidebar -->
        <aside class="app-sidebar">
            <div class="app-sidebar-header">
                <a href="<?= adminUrl('index.php') ?>" class="app-sidebar-logo">🏏 CricApp</a>
            </div>
            <nav class="app-sidebar-nav">
                <?php foreach ($sidebarNav as $key => $item): ?>
                    <a href="<?= e($item['url']) ?>" 
                       class="app-nav-item <?= $activeNav === $key ? 'active' : '' ?>">
                        <span class="app-nav-icon"><?= e($item['icon']) ?></span>
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= adminUrl('logout.php') ?>" class="app-nav-item">
                    <span class="app-nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Desktop Top Bar -->
        <div class="app-topbar">
            <h2 class="app-topbar-title"><?= e($pageTitle) ?></h2>
            <div class="app-topbar-actions">
                <div class="app-topbar-search">
                    <input type="search" id="topbar-search" placeholder="Search..." aria-label="Search">
                    <svg class="search-bar-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="app-topbar-user">
                    <span><?= e($currentUser) ?></span>
                    <span style="color: var(--border-color);">•</span>
                    <span><?= e($currentRole) ?></span>
                </div>
                <?= $topbarActions ?>
            </div>
        </div>

        <!-- Main Content -->
        <main class="app-content <?= $showBottomNav ? 'with-bottom-nav' : '' ?>">
            <?php
            // Content will be included here
            if (isset($content)) {
                echo $content;
            }
            ?>
        </main>

        <!-- Mobile Bottom Navigation -->
        <?php if ($showBottomNav): ?>
        <nav class="app-bottom-nav">
            <?php foreach ($bottomNavItems as $key => $item): ?>
                <a href="<?= e($item['url']) ?>" 
                   class="app-bottom-nav-item <?= $activeNav === $key ? 'active' : '' ?>"
                   aria-label="<?= e($item['label']) ?>">
                    <span class="app-bottom-nav-icon"><?= e($item['icon']) ?></span>
                    <span class="app-bottom-nav-label"><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.app-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth < 1024) {
                sidebar.classList.toggle('open');
                if (overlay) {
                    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
                }
            }
        }
        
        function closeSidebar() {
            const sidebar = document.querySelector('.app-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('open');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
        
        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    </script>
    
    <style>
        /* Mobile sidebar overlay */
        .mobile-sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        @media (min-width: 1024px) {
            .mobile-sidebar-overlay {
                display: none !important;
            }
            
            .app-sidebar {
                transform: translateX(0) !important;
            }
        }
        
        @media (max-width: 1023px) {
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .app-sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</body>
</html>








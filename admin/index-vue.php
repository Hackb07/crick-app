<?php
/**
 * Admin Dashboard - Vue.js Version
 * Modern admin panel with Vue.js
 */

session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
    header('Location: /cricapp/admin/login.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLiveMatches();
$recentMatches = $matchModel->getRecentMatches(10);

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cricket Scoring</title>
    
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Modern CSS -->
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/vue-modern.css">
    <link rel="stylesheet" href="/cricapp/assets/css/mobile-app.css">
    
    <style>
        body {
            background: var(--gray-50);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
            padding-bottom: 100px;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .quick-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .matches-table-modern {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 0.75rem;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
        }
        
        td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        tr:hover {
            background: var(--gray-50);
        }
        
        .admin-container {
            padding-top: 0;
        }
        
        .app-main {
            flex: 1;
            overflow-y: auto;
            margin-top: 56px; /* Height of app-header */
            padding-bottom: 80px; /* Space for bottom nav on mobile */
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        @media (min-width: 769px) {
            .app-bottom-nav {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 1rem;
                padding-top: 76px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div id="app" class="app-container">
        <!-- App Header -->
        <header class="app-header">
            <div class="app-header-left">
                <button class="menu-toggle" @click="toggleSidebar" aria-label="Toggle menu">
                    ☰
                </button>
                <h1 class="app-header-title">Admin Dashboard</h1>
            </div>
            <div class="app-header-actions">
                <span style="font-size: 0.875rem; display: none;" class="d-md-inline">{{ user.username }}</span>
                <a href="/cricapp/admin/logout.php" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem; color: white; background: rgba(255,255,255,0.2); border: none; text-decoration: none; border-radius: 6px;">Logout</a>
            </div>
        </header>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="closeSidebar"></div>

        <!-- Sidebar/Drawer Menu -->
        <aside class="app-sidebar" :class="{ 'open': sidebarOpen }">
            <div class="sidebar-header">
                <h3>Menu</h3>
                <button class="sidebar-close" @click="closeSidebar" aria-label="Close menu">×</button>
            </div>
            
            <nav>
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/" :class="['sidebar-menu-link', { 'active': currentPage === 'dashboard' }]">
                            <span class="sidebar-menu-icon">🏠</span>
                            <span class="sidebar-menu-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/matches/" :class="['sidebar-menu-link', { 'active': currentPage === 'matches' }]">
                            <span class="sidebar-menu-icon">⚽</span>
                            <span class="sidebar-menu-text">Matches</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/players/" :class="['sidebar-menu-link', { 'active': currentPage === 'players' }]">
                            <span class="sidebar-menu-icon">👤</span>
                            <span class="sidebar-menu-text">Players</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/teams/" :class="['sidebar-menu-link', { 'active': currentPage === 'teams' }]">
                            <span class="sidebar-menu-icon">👥</span>
                            <span class="sidebar-menu-text">Teams</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/series/" :class="['sidebar-menu-link', { 'active': currentPage === 'series' }]">
                            <span class="sidebar-menu-icon">📅</span>
                            <span class="sidebar-menu-text">Series</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="/cricapp/admin/stats/" :class="['sidebar-menu-link', { 'active': currentPage === 'stats' }]">
                            <span class="sidebar-menu-icon">📊</span>
                            <span class="sidebar-menu-text">Statistics</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item" v-if="user.role === 'admin'">
                        <a href="/cricapp/admin/settings/" :class="['sidebar-menu-link', { 'active': currentPage === 'settings' }]">
                            <span class="sidebar-menu-icon">⚙️</span>
                            <span class="sidebar-menu-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ user.username }}</div>
                <div class="sidebar-user-role">{{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}</div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main">
            <div class="admin-container">
            <!-- Admin Header -->
            <div class="admin-header">
                <div class="admin-title">Admin Dashboard</div>
                <div style="font-size: 0.875rem; opacity: 0.9;">
                    Welcome, {{ user.username }}
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Live Matches</div>
                    <div class="stat-value">{{ matches.live.length }}</div>
                    <a href="/cricapp/admin/matches/?state=live" class="btn-modern btn-primary" style="width: 100%;">View Live</a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Recent Matches</div>
                    <div class="stat-value">{{ matches.recent.length }}</div>
                    <a href="/cricapp/admin/matches/?state=completed" class="btn-modern btn-secondary" style="width: 100%;">View All</a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Quick Actions</div>
                    <div class="quick-actions">
                        <a href="/cricapp/admin/matches/create.php" class="btn-modern btn-success">Create Match</a>
                        <a href="/cricapp/admin/players/create.php" class="btn-modern btn-secondary">Add Player</a>
                    </div>
                </div>
            </div>

            <!-- Recent Matches Table -->
            <div class="matches-table-modern">
                <h2 style="margin-top: 0; margin-bottom: 1rem; color: var(--gray-900);">Recent Matches</h2>
                <div v-if="loading" class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="matches.recent.length === 0" class="empty-state-modern">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">No Recent Matches</div>
                </div>
                <table v-else>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Teams</th>
                            <th>Series</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="match in matches.recent" :key="match.match_id">
                            <td>{{ formatDate(match.match_date) }}</td>
                            <td>{{ match.team1_name }} vs {{ match.team2_name }}</td>
                            <td>{{ match.series_name || '-' }}</td>
                            <td>
                                <span :class="['status-badge', match.state]">{{ match.state }}</span>
                            </td>
                            <td>
                                <a :href="`/cricapp/admin/matches/view.php?id=${match.match_id}`" class="btn-modern btn-primary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        </main>

        <!-- Bottom Navigation - Mobile Only -->
        <nav class="app-bottom-nav">
            <div class="app-bottom-nav-items">
                <a href="/cricapp/admin/" :class="['app-bottom-nav-item', { 'active': currentPage === 'dashboard' }]">
                    <span class="app-bottom-nav-icon">🏠</span>
                    <span class="app-bottom-nav-label">Home</span>
                </a>
                <a href="/cricapp/admin/matches/" :class="['app-bottom-nav-item', { 'active': currentPage === 'matches' }]">
                    <span class="app-bottom-nav-icon">⚽</span>
                    <span class="app-bottom-nav-label">Matches</span>
                </a>
                <a href="/cricapp/admin/players/" :class="['app-bottom-nav-item', { 'active': currentPage === 'players' }]">
                    <span class="app-bottom-nav-icon">👤</span>
                    <span class="app-bottom-nav-label">Players</span>
                </a>
                <a href="/cricapp/admin/stats/" :class="['app-bottom-nav-item', { 'active': currentPage === 'stats' }]">
                    <span class="app-bottom-nav-icon">📊</span>
                    <span class="app-bottom-nav-label">Stats</span>
                </a>
                <a href="/cricapp/admin/settings/" :class="['app-bottom-nav-item', { 'active': currentPage === 'settings' }]" v-if="user.role === 'admin'">
                    <span class="app-bottom-nav-icon">⚙️</span>
                    <span class="app-bottom-nav-label">Settings</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Vue App Script - Load after Vue.js -->
    <script src="/cricapp/assets/js/vue-app.js"></script>
    <script>
        // Wait for Vue.js and VueApp to be available
        function initVueApp() {
            if (typeof Vue === 'undefined') {
                console.error('Vue.js failed to load. Check CDN connection.');
                document.getElementById('app').innerHTML = '<div style="padding: 2rem; text-align: center;"><h2>Error Loading Page</h2><p>Vue.js failed to load. Please check your internet connection and try again.</p></div>';
                return;
            }
            
            if (typeof VueApp === 'undefined' || !VueApp.createApp) {
                console.error('VueApp is not defined. Check vue-app.js file.');
                document.getElementById('app').innerHTML = '<div style="padding: 2rem; text-align: center;"><h2>Error Loading Page</h2><p>Vue.js application failed to initialize. Please refresh the page.</p></div>';
                return;
            }
            
            const { createApp } = Vue;
            
            // Set token for API client
            if (VueApp.apiClient) {
                VueApp.apiClient.setToken('<?= $_SESSION['token'] ?>');
            }
            
            createApp({
            data() {
                const liveData = <?= json_encode($liveMatches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                const recentData = <?= json_encode($recentMatches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                // Determine current page
                const path = window.location.pathname;
                let currentPage = 'dashboard';
                if (path.includes('/matches')) currentPage = 'matches';
                else if (path.includes('/players')) currentPage = 'players';
                else if (path.includes('/teams')) currentPage = 'teams';
                else if (path.includes('/series')) currentPage = 'series';
                else if (path.includes('/stats')) currentPage = 'stats';
                else if (path.includes('/settings')) currentPage = 'settings';
                
                return {
                    user: <?= json_encode($user, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    matches: {
                        live: Array.isArray(liveData) ? liveData : [],
                        recent: Array.isArray(recentData) ? recentData : []
                    },
                    loading: false,
                    currentPage: currentPage,
                    sidebarOpen: false
                };
            },
            mounted() {
                // Auto-refresh every 30 seconds
                setInterval(() => {
                    this.refreshData();
                }, 30000);
            },
            methods: {
                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },
                closeSidebar() {
                    this.sidebarOpen = false;
                },
                async refreshData() {
                    if (!VueApp || !VueApp.apiClient) {
                        console.error('API client not available');
                        return;
                    }
                    this.loading = true;
                    try {
                        const liveData = await VueApp.apiClient.getMatches({ state: 'live' });
                        if (liveData && liveData.success && liveData.data) {
                            this.matches.live = Array.isArray(liveData.data) ? liveData.data : [];
                        }
                    } catch (error) {
                        console.error('Failed to refresh:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                formatDate(date) {
                    return new Date(date).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });
                }
            }
            }).mount('#app');
        }
        
        // Initialize when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initVueApp);
        } else {
            initVueApp();
        }
    </script>
    
    <style>
        .admin-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-around;
            padding: 0.75rem 0;
            box-shadow: var(--shadow-lg);
            z-index: 100;
        }
        
        .admin-bottom-bar .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--gray-500);
            transition: var(--transition);
            border-radius: var(--radius);
        }
        
        .admin-bottom-bar .nav-item.active {
            color: var(--primary);
            background: var(--gray-50);
        }
        
        .admin-bottom-bar .nav-icon {
            font-size: 1.5rem;
        }
        
        .admin-bottom-bar .nav-label {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        @media (min-width: 769px) {
            .admin-bottom-bar {
                display: none;
            }
            .admin-container {
                padding-bottom: 2rem;
            }
        }
    </style>
</body>
</html>


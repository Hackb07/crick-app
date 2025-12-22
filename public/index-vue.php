<?php
/**
 * Public Portal - Home Page (Vue.js Version)
 * Modern Vue.js frontend with upgraded design
 */

// Load core PHP bootstrap
require_once __DIR__ . '/../includes/bootstrap.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLiveMatches();
$recentMatches = $matchModel->getRecentMatches(5);
$scheduledMatches = $matchModel->getScheduledMatches();

// Convert to JSON for Vue.js
$matchesData = [
    'live' => $liveMatches,
    'recent' => $recentMatches,
    'scheduled' => $scheduledMatches
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Scoring - Live Scores & Match Updates</title>
    
    <!-- Vue.js 3 CDN (Shared Hosting Compatible) -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Modern CSS -->
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/vue-modern.css') ?>">
    
    <style>
        body {
            background: var(--gray-50);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Modern Header -->
        <header class="header-modern">
            <div class="header-content">
                <div class="header-title">
                    🏏 Cricket Scoring
                </div>
                <nav class="header-nav">
                    <a href="<?= publicUrl() ?>" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'home' }]" style="color: white; background: rgba(255,255,255,0.2);">Home</a>
                    <a href="<?= publicUrl('matches.php') ?>" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'matches' }]" style="color: white; background: rgba(255,255,255,0.2);">Matches</a>
                    <a href="<?= publicUrl('live.php') ?>" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'live' }]" style="color: white; background: rgba(255,255,255,0.2);">Live</a>
                    <a href="<?= publicUrl('leaderboard.php') ?>" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'leaderboard' }]" style="color: white; background: rgba(255,255,255,0.2);">Leaderboard</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container-modern">
            <!-- Live Matches Section -->
            <section class="section-modern">
                <h2>🔥 Live Matches</h2>
                <div v-if="loading.live" class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading live matches...</p>
                </div>
                <div v-else-if="!matches.live || matches.live.length === 0" class="empty-state-modern">
                    <div class="empty-icon">📺</div>
                    <div class="empty-title">No Live Matches</div>
                    <div class="empty-message">Check back soon for live cricket action!</div>
                </div>
                <div v-else class="matches-grid-modern">
                    <match-card v-for="match in matches.live" :key="match.match_id" :match="match"></match-card>
                </div>
            </section>

            <!-- Recent Matches Section -->
            <section class="section-modern">
                <h2>📅 Recent Matches</h2>
                <div v-if="loading.recent" class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="!matches.recent || matches.recent.length === 0" class="empty-state-modern">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">No Recent Matches</div>
                </div>
                <div v-else class="matches-grid-modern">
                    <match-card v-for="match in matches.recent" :key="match.match_id" :match="match"></match-card>
                </div>
                <div v-if="matches.recent.length > 0" style="text-align: center; margin-top: 1.5rem;">
                    <a href="/cricapp/public/recent-matches.php" class="btn-modern btn-secondary">View All Recent Matches</a>
                </div>
            </section>

            <!-- Scheduled Matches Section -->
            <section class="section-modern">
                <h2>📆 Scheduled Matches</h2>
                <div v-if="loading.scheduled" class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="!matches.scheduled || matches.scheduled.length === 0" class="empty-state-modern">
                    <div class="empty-icon">📅</div>
                    <div class="empty-title">No Scheduled Matches</div>
                </div>
                <div v-else class="matches-grid-modern">
                    <match-card v-for="match in matches.scheduled" :key="match.match_id" :match="match"></match-card>
                </div>
                <div v-if="matches.scheduled.length > 0" style="text-align: center; margin-top: 1.5rem;">
                    <a href="/cricapp/public/scheduled-matches.php" class="btn-modern btn-secondary">View All Scheduled Matches</a>
                </div>
            </section>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav-modern">
            <a href="/cricapp/public/" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="/cricapp/public/matches.php" class="nav-item">
                <span class="nav-icon">📅</span>
                <span class="nav-label">Matches</span>
            </a>
            <a href="/cricapp/public/live.php" class="nav-item">
                <span class="nav-icon">⚡</span>
                <span class="nav-label">Live</span>
            </a>
            <a href="/cricapp/public/leaderboard.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                <span class="nav-label">Leaderboard</span>
            </a>
            <a href="/cricapp/public/profile.php" class="nav-item">
                <span class="nav-icon">👤</span>
                <span class="nav-label">Profile</span>
            </a>
        </nav>
    </div>

    <!-- JavaScript Config - Output dynamic paths -->
    <?php outputJsConfig(); ?>
    
    <!-- Vue App Script - Load after Vue.js -->
    <script src="<?= assetUrl('js/vue-app.js') ?>"></script>
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
            
            createApp({
            data() {
                const matchesData = <?= json_encode($matchesData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                // Determine current page
                const path = window.location.pathname;
                let currentPage = 'home';
                if (path.includes('/live')) currentPage = 'live';
                else if (path.includes('/matches')) currentPage = 'matches';
                else if (path.includes('/leaderboard')) currentPage = 'leaderboard';
                
                return {
                    matches: {
                        live: Array.isArray(matchesData.live) ? matchesData.live : [],
                        recent: Array.isArray(matchesData.recent) ? matchesData.recent : [],
                        scheduled: Array.isArray(matchesData.scheduled) ? matchesData.scheduled : []
                    },
                    loading: {
                        live: false,
                        recent: false,
                        scheduled: false
                    },
                    currentPage: currentPage
                };
            },
            mounted() {
                // Auto-refresh live matches every 10 seconds
                if (this.matches.live && this.matches.live.length > 0) {
                    setInterval(() => {
                        this.refreshLiveMatches();
                    }, 10000);
                }
            },
            methods: {
                async refreshLiveMatches() {
                    if (!VueApp || !VueApp.apiClient) {
                        console.error('API client not available');
                        return;
                    }
                    this.loading.live = true;
                    try {
                        const data = await VueApp.apiClient.getMatches({ state: 'live' });
                        if (data && data.success && data.data) {
                            this.matches.live = Array.isArray(data.data) ? data.data : [];
                        }
                    } catch (error) {
                        console.error('Failed to refresh live matches:', error);
                    } finally {
                        this.loading.live = false;
                    }
                }
            },
            components: VueApp.VueComponents || {}
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
        .bottom-nav-modern {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0;
            box-shadow: var(--shadow-lg);
            z-index: 100;
        }
        
        .nav-item {
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
        
        .nav-item.active {
            color: var(--primary);
        }
        
        .nav-icon {
            font-size: 1.25rem;
        }
        
        .nav-label {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .container-modern {
            padding-bottom: 80px; /* Space for bottom nav */
        }
        
        @media (min-width: 769px) {
            .bottom-nav-modern {
                display: none;
            }
            .container-modern {
                padding-bottom: 2rem;
            }
        }
    </style>
</body>
</html>


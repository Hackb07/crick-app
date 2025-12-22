<?php
/**
 * Public Profile Page - Settings and saved preferences
 */

require_once __DIR__ . '/../includes/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Cricket Scoring</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/public.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
    <style>
        @media (max-width: 768px) {
            .compact-header {
                padding: 6px 10px !important;
            }
            
            .compact-header div:first-child {
                font-size: 0.75rem !important;
            }
            
            #menu-toggle {
                padding: 4px 8px !important;
                font-size: 1.125rem !important;
            }
            
            #menu-dropdown {
                right: 0 !important;
                min-width: 160px !important;
            }
            
            #menu-dropdown a {
                padding: 10px 14px !important;
                font-size: 0.8125rem !important;
            }

            .card-premium {
                padding: 0.875rem !important;
            }

            .card-premium h3 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <header class="compact-header" style="position: sticky; top: 0; z-index: 200; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 8px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; position: relative;">
            <div style="font-size: 0.875rem; font-weight: 600;">🏏 Cricket</div>
            <div style="position: relative;">
                <button id="menu-toggle" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.25rem; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'" onclick="toggleMenu()">☰</button>
                <div id="menu-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 180px; z-index: 201;">
                    <a href="/cricapp/public/" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏠 Home</a>
                    <a href="/cricapp/public/leaderboard.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏆 Leaderboard</a>
                    <a href="/cricapp/public/matches.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">📅 Matches</a>
                    <a href="/cricapp/public/live.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; font-size: 0.875rem;">⚡ Live</a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            const menu = document.getElementById('menu-dropdown');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('menu-dropdown');
            const toggle = document.getElementById('menu-toggle');
            if (menu && !menu.contains(event.target) && event.target !== toggle) {
                menu.style.display = 'none';
            }
        });
    </script>

    <main class="container main-content">
        <div style="margin-bottom: 1.5rem;">
            <h1>👤 Profile</h1>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">Your preferences and settings</p>
        </div>

        <!-- Settings -->
        <div class="card-premium" style="margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Settings</h3>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--border-light);">
                <div>
                    <div style="font-weight: var(--font-weight-semibold);">Dark Mode</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Switch to dark theme</div>
                </div>
                <label style="position: relative; display: inline-block; width: 48px; height: 24px;">
                    <input type="checkbox" style="opacity: 0; width: 0; height: 0;" onchange="toggleDarkMode(this.checked)">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--bg-soft); border-radius: 24px; transition: 0.3s;">
                        <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s;"></span>
                    </span>
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
                <div>
                    <div style="font-weight: var(--font-weight-semibold);">Notifications</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Match updates and alerts</div>
                </div>
                <label style="position: relative; display: inline-block; width: 48px; height: 24px;">
                    <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--primary); border-radius: 24px; transition: 0.3s;">
                        <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 27px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s;"></span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Saved Teams -->
        <div class="card-premium" style="margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 1rem;">Saved Teams</h3>
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                No saved teams yet
            </div>
        </div>

        <!-- About -->
        <div class="card-premium">
            <h3 style="margin-bottom: 1rem;">About</h3>
            <div style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.6;">
                <p style="margin-bottom: 1rem;">
                    CricApp - Premium Cricket Scoring Application
                </p>
                <p style="margin-bottom: 0.5rem;">
                    Version: 1.0.0
                </p>
                <p>
                    <a href="/cricapp/admin/login.php" style="color: var(--primary); text-decoration: none;">
                        Admin Login →
                    </a>
                </p>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav" id="bottom-nav">
        <div class="bottom-nav-container">
            <a href="/cricapp/public/" class="bottom-nav-item">
                <span class="bottom-nav-icon">🏠</span>
                <span class="bottom-nav-label">Home</span>
            </a>
            <a href="/cricapp/public/matches.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">📅</span>
                <span class="bottom-nav-label">Matches</span>
            </a>
            <a href="/cricapp/public/live.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">⚡</span>
                <span class="bottom-nav-label">Live</span>
            </a>
            <a href="/cricapp/public/leaderboard.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">🏆</span>
                <span class="bottom-nav-label">Leaderboard</span>
            </a>
            <a href="/cricapp/public/profile.php" class="bottom-nav-item active">
                <span class="bottom-nav-icon">👤</span>
                <span class="bottom-nav-label">Profile</span>
            </a>
        </div>
    </nav>

    <script src="/cricapp/assets/js/bottom-nav.js"></script>
    <script>
        function toggleDarkMode(enabled) {
            // Dark mode implementation (placeholder)
            document.body.classList.toggle('dark-mode', enabled);
            localStorage.setItem('darkMode', enabled);
        }

        // Load saved preference
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            document.body.classList.add('dark-mode');
            document.querySelector('input[type="checkbox"]').checked = true;
        }
    </script>
</body>
</html>


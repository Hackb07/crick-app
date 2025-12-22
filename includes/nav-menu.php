
<!-- Hamburger Menu Component -->
<style>
    /* Hamburger Menu */
    .hamburger {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1000;
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .hamburger:hover {
        background: var(--primary);
    }
    
    .hamburger span {
        width: 24px;
        height: 3px;
        background: #334155;
        margin: 3px 0;
        transition: all 0.3s;
        border-radius: 2px;
    }
    
    .hamburger:hover span {
        background: white;
    }
    
    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(6px, 6px);
    }
    
    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }
    
    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(6px, -6px);
    }
    
    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: -300px;
        width: 300px;
        height: 100vh;
        background: white;
        box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        transition: left 0.3s;
        z-index: 999;
        overflow-y: auto;
    }
    
    .sidebar.active {
        left: 0;
    }
    
    .sidebar-header {
        padding: 30px 20px;
        background: linear-gradient(135deg, var(--primary) 0%, #00b894 100%);
        color: white;
    }
    
    .sidebar-header h2 {
        font-size: 20px;
        margin-bottom: 4px;
    }
    
    .sidebar-header p {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .sidebar-menu {
        padding: 20px 0;
    }
    
    .menu-item {
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }
    
    .menu-item:hover {
        background: #f1f5f9;
        border-left-color: var(--primary);
        color: var(--primary);
    }
    
    .menu-item.active {
        background: #e0f2fe;
        border-left-color: var(--primary);
        color: var(--primary);
        font-weight: 600;
    }
    
    .menu-icon {
        font-size: 20px;
    }
    
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 998;
        display: none;
    }
    
    .overlay.active {
        display: block;
    }
    
    /* Fix header to prevent overlap */
    .app-header {
        padding-left: 80px; /* Make room for hamburger */
    }
    
    @media (max-width: 768px) {
        .app-header {
            padding-left: 80px;
        }
    }
</style>

<!-- Hamburger Button -->
<div class="hamburger" id="hamburger">
    <span></span>
    <span></span>
    <span></span>
</div>

<!-- Sidebar Menu -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>🏏 CricApp</h2>
        <p>Live Cricket Scores</p>
    </div>
    
    <div class="sidebar-menu">
        <a href="<?= publicUrl('index.php') ?>" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <span class="menu-icon">🏠</span>
            <span>Home</span>
        </a>
        <a href="<?= publicUrl('matches.php') ?>" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'matches.php' ? 'active' : '' ?>">
            <span class="menu-icon">🏏</span>
            <span>Matches</span>
        </a>
        <a href="<?= publicUrl('series.php') ?>" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'series.php' ? 'active' : '' ?>">
            <span class="menu-icon">🏆</span>
            <span>Series</span>
        </a>
        <a href="<?= publicUrl('leaderboard.php') ?>" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'leaderboard.php' ? 'active' : '' ?>">
            <span class="menu-icon">📊</span>
            <span>Leaderboard</span>
        </a>
        <?php
        // Safe check for session state to prevent "headers already sent" warnings
        $isLoggedIn = false;
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $isLoggedIn = true;
        }
        
        if ($isLoggedIn):
        ?>
            <a href="user-dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'user-dashboard.php' ? 'active' : '' ?>">
                <span class="menu-icon">📊</span>
                <span>My Dashboard</span>
            </a>
            <a href="create-match.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'create-match.php' ? 'active' : '' ?>">
                <span class="menu-icon">➕</span>
                <span>Create Match</span>
            </a>
            <a href="user-logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        <?php else: ?>
            <a href="user-login.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'user-login.php' ? 'active' : '' ?>">
                <span class="menu-icon">👤</span>
                <span>User Login</span>
            </a>
        <?php endif; ?>
        <a href="settings.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <span class="menu-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay"></div>

<script>
    // Hamburger menu toggle
    (function() {
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        if (!hamburger || !sidebar || !overlay) return;
        
        function toggleMenu() {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        hamburger.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });
    })();
</script>

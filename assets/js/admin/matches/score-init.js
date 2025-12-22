/**
 * Score Page - Initialization
 * Handles page load initialization and event listener setup
 */

// Match data (will be injected from PHP)
let matchData = {};

/**
 * Initialize the scoring page
 * Called on page load with data from PHP
 */
function initializeScoringPage(phpData) {
    // Initialize state
    initializeState(phpData);

    // Sanitize state
    if (typeof currentOvers !== 'undefined') currentOvers = Math.floor(currentOvers);

    // API endpoints and Match Data are now global constants/variables
    // No need to re-assign them from phpData if they are already set globally in scorer.php

    // Ensure matchData is populated if not already (legacy support)
    if (typeof matchData === 'undefined' || Object.keys(matchData).length === 0) {
        matchData = {
            matchId: MATCH_CONFIG.matchId,
            team1_id: MATCH_CONFIG.team1_id,
            team2_id: MATCH_CONFIG.team2_id
        };
    }

    // Initialize UI with current selections
    if (document.getElementById('striker')?.value) updateStriker();
    if (document.getElementById('non-striker')?.value) updateNonStriker();
    if (document.getElementById('bowler')?.value) updateBowler();

    // Update displays
    updateScoreDisplay();
    updateCurrentOver();
    updatePlayerStats();

    // Setup modal event listeners
    setupModalEventListeners();

    // Setup keyboard shortcuts (optional)
    setupKeyboardShortcuts();

    // Check if innings is complete on load
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        disableScoringButtons();
    }

    console.log('Scoring page initialized successfully');
}

/**
 * Setup modal event listeners
 */
function setupModalEventListeners() {
    // Close modals when clicking outside
    const modals = [
        'wicket-modal',
        'extra-runs-modal',
        'new-batsman-modal',
        'retired-hurt-modal',
        'fielder-modal',
        'run-out-modal'
    ];

    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        }
    });
}

/**
 * Setup keyboard shortcuts for quick scoring
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Only enable shortcuts if no modal is open and no input is focused
        const activeModal = document.querySelector('.wicket-modal.active');
        const focusedInput = document.activeElement.tagName === 'INPUT' ||
            document.activeElement.tagName === 'SELECT' ||
            document.activeElement.tagName === 'TEXTAREA';

        if (activeModal || focusedInput) return;

        // Number keys 0-6 for runs
        if (e.key >= '0' && e.key <= '6') {
            e.preventDefault();
            recordRun(parseInt(e.key));
        }

        // W for wicket
        if (e.key === 'w' || e.key === 'W') {
            e.preventDefault();
            showWicketModal();
        }

        // B for bye
        if (e.key === 'b' || e.key === 'B') {
            e.preventDefault();
            recordExtra('bye');
        }

        // L for leg-bye
        if (e.key === 'l' || e.key === 'L') {
            e.preventDefault();
            recordExtra('leg-bye');
        }

        // N for no-ball
        if (e.key === 'n' || e.key === 'N') {
            e.preventDefault();
            recordExtra('no-ball');
        }

        // D for wide
        if (e.key === 'd' || e.key === 'D') {
            e.preventDefault();
            recordExtra('wide');
        }

        // U for undo
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
            e.preventDefault();
            undoLastBall();
        }
    });
}

/**
 * Mobile sidebar toggle
 */
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

/**
 * Close sidebar
 */
function closeSidebar() {
    const sidebar = document.querySelector('.app-sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebar) {
        sidebar.classList.remove('open');
    }
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Close sidebar on window resize
window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024) {
        closeSidebar();
    }
});

// Service Worker disabled for admin panel (needs real-time data)
// PWA features are only enabled for public-facing pages
/*
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        const basePath = window.APP_BASE_PATH || '';
        const swPath = basePath ? basePath + '/sw.js' : '/sw.js';

        navigator.serviceWorker.register(swPath, {
            scope: basePath ? basePath + '/' : '/'
        })
            .then(function (reg) {
                console.log('Service Worker registered successfully:', reg.scope);
            })
            .catch(function (err) {
                console.error('Service Worker registration failed:', err);
            });
    });
}
*/

/**
 * Open Player Selection Modal
 * @param {string} type - 'striker', 'non-striker', or 'bowler'
 */

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, initializing scoring page...');

    // Check if we have the required global variables
    if (typeof MATCH_CONFIG === 'undefined') {
        console.error('MATCH_CONFIG not found. Scorer page cannot initialize.');
        return;
    }

    // Initialize the scoring page
    initializeScoringPage({
        matchId: MATCH_CONFIG.matchId,
        currentInnings: MATCH_CONFIG.currentInnings,
        maxOvers: MATCH_CONFIG.maxOvers,
        team1_id: MATCH_CONFIG.team1_id,
        team2_id: MATCH_CONFIG.team2_id
    });

    console.log('Scoring page initialization complete');
});

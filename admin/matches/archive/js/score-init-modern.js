/**
 * Modern Score Page - Initialization
 * Compatibility layer - checks if functions exist before setting up alternatives
 * Works alongside existing score-*.js files
 */

(function () {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('🚀 Modern Score Page Init - Checking compatibility');

        // Only set up fallback if core functions don't exist
        if (typeof recordRun === 'undefined') {
            console.log('⚠️ Core functions not found, setting up fallback');
            setupScoringButtonListeners();
        } else {
            console.log('✅ Existing scoring functions found');
        }

        // Load initial state (from existing score-init.js) 
        if (typeof loadInitialState === 'function') {
            loadInitialState();
        }
    }

    /**
     * Fallback: Setup scoring button listeners using event delegation
     * ONLY used if existing functions don't exist
     */
    function setupScoringButtonListeners() {
        const scoringSection = document.querySelector('.scoring-section');
        if (!scoringSection) return;

        scoringSection.addEventListener('click', function (e) {
            const button = e.target.closest('button[data-action]');
            if (!button) return;

            const action = button.dataset.action;

            switch (action) {
                case 'run':
                    const runs = parseInt(button.dataset.runs);
                    if (!isNaN(runs) && typeof recordRun === 'function') {
                        recordRun(runs);
                    }
                    break;

                case 'extra':
                    const extraType = button.dataset.extraType;
                    if (extraType && typeof recordExtra === 'function') {
                        recordExtra(extraType);
                    }
                    break;

                case 'wicket':
                    if (typeof confirmWicket === 'function') {
                        confirmWicket();
                    }
                    break;

                case 'swap':
                    if (typeof swapBatsmen === 'function') {
                        swapBatsmen(e);
                    }
                    break;

                case 'undo':
                    if (typeof undoLastBall === 'function') {
                        undoLastBall();
                    }
                    break;
            }
        });
    }

    // Expose init function globally if needed
    window.initModernScore = init;

})();

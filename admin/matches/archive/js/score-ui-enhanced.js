/**
 * Score.php UI/UX Enhancement JavaScript
 * Handles toast notifications, loading states, and UI interactions
 */

// ========================================
// TOAST NOTIFICATION SYSTEM
// ========================================

const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'info', duration = 3000) {
        this.init();

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        this.container.appendChild(toast);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    },

    success(message, duration) {
        return this.show(message, 'success', duration);
    },

    error(message, duration) {
        return this.show(message, 'error', duration);
    },

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

// Add slide out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========================================
// LOADING OVERLAY
// ========================================

const LoadingOverlay = {
    overlay: null,

    show(message = 'Loading...') {
        if (!this.overlay) {
            this.overlay = document.createElement('div');
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-text">${message}</div>
            `;
            document.body.appendChild(this.overlay);
        }
        this.overlay.style.display = 'flex';
    },

    hide() {
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
    },

    updateMessage(message) {
        if (this.overlay) {
            const textEl = this.overlay.querySelector('.loading-text');
            if (textEl) {
                textEl.textContent = message;
            }
        }
    }
};

// ========================================
// BUTTON LOADING STATE
// ========================================

function setButtonLoading(button, loading = true) {
    if (loading) {
        button.classList.add('btn-loading');
        button.disabled = true;
        button.dataset.originalText = button.textContent;
    } else {
        button.classList.remove('btn-loading');
        button.disabled = false;
        if (button.dataset.originalText) {
            button.textContent = button.dataset.originalText;
        }
    }
}

// ========================================
// ENHANCED RUN BUTTON INTERACTIONS
// ========================================

function enhanceRunButtons() {
    const runButtons = document.querySelectorAll('.run-btn');

    runButtons.forEach(button => {
        // Add ripple effect on click
        button.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.className = 'ripple';

            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;

            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        // Add haptic feedback for mobile
        button.addEventListener('touchstart', function () {
            if (navigator.vibrate) {
                navigator.vibrate(10);
            }
        });
    });
}

// Add ripple animation
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .run-btn {
        position: relative;
        overflow: hidden;
    }
`;
document.head.appendChild(rippleStyle);

// ========================================
// PLAYER CARD ANIMATIONS
// ========================================

function animatePlayerCards() {
    const playerCards = document.querySelectorAll('.player-card');

    playerCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.3s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// ========================================
// MODAL ENHANCEMENTS
// ========================================

function enhanceModals() {
    // Close modal on overlay click
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            const modal = e.target.closest('.modal-overlay');
            if (modal) {
                closeModal(modal);
            }
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => closeModal(modal));
        }
    });
}

function closeModal(modal) {
    modal.style.animation = 'fadeOut 0.2s ease-out';
    const container = modal.querySelector('.modal-container');
    if (container) {
        container.style.animation = 'slideDown 0.3s ease-out';
    }

    setTimeout(() => {
        modal.remove();
    }, 300);
}

// Add modal animations
const modalStyle = document.createElement('style');
modalStyle.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(20px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(modalStyle);

// ========================================
// SCORE UPDATE ANIMATIONS
// ========================================

function animateScoreUpdate(element, newValue) {
    element.style.transform = 'scale(1.2)';
    element.style.color = '#3b82f6';

    setTimeout(() => {
        element.textContent = newValue;
        element.style.transform = 'scale(1)';
        element.style.color = '';
    }, 200);
}

// ========================================
// LIVE STATS CALCULATOR
// ========================================

function updateLiveStats() {
    // Safely access global variables from score.php
    const globalMaxOvers = window.maxOvers || 20;
    const globalCurrentInnings = window.currentInnings || 1;

    const currentScore = parseInt(document.querySelector('.score-big')?.textContent?.split('/')[0] || 0);
    const currentOvers = parseFloat(document.querySelector('.score-small')?.textContent?.replace(/[()]/g, '') || 0);

    // Current Run Rate
    const crr = currentOvers > 0 ? (currentScore / currentOvers).toFixed(2) : '0.00';
    const crrElement = document.getElementById('run-rate');
    if (crrElement) {
        crrElement.textContent = crr;
    }

    // Required Run Rate (2nd innings only)
    if (globalCurrentInnings === 2) {
        const runsNeeded = parseInt(document.getElementById('runs-needed')?.textContent || 0);
        const oversLeft = globalMaxOvers - currentOvers;
        const rrr = oversLeft > 0 ? (runsNeeded / oversLeft).toFixed(2) : '0.00';

        const rrrElement = document.getElementById('required-rr');
        if (rrrElement) {
            rrrElement.textContent = rrr;

            // Color code based on difficulty
            if (parseFloat(rrr) > parseFloat(crr) + 3) {
                rrrElement.style.color = '#dc2626'; // Red - difficult
            } else if (parseFloat(rrr) > parseFloat(crr)) {
                rrrElement.style.color = '#f59e0b'; // Orange - challenging
            } else {
                rrrElement.style.color = '#10b981'; // Green - comfortable
            }
        }
    }

    // Projected Score (1st innings only)
    if (globalCurrentInnings === 1) {
        const projected = currentOvers > 0 ? Math.round((currentScore / currentOvers) * globalMaxOvers) : 0;
        const projectedElement = document.getElementById('projected-score');
        if (projectedElement) {
            projectedElement.textContent = projected;
        }
    }
}

// ========================================
// KEYBOARD SHORTCUTS
// ========================================

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        // Don't trigger if typing in input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
            return;
        }

        // Number keys for runs (0-6)
        if (e.key >= '0' && e.key <= '6') {
            const runBtn = document.querySelector(`[data-runs="${e.key}"]`);
            if (runBtn) {
                runBtn.click();
                runBtn.classList.add('keyboard-pressed');
                setTimeout(() => runBtn.classList.remove('keyboard-pressed'), 200);
            }
        }

        // W for wicket
        if (e.key.toLowerCase() === 'w') {
            const wicketBtn = document.querySelector('[data-action="wicket"]');
            if (wicketBtn) wicketBtn.click();
        }

        // E for extras
        if (e.key.toLowerCase() === 'e') {
            const extraBtn = document.querySelector('[data-action="extra"]');
            if (extraBtn) extraBtn.click();
        }

        // U for undo
        if (e.key.toLowerCase() === 'u' && e.ctrlKey) {
            e.preventDefault();
            const undoBtn = document.querySelector('[data-action="undo"]');
            if (undoBtn) undoBtn.click();
        }
    });
}

// Add keyboard press effect
const keyboardStyle = document.createElement('style');
keyboardStyle.textContent = `
    .keyboard-pressed {
        transform: scale(0.95) !important;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;
    }
`;
document.head.appendChild(keyboardStyle);

// ========================================
// INITIALIZE ON PAGE LOAD
// ========================================

document.addEventListener('DOMContentLoaded', function () {
    // Initialize toast system
    Toast.init();

    // Enhance UI elements
    enhanceRunButtons();
    enhanceModals();
    animatePlayerCards();
    setupKeyboardShortcuts();

    // Update live stats every 500ms
    setInterval(updateLiveStats, 500);

    // Show welcome message
    setTimeout(() => {
        Toast.info('Keyboard shortcuts: 0-6 for runs, W for wicket, E for extras', 5000);
    }, 1000);

    console.log('✅ Score UI/UX enhancements loaded');
});

// Export for use in other scripts
window.ScoreUI = {
    Toast,
    LoadingOverlay,
    setButtonLoading,
    animateScoreUpdate,
    updateLiveStats
};

/**
 * Score Page - Utility Functions
 * Helper functions for calculations and validations
 */

/**
 * Check if innings is complete
 * @param {number} wickets - Current wicket count
 * @param {number} completedOvers - Number of completed overs
 * @param {number} currentBalls - Current balls in the over (0-5)
 * @param {number} maxOvers - Maximum overs for the innings
 * @returns {boolean}
 */
function isInningsComplete(wickets, completedOvers, currentBalls, maxOvers) {
    // Check wickets
    const teamMaxWickets = typeof maxWickets !== 'undefined' ? maxWickets : 10;
    if (wickets >= teamMaxWickets) return true;

    // Calculate total overs
    const totalOvers = completedOvers + (currentBalls / 6);

    // Check if max overs reached
    if (totalOvers >= maxOvers) return true;

    // For innings 2, check if target is reached
    if (currentInnings === 2 && firstInningsTotal > 0) {
        const target = firstInningsTotal + 1;
        if (currentScore >= target) {
            return true;
        }
    }

    return false;
}

/**
 * Calculate overs from balls
 * @param {number} balls - Total balls
 * @returns {number} - Overs in decimal format (e.g., 2.3)
 */
function calculateOvers(balls) {
    const completedOvers = Math.floor(balls / 6);
    const remainingBalls = balls % 6;
    return completedOvers + (remainingBalls / 10);
}

/**
 * Calculate run rate
 * @param {number} runs - Total runs
 * @param {number} balls - Total balls
 * @returns {number} - Run rate per over
 */
function calculateRunRate(runs, balls) {
    if (balls === 0) return 0.0;
    return (runs / balls) * 6;
}

/**
 * Calculate target score
 * @param {number} firstInningsTotal - First innings total
 * @returns {number} - Target score
 */
function calculateTarget(firstInningsTotal) {
    return firstInningsTotal + 1;
}

/**
 * Calculate required run rate
 * @param {number} target - Target score
 * @param {number} currentScore - Current score
 * @param {number} remainingOvers - Remaining overs
 * @returns {number} - Required run rate
 */
function calculateRequiredRunRate(target, currentScore, remainingOvers) {
    const remainingRuns = target - currentScore;
    if (remainingRuns <= 0 || remainingOvers <= 0) {
        return 0.0;
    }
    return remainingRuns / remainingOvers;
}

/**
 * Check if a ball is legal (counts towards over completion)
 * @param {string} type - Event type ('run', 'wicket', 'extra')
 * @param {string} extraType - Extra type if applicable ('wide', 'no-ball', 'bye', 'leg-bye')
 * @returns {boolean}
 */
function isLegalBall(type, extraType) {
    if (type === 'extra') {
        // Wide and no-ball don't count as legal deliveries
        return !(extraType === 'wide' || extraType === 'no-ball');
    }
    // Runs and wickets count as legal balls
    return true;
}

/**
 * Check if strike should rotate after runs
 * @param {number} runs - Runs scored
 * @param {boolean} isNoBall - Whether it was a no-ball
 * @returns {boolean}
 */
function shouldRotateStrike(runs, isNoBall) {
    // Odd runs (1, 3, 5) rotate strike
    return (runs % 2 === 1);
}

/**
 * Generate a UUID for event tracking
 * @returns {string} - UUID string
 */
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

/**
 * Trigger haptic feedback
 * @param {number} duration - Duration in ms
 */
function vibrateOnTap(duration = 15) {
    if (navigator.vibrate) {
        navigator.vibrate(duration);
    }
}

/**
 * Set loading state for buttons
 * @param {boolean} isLoading - Whether to show loading state
 */
function setLoadingState(isLoading) {
    const buttons = document.querySelectorAll('.c-btn');
    buttons.forEach(btn => {
        if (isLoading) {
            btn.classList.add('loading');
            btn.disabled = true;
        } else {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    });
}

/**
 * Show toast notification
 * @param {string} title - Notification title
 * @param {string} message - Notification message
 * @param {string} type - 'success', 'error', or 'info'
 */
function showNotification(title, message, type = 'info') {
    // Remove existing toasts
    const existing = document.querySelectorAll('.toast-notification');
    existing.forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
        <div class="toast-icon">${type === 'success' ? '✅' : (type === 'error' ? '❌' : 'ℹ️')}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
    `;

    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    // Auto dismiss
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Alias showToast to showNotification for compatibility
const showToast = showNotification;

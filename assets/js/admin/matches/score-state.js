/**
 * Score Page - State Management
 * Manages global state variables for the scoring interface
 * 
 * NOTE: ALL state variables are declared in score.php inline script (lines 359-396)
 * This file is kept for organizational purposes and future state management functions
 */

// Pending wicket data (for modal flow) - only variable not in score.php
let pendingWicketData = null;

/**
 * Initialize additional state from PHP data (if needed)
 * Core variables are already initialized in score.php
 * @param {Object} phpData - Data passed from PHP
 */
function initializeState(phpData) {
    // All variables are already set in score.php inline script
    // This function can be used for any additional initialization if needed
    console.log('State initialized for match:', MATCH_CONFIG.matchId);
}

/**
 * Global Error Handler (@quality:errors)
 * Catches runtime errors (like ReferenceError) and shows visible feedback
 */
window.onerror = function (message, source, lineno, colno, error) {
    console.error('Global Error Caught:', message, error);

    // Attempt to show user-friendly message
    const displayMsg = String(message).replace('Uncaught ReferenceError:', 'Missing Function:');

    if (typeof Toast !== 'undefined' && Toast.error) {
        Toast.error('System Error: ' + displayMsg);
    }

    return false; // Allow default console logging
};

window.onunhandledrejection = function (event) {
    console.error('Unhandled Promise Rejection:', event.reason);
};

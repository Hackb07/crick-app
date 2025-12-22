/**
 * Score Page - UI Update Functions
 * Handles all UI updates for players, scores, and display elements
 * Updated for New 3-Panel Design
 */

/**
 * Update striker dropdown and display
 */
function updateStriker() {
    const select = document.getElementById('striker');
    if (!select) return;

    const playerId = select.value;
    const playerName = select.options[select.selectedIndex]?.text || 'Select Striker';

    if (playerId) {
        currentStrikerId = parseInt(playerId);

        // Update display view
        const display = document.getElementById('striker-name-display');
        const status = document.getElementById('striker-status');
        const card = document.querySelector('.player-row[onclick*="striker"]');

        if (display) display.textContent = playerName;
        if (status) status.classList.add('active');
        if (card) card.classList.add('active'); // Highlight active striker card

        if (!playerStats.batsmen[playerId]) {
            playerStats.batsmen[playerId] = { runs: 0, balls: 0, fours: 0, sixes: 0 };
        }
        updateStrikerStats();
    } else {
        currentStrikerId = null;

        const display = document.getElementById('striker-name-display');
        if (display) display.textContent = 'Select Striker';
    }
}

/**
 * Update non-striker dropdown and display
 */
function updateNonStriker() {
    const select = document.getElementById('non-striker');
    if (!select) return;

    const playerId = select.value;
    const playerName = select.options[select.selectedIndex]?.text || 'Select Non-Striker';

    if (playerId) {
        currentNonStrikerId = parseInt(playerId);

        // Update display view
        const display = document.getElementById('non-striker-name-display');
        if (display) display.textContent = playerName;

        if (!playerStats.batsmen[playerId]) {
            playerStats.batsmen[playerId] = { runs: 0, balls: 0, fours: 0, sixes: 0 };
        }
        updateNonStrikerStats();
    } else {
        currentNonStrikerId = null;

        const display = document.getElementById('non-striker-name-display');
        if (display) display.textContent = 'Select Non-Striker';
    }
}

/**
 * Update bowler dropdown and display
 */
function updateBowler() {
    const select = document.getElementById('bowler');
    if (!select) return;

    const playerId = select.value;
    const playerName = select.options[select.selectedIndex]?.text || 'Select Bowler';

    if (playerId) {
        // Reset bowler ball count when changing bowler
        if (currentBowlerId !== parseInt(playerId)) {
            currentBowlerBalls = 0;
            userChangedBowlerAfterOver = true;
        }
        currentBowlerId = parseInt(playerId);

        // Update display view
        const display = document.getElementById('bowler-name-display');
        if (display) display.textContent = playerName;

        if (!playerStats.bowlers[playerId]) {
            playerStats.bowlers[playerId] = { runs: 0, balls: 0, wickets: 0, overs: 0, maidens: 0 };
        }
        updateBowlerStats();
        updateBowlerSelectState();
    } else {
        currentBowlerId = null;
        currentBowlerBalls = 0;

        const display = document.getElementById('bowler-name-display');
        if (display) display.textContent = 'Select Bowler';

        updateBowlerSelectState();
    }
}

/**
 * Update bowler select disabled state
 */
function updateBowlerSelectState() {
    const bowlerSelect = document.getElementById('bowler');
    if (!bowlerSelect) return;

    bowlerSelect.disabled = false;
    bowlerSelect.title = '';
    bowlerSelect.style.opacity = '1';
    bowlerSelect.style.cursor = 'pointer';
}

/**
 * Update striker statistics display
 */
function updateStrikerStats() {
    if (!currentStrikerId || !playerStats.batsmen[currentStrikerId]) return;

    const stats = playerStats.batsmen[currentStrikerId];

    const runsEl = document.getElementById('striker-runs-display');
    const ballsEl = document.getElementById('striker-balls-display');

    if (runsEl) runsEl.textContent = stats.runs;
    if (ballsEl) ballsEl.textContent = stats.balls;
}

/**
 * Update non-striker statistics display
 */
function updateNonStrikerStats() {
    if (!currentNonStrikerId || !playerStats.batsmen[currentNonStrikerId]) return;

    const stats = playerStats.batsmen[currentNonStrikerId];

    const runsEl = document.getElementById('non-striker-runs-display');
    const ballsEl = document.getElementById('non-striker-balls-display');

    if (runsEl) runsEl.textContent = stats.runs;
    if (ballsEl) ballsEl.textContent = stats.balls;
}

/**
 * Update bowler statistics display
 */
function updateBowlerStats() {
    if (!currentBowlerId || !playerStats.bowlers[currentBowlerId]) return;

    const stats = playerStats.bowlers[currentBowlerId];
    const overs = Math.floor(stats.balls / 6);
    const balls = stats.balls % 6;
    const oversStr = overs + '.' + balls;
    const oversDecimal = overs + (balls / 6);
    const economy = oversDecimal > 0 ? (stats.runs / oversDecimal).toFixed(1) : '0.0';

    // Elements
    const oversEl = document.getElementById('bowler-overs-display');
    const maidensEl = document.getElementById('bowler-maidens-display');
    const runsEl = document.getElementById('bowler-runs-display');
    const wicketsEl = document.getElementById('bowler-wickets-display');
    const ecoEl = document.getElementById('bowler-eco-display');

    if (oversEl) oversEl.textContent = oversStr;
    if (maidensEl) maidensEl.textContent = stats.maidens || 0;
    if (runsEl) runsEl.textContent = stats.runs;
    if (wicketsEl) wicketsEl.textContent = stats.wickets;
    if (ecoEl) ecoEl.textContent = economy;
}

/**
 * Update all player statistics
 */
function updatePlayerStats() {
    updateStrikerStats();
    updateNonStrikerStats();
    updateBowlerStats();
}

/**
 * Update score display
 */
function updateScoreDisplay() {
    // Current score string (Runs/Wickets)
    const runsEl = document.getElementById('score-runs-display');
    const wicketsEl = document.getElementById('score-wickets-display');

    if (runsEl) runsEl.textContent = currentScore;
    if (wicketsEl) wicketsEl.textContent = currentWickets;

    // Overs display
    const overs = Math.floor(currentBalls / 6);
    const balls = currentBalls % 6;
    const oversStr = '(' + (currentOvers + overs) + '.' + balls + ')';

    const oversEl = document.getElementById('score-overs-display');
    if (oversEl) oversEl.textContent = oversStr;

    // Update run rate
    const totalBalls = (currentOvers * 6) + currentBalls;
    const runRate = calculateRunRate(currentScore, totalBalls).toFixed(2);
    const rrEl = document.getElementById('run-rate');
    if (rrEl) rrEl.textContent = runRate;

    // Update required run rate for innings 2
    if (typeof currentInnings !== 'undefined' && currentInnings === 2 && typeof firstInningsTotal !== 'undefined') {
        const target = calculateTarget(firstInningsTotal);
        // Calculate balls remaining in innings (assuming maxOvers global is set)
        const oversPlayed = currentOvers + (currentBalls / 6);
        const remainingOvers = (typeof maxOvers !== 'undefined' ? maxOvers : 20) - oversPlayed;

        const reqRR = calculateRequiredRunRate(target, currentScore, remainingOvers).toFixed(2);

        const reqRREl = document.getElementById('required-rr');
        const needsEl = document.getElementById('runs-needed');

        if (reqRREl) reqRREl.textContent = reqRR;
        if (needsEl) needsEl.textContent = Math.max(0, target - currentScore);
    }

    // Update projected for innings 1
    if (typeof currentInnings !== 'undefined' && currentInnings === 1) {
        const projEl = document.getElementById('projected-score');
        if (projEl && totalBalls > 0) {
            const crr = currentScore / (totalBalls / 6);
            const remaining = (typeof maxOvers !== 'undefined' ? maxOvers : 20) * 6 - totalBalls;
            const proj = Math.round(currentScore + (crr * (remaining / 6)));
            projEl.textContent = proj;
        }
    }
}

/**
 * Update current over display
 * Populates the #ball-tracker in the middle panel
 */
function updateCurrentOver() {
    // NOTE: This function might be redundant if we use addBallToTracker directly, 
    // but useful for rebuilding state on reload

    // Reuse addBallToTracker logic mainly, or rebuild from currentOverState
    const tracker = document.getElementById('ball-tracker');
    if (!tracker) return;

    // Clear and rebuild
    tracker.innerHTML = '';

    if (typeof currentOverState !== 'undefined' && currentOverState.balls) {
        currentOverState.balls.forEach(ball => {
            // Mapping formatted ball data to display
            createBallElement(ball, tracker);
        });
    }
}

/**
 * Helper to create ball element
 */
function createBallElement(ballData, container) {
    const ballDiv = document.createElement('div');
    ballDiv.className = 'ball-item';

    let text = ballData.runs;
    let typeClass = 'ball-run'; // Default

    if (ballData.isWicket) {
        text = 'W';
        typeClass = 'ball-wicket';
    } else if (ballData.runs === 4) {
        text = '4';
        typeClass = 'ball-four';
    } else if (ballData.runs === 6) {
        text = '6';
        typeClass = 'ball-six';
    } else if (ballData.isExtra) {
        text = ballData.extraType ? ballData.extraType.substring(0, 2).toUpperCase() : 'EX';
        typeClass = 'ball-extra';

        // Handle specific extras better
        if (ballData.extraType === 'wide') text = 'WD';
        else if (ballData.extraType === 'no-ball') text = 'NB';
        else if (ballData.extraType === 'bye') text = 'BYE'; // Full text fits
        else if (ballData.extraType === 'leg-bye') text = 'LB';
    } else if (ballData.runs === 0) {
        text = '•'; // Dot ball
        typeClass = 'ball-dot';
    }

    ballDiv.textContent = text;
    ballDiv.classList.add(typeClass);

    container.appendChild(ballDiv);
}

/**
 * Swap striker and non-striker
 */
function swapStrike() {
    const temp = currentStrikerId;
    currentStrikerId = currentNonStrikerId;
    currentNonStrikerId = temp;

    const strikerSelect = document.getElementById('striker');
    const nonStrikerSelect = document.getElementById('non-striker');

    if (currentStrikerId && strikerSelect) {
        strikerSelect.value = currentStrikerId;
        updateStriker();
    }
    if (currentNonStrikerId && nonStrikerSelect) {
        nonStrikerSelect.value = currentNonStrikerId;
        updateNonStriker();
    }
}

/**
 * Filter bowler dropdown
 */
function filterBowlerDropdown(excludeBowlerId) {
    if (!excludeBowlerId) return;

    const bowlerSelect = document.getElementById('bowler');
    if (!bowlerSelect) return;

    Array.from(bowlerSelect.options).forEach(option => {
        if (option.value == excludeBowlerId) {
            option.disabled = true;
            option.textContent = option.textContent.replace(' (bowled last over)', '') + ' (bowled last over)';
        } else {
            option.disabled = false;
            option.textContent = option.textContent.replace(' (bowled last over)', '');
        }
    });
}

/**
 * Enable/Disable buttons
 */
function enableScoringButtons() {
    document.querySelectorAll('.btn-run, .btn-boundary, .btn-extra, .btn-wicket').forEach(btn => {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    });
}

function disableScoringButtons() {
    document.querySelectorAll('.btn-run, .btn-boundary, .btn-extra, .btn-wicket').forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    });
}

/**
 * Add ball to tracker (called live)
 */
function addBallToTracker(ballData) {
    const tracker = document.getElementById('ball-tracker');
    if (!tracker) return;

    // Map the incoming data to what createBallElement expects
    // ballData typically comes from score-events.js

    const displayData = {
        runs: ballData.runs,
        isWicket: ballData.type === 'wicket',
        isExtra: ballData.type === 'extra',
        extraType: ballData.extra_type
    };

    createBallElement(displayData, tracker);

    // Scroll to end
    tracker.scrollLeft = tracker.scrollWidth;
}

/**
 * Clear ball tracker
 */
function clearBallTracker() {
    const tracker = document.getElementById('ball-tracker');
    if (tracker) {
        tracker.innerHTML = '';
    }
}

/**
 * Display Over Notification
 */
// showOverNotification is handled by score-modals.js

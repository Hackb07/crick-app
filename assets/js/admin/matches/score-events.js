/**
 * Score Page - Event Handling
 * Handles all scoring events: runs, extras, wickets, and ball recording
 */

// API endpoint (will be injected from PHP)
// API endpoint (injected from PHP)
// const eventsApiUrl is defined in score.php

/**
 * Record a run
 * @param {number} runs - Number of runs scored
 */
function recordRun(runs) {
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        alert('Innings is complete! Please start 2nd innings.');
        return;
    }

    recordBall({
        type: 'run',
        runs: runs
    });
}

/**
 * Record an extra
 * @param {string} type - Extra type ('wide', 'no-ball', 'bye', 'leg-bye')
 */
function recordExtra(type) {
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        alert('Innings is complete! Please start 2nd innings.');
        return;
    }

    // Wide and no-ball can have additional runs
    if (type === 'wide' || type === 'no-ball') {
        showExtraRunsModal(type);
    } else {
        // Bye and leg-bye default to 1 run
        recordBall({
            type: 'extra',
            extra_type: type,
            runs: 1
        });
    }
}

/**
 * Validates that striker and bowler are selected
 * @returns {Object|null} {striker, nonStriker, bowler} or null if invalid
 */
function validateScoringSelection() {
    const striker = document.getElementById('striker').value;
    const nonStriker = document.getElementById('non-striker').value;
    const bowler = document.getElementById('bowler').value;

    if (!striker || !bowler) {
        alert('Please select striker and bowler');
        return null;
    }
    return { striker, nonStriker, bowler };
}

/**
 * Checks if bowler can bowl (rotation rule)
 * @param {string} bowler - Selected bowler ID
 * @returns {boolean} True if allowed
 */
function checkBowlerRotation(bowler) {
    if (currentBalls === 0 && lastOverBowlerId && String(bowler) === String(lastOverBowlerId)) {
        alert('Bowler cannot bowl consecutive overs. Please select a different bowler.');
        const bowlerSelect = document.getElementById('bowler');
        bowlerSelect.value = '';
        currentBowlerId = null;
        currentBowlerBalls = 0;

        const compactElements = {
            name: document.getElementById('bowler-name-compact'),
            card: document.getElementById('bowler-compact'),
            stats: document.getElementById('bowler-stats-compact')
        };

        if (compactElements.name) compactElements.name.textContent = 'Not Selected';
        if (compactElements.card) compactElements.card.classList.remove('active');
        if (compactElements.stats) compactElements.stats.style.display = 'none';

        return false;
    }
    return true;
}

/**
 * Creates the event object and payload for API/History
 * @param {Object} data - Input ball data
 * @param {Object} selection - {striker, nonStriker, bowler}
 * @returns {Object} {event, eventData}
 */
function createEventObject(data, selection) {
    const payload = {
        type: data.type,
        runs: data.runs || 0,
        striker_id: parseInt(selection.striker),
        non_striker_id: selection.nonStriker ? parseInt(selection.nonStriker) : null,
        bowler_id: parseInt(selection.bowler),
        extra_type: data.extra_type || null,
        dismissal_type: data.dismissal_type || null,
        fielder_id: data.fielder_id || null,
        innings: currentInnings
    };

    // Handle Run Out (Non-Striker)
    if (data._runOutPlayer === 'non_striker') {
        [payload.striker_id, payload.non_striker_id] = [payload.non_striker_id, payload.striker_id];
    }

    if (data.type === 'wicket' && data.new_batsman_id) payload.new_batsman_id = data.new_batsman_id;
    if (data.type === 'retired_hurt') {
        payload.retired_player_id = data.retired_player_id;
        payload.replacement_player_id = data.replacement_player_id;
    }

    const event = {
        event_uuid: generateUUID(),
        client_id: 'web-scorer-' + Date.now(),
        client_ts: new Date().toISOString(),
        client_base_seq: serverSeq,
        ball_index: currentBalls,
        appearance_id: null,
        payload_json: payload
    };

    const eventHistoryData = {
        ...payload,
        timestamp: Date.now()
    };

    return { event, eventHistoryData };
}

/**
 * Updates local state and player statistics
 * @param {Object} data - Ball data
 * @param {string} striker - Striker ID
 * @param {string} bowler - Bowler ID
 */
function updateLocalStats(data, striker, bowler) {
    if (data.type === 'run') {
        currentScore += data.runs || 0;
        currentBalls++;
        if (playerStats.batsmen[striker]) {
            playerStats.batsmen[striker].runs += data.runs || 0;
            playerStats.batsmen[striker].balls++;
            if (data.runs === 4) playerStats.batsmen[striker].fours++;
            if (data.runs === 6) playerStats.batsmen[striker].sixes++;
        }
        if (playerStats.bowlers[bowler]) {
            playerStats.bowlers[bowler].runs += data.runs || 0;
            playerStats.bowlers[bowler].balls++;
        }
        currentOverRuns += data.runs || 0;
        currentOverLegalBalls++;
        if (shouldRotateStrike(data.runs || 0, false)) swapStrike();
    } else if (data.type === 'wicket') {
        currentWickets++;
        currentBalls++;
        if (playerStats.batsmen[striker]) playerStats.batsmen[striker].balls++;
        if (playerStats.bowlers[bowler]) {
            playerStats.bowlers[bowler].wickets++;
            playerStats.bowlers[bowler].balls++;
        }
        currentOverLegalBalls++;
        currentOverBalls.push('W');
        if (data._newBatsmanId) {
            document.getElementById('striker').value = data._newBatsmanId;
            updateStriker();
        }
    } else if (data.type === 'extra') {
        const extraRuns = data.runs || 1;
        currentScore += extraRuns;
        if (playerStats.bowlers[bowler]) playerStats.bowlers[bowler].runs += extraRuns;
        currentOverRuns += extraRuns;
        currentOverExtras += extraRuns;
        if ((data.extra_type === 'wide' || data.extra_type === 'no-ball') && extraRuns > 1) {
            if (shouldRotateStrike(extraRuns - 1, data.extra_type === 'no-ball')) swapStrike();
        }
        if (isLegalBall('extra', data.extra_type)) {
            currentBalls++;
            currentOverLegalBalls++;
            if (playerStats.bowlers[bowler]) playerStats.bowlers[bowler].balls++;
        }
    }
}

/**
 * Handles over and innings completion logic
 * @param {Object} data - Ball data
 */
function handleOverInningsCompletion(data) {
    const isLegalDelivery = isLegalBall(data.type, data.extra_type);
    if (isLegalDelivery && currentBowlerId) {
        currentBowlerBalls++;
        updateBowlerSelectState();
    }
    if (isLegalDelivery && currentBalls % 6 === 0 && currentBalls > 0) {
        currentOvers++;
        currentBalls = 0;
        currentOverBalls = [];
        swapStrike();
        showOverNotification();
        lastOverBowlerId = currentBowlerId;
    }
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        checkInningsCompletion();
    }
}

/**
 * Main function to record a ball event (Refactored)
 * @param {Object} data - Ball data {type, runs?, extra_type?, dismissal_type?}
 */
function recordBall(data) {
    const selection = validateScoringSelection();
    if (!selection) return;

    if (!checkBowlerRotation(selection.bowler)) return;

    const { event, eventHistoryData } = createEventObject(data, selection);
    eventHistory.push(eventHistoryData);

    saveEvent(event)
        .then(result => {
            if (result.success) {
                if (result.server_seq) serverSeq = result.server_seq;
                clientSeq++;
                userChangedBowlerAfterOver = false;

                updateLocalStats(data, selection.striker, selection.bowler);

                // Add to Current Over State for tracker
                const ballObj = {
                    runs: data.runs || 0,
                    isWicket: data.type === 'wicket',
                    isExtra: data.type === 'extra',
                    extraType: data.extra_type
                };
                if (typeof currentOverState !== 'undefined') {
                    if (!currentOverState.balls) currentOverState.balls = [];
                    currentOverState.balls.push(ballObj);
                }

                // Legacy tracker update
                if (typeof currentOverBalls !== 'undefined') {
                    if (data.type === 'run') currentOverBalls.push(data.runs);
                    else if (data.type === 'wicket') currentOverBalls.push('W');
                    else if (data.type === 'extra') {
                        currentOverBalls.push((data.extra_type === 'wide' ? 'Wd' : 'Nb') + (data.runs > 1 ? data.runs : ''));
                    }
                }

                updateScoreDisplay();
                updateCurrentOver();
                updatePlayerStats();
                handleOverInningsCompletion(data);
                document.getElementById('undo-btn').disabled = false;
            } else {
                alert('Error: ' + (result.error || 'Failed to record ball'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to record ball');
        });
}

/**
 * Helper to manually create ball element (Fallback)
 * @param {Object} data - Ball data
 * @param {HTMLElement} container - Tracker container
 */
function createBallElementFallback(data, container) {
    const ballDiv = document.createElement('div');
    ballDiv.className = 'ball-item';

    switch (data.type) {
        case 'wicket':
            ballDiv.className += ' ball-wicket';
            ballDiv.textContent = 'W';
            break;
        case 'run':
            const runClasses = { 4: 'ball-four', 6: 'ball-six', 0: 'ball-dot' };
            const runTexts = { 0: '•' };
            ballDiv.className += ` ${runClasses[data.runs] || 'ball-run'}`;
            ballDiv.textContent = runTexts[data.runs] || data.runs;
            break;
        case 'extra':
            ballDiv.className += ' ball-extra';
            const extraLabels = { wide: 'WD', 'no-ball': 'NB', bye: 'BYE', 'leg-bye': 'LB' };
            ballDiv.textContent = extraLabels[data.extra_type] || 'EX';
            break;
    }
    container.appendChild(ballDiv);
}

/**
 * Add recent ball to display
 * @param {Object} data - Ball data
 */
function addRecentBall(data) {
    const tracker = document.getElementById('ball-tracker');
    if (!tracker) return;

    const ballData = {
        runs: data.runs || 0,
        isWicket: data.type === 'wicket',
        isExtra: data.type === 'extra',
        extraType: data.extra_type
    };

    if (typeof createBallElement === 'function') {
        createBallElement(ballData, tracker);
    } else {
        createBallElementFallback(data, tracker);
    }

    tracker.scrollLeft = tracker.scrollWidth;

    if (typeof currentOverBalls === 'undefined') return;

    switch (data.type) {
        case 'run': currentOverBalls.push(data.runs); break;
        case 'wicket': currentOverBalls.push('W'); break;
        case 'extra':
            const prefix = data.extra_type === 'wide' ? 'Wd' : 'Nb';
            currentOverBalls.push(prefix + (data.runs > 1 ? data.runs : ''));
            break;
    }
}

/**
 * Reverts player stats for an event
 * @param {Object} event - Event to revert
 */
function revertStatsForEvent(event) {
    const { type, runs, striker_id, bowler_id, extra_type } = event;
    const batsman = playerStats.batsmen[striker_id];
    const bowler = playerStats.bowlers[bowler_id];

    switch (type) {
        case 'run':
            currentScore -= runs || 0;
            currentBalls--;
            if (batsman) {
                batsman.runs -= runs || 0;
                batsman.balls--;
                if (runs === 4) batsman.fours--;
                if (runs === 6) batsman.sixes--;
            }
            if (bowler) {
                bowler.runs -= runs || 0;
                bowler.balls--;
            }
            break;
        case 'wicket':
            currentWickets--;
            currentBalls--;
            if (batsman) batsman.balls--;
            if (bowler) {
                bowler.wickets--;
                bowler.balls--;
            }
            break;
        case 'extra':
            const extraRuns = runs || 1;
            currentScore -= extraRuns;
            if (bowler) bowler.runs -= extraRuns;
            if (isLegalBall('extra', extra_type)) {
                currentBalls--;
                if (bowler) bowler.balls--;
            }
            break;
    }
}

/**
 * Undo the last ball (Refactored)
 */
function undoLastBall() {
    if (eventHistory.length === 0) return;
    if (!confirm('Undo last ball? This cannot be undone.')) return;

    const lastEvent = eventHistory.pop();
    revertStatsForEvent(lastEvent);

    // Adjust overs if needed
    if (currentBalls < 0) {
        currentOvers--;
        currentBalls = 5;
    }

    updateScoreDisplay();
    updateCurrentOver();
    updatePlayerStats();
    loadMatchState();

    if (eventHistory.length === 0) {
        document.getElementById('undo-btn').disabled = true;
    }
}

/**
 * Confirm wicket - opens wicket modal
 */
function confirmWicket() {
    showWicketModal();
}

/**
 * Swap batsmen (change strike)
 * @param {Event} event - Click event
 */
function swapBatsmen(event) {
    if (event) {
        event.preventDefault();
    }

    if (!currentStrikerId || !currentNonStrikerId) {
        if (window.Toast) {
            Toast.warning('Both batsmen must be selected to swap');
        } else {
            alert('Both batsmen must be selected to swap');
        }
        return;
    }

    // Swap the IDs
    const tempId = currentStrikerId;
    currentStrikerId = currentNonStrikerId;
    currentNonStrikerId = tempId;

    // Update the UI
    const strikerSelect = document.getElementById('striker');
    const nonStrikerSelect = document.getElementById('non-striker');

    if (strikerSelect && nonStrikerSelect) {
        strikerSelect.value = currentStrikerId;
        nonStrikerSelect.value = currentNonStrikerId;

        updateStriker();
        updateNonStriker();
    }

    if (window.Toast) {
        Toast.success('Batsmen swapped');
    }
}

/**
 * Select striker from mobile list
 * @param {number} playerId - Player ID
 * @param {string} playerName - Player name
 */
function selectStriker(playerId, playerName) {
    const strikerSelect = document.getElementById('striker');
    if (strikerSelect) {
        strikerSelect.value = playerId;
        updateStriker();
    }

    // Close the modal
    closeModal();

    if (window.Toast) {
        Toast.success(`${playerName} selected as striker`);
    }
}

/**
 * Select non-striker from mobile list
 * @param {number} playerId - Player ID
 * @param {string} playerName - Player name
 */
function selectNonStriker(playerId, playerName) {
    const nonStrikerSelect = document.getElementById('non-striker');
    if (nonStrikerSelect) {
        nonStrikerSelect.value = playerId;
        updateNonStriker();
    }

    // Close the modal
    closeModal();

    if (window.Toast) {
        Toast.success(`${playerName} selected as non-striker`);
    }
}

/**
 * Select bowler from mobile list
 * @param {number} playerId - Player ID
 * @param {string} playerName - Player name
 */
function selectBowler(playerId, playerName) {
    const bowlerSelect = document.getElementById('bowler');
    if (bowlerSelect) {
        bowlerSelect.value = playerId;
        updateBowler();
    }

    // Close the modal
    closeModal();

    if (window.Toast) {
        Toast.success(`${playerName} selected as bowler`);
    }
}

/**
 * Score Page - API Functions
 * Handles all API calls for match state, innings changes, and finalization
 */

// API endpoints (will be injected from PHP)
// API endpoints (injected from PHP)
// const matchesApiUrl, eventsApiEndpoint defined in score.php

/**
 * Change innings (start 2nd innings)
 */
function changeInningsAjax() {
    if (!confirm('Change to 2nd innings? This will reset the scoring interface.')) {
        return;
    }

    fetch(matchesApiUrl + '/' + currentMatchId + '/change-innings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON. Check server logs for PHP errors.');
                });
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                closeStartInningsModal();

                showNotification(
                    '✅ 2nd Innings Started!',
                    'Redirecting to 2nd innings scoring...',
                    'success'
                );

                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                const errorMsg = result.error || 'Failed to change innings';
                console.error('Change innings error:', errorMsg);
                alert('Error: ' + errorMsg);
            }
        })
        .catch(error => {
            console.error('Change innings error:', error);
            alert('Failed to change innings: ' + error.message);
        });
}

/**
 * Finalize match when innings 2 completes
 * @param {boolean} won - True if batting team won
 */
function finalizeMatch(won) {
    const battingTeamId = currentInnings === 1 ? MATCH_CONFIG.team1_id : MATCH_CONFIG.team2_id;
    const team1Id = MATCH_CONFIG.team1_id;
    const team2Id = MATCH_CONFIG.team2_id;

    let winnerId;
    if (won) {
        winnerId = battingTeamId;
    } else {
        winnerId = (battingTeamId === team1Id) ? team2Id : team1Id;
    }

    fetch(matchesApiUrl + '/' + currentMatchId + '/finalize', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            winner_id: winnerId
        })
    })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                showNotification(
                    '🏆 Match Complete!',
                    'Match has been finalized. Redirecting...',
                    'success'
                );

                setTimeout(() => {
                    window.location.href = matchViewUrl;
                }, 2000);
            } else {
                console.error('Finalize error:', result.error);
                alert('Error finalizing match: ' + (result.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Finalize error:', error);
            alert('Failed to finalize match: ' + error.message);
        });
}

/**
 * Fetch events from API
 */
function fetchEvents() {
    fetch(eventsApiEndpoint + '?match_id=' + currentMatchId + '&innings=' + currentInnings, {
        credentials: 'same-origin'
    })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Events non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
            return response.json();
        })
        .then(result => {
            if (result.success && result.events) {
                // Process events
                updateRecentBalls(result.events);
            }
        })
        .catch(error => {
            console.error('Fetch events error:', error);
        });
}

/**
 * Updates a state object based on an event payload
 * @param {Object} state - {score, wickets, balls}
 * @param {Object} payload - Event payload
 */
function updateCalculatedStateFromEvent(state, payload) {
    switch (payload.type) {
        case 'run':
            state.score += payload.runs || 0;
            state.balls++;
            break;
        case 'wicket':
            state.wickets++;
            state.balls++;
            break;
        case 'extra':
            state.score += payload.runs || 1;
            if (payload.extra_type !== 'wide' && payload.extra_type !== 'no-ball') {
                state.balls++;
            }
            break;
    }
}

/**
 * Processes a list of events to calculate match state
 * @param {Array} events - List of event objects
 * @returns {Object} Calculated state
 */
function processEventsForState(events) {
    const state = { score: 0, wickets: 0, balls: 0, strikerId: null, nonStrikerId: null, bowlerId: null, currentInningsEvents: [] };

    events.forEach(event => {
        const payload = JSON.parse(event.payload_json || '{}');
        if ((payload.innings || 1) !== currentInnings) return;

        state.currentInningsEvents.push(event);
        updateCalculatedStateFromEvent(state, payload);

        if (payload.striker_id) state.strikerId = payload.striker_id;
        if (payload.non_striker_id) state.nonStrikerId = payload.non_striker_id;
        if (payload.bowler_id) state.bowlerId = payload.bowler_id;
    });

    return state;
}

/**
 * Synchronizes select elements with current state
 * @param {Object} state - Calculated match state
 */
function syncDropdowns(state) {
    const selectors = {
        striker: document.getElementById('striker'),
        nonStriker: document.getElementById('non-striker'),
        bowler: document.getElementById('bowler')
    };

    if (state.strikerId && selectors.striker && selectors.striker.value != state.strikerId) {
        selectors.striker.value = state.strikerId;
        updateStriker();
    }
    if (state.nonStrikerId && selectors.nonStriker && selectors.nonStriker.value != state.nonStrikerId) {
        selectors.nonStriker.value = state.nonStrikerId;
        updateNonStriker();
    }
    if (state.bowlerId && selectors.bowler && selectors.bowler.value != state.bowlerId && !userChangedBowlerAfterOver) {
        selectors.bowler.value = state.bowlerId;
        updateBowler();
    }
}

/**
 * Updates all scoring UI components
 */
function updateScoringUI() {
    updateScoreDisplay();
    updateCurrentOver();
    updatePlayerStats();

    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        disableScoringButtons();
    } else {
        enableScoringButtons();
    }
}

/**
 * Load match state from server (Refactored)
 */
function loadMatchState() {
    fetch(`${eventsApiEndpoint}?match_id=${currentMatchId}&innings=${currentInnings}`, {
        credentials: 'same-origin'
    })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Events non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
            return response.json();
        })
        .then(result => {
            if (!result.success || !result.events) return;

            const state = processEventsForState(result.events);

            // Update globals
            currentScore = state.score;
            currentWickets = state.wickets;
            currentBalls = state.balls % 6;
            currentOvers = Math.floor(state.balls / 6);

            if (state.strikerId) currentStrikerId = state.strikerId;
            if (state.nonStrikerId) currentNonStrikerId = state.nonStrikerId;
            if (state.bowlerId) currentBowlerId = state.bowlerId;

            syncDropdowns(state);
            updateScoringUI();

            document.getElementById('undo-btn').disabled = eventHistory.length === 0;
        })
        .catch(error => {
            console.error('Load match state error:', error);
        });
}

/**
 * Update recent balls display
 * @param {Array} events - Array of event objects
 */
function updateRecentBalls(events) {
    const recentBallsCompact = document.getElementById('recent-balls-compact');
    if (recentBallsCompact) {
        recentBallsCompact.innerHTML = '';

        if (events.length === 0) {
            recentBallsCompact.innerHTML = '<div style="color: var(--text-secondary); font-size: 0.75rem;">No balls yet</div>';
        } else {
            events.slice(-12).forEach(event => {
                const payload = JSON.parse(event.payload_json || '{}');
                const ballDiv = document.createElement('div');
                ballDiv.className = 'ball-badge-compact';

                if (payload.type === 'run') {
                    ballDiv.className += ' ball-' + payload.runs;
                    ballDiv.textContent = payload.runs;
                } else if (payload.type === 'wicket') {
                    ballDiv.className += ' ball-w';
                    ballDiv.textContent = 'W';
                } else if (payload.type === 'extra') {
                    ballDiv.className += ' ball-' + (payload.extra_type === 'wide' ? 'wb' : 'nb');
                    ballDiv.textContent = payload.extra_type === 'wide' ? 'Wd' : 'Nb';
                }

                recentBallsCompact.appendChild(ballDiv);
            });
        }
    }
}

/**
 * Check innings completion and show modal
 */
function checkInningsCompletion() {
    if (currentInnings === 1) {
        disableScoringButtons();
        showNotification(
            '🏏 1st Innings Complete!',
            'Ready to start 2nd innings?',
            'success'
        );
        showStartInningsModal();
    } else {
        // Innings 2 complete - determine winner
        disableScoringButtons();
        const target = firstInningsTotal + 1;
        const won = currentScore >= target;

        if (won) {
            showNotification(
                '🎉 Match Won!',
                `Target achieved! ${currentScore}/${currentWickets}`,
                'success'
            );
        } else {
            showNotification(
                '📊 Match Complete!',
                `All out or overs complete. ${currentScore}/${currentWickets}`,
                'info'
            );
        }

        // Auto-finalize after 3 seconds
        setTimeout(() => {
            finalizeMatch(won);
        }, 3000);
    }
}

/**
 * Show start innings modal
 */
function showStartInningsModal() {
    const modal = document.getElementById('start-innings-modal');
    if (modal) {
        const scoreEl = document.getElementById('modal-innings-score');
        if (scoreEl && typeof currentScore !== 'undefined') {
            scoreEl.textContent = currentScore + '/' + currentWickets;
        }

        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }
}

/**
 * Close start innings modal
 */
function closeStartInningsModal() {
    const modal = document.getElementById('start-innings-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
}
/**
 * Offline Support Logic
 */
// offlineQueue is already declared in the PHP-injected script
// Just ensure it's initialized from localStorage if needed
if (typeof offlineQueue === 'undefined') {
    // This should never happen, but as a fallback
    offlineQueue = JSON.parse(localStorage.getItem('score_offline_queue') || '[]');
} else if (offlineQueue.length === 0) {
    // Reload from localStorage if empty
    const stored = JSON.parse(localStorage.getItem('score_offline_queue') || '[]');
    if (stored.length > 0) {
        offlineQueue = stored;
    }
}
let isOnline = navigator.onLine;


window.addEventListener('online', () => {
    isOnline = true;
    showNotification('🌐 Online', 'Connection restored. Syncing...', 'success');
    processOfflineQueue();
});

window.addEventListener('offline', () => {
    isOnline = false;
    showNotification('📡 Offline', 'You are offline. Changes will be saved locally.', 'info');
});

/**
 * Save event (send to server or queue if offline)
 * @param {Object} eventData - The event payload
 * @returns {Promise}
 */
function saveEvent(eventData) {
    return new Promise((resolve, reject) => {
        if (isOnline) {
            sendEventToServer(eventData)
                .then(result => resolve(result))
                .catch(error => {
                    console.error('Send failed, queuing:', error);
                    queueEvent(eventData);
                    resolve({ success: true, offline: true });
                });
        } else {
            queueEvent(eventData);
            resolve({ success: true, offline: true });
        }
    });
}

/**
 * Queue event for later sync
 * @param {Object} eventData 
 */
function queueEvent(eventData) {
    offlineQueue.push({
        data: eventData,
        timestamp: Date.now()
    });
    localStorage.setItem('score_offline_queue', JSON.stringify(offlineQueue));
    updateOfflineIndicator();
}

/**
 * Process offline queue (Batched)
 */
function processOfflineQueue() {
    if (offlineQueue.length === 0 || !isOnline) return;

    // Batch size of 10 as per API specification
    const BATCH_SIZE = 10;
    const batch = offlineQueue.slice(0, BATCH_SIZE);
    const eventPayloads = batch.map(item => item.data);

    showNotification(
        '🔄 Syncing',
        `Batch uploading ${batch.length} of ${offlineQueue.length} pending events...`,
        'info'
    );

    sendEventToServer(eventPayloads)
        .then(result => {
            if (result.success) {
                // Remove the successfully processed batch from the queue
                offlineQueue.splice(0, batch.length);
                localStorage.setItem('score_offline_queue', JSON.stringify(offlineQueue));

                if (offlineQueue.length > 0) {
                    // Slight delay before next batch to avoid flooding but maintain speed
                    setTimeout(processOfflineQueue, 300);
                } else {
                    showNotification('✅ Synced', 'All events uploaded successfully', 'success');
                    updateOfflineIndicator();
                    // Optional: reload state if many events were synced to ensure UI is perfectly in sync
                    if (batch.length > 3) {
                        loadMatchState();
                    }
                }
            }
        })
        .catch(error => {
            console.error('Sync error:', error);
            updateOfflineIndicator();
        });
}

/**
 * Send events to server in batch
 * @param {Array} events - Array of event payloads
 */
function sendEventToServer(events) {
    if (!Array.isArray(events)) {
        events = [events];
    }

    return fetch(eventsApiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            match_id: currentMatchId,
            client_base_seq: serverSeq,
            events: events
        })
    })
        .then(response => {
            if (!response.ok && response.status !== 409) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        });
}

/**
 * Update offline indicator UI
 */
function updateOfflineIndicator() {
    const indicator = document.getElementById('offline-indicator');
    if (indicator) {
        if (offlineQueue.length > 0) {
            indicator.style.display = 'block';
            indicator.textContent = `⚠️ ${offlineQueue.length} unsynced`;
        } else {
            indicator.style.display = 'none';
        }
    }
}

// Check queue on load
if (offlineQueue.length > 0) {
    setTimeout(processOfflineQueue, 2000);
}

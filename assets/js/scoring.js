/**
 * Scoring Interface Logic
 * 5th-grade friendly scoring system
 */

// Ensure api is available
if (typeof api === 'undefined') {
    console.error('API client not loaded. Please ensure api.js is loaded before scoring.js');
}

let currentMatchData = null;
let eventQueue = [];
let lastServerSeq = 0;
let currentBall = 1; // Current ball in the over (1-6)
let currentOver = 0; // Current over number
let matchEvents = []; // Cache of match events for calculating current ball
let currentInnings = 1; // Current innings (1 or 2)
let totalRuns = 0;
let totalWickets = 0;
let maxOversPerInnings = 0; // Set from match data

// Player tracking
let currentStriker = null; // {appearance_id, player_id, player_name}
let currentNonStriker = null;
let currentBowler = null;
let lastBowler = null; // Track last bowler to prevent consecutive overs

// Track dismissed batsmen (appearance_ids) to filter wicket dropdown
let dismissedBatsmen = new Set(); // Set of appearance_ids who have been dismissed

// Player stats cache
let playerStats = {}; // {appearance_id: {runs, balls, fours, sixes, wickets, overs, runs_conceded}}

/**
 * Load current match data
 */
async function loadMatchData() {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        if (typeof toast !== 'undefined') {
            toast.error('Match ID is not defined. Please refresh the page.');
        }
        return;
    }
    
    try {
        const response = await api.getMatch(matchId);
        if (!response || !response.success) {
            console.error('Failed to load match data - invalid response:', response);
            if (typeof toast !== 'undefined') {
                toast.error('Failed to load match data. Please refresh the page.');
            }
            return;
        }
        
        if (response.data) {
            const previousInnings = currentInnings;
            currentMatchData = response.data;
            maxOversPerInnings = parseFloat(currentMatchData.overs_per_innings || 20);
            
            // Update current innings
            if (currentMatchData.current_innings) {
                currentInnings = parseInt(currentMatchData.current_innings);
            } else {
                // Default to 1 if not set
                currentInnings = 1;
            }
            
            // If innings changed (e.g., from 1 to 2), reset scoring state IMMEDIATELY
            if (previousInnings !== currentInnings && currentInnings === 2) {
                console.log('Innings changed from 1 to 2 - resetting scoring state');
                resetInningsState();
                
                // Also clear matchEvents so we start fresh for innings 2
                matchEvents = [];
                totalRuns = 0;
                totalWickets = 0;
                currentOver = 0;
                currentBall = 1;
                
                // Update display immediately
                updateScorecard();
            }
            
            // Check if innings is complete and show button
            updateCompleteInningsButton();
        } else {
            console.error('Match data is empty:', response);
            if (typeof toast !== 'undefined') {
                toast.error('Match data is empty. Please refresh the page.');
            }
            return;
        }
        
        // Load events to calculate current ball/over (filtered by innings if needed)
        await loadEvents();
        updateScorecard();
        checkInningsComplete();
        updateCompleteInningsButton();
    } catch (error) {
        console.error('Failed to load match data:', error);
        if (typeof toast !== 'undefined') {
            toast.error('Error loading match data: ' + (error.message || 'Unknown error') + '. Please refresh the page.');
        }
    }
}

/**
 * Reset scoring state for new innings
 */
function resetInningsState() {
    currentOver = 0;
    currentBall = 1;
    totalRuns = 0;
    totalWickets = 0;
    currentStriker = null;
    currentNonStriker = null;
    currentBowler = null;
    playerStats = {};
    // Don't clear matchEvents here - let loadEvents() handle filtering by innings
    // matchEvents = [];
    
    // Clear dropdown selections
    const strikerSelect = document.getElementById('striker-select');
    const nonStrikerSelect = document.getElementById('non-striker-select');
    const bowlerSelect = document.getElementById('bowler-select');
    
    if (strikerSelect) strikerSelect.value = '';
    if (nonStrikerSelect) nonStrikerSelect.value = '';
    if (bowlerSelect) bowlerSelect.value = '';
    
    // Clear displays
    updatePlayerDisplays();
    updateScorecard();
}

/**
 * Update complete innings button visibility
 */
function updateCompleteInningsButton() {
    const completeSection = document.getElementById('complete-innings-section');
    if (!completeSection) return;
    
    const isComplete = checkInningsComplete();
    const isFirstInnings = currentInnings === 1;
    
    if (isComplete && isFirstInnings) {
        completeSection.style.display = 'block';
    } else {
        completeSection.style.display = 'none';
    }
}

/**
 * Load match events and calculate current state
 */
async function loadEvents() {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        return;
    }
    
    try {
        const response = await api.getMatchEvents(matchId);
        if (!response || !response.success) {
            console.error('Failed to load events - invalid response:', response);
            // Reset to safe state for new match
            matchEvents = [];
            currentOver = 0;
            currentBall = 1;
            totalRuns = 0;
            totalWickets = 0;
            updateScorecard();
            return;
        }
        
        if (response.data) {
            // Ensure response.data is an array
            const allEvents = Array.isArray(response.data) ? response.data : [];
            
            // CRITICAL: Store all events for target calculation (innings 1 + 2)
            allMatchEvents = allEvents;
            
            // CRITICAL: Filter events based on current innings
            // For Innings 2, we need to find where Innings 1 ended and only count Innings 2 events
            if (currentInnings === 2) {
                // Find the transition point: when current_innings changed to 2
                // We can detect this by finding when the match was updated to innings 2
                // OR by tracking the last event sequence before innings 2 started
                
                let innings2StartSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
                
                // If we don't have stored sequence, try to determine it from events
                // Strategy: If match says innings 2, we need to find the last event of innings 1
                // We can do this by checking team changes or by using a marker
                if (!innings2StartSeq && allEvents.length > 0 && currentMatchData) {
                    // Try to find where innings transition happened
                    // The transition happens when we call completeInnings API
                    // We can detect this by finding the last max-overs-complete event for innings 1
                    // OR we can use the match's last_seq at the time innings 2 started
                    
                    // For now, if we're in innings 2 but have no stored seq, we'll try a different approach:
                    // Check if we can determine innings 2 start by looking at team changes
                    // OR just use the last event sequence as a marker
                    
                    // Since we don't have a perfect marker, let's use a heuristic:
                    // If match is in innings 2 and we have events, find where max overs were reached
                    // This is tricky, so for safety, we'll use ALL events if we can't determine
                    // BUT the key is: we need to RESET the score calculation for innings 2
                    
                    console.warn('Innings 2: No stored start sequence. Attempting to detect transition point.');
                    
                    // Try to find transition point by checking if innings 1 reached max overs
                    // We'll look for a pattern where overs reach maxOversPerInnings
                    let potentialTransitionSeq = null;
                    let innings1LegalBalls = 0;
                    
                    for (let i = 0; i < allEvents.length; i++) {
                        const event = allEvents[i];
                        const payload = typeof event.payload_json === 'string' 
                            ? JSON.parse(event.payload_json) 
                            : event.payload_json;
                        
                        if (!payload || typeof payload !== 'object') continue;
                        
                        // Count legal balls for innings 1
                        if (payload.event_type === 'runs' || payload.event_type === 'wicket') {
                            innings1LegalBalls++;
                        } else if (payload.event_type === 'extra' && 
                                   payload.extra_type !== 'wide' && 
                                   payload.extra_type !== 'noball') {
                            innings1LegalBalls++;
                        }
                        
                        // If we've reached max overs for innings 1, the next event might be innings 2
                        if (innings1LegalBalls >= (maxOversPerInnings * 6)) {
                            potentialTransitionSeq = event.assigned_server_seq;
                            // Take the next event as innings 2 start
                            if (i + 1 < allEvents.length) {
                                innings2StartSeq = allEvents[i + 1].assigned_server_seq;
                                break;
                            }
                        }
                    }
                    
                    if (innings2StartSeq) {
                        sessionStorage.setItem(`innings2_start_seq_${matchId}`, innings2StartSeq);
                        console.log(`Innings 2: Detected transition at seq ${innings2StartSeq}`);
                    }
                }
                
                if (innings2StartSeq) {
                    // Only use events after this sequence number (innings 2 events only)
                    matchEvents = allEvents.filter(event => 
                        (event.assigned_server_seq || 0) > parseInt(innings2StartSeq)
                    );
                    console.log(`Innings 2: Filtered events. Start seq: ${innings2StartSeq}, Total events: ${allEvents.length}, Innings 2 events: ${matchEvents.length}`);
                } else {
                    // Fallback: If we can't determine transition, start fresh for innings 2
                    // This means we'll only count NEW events that happen after page load
                    // For safety, reset to empty and let new events accumulate
                    console.log('Innings 2: Cannot determine transition point. Starting fresh - score resets to 0-0.');
                    matchEvents = [];
                    
                    // IMPORTANT: Reset score state when innings 2 starts
                    totalRuns = 0;
                    totalWickets = 0;
                    currentOver = 0;
                    currentBall = 1;
                    playerStats = {};
                    dismissedBatsmen = new Set();
                }
            } else {
                // Innings 1: Use all events
                matchEvents = allEvents;
                // Clear any stored innings 2 start seq (in case we're back in innings 1)
                sessionStorage.removeItem(`innings2_start_seq_${matchId}`);
            }
            
            // Only calculate if we have events AND we're not in a transition state
            if (matchEvents.length > 0 && currentInnings === 2) {
                // For innings 2, make sure we're only using innings 2 events
                // Double-check that we're not including innings 1 data
                const innings2StartSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
                if (innings2StartSeq) {
                    // Verify all events are after the transition point
                    const validEvents = matchEvents.filter(event => 
                        (event.assigned_server_seq || 0) > parseInt(innings2StartSeq)
                    );
                    
                    if (validEvents.length !== matchEvents.length) {
                        // Some events are from innings 1 - filter them out
                        console.warn('Innings 2: Filtering out innings 1 events');
                        matchEvents = validEvents;
                    }
                }
            }
            
            if (matchEvents.length > 0) {
                // Reset totals before calculating from events (for innings 2)
                if (currentInnings === 2) {
                    // Start fresh - events will rebuild the score
                    totalRuns = 0;
                    totalWickets = 0;
                }
                
                calculateCurrentBall(); // This recalculates totalRuns and totalWickets from events
                calculatePlayerStats();
                restoreLastState();
            } else {
                // No events yet - reset to initial state
                // CRITICAL: For innings 2, always reset to 0-0
                if (currentInnings === 2) {
                    console.log('Innings 2: No events yet - resetting to 0-0');
                    currentOver = 0;
                    currentBall = 1;
                    totalRuns = 0;
                    totalWickets = 0;
                    playerStats = {};
                    dismissedBatsmen = new Set();
                } else {
                    // Innings 1 - normal reset
                    currentOver = 0;
                    currentBall = 1;
                    totalRuns = 0;
                    totalWickets = 0;
                }
            }
            
            loadCommentary();
            
            // IMPORTANT: Update scorecard after recalculating from server events
            // This ensures the display matches the server state
            updateScorecard();
            
            // Also update scorecard after a short delay to ensure DOM is ready
            setTimeout(() => {
                updateScorecard();
            }, 100);
        } else {
            // No events data - reset to initial state for new match
            console.log('No events found - resetting to initial state');
            matchEvents = [];
            currentOver = 0;
            currentBall = 1;
            totalRuns = 0;
            totalWickets = 0;
            updateScorecard();
        }
    } catch (error) {
        console.error('Failed to load events:', error);
        // On error, don't break - just log it
        // Reset to safe state
        matchEvents = [];
        currentOver = 0;
        currentBall = 1;
        totalRuns = 0;
        totalWickets = 0;
        updateScorecard();
    }
}

/**
 * Restore last state from events (striker, non-striker, bowler)
 */
function restoreLastState() {
    if (matchEvents.length === 0) {
        return; // No events yet, no state to restore
    }
    
    // Track current striker and non-striker as we process events
    // Start with initial state (unknown, will be set from first event)
    let currentStrikerId = null;
    let currentNonStrikerId = null;
    let currentBowlerId = null;
    let legalBalls = 0;
    
    // Process all events in order to determine final state
    for (const event of matchEvents) {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        if (!payload || typeof payload !== 'object') {
            continue;
        }
        
        // Count legal balls
        if (payload.event_type === 'runs' || payload.event_type === 'wicket') {
            legalBalls++;
        } else if (payload.event_type === 'extra') {
            if (payload.extra_type !== 'wide' && payload.extra_type !== 'noball') {
                legalBalls++;
            }
        }
        
        // Get striker from event (appearance_id)
        if (payload.event_type === 'runs' && event.appearance_id) {
            // Runs: appearance_id is the striker who scored
            currentStrikerId = event.appearance_id;
        } else if (payload.event_type === 'wicket' && event.appearance_id) {
            // Wicket: appearance_id is the NEW batsman coming in (stored as currentStriker after wicket)
            // The wicket indicates previous striker got out
            if (payload.new_batsman_appearance_id) {
                currentStrikerId = payload.new_batsman_appearance_id;
            } else {
                // Fallback: use appearance_id from event (should be new batsman)
                currentStrikerId = event.appearance_id;
            }
        }
        
        // Get bowler from payload (most recent bowler)
        if (payload.bowler_appearance_id) {
            currentBowlerId = payload.bowler_appearance_id;
        }
        
        // Get non-striker from payload (if explicitly saved)
        if (payload.non_striker_appearance_id) {
            currentNonStrikerId = payload.non_striker_appearance_id;
        }
        
        // Infer non-striker if not explicitly saved
        // When we have a new striker, the old striker becomes non-striker
        // This handles the case where non_striker_appearance_id wasn't saved in older events
        if (!currentNonStrikerId && currentStrikerId) {
            // Find the previous striker (before current one) to use as non-striker
            const eventIndex = matchEvents.indexOf(event);
            if (eventIndex > 0) {
                // Look backwards for previous striker
                for (let i = eventIndex - 1; i >= 0; i--) {
                    const prevEvent = matchEvents[i];
                    const prevPayload = typeof prevEvent.payload_json === 'string' 
                        ? JSON.parse(prevEvent.payload_json) 
                        : prevEvent.payload_json;
                    
                    if ((prevPayload.event_type === 'runs' || prevPayload.event_type === 'wicket') && prevEvent.appearance_id) {
                        // Found previous striker - use as non-striker if different from current
                        if (prevEvent.appearance_id !== currentStrikerId) {
                            currentNonStrikerId = prevEvent.appearance_id;
                            break;
                        }
                    }
                }
            }
        }
        
        // Handle wicket: previous striker is out, new batsman comes in
        if (payload.event_type === 'wicket') {
            // Track dismissed batsman (previous striker)
            if (currentStrikerId && payload.out_batsman_appearance_id) {
                dismissedBatsmen.add(payload.out_batsman_appearance_id);
            } else if (currentStrikerId) {
                // Fallback: track previous striker as dismissed
                dismissedBatsmen.add(currentStrikerId);
            }
            
            // For wicket, the appearance_id is the NEW batsman coming in
            // The previous striker got out
            // After wicket, new batsman becomes striker
            if (currentStrikerId && payload.new_batsman_appearance_id) {
                // Previous striker was out - new batsman comes in as striker
                // Non-striker remains the same
                currentStrikerId = payload.new_batsman_appearance_id;
                // Keep currentNonStrikerId unchanged (they stay at non-striker end)
            } else if (event.appearance_id) {
                // Fallback: use appearance_id from event as new striker
                const previousStrikerId = currentStrikerId;
                currentStrikerId = event.appearance_id;
                // If we had a previous striker, they might become non-striker, but after wicket
                // the new batsman comes in as striker, non-striker stays same
            }
        }
        
        // Track bowler changes - update lastBowler when bowler changes
        if (payload.bowler_appearance_id && currentBowlerId !== payload.bowler_appearance_id) {
            if (currentBowlerId) {
                lastBowler = {
                    appearance_id: currentBowlerId,
                    player_id: null, // Will be restored from dropdown
                    player_name: null
                };
            }
        }
        
        // Apply strike rotation if odd runs were scored
        if (payload.event_type === 'runs') {
            const runs = payload.runs || 0;
            if (runs === 1 || runs === 3 || runs === 5) {
                // Swap striker and non-striker
                const temp = currentStrikerId;
                currentStrikerId = currentNonStrikerId;
                currentNonStrikerId = temp;
            }
        }
        
        // Check if over completed (every 6 legal balls) - swap strike AFTER processing the 6th ball
        // CRITICAL: Track last bowler when over completes (cannot bowl consecutive overs)
        if (legalBalls > 0 && legalBalls % 6 === 0) {
            // Over completed - track the bowler who just completed this over
            if (currentBowlerId) {
                lastBowler = {
                    appearance_id: currentBowlerId,
                    player_id: null, // Will be restored from dropdown
                    player_name: null
                };
            }
            
            // Over completed on this ball - swap strike (unless we just swapped on odd runs or wicket)
            if (payload.event_type === 'runs') {
                const runs = payload.runs || 0;
                if (!(runs === 1 || runs === 3 || runs === 5)) {
                    // Only swap if last ball wasn't odd runs (would have already swapped)
                    const temp = currentStrikerId;
                    currentStrikerId = currentNonStrikerId;
                    currentNonStrikerId = temp;
                }
            } else if (payload.event_type === 'wicket') {
                // After wicket on 6th ball, new batsman is striker, swap on over completion
                const temp = currentStrikerId;
                currentStrikerId = currentNonStrikerId;
                currentNonStrikerId = temp;
            } else {
                // Not a runs event, but over completed - swap strike
                const temp = currentStrikerId;
                currentStrikerId = currentNonStrikerId;
                currentNonStrikerId = temp;
            }
        }
    }
    
    // Restore players from dropdown options using appearance_id
    if (currentStrikerId) {
        restorePlayerFromAppearanceId('striker-select', currentStrikerId);
    }
    if (currentNonStrikerId) {
        restorePlayerFromAppearanceId('non-striker-select', currentNonStrikerId);
    }
    if (currentBowlerId) {
        restorePlayerFromAppearanceId('bowler-select', currentBowlerId);
    }
    
    // Populate lastBowler player name from dropdown if available
    if (lastBowler && lastBowler.appearance_id) {
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            for (let option of bowlerSelect.options) {
                if (option.value && parseInt(option.value) === lastBowler.appearance_id) {
                    lastBowler.player_id = parseInt(option.dataset.playerId || 0);
                    lastBowler.player_name = option.dataset.playerName || option.textContent.trim();
                    break;
                }
            }
        }
    }
    
    // Filter bowler dropdown to exclude last bowler (cannot bowl consecutive overs)
    filterBowlerDropdown();
    
    // Check if we're between overs (need new bowler) and show indicator
    if (lastBowler && currentBowler === null) {
        const bowlerIndicator = document.getElementById('bowler-change-indicator');
        if (bowlerIndicator) {
            bowlerIndicator.style.display = 'block';
        }
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            bowlerSelect.style.border = '3px solid #FF9800';
            bowlerSelect.style.boxShadow = '0 0 10px rgba(255, 152, 0, 0.5)';
            bowlerSelect.style.background = '#fff9e6';
        }
    }
    
    console.log('State restored - Striker:', currentStrikerId, 'Non-Striker:', currentNonStrikerId, 'Bowler:', currentBowlerId, 'Last Bowler:', lastBowler);
}

/**
 * Restore player selection from appearance_id
 */
function restorePlayerFromAppearanceId(selectId, appearanceId) {
    if (!appearanceId) {
        return;
    }
    
    const select = document.getElementById(selectId);
    if (!select) {
        return;
    }
    
    // Find option with matching appearance_id
    for (let option of select.options) {
        if (parseInt(option.value) === parseInt(appearanceId)) {
            select.value = appearanceId;
            
            // Get player data from option
            const playerData = {
                appearance_id: parseInt(option.value),
                player_id: parseInt(option.dataset.playerId || 0),
                player_name: option.dataset.playerName || option.textContent.trim()
            };
            
            // Update global variables directly
            if (selectId === 'striker-select') {
                currentStriker = playerData;
            } else if (selectId === 'non-striker-select') {
                currentNonStriker = playerData;
            } else if (selectId === 'bowler-select') {
                currentBowler = playerData;
            }
            
            // Update display
            updatePlayerDisplays();
            
            console.log(`Restored ${selectId}: ${playerData.player_name}`);
            return true; // Successfully restored
        }
    }
    
    console.warn(`Could not restore ${selectId} - appearance_id ${appearanceId} not found in dropdown`);
    return false;
}

/**
 * Calculate current ball and over from events
 */
function calculateCurrentBall() {
    if (matchEvents.length === 0) {
        currentBall = 1;
        currentOver = 0;
        totalRuns = 0;
        totalWickets = 0;
        return;
    }
    
    // Reset counters
    totalRuns = 0;
    totalWickets = 0;
    let legalBalls = 0; // Count only legal deliveries
    
    for (const event of matchEvents) {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        if (!payload || typeof payload !== 'object') {
            continue;
        }
        
        // Count runs and wickets
        if (payload.event_type === 'runs') {
            totalRuns += payload.runs || 0;
            legalBalls++; // Runs count as a legal ball
        } else if (payload.event_type === 'wicket') {
            totalWickets += 1;
            legalBalls++; // Wickets count as a legal ball
        } else if (payload.event_type === 'extra') {
            // Count ALL runs from extras (including wides and no-balls)
            // Even though they don't count as legal balls, runs still count
            if (payload.runs) {
                totalRuns += payload.runs;
            }
            // Wide and no-ball don't count as legal deliveries (they get rebowled)
            if (payload.extra_type !== 'wide' && payload.extra_type !== 'noball') {
                legalBalls++;
            }
        }
    }
    
    // Calculate current over and ball for NEXT delivery
    // legalBalls = number of legal balls bowled so far
    if (legalBalls === 0) {
        currentOver = 0;
        currentBall = 1;
    } else {
        currentOver = Math.floor(legalBalls / 6);
        currentBall = (legalBalls % 6) + 1;
        
        // If exactly divisible by 6, we completed an over, next ball is ball 1 of next over
        if (legalBalls % 6 === 0) {
            currentOver = legalBalls / 6; // Already at this over
            currentBall = 1; // Start of next over
        }
    }
}

/**
 * Update scorecard display
 */
function updateScorecard() {
    if (!currentMatchData) return;
    
    // Update score display
    const scoreEl = document.getElementById('current-score');
    if (scoreEl) {
        // Display: runs/wickets (overs.balls)
        // e.g., if currentBall is 4, that means we're on ball 4, so display as "0.3" (over 0, 3 balls completed)
        const ballsCompleted = currentBall > 1 ? currentBall - 1 : 0;
        const overDisplay = currentOver + '.' + ballsCompleted;
        scoreEl.textContent = `${totalRuns} / ${totalWickets} (${overDisplay})`;
    }
    
    // Update player displays
    updatePlayerDisplays();
    
    // Update player stats display
    updatePlayerStatsDisplay();
}

/**
 * Update player selection displays
 */
function updatePlayerDisplays() {
    const strikerDisplay = document.getElementById('striker-display');
    const nonStrikerDisplay = document.getElementById('non-striker-display');
    const bowlerDisplay = document.getElementById('bowler-display');
    
    if (strikerDisplay && currentStriker) {
        strikerDisplay.textContent = `Striker: ${currentStriker.player_name}`;
    }
    if (nonStrikerDisplay && currentNonStriker) {
        nonStrikerDisplay.textContent = `Non-Striker: ${currentNonStriker.player_name}`;
    }
    if (bowlerDisplay && currentBowler) {
        bowlerDisplay.textContent = `Bowler: ${currentBowler.player_name}`;
    }
}

/**
 * Calculate player stats from events
 */
function calculatePlayerStats() {
    // Reset stats
    playerStats = {};
    
    if (matchEvents.length === 0) {
        return;
    }
    
    // Track which bowler is bowling each ball
    let currentBowlerId = null;
    let bowlerOvers = {}; // {appearance_id: {legalBalls, runs, wickets}}
    
    for (const event of matchEvents) {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        if (!payload || typeof payload !== 'object') {
            continue;
        }
        
        // Get bowler for this delivery
        if (payload.bowler_appearance_id) {
            currentBowlerId = payload.bowler_appearance_id;
        }
        
        // Initialize bowler tracking if not exists
        if (currentBowlerId && !bowlerOvers[currentBowlerId]) {
            bowlerOvers[currentBowlerId] = { legalBalls: 0, runs: 0, wickets: 0 };
        }
        
        // Batting stats (for striker)
        if (payload.event_type === 'runs' && event.appearance_id) {
            const playerId = event.appearance_id;
            if (!playerStats[playerId]) {
                playerStats[playerId] = {
                    runs: 0,
                    balls: 0,
                    fours: 0,
                    sixes: 0,
                    wickets: 0,
                    overs: 0,
                    runs_conceded: 0
                };
            }
            
            // Count runs
            const runs = payload.runs || 0;
            playerStats[playerId].runs += runs;
            playerStats[playerId].balls += 1;
            
            if (runs === 4) playerStats[playerId].fours += 1;
            if (runs === 6) playerStats[playerId].sixes += 1;
        } else if (payload.event_type === 'wicket') {
            // For wicket: event.appearance_id is the NEW batsman coming in
            // The ball should be counted for the OUT batsman, not the new batsman
            const outBatsmanId = payload.out_batsman_appearance_id || null;
            
            // Count the ball for the OUT batsman (if available)
            if (outBatsmanId) {
                if (!playerStats[outBatsmanId]) {
                    playerStats[outBatsmanId] = {
                        runs: 0,
                        balls: 0,
                        fours: 0,
                        sixes: 0,
                        wickets: 0,
                        overs: 0,
                        runs_conceded: 0
                    };
                }
                // Count the ball for the dismissed batsman
                playerStats[outBatsmanId].balls += 1;
            }
            
            // Initialize new batsman stats (but DON'T count the ball for them)
            // The new batsman comes in with 0 balls until they face their first delivery
            const newBatsmanId = payload.new_batsman_appearance_id || event.appearance_id || null;
            if (newBatsmanId && !playerStats[newBatsmanId]) {
                playerStats[newBatsmanId] = {
                    runs: 0,
                    balls: 0, // CRITICAL: Start with 0 balls - they haven't faced a delivery yet
                    fours: 0,
                    sixes: 0,
                    wickets: 0,
                    overs: 0,
                    runs_conceded: 0
                };
            }
        }
        
        // Bowling stats
        if (currentBowlerId && bowlerOvers[currentBowlerId]) {
            const bowler = bowlerOvers[currentBowlerId];
            
            if (payload.event_type === 'runs') {
                bowler.runs += payload.runs || 0;
                bowler.legalBalls += 1;
            } else if (payload.event_type === 'wicket') {
                bowler.wickets += 1;
                bowler.legalBalls += 1;
            } else if (payload.event_type === 'extra') {
                // Extras count as runs against bowler (even if not legal ball)
                if (payload.runs) {
                    bowler.runs += payload.runs;
                }
                // Only legal extras count as balls
                if (payload.extra_type !== 'wide' && payload.extra_type !== 'noball') {
                    bowler.legalBalls += 1;
                }
            }
        }
    }
    
    // Convert bowler overs to stats
    for (const [appearanceId, stats] of Object.entries(bowlerOvers)) {
        if (!playerStats[appearanceId]) {
            playerStats[appearanceId] = {
                runs: 0,
                balls: 0,
                fours: 0,
                sixes: 0,
                wickets: 0,
                overs: 0,
                runs_conceded: 0
            };
        }
        // Convert legal balls to overs (e.g., 13 balls = 2.1 overs)
        const overs = Math.floor(stats.legalBalls / 6);
        const balls = stats.legalBalls % 6;
        playerStats[appearanceId].overs = parseFloat((overs + balls / 10).toFixed(1));
        playerStats[appearanceId].runs_conceded = stats.runs;
        playerStats[appearanceId].wickets = stats.wickets;
    }
}

/**
 * Update player stats display (like Cricbuzz)
 */
function updatePlayerStatsDisplay() {
    const statsContainer = document.getElementById('player-stats-container');
    if (!statsContainer) {
        return;
    }
    
    let html = '<div class="player-stats-grid">';
    
    // Striker stats
    if (currentStriker && playerStats[currentStriker.appearance_id]) {
        const stats = playerStats[currentStriker.appearance_id];
        const strikeRate = stats.balls > 0 ? ((stats.runs / stats.balls) * 100).toFixed(2) : '0.00';
        html += `
            <div class="player-stat-card striker-stat">
                <div class="stat-player-name">${currentStriker.player_name} <span class="stat-label">(Striker)</span></div>
                <div class="stat-row">
                    <span class="stat-value">${stats.runs}</span>
                    <span class="stat-value">(${stats.balls})</span>
                    <span class="stat-label">SR: ${strikeRate}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">4s: ${stats.fours}</span>
                    <span class="stat-label">6s: ${stats.sixes}</span>
                </div>
            </div>
        `;
    }
    
    // Non-Striker stats
    if (currentNonStriker && playerStats[currentNonStriker.appearance_id]) {
        const stats = playerStats[currentNonStriker.appearance_id];
        const strikeRate = stats.balls > 0 ? ((stats.runs / stats.balls) * 100).toFixed(2) : '0.00';
        html += `
            <div class="player-stat-card non-striker-stat">
                <div class="stat-player-name">${currentNonStriker.player_name} <span class="stat-label">(Non-Striker)</span></div>
                <div class="stat-row">
                    <span class="stat-value">${stats.runs}</span>
                    <span class="stat-value">(${stats.balls})</span>
                    <span class="stat-label">SR: ${strikeRate}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">4s: ${stats.fours}</span>
                    <span class="stat-label">6s: ${stats.sixes}</span>
                </div>
            </div>
        `;
    }
    
    // Bowler stats
    if (currentBowler && playerStats[currentBowler.appearance_id]) {
        const stats = playerStats[currentBowler.appearance_id];
        const oversDisplay = stats.overs.toFixed(1);
        const economy = stats.overs > 0 ? (stats.runs_conceded / stats.overs).toFixed(2) : '0.00';
        html += `
            <div class="player-stat-card bowler-stat">
                <div class="stat-player-name">${currentBowler.player_name} <span class="stat-label">(Bowler)</span></div>
                <div class="stat-row">
                    <span class="stat-value">${oversDisplay} O</span>
                    <span class="stat-value">${stats.runs_conceded} R</span>
                    <span class="stat-value">${stats.wickets} W</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Economy: ${economy}</span>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    statsContainer.innerHTML = html;
}

// Store all match events (innings 1 + 2) for target calculation
let allMatchEvents = [];

/**
 * Calculate innings 1 total for target calculation (only in innings 2)
 */
function calculateInnings1Total() {
    if (currentInnings !== 2) {
        return null;
    }
    
    const innings2StartSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
    if (!innings2StartSeq) {
        // If no start seq, can't calculate innings 1 total
        return null;
    }
    
    // Use allMatchEvents which should contain all events (innings 1 + 2)
    // If allMatchEvents is empty, try to use matchEvents but check if they include innings 1
    let eventsToUse = allMatchEvents.length > 0 ? allMatchEvents : matchEvents;
    
    // Filter for innings 1 events (events before innings 2 start)
    const innings1Events = eventsToUse.filter(event => 
        event.assigned_server_seq <= parseInt(innings2StartSeq)
    );
    
    if (innings1Events.length === 0) {
        // If no innings 1 events found, we need to fetch all events
        // This might happen if events were filtered earlier
        return null;
    }
    
    let innings1Total = 0;
    
    for (const event of innings1Events) {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        if (!payload || typeof payload !== 'object') {
            continue;
        }
        
        if (payload.event_type === 'runs') {
            innings1Total += payload.runs || 0;
        } else if (payload.event_type === 'extra') {
            innings1Total += payload.runs || 0;
        }
        // Wickets don't add to total runs
    }
    
    return innings1Total > 0 ? innings1Total : null;
}

/**
 * Check if target is achieved in innings 2
 */
function checkTargetAchieved() {
    if (currentInnings !== 2) {
        return false;
    }
    
    const innings1Total = calculateInnings1Total();
    if (innings1Total === null) {
        return false; // Can't determine target if innings 1 total is unknown
    }
    
    const target = innings1Total + 1; // Target is innings 1 total + 1
    return totalRuns >= target;
}

/**
 * Score runs (0-6)
 * Includes strike rotation logic
 */
function scoreRuns(runs) {
    if (!currentMatchData) {
        if (typeof toast !== 'undefined') {
            toast.warning('Match data not loaded. Please wait...');
        }
        return;
    }
    
    // Validate players are selected
    if (!currentStriker || !currentNonStriker || !currentBowler) {
        if (typeof toast !== 'undefined') {
            toast.warning('Please select Striker, Non-Striker, and Bowler before scoring.');
        }
        return;
    }
    
    // Optimistically update score immediately for better UX
    // Note: The server will recalculate from events, but this gives immediate feedback
    totalRuns += runs;
    
    // CRITICAL: Check if target is achieved in innings 2 before adding event
    // If target is achieved, automatically complete the match
    if (currentInnings === 2 && checkTargetAchieved()) {
        // Target achieved! Automatically finish the match
        if (typeof toast !== 'undefined') {
            toast.success(`Target achieved! Match will be completed automatically.`, 3000);
        }
        // Still add the event first so it's recorded, then finish match
        addEventToQueue({
            event_type: 'runs',
            runs: runs
        });
        // Give it a moment for the event to be added, then finish match
        setTimeout(() => {
            finishMatch();
        }, 500);
        return;
    }
    
    // Update scorecard immediately for instant feedback
    updateScorecard();
    
    // Add event to queue first (before updating ball count)
    // The server response will recalculate everything correctly
    addEventToQueue({
        event_type: 'runs',
        runs: runs
    });
    
    // Strike rotation: Odd runs (1, 3, 5) = swap striker and non-striker
    // Even runs (0, 2, 4, 6) = stay same
    // Note: This is optimistic - server will confirm the rotation when events are reloaded
    if (runs === 1 || runs === 3 || runs === 5) {
        swapStrike();
        updateScorecard(); // Update again after strike swap
    }
}

/**
 * Swap striker and non-striker
 */
function swapStrike() {
    if (!currentStriker || !currentNonStriker) {
        return; // Can't swap if both aren't set
    }
    
    const temp = currentStriker;
    currentStriker = currentNonStriker;
    currentNonStriker = temp;
    
    // Update dropdown selections
    const strikerSelect = document.getElementById('striker-select');
    const nonStrikerSelect = document.getElementById('non-striker-select');
    
    if (strikerSelect) {
        strikerSelect.value = currentStriker.appearance_id;
    }
    if (nonStrikerSelect) {
        nonStrikerSelect.value = currentNonStriker.appearance_id;
    }
    
    // Update displays
    updatePlayerDisplays();
    console.log('Strike rotated: Striker and Non-Striker swapped');
}

/**
 * Swap strike when over completes
 */
function swapStrikeOnOverComplete() {
    if (!currentStriker || !currentNonStriker) {
        console.warn('Cannot swap strike - striker or non-striker not set');
        // Still prompt for new bowler even if strike can't swap
        promptNewBowler();
        return;
    }
    
    swapStrike();
    console.log('Over completed: Striker and Non-Striker swapped automatically');
    updateScorecard(); // Update display after strike swap
    
    // Prompt for new bowler selection
    promptNewBowler();
    
    // Show notification
    const notification = document.createElement('div');
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #4CAF50; color: white; padding: 1rem; border-radius: 4px; z-index: 9999; box-shadow: 0 2px 8px rgba(0,0,0,0.2);';
    notification.textContent = 'Over completed! Strike rotated. Please select new bowler.';
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

/**
 * Prompt for new bowler selection when over completes
 */
function promptNewBowler() {
    const bowlerSelect = document.getElementById('bowler-select');
    const bowlerIndicator = document.getElementById('bowler-change-indicator');
    
    if (!bowlerSelect) {
        return;
    }
    
    // CRITICAL: Track last bowler BEFORE resetting (so we can capture who just bowled)
    trackLastBowler();
    
    // Reset current bowler (must select new one)
    currentBowler = null;
    bowlerSelect.value = '';
    updatePlayerDisplays();
    
    // Show indicator
    if (bowlerIndicator) {
        bowlerIndicator.style.display = 'block';
    }
    
    // Highlight bowler dropdown
    bowlerSelect.style.border = '3px solid #FF9800';
    bowlerSelect.style.boxShadow = '0 0 10px rgba(255, 152, 0, 0.5)';
    bowlerSelect.style.background = '#fff9e6';
    
    // Scroll to bowler section
    const bowlerSection = document.getElementById('bowler-selector');
    if (bowlerSection) {
        bowlerSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Filter bowler dropdown to exclude last bowler (no consecutive overs)
    filterBowlerDropdown();
    
    // Show toast notification
    if (typeof toast !== 'undefined') {
        toast.warning('Over completed! Please select a new bowler for the next over. (Same bowler cannot bowl consecutive overs)', 5000);
    } else {
        setTimeout(() => {
            if (typeof toast !== 'undefined') {
                toast.info('Over completed! Please select the new bowler for the next over.');
            }
            bowlerSelect.focus();
        }, 500);
    }
    
    // Note: Bowler change handler should be set up in score.php initialization
    // This function just prompts user, doesn't set up handler
}

/**
 * Handle bowler change
 */
function handleBowlerChange() {
    const bowlerSelect = document.getElementById('bowler-select');
    const bowlerIndicator = document.getElementById('bowler-change-indicator');
    
    if (!bowlerSelect) {
        return;
    }
    
    const option = bowlerSelect.options[bowlerSelect.selectedIndex];
    if (option.value) {
        const selectedAppearanceId = parseInt(option.value);
        
        // Validate: Check if selected bowler is the same as last bowler (cannot bowl consecutive overs)
        if (lastBowler && selectedAppearanceId === lastBowler.appearance_id) {
            // Prevent selection - same bowler cannot bowl consecutive overs
            if (typeof toast !== 'undefined') {
                toast.error(`${lastBowler.player_name} just bowled the previous over. Please select a different bowler for the next over.`, 5000);
            } else {
                alert(`${lastBowler.player_name} just bowled the previous over. Please select a different bowler for the next over.`);
            }
            
            // Reset dropdown selection
            bowlerSelect.value = '';
            currentBowler = null;
            updatePlayerDisplays();
            
            // Re-filter dropdown to ensure last bowler stays disabled
            filterBowlerDropdown();
            
            // Keep highlight and indicator visible
            bowlerSelect.style.border = '3px solid #FF9800';
            bowlerSelect.style.boxShadow = '0 0 10px rgba(255, 152, 0, 0.5)';
            bowlerSelect.style.background = '#fff9e6';
            
            if (bowlerIndicator) {
                bowlerIndicator.style.display = 'block';
            }
            
            // Focus on dropdown
            bowlerSelect.focus();
            
            console.log('Blocked: Same bowler selected -', lastBowler.player_name);
            return; // Exit without setting bowler
        }
        
        // Also check if option is disabled (safety check)
        if (option.disabled) {
            if (typeof toast !== 'undefined') {
                toast.error('This bowler cannot be selected. Please choose a different bowler.', 3000);
            }
            bowlerSelect.value = '';
            currentBowler = null;
            updatePlayerDisplays();
            filterBowlerDropdown();
            bowlerSelect.focus();
            return;
        }
        
        // Valid selection - proceed
        currentBowler = {
            appearance_id: selectedAppearanceId,
            player_id: parseInt(option.dataset.playerId),
            player_name: option.dataset.playerName
        };
        updatePlayerDisplays();
        
        // Remove highlight
        bowlerSelect.style.border = '';
        bowlerSelect.style.boxShadow = '';
        bowlerSelect.style.background = '';
        
        // Hide indicator
        if (bowlerIndicator) {
            bowlerIndicator.style.display = 'none';
        }
        
        console.log('New bowler selected: ' + currentBowler.player_name);
    } else {
        // Bowler deselected
        currentBowler = null;
        updatePlayerDisplays();
    }
}

/**
 * Score wicket - show modal for wicket type and new batsman selection
 */
function scoreWicket() {
    // Don't optimistically update for wicket - wait for modal confirmation
    showWicketModal();
}

/**
 * Show wicket selection modal
 */
function showWicketModal() {
    const modal = document.getElementById('wicket-modal');
    if (!modal) {
        // Create modal if it doesn't exist
        createWicketModal();
    }
    
    // Reset form
    const form = document.getElementById('wicket-form');
    if (form) {
        form.reset();
    }
    
    // Populate wicket dropdown with only remaining players (who haven't batted)
    populateWicketDropdown();
    
    // Show modal (ensure it's centered) - reuse modal variable from above
    if (modal) {
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
    }
}

/**
 * Create wicket modal HTML
 */
function createWicketModal() {
    const modalHTML = `
        <div id="wicket-modal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10000; display: flex; align-items: center; justify-content: center;">
            <div class="modal-content" style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
                <h2 style="margin-top: 0;">Record Wicket</h2>
                <form id="wicket-form">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Wicket Type *</label>
                        <select id="wicket-type-select" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            <option value="">Select wicket type</option>
                            <option value="bowled">Bowled</option>
                            <option value="caught">Caught</option>
                            <option value="lbw">LBW (Leg Before Wicket)</option>
                            <option value="runout">Run Out</option>
                            <option value="stumped">Stumped</option>
                            <option value="hitwicket">Hit Wicket</option>
                            <option value="retired">Retired</option>
                            <option value="retiredhurt">Retired Hurt</option>
                            <option value="obstructing">Obstructing the Field</option>
                        </select>
                    </div>
                    
                    <div id="fielder-section" class="form-group" style="margin-bottom: 1.5rem; display: none;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Fielder (for Caught/Run Out)</label>
                        <select id="fielder-select" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            <option value="">Select fielder</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">New Batsman *</label>
                        <select id="new-batsman-select" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            <option value="">Select new batsman</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Commentary (Optional)</label>
                        <input type="text" id="wicket-commentary" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;" placeholder="e.g., Bowled! Clean bowled">
                    </div>
                    
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" onclick="closeWicketModal()" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background: #7c2d12; color: white; border: none; border-radius: 4px; cursor: pointer;">Record Wicket</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Populate new batsman dropdown (only remaining players who haven't batted)
    populateWicketDropdown();
    
    // Populate fielder dropdown (all players from opposing team)
    const fielderSelect = document.getElementById('fielder-select');
    if (fielderSelect && currentBowler) {
        // Use bowler's team players (opposite of batting team)
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            for (let option of bowlerSelect.options) {
                if (option.value) {
                    const newOption = option.cloneNode(true);
                    fielderSelect.appendChild(newOption);
                }
            }
        }
    }
    
    // Show/hide fielder based on wicket type
    const wicketTypeSelect = document.getElementById('wicket-type-select');
    if (wicketTypeSelect) {
        wicketTypeSelect.addEventListener('change', function() {
            const fielderSection = document.getElementById('fielder-section');
            if (fielderSection) {
                const requiresFielder = ['caught', 'runout'].includes(this.value);
                fielderSection.style.display = requiresFielder ? 'block' : 'none';
                if (!requiresFielder) {
                    fielderSelect.value = '';
                }
            }
        });
    }
    
    // Handle form submission
    const form = document.getElementById('wicket-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitWicket();
        });
    }
}

/**
 * Submit wicket event
 */
function submitWicket() {
    const wicketType = document.getElementById('wicket-type-select').value;
    const newBatsmanId = document.getElementById('new-batsman-select').value;
    const fielderId = document.getElementById('fielder-select').value;
    const commentary = document.getElementById('wicket-commentary').value;
    
    if (!wicketType || !newBatsmanId) {
        if (typeof toast !== 'undefined') {
            toast.error('Please select wicket type and new batsman');
        }
        return;
    }
    
    // Get new batsman details
    const newBatsmanSelect = document.getElementById('new-batsman-select');
    const selectedOption = newBatsmanSelect.options[newBatsmanSelect.selectedIndex];
    
    const wicketPayload = {
        event_type: 'wicket',
        wicket_type: wicketType
    };
    
    if (fielderId) {
        wicketPayload.fielder_appearance_id = parseInt(fielderId);
    }
    
    // Update striker to new batsman BEFORE adding event
    // This ensures the event is recorded with correct new batsman
    const previousStriker = currentStriker;
    currentStriker = {
        appearance_id: parseInt(newBatsmanId),
        player_id: parseInt(selectedOption.dataset.playerId),
        player_name: selectedOption.dataset.playerName
    };
    
    // Update dropdowns
    const strikerSelect = document.getElementById('striker-select');
    if (strikerSelect) {
        strikerSelect.value = newBatsmanId;
    }
    
    // The wicket event should have the NEW batsman's appearance_id (they're coming in)
    // But wicket_type indicates who got out (the previous striker)
    // Add previous striker info to payload for reference
    if (previousStriker) {
        wicketPayload.out_batsman_appearance_id = previousStriker.appearance_id;
        // Track dismissed batsman
        dismissedBatsmen.add(previousStriker.appearance_id);
    }
    
    // Add new batsman info
    wicketPayload.new_batsman_appearance_id = currentStriker.appearance_id;
    
    // Add event with commentary
    const commentaryInput = document.getElementById('commentary-input');
    if (commentaryInput) {
        commentaryInput.value = commentary || '';
    }
    
    // Optimistically update score immediately
    // Note: The server will recalculate from events, but this gives immediate feedback
    totalWickets += 1;
    
    // Now add event with updated striker
    addEventToQueue(wicketPayload);
    
    updatePlayerDisplays();
    
    // Update scorecard immediately
    updateScorecard();
    
    // Close modal
    closeWicketModal();
    
    console.log('Wicket recorded. New striker:', currentStriker.player_name);
}

/**
 * Close wicket modal
 */
function closeWicketModal() {
    const modal = document.getElementById('wicket-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('wicket-modal');
    if (modal && e.target === modal) {
        closeWicketModal();
    }
});

/**
 * Check if current innings is complete
 */
function checkInningsComplete() {
    // If no overs limit set, innings can't be complete
    if (!maxOversPerInnings || maxOversPerInnings <= 0) {
        return false;
    }
    
    // CRITICAL: If innings 2 hasn't started yet (no events for innings 2), it can't be complete
    // Check if we're in innings 2 and if there are any events for innings 2
    if (currentInnings === 2) {
        const innings2StartSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
        if (innings2StartSeq) {
            // Check if there are events after innings 1 completed (innings 2 events)
            const innings2Events = matchEvents.filter(event => 
                event.assigned_server_seq > parseInt(innings2StartSeq)
            );
            
            // If no events for innings 2 yet, innings 2 hasn't started - can't be complete
            if (innings2Events.length === 0) {
                console.log('Innings 2 not started yet - no events for innings 2');
                return false;
            }
        } else if (matchEvents.length === 0) {
            // If no start seq and no events, innings 2 hasn't started
            console.log('Innings 2 not started yet - no events at all');
            return false;
        }
    }
    
    // Calculate total legal balls bowled so far for CURRENT innings
    // currentOver = completed overs (0-based), currentBall = next ball to bowl (1-6)
    // If currentBall is 1, we just completed an over, so total = currentOver * 6
    // If currentBall > 1, we're partway through an over, so total = (currentOver * 6) + (currentBall - 1)
    const totalLegalBalls = (currentOver * 6) + (currentBall > 1 ? currentBall - 1 : 0);
    const maxLegalBalls = maxOversPerInnings * 6;
    
    // Debug logging for troubleshooting
    if (totalLegalBalls >= maxLegalBalls) {
        console.log(`Innings ${currentInnings} complete: totalLegalBalls=${totalLegalBalls}, maxLegalBalls=${maxLegalBalls}, currentOver=${currentOver}, currentBall=${currentBall}`);
        return true;
    }
    
    return false;
}

/**
 * Complete current innings
 */
/**
 * Show innings summary before starting next innings
 */
/**
 * Show innings summary with runs and target (for innings 1)
 * Shows as centered modal dialog
 */
async function showInningsSummary(innings, runs, wickets, overs) {
    let title = `INNINGS ${innings} COMPLETE`;
    let message = `Final Score: ${runs}/${wickets} (${overs.toFixed(1)} overs)`;
    
    // For innings 1, show target for innings 2
    if (innings === 1) {
        const target = runs + 1; // Target is innings 1 total + 1
        message += `\n\n🏆 TARGET FOR INNINGS 2: ${target} runs`;
    } else {
        message += `\n\nMatch Complete!`;
    }
    
    // Show as centered confirm dialog (not toast notification)
    if (typeof toast !== 'undefined') {
        // Use toast.confirm for centered display (but make it info-only, auto-close)
        // For info display, we'll create a custom centered modal
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 10001; display: flex; align-items: center; justify-content: center; padding: 20px;';
        overlay.id = 'innings-summary-overlay';
        
        const dialog = document.createElement('div');
        dialog.style.cssText = 'background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; padding: 24px; text-align: center;';
        
        dialog.innerHTML = `
            <h2 style="margin: 0 0 16px 0; font-size: 24px; font-weight: 700; color: #111827;">${title}</h2>
            <div style="font-size: 16px; line-height: 1.6; color: #374151; white-space: pre-line; margin-bottom: 20px;">${message}</div>
            <button onclick="document.getElementById('innings-summary-overlay').remove()" style="padding: 10px 24px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 500; cursor: pointer;">OK</button>
        `;
        
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        
        // Auto-close after 5 seconds or on button click
        setTimeout(() => {
            const elem = document.getElementById('innings-summary-overlay');
            if (elem) elem.remove();
        }, 5000);
        
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
            }
        });
    } else {
        alert(`${title}\n\n${message}`);
    }
}

/**
 * Finish match when innings 2 completes
 */
async function finishMatch() {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        if (typeof toast !== 'undefined') {
            toast.error('Match ID is not defined. Please refresh the page.');
        }
        return;
    }
    
    // CRITICAL CHECK: Only finish match if we're actually in innings 2
    // AND innings 2 has actually been played (has events or overs completed)
    if (currentInnings < 2) {
        console.error('finishMatch called but currentInnings is not 2. Current innings:', currentInnings);
        if (typeof toast !== 'undefined') {
            toast.error('Cannot finish match. Innings 2 has not been completed yet.');
        }
        return;
    }
    
    // CRITICAL: Additional validation - Make sure innings 2 has actually been PLAYED
    // Just because current_innings = 2 doesn't mean innings 2 has started
    // Innings 2 starts when the first ball is scored AFTER innings 1 completed
    
    // Calculate overs completed in innings 2
    const oversCompleted = currentOver + (currentBall > 1 ? (currentBall - 1) / 6 : 0);
    
    // Check if we have events specifically for innings 2
    // If we have innings2_start_seq, only count events after it
    const innings2StartSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
    let hasInnings2Events = false;
    let innings2EventCount = 0;
    
    if (innings2StartSeq) {
        // Count events that occurred after innings 1 completed (innings 2 events)
        innings2EventCount = matchEvents.filter(event => 
            event.assigned_server_seq > parseInt(innings2StartSeq)
        ).length;
        hasInnings2Events = innings2EventCount > 0;
    } else {
        // If no start seq stored but currentInnings is 2, check if matchEvents are for innings 2
        // This might happen if page was refreshed - check if we have ANY events
        hasInnings2Events = matchEvents.length > 0;
    }
    
    // Only allow finishing if innings 2 has actually been played:
    // 1. Either we have events for innings 2, OR
    // 2. Overs have been completed (oversCompleted > 0)
    // If both are false (no events and no overs), innings 2 hasn't started yet
    if (!hasInnings2Events && oversCompleted === 0) {
        console.error('finishMatch called but innings 2 has not started yet');
        console.error('State check - Innings:', currentInnings, 'Over:', currentOver, 'Ball:', currentBall);
        console.error('Events for innings 2:', innings2EventCount, 'Total events:', matchEvents.length);
        console.error('Innings 2 start seq:', innings2StartSeq);
        if (typeof toast !== 'undefined') {
            toast.error('Cannot finish match. Innings 2 has not started yet. Please start scoring innings 2 first.\n\nAfter completing innings 1, you need to score at least one ball in innings 2 before the match can be finished.', 6000);
        }
        return;
    }
    
    // Show innings 2 summary
    showInningsSummary(2, totalRuns, totalWickets, oversCompleted);
    
    // Show POTM selection modal before finalizing
    const potmSelected = await showPOTMSelectionModal();
    
    if (!potmSelected) {
        // User cancelled POTM selection, ask if they want to finalize without POTM
        if (typeof toast !== 'undefined') {
            const proceed = await toast.confirm('No POTM selected. Finalize match without POTM?', {
                title: 'Finalize Match',
                confirmText: 'Yes, Finalize',
                cancelText: 'Cancel'
            });
            if (!proceed) {
                return;
            }
        }
    }
    
    try {
        // Call API to finalize match
        const response = await api.request(`/matches.php/${matchId}/finalize`, {
            method: 'POST',
            body: JSON.stringify({
                reason: 'Both innings completed'
            })
        });
        
        if (response.success) {
            if (typeof toast !== 'undefined') {
                toast.success('Match completed! Redirecting to complete scorecard...', 2000);
            }
            // Redirect to match view page (complete scorecard)
            window.location.href = `/cricapp/admin/matches/view.php?id=${matchId}`;
        } else {
            if (typeof toast !== 'undefined') {
                toast.warning('Match completed but failed to finalize: ' + (response.error || response.message || 'Unknown error') + '\n\nRedirecting to scorecard anyway...', 4000);
            }
            // Redirect anyway to show scorecard
            window.location.href = `/cricapp/admin/matches/view.php?id=${matchId}`;
        }
    } catch (error) {
        console.error('Failed to finalize match:', error);
        if (typeof toast !== 'undefined') {
            toast.error('Match completed but error finalizing. Redirecting to scorecard...', 3000);
        }
        // Redirect anyway to show scorecard
        window.location.href = `/cricapp/admin/matches/view.php?id=${matchId}`;
    }
}

async function completeInnings() {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        if (typeof toast !== 'undefined') {
            toast.error('Match ID is not defined. Please refresh the page.');
        }
        return;
    }
    
    if (currentInnings >= 2) {
        if (typeof toast !== 'undefined') {
            toast.warning('Match already completed both innings!');
        }
        return;
    }
    
    // CRITICAL: FIRST show runs and target summary (for innings 1)
    const oversCompleted = currentOver + (currentBall > 1 ? (currentBall - 1) / 6 : 0);
    const target = totalRuns + 1; // Target for innings 2
    
    // Show innings summary FIRST (includes runs and target)
    showInningsSummary(1, totalRuns, totalWickets, oversCompleted);
    
    // Wait a moment for user to see the summary, then show confirmation dialog
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // Then show confirmation to start innings 2
    let shouldProceed = false;
    if (typeof toast !== 'undefined') {
        shouldProceed = await toast.confirm(`Innings ${currentInnings} is complete!\n\nFinal Score: ${totalRuns}/${totalWickets} (${oversCompleted.toFixed(1)} overs)\nTarget for Innings 2: ${target} runs\n\nStart Innings ${currentInnings + 1}?`, {
            title: 'Complete Innings',
            confirmText: 'Yes, Start Innings ' + (currentInnings + 1),
            cancelText: 'Cancel'
        });
    } else {
        shouldProceed = confirm(`Innings ${currentInnings} is complete! Final Score: ${totalRuns}/${totalWickets}. Target: ${target} runs. Start innings ${currentInnings + 1}?`);
    }
    
    if (!shouldProceed) {
        return;
    }
    
    try {
        // Call API to complete innings and start next
        const response = await api.request(`/matches.php/${matchId}/complete-innings`, {
            method: 'POST',
            body: JSON.stringify({
                innings: currentInnings,
                total_runs: totalRuns,
                total_wickets: totalWickets
            })
        });
        
        if (response.success) {
            // CRITICAL: Store the transition point - the last event sequence of innings 1
            // Events AFTER this sequence belong to innings 2
            let transitionSeq = 0;
            
            // First priority: Use last_seq from API response (most accurate)
            if (response.data && response.data.last_seq !== undefined) {
                transitionSeq = parseInt(response.data.last_seq);
                console.log(`Using last_seq from API: ${transitionSeq}`);
            } 
            // Second priority: Use last event sequence from current events
            else if (matchEvents.length > 0) {
                const maxSeq = Math.max(...matchEvents.map(e => e.assigned_server_seq || 0));
                transitionSeq = maxSeq;
                console.log(`Using max event sequence: ${transitionSeq}`);
            }
            // Third priority: Use stored lastServerSeq
            else if (lastServerSeq > 0) {
                transitionSeq = lastServerSeq;
                console.log(`Using stored lastServerSeq: ${transitionSeq}`);
            }
            
            // Store transition point - events with assigned_server_seq > transitionSeq are innings 2
            sessionStorage.setItem(`innings2_start_seq_${matchId}`, transitionSeq);
            
            console.log(`Innings ${currentInnings} completed. Transition point stored: ${transitionSeq}`);
            console.log(`Innings ${currentInnings + 1} will start at sequence ${transitionSeq + 1}`);
            
            if (typeof toast !== 'undefined') {
                toast.success(`Innings ${currentInnings} completed!\n\nFinal Score: ${totalRuns}/${totalWickets} (${currentOver}.${currentBall > 1 ? currentBall - 1 : 0})\n\nStarting Innings ${currentInnings + 1} from 0-0 (0.0)...`, 5000);
            }
            
            // Reload page to update team selection (teams swap in innings 2) and reset score
            window.location.reload();
        } else {
            if (typeof toast !== 'undefined') {
                toast.error('Failed to complete innings: ' + (response.error || response.message || 'Unknown error'));
            }
        }
    } catch (error) {
        console.error('Failed to complete innings:', error);
        if (typeof toast !== 'undefined') {
            toast.error('Error completing innings: ' + (error.message || 'Unknown error'));
        }
    }
}

/**
 * Score extra (wide/no-ball)
 * Note: Wides and no-balls don't increment ball_index
 * But runs scored from extras still rotate strike based on total runs
 */
function scoreExtra(type) {
    // Validate that bowler is selected before showing modal
    if (!currentBowler) {
        if (typeof toast !== 'undefined') {
            toast.warning('Please select a Bowler before scoring extras.');
        }
        
        // Highlight bowler dropdown if not selected
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            bowlerSelect.style.border = '3px solid #ff0000';
            bowlerSelect.focus();
        }
        return;
    }
    
    // Validate match data is loaded
    if (!currentMatchData) {
        if (typeof toast !== 'undefined') {
            toast.warning('Match data not loaded. Please wait...');
        }
        return;
    }
    
    if (type === 'noball') {
        // No ball can have runs (0, 1, 2, 3, 4, 6)
        showNoBallModal();
    } else if (type === 'wide') {
        // Wide usually gives 1 run, but can have more (1, 2, 3, 4, 6)
        showWideModal();
    }
}

/**
 * Show no-ball modal to get runs
 */
async function showNoBallModal() {
    let runsInput = null;
    
    if (typeof toast !== 'undefined') {
        runsInput = await toast.prompt('No Ball! Additional runs? (0 = no extra runs, 1, 2, 3, 4, or 6)', '0', {
            title: 'No Ball',
            placeholder: 'Enter 0-6',
            type: 'number'
        });
    } else {
        runsInput = prompt('No Ball! Additional runs? (0 = no extra runs, 1, 2, 3, 4, or 6)', '0');
    }
    
    if (runsInput === null || runsInput === '') {
        return; // User cancelled
    }
    
    const additionalRuns = parseInt(runsInput);
    
    if (isNaN(additionalRuns) || (additionalRuns !== 0 && additionalRuns !== 1 && additionalRuns !== 2 && additionalRuns !== 3 && additionalRuns !== 4 && additionalRuns !== 6)) {
        if (typeof toast !== 'undefined') {
            toast.error('Invalid runs. Please enter 0, 1, 2, 3, 4, or 6');
        }
        return;
    }
    
    // No-ball: 1 run automatically added (penalty), plus any additional runs scored
    const noBallTotalRuns = 1 + additionalRuns;
    
    // Optimistically update score immediately
    totalRuns += noBallTotalRuns;
    updateScorecard();
    
    // Store total runs in event (1 penalty + additional runs)
    addEventToQueue({
        event_type: 'extra',
        extra_type: 'noball',
        runs: noBallTotalRuns, // Total runs: 1 (penalty) + additional runs
        additional_runs: additionalRuns // Additional runs scored (for strike rotation logic)
    }, false); // false = don't increment ball
    
    // Strike rotation: Based on additional runs scored (not the 1 run penalty)
    // Odd runs (1, 3) = rotate strike, Even runs (2, 4, 6) or 0 = no rotation
    if (additionalRuns === 1 || additionalRuns === 3) {
        swapStrike();
    }
    // No strike rotation if additional runs are 0, 2, 4, or 6
}

/**
 * Show wide modal to get runs
 */
async function showWideModal() {
    let runsInput = null;
    
    if (typeof toast !== 'undefined') {
        runsInput = await toast.prompt('Wide! Additional runs? (0 = no extra runs, 1, 2, 3, 4, or 6)', '0', {
            title: 'Wide',
            placeholder: 'Enter 0-6',
            type: 'number'
        });
    } else {
        runsInput = prompt('Wide! Additional runs? (0 = no extra runs, 1, 2, 3, 4, or 6)', '0');
    }
    
    if (runsInput === null || runsInput === '') {
        return; // User cancelled
    }
    
    const additionalRuns = parseInt(runsInput);
    
    if (isNaN(additionalRuns) || (additionalRuns !== 0 && additionalRuns !== 1 && additionalRuns !== 2 && additionalRuns !== 3 && additionalRuns !== 4 && additionalRuns !== 6)) {
        if (typeof toast !== 'undefined') {
            toast.error('Invalid runs. Please enter 0, 1, 2, 3, 4, or 6');
        }
        return;
    }
    
    // Wide: 1 run automatically added (penalty), plus any additional runs
    const wideTotalRuns = 1 + additionalRuns;
    
    // Optimistically update score immediately
    totalRuns += wideTotalRuns;
    updateScorecard();
    
    addEventToQueue({
        event_type: 'extra',
        extra_type: 'wide',
        runs: wideTotalRuns, // Total runs: 1 (penalty) + additional runs
        additional_runs: additionalRuns // Additional runs for tracking
    }, false); // false = don't increment ball
    
    // Wide: NO strike rotation regardless of runs (batsman didn't hit the ball)
    // The runs come from the wide itself, not from batsman scoring
}

/**
 * Add event to queue
 * @param {Object} eventData - The event payload
 * @param {Boolean} incrementBall - Whether to increment ball_index (false for wides/noballs)
 */
function addEventToQueue(eventData, incrementBall = true) {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        if (typeof toast !== 'undefined') {
            toast.error('Match ID is not defined. Please refresh the page.');
        }
        return;
    }
    
    // Validate eventData
    if (!eventData || typeof eventData !== 'object') {
        console.error('Invalid event data:', eventData);
        if (typeof toast !== 'undefined') {
            toast.error('Invalid event data. Please try again.');
        }
        return;
    }
    
    // CRITICAL: DO NOT check target or innings complete here - it blocks events from being added!
    // These checks should happen AFTER the event is added and processed
    // The event must be added first, then we can check if match/innings should complete
    
    // Check overs limit BEFORE adding event (to prevent adding invalid events)
    // But don't check innings complete here - let the event be added first
    if (maxOversPerInnings > 0) {
        const totalLegalBalls = (currentOver * 6) + (currentBall > 1 ? currentBall - 1 : 0);
        const maxLegalBalls = maxOversPerInnings * 6;
        
        // Only block if we're already at max (before this event)
        // If this event would push us over, still allow it but then check innings complete
        if (totalLegalBalls >= maxLegalBalls) {
            // We're already at max - check if innings is complete
            if (checkInningsComplete()) {
                // Innings already complete - handle completion
                if (currentInnings === 1) {
                    const oversCompleted = currentOver + (currentBall > 1 ? (currentBall - 1) / 6 : 0);
                    showInningsSummary(1, totalRuns, totalWickets, oversCompleted);
                    setTimeout(() => {
                        updateCompleteInningsButton();
                    }, 2000);
                } else if (currentInnings === 2) {
                    finishMatch();
                }
                return; // Don't add event if innings already complete
            } else {
                // Not complete yet, but at max - show warning
                updateCompleteInningsButton();
                if (typeof toast !== 'undefined') {
                    toast.warning(`Overs limit reached! Maximum ${maxOversPerInnings} overs per innings.\n\nPlease click "Complete Innings" button below to start the next innings.`, 5000);
                }
                return;
            }
        }
    }
    
    // Validate players are selected based on event type
    if (eventData.event_type === 'runs' || eventData.event_type === 'wicket') {
        // Runs and wickets require striker, non-striker, and bowler
        if (!currentStriker) {
            if (typeof toast !== 'undefined') {
                toast.warning('Please select a Striker before scoring');
            }
            return;
        }
        if (!currentNonStriker) {
            if (typeof toast !== 'undefined') {
                toast.warning('Please select a Non-Striker before scoring');
            }
            return;
        }
    }
    
    // All events (runs, wicket, extra) require a bowler
    if (!currentBowler) {
        if (typeof toast !== 'undefined') {
            toast.warning('Please select a Bowler before scoring. Over may have completed - select new bowler.');
        }
        
        // Highlight bowler dropdown if not selected
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            bowlerSelect.style.border = '3px solid #ff0000';
            bowlerSelect.focus();
        }
        return;
    }
    
    // CRITICAL: Validate that same bowler cannot bowl consecutive overs
    // Check if we're at ball 1 (start of new over) and if current bowler is same as last bowler
    if (currentBall === 1 && lastBowler && currentBowler && currentBowler.appearance_id === lastBowler.appearance_id) {
        // Same bowler trying to bowl consecutive over - BLOCK IT
        if (typeof toast !== 'undefined') {
            toast.error(`${lastBowler.player_name || 'This bowler'} just bowled the previous over. Please select a different bowler for the next over.`, 5000);
        } else {
            alert(`${lastBowler.player_name || 'This bowler'} just bowled the previous over. Please select a different bowler for the next over.`);
        }
        
        // Reset bowler selection
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect) {
            bowlerSelect.value = '';
        }
        currentBowler = null;
        updatePlayerDisplays();
        
        // Re-filter dropdown and show indicator
        filterBowlerDropdown();
        promptNewBowler();
        
        console.log('Blocked: Same bowler attempting to bowl consecutive over -', lastBowler.player_name);
        return; // Block the event
    }
    
    // Add player appearances to event
    const event = {
        event_uuid: generateUUID(),
        match_id: matchId,
        client_id: getClientId(),
        client_ts: Date.now(),
        client_base_seq: lastServerSeq,
        ball_index: currentBall,
        payload: eventData,
        commentary: document.getElementById('commentary-input')?.value || null,
        appearance_id: currentStriker?.appearance_id || null
    };
    
    // Add bowler info to payload if needed
    if (currentBowler && (eventData.event_type === 'runs' || eventData.event_type === 'wicket' || eventData.event_type === 'extra')) {
        event.payload.bowler_appearance_id = currentBowler.appearance_id;
    }
    
    // Add non-striker info for tracking
    if (currentNonStriker) {
        event.payload.non_striker_appearance_id = currentNonStriker.appearance_id;
    }
    
    eventQueue.push(event);
    
    // Don't increment here - wait for server confirmation
    // The server will calculate the correct ball_index based on existing events
    
    // Clear commentary input
    if (document.getElementById('commentary-input')) {
        document.getElementById('commentary-input').value = '';
    }
    
    // Send immediately
    sendEvents();
}

/**
 * Send events to server
 */
async function sendEvents() {
    if (eventQueue.length === 0) return;
    
    let events = []; // Declare outside try block for catch block access
    try {
        events = eventQueue.splice(0, 10); // Batch of up to 10
        
        let response;
        try {
            response = await api.request('/events.php', {
                method: 'POST',
                body: JSON.stringify({
                    match_id: matchId,
                    client_base_seq: lastServerSeq,
                    events: events
                })
            });
        } catch (error) {
            // Handle 409 Conflict specifically
            if (error.code === 409) {
                console.log('409 Conflict caught:', error);
                console.log('Conflict error.data:', error.data);
                console.log('Conflict error.originalResponse:', error.originalResponse);
                // error.data contains the conflict details (object with server_seq, missing_events, etc.)
                // Pass it to handleConflict which will extract server_seq
                const conflictData = error.data || error.originalResponse?.error || {};
                await handleConflict(conflictData);
                return; // Don't continue processing - conflict handled
            }
            // Re-throw other errors to be handled below
            throw error;
        }
        
        if (response && response.success) {
            lastServerSeq = response.data.server_seq;
            
            // Store previous state before refresh
            const previousOver = currentOver;
            const previousBall = currentBall;
            const previousBowler = currentBowler ? { ...currentBowler } : null;
            
            // Refresh events to get accurate state from server
            await loadEvents();
            
            // Restore state from events - this ensures consistency
            // This will restore striker, non-striker, bowler from event history
            restoreLastState();
            
            // Update dropdowns to match restored state
            if (currentStriker) {
                const strikerSelect = document.getElementById('striker-select');
                if (strikerSelect) {
                    strikerSelect.value = currentStriker.appearance_id;
                }
            }
            if (currentNonStriker) {
                const nonStrikerSelect = document.getElementById('non-striker-select');
                if (nonStrikerSelect) {
                    nonStrikerSelect.value = currentNonStriker.appearance_id;
                }
            }
            if (currentBowler) {
                const bowlerSelect = document.getElementById('bowler-select');
                if (bowlerSelect) {
                    bowlerSelect.value = currentBowler.appearance_id;
                }
            }
            
            updatePlayerDisplays();
            updateScorecard();
            
            // CRITICAL: Check target achievement and innings completion AFTER event is saved
            // Check target achievement FIRST (for innings 2)
            if (currentInnings === 2 && checkTargetAchieved()) {
                // Target achieved! Automatically finish the match
                if (typeof toast !== 'undefined') {
                    toast.success(`Target achieved! Match will be completed automatically.`, 3000);
                }
                setTimeout(() => {
                    finishMatch();
                }, 500);
                return; // Don't check over completion if match is finishing
            }
            
            // Check if innings is complete FIRST (before checking over completion)
            // CRITICAL: If innings completes AND over completes at same time, only show innings completion
            const inningsIsComplete = checkInningsComplete();
            
            if (inningsIsComplete) {
                // Handle innings completion
                if (currentInnings === 1) {
                    const oversCompleted = currentOver + (currentBall > 1 ? (currentBall - 1) / 6 : 0);
                    showInningsSummary(1, totalRuns, totalWickets, oversCompleted);
                    setTimeout(() => {
                        updateCompleteInningsButton();
                    }, 2000);
                    // Still check over completion below (but don't show popup)
                } else if (currentInnings === 2) {
                    finishMatch();
                    return; // Don't check over completion if match is finishing
                }
            }
            
            // Check if over completed: ball went from 6 to 1, OR over number increased
            const overCompleted = (previousBall === 6 && currentBall === 1) || 
                                 (currentOver > previousOver && previousOver > 0);
            
            // CRITICAL: If target is achieved in innings 2, skip all over completion logic
            // Match will finish automatically - no need to complete the over
            const targetAchieved = (currentInnings === 2 && checkTargetAchieved());
            if (targetAchieved) {
                console.log('Target achieved! Skipping over completion logic - match will finish.');
                return; // Match finishing, no need to handle over completion
            }
            
            // CRITICAL: Check if innings completes FIRST
            // If innings completes AND over completes at same time, ONLY show innings completion
            if (inningsIsComplete) {
                console.log('Innings completed! Skipping over completion popup.');
                // Innings completion popup will be triggered by checkInningsComplete() logic in addEventToQueue
                // Don't show over completion popup if innings also completes
                // Still need to swap strike silently (without popup) for consistency
                if (overCompleted) {
                    // Silently swap strike but don't show over completion popup
                    if (currentStriker && currentNonStriker) {
                        const temp = currentStriker;
                        currentStriker = currentNonStriker;
                        currentNonStriker = temp;
                        
                        // Update dropdowns silently
                        const strikerSelect = document.getElementById('striker-select');
                        const nonStrikerSelect = document.getElementById('non-striker-select');
                        if (strikerSelect) strikerSelect.value = currentStriker.appearance_id;
                        if (nonStrikerSelect) nonStrikerSelect.value = currentNonStriker.appearance_id;
                        updatePlayerDisplays();
                    }
                }
            } else if (overCompleted) {
                // Only show over completion if innings is NOT complete
                console.log('Over completed! Previous:', previousOver + '.' + previousBall, 'Current:', currentOver + '.' + currentBall);
                
                // Over completed - swap strike (unless odd runs on 6th ball already swapped)
                // Check if last event was odd runs
                let strikeAlreadySwapped = false;
                if (matchEvents.length > 0) {
                    const lastEvent = matchEvents[matchEvents.length - 1];
                    const payload = typeof lastEvent.payload_json === 'string' 
                        ? JSON.parse(lastEvent.payload_json) 
                        : lastEvent.payload_json;
                    
                    const lastRuns = payload.runs || (payload.event_type === 'extra' ? payload.runs : 0);
                    // If 6th ball was odd runs, strike already swapped, don't swap again
                    if (previousBall === 6 && (lastRuns === 1 || lastRuns === 3 || lastRuns === 5)) {
                        console.log('Strike already rotated on 6th ball (odd runs)');
                        strikeAlreadySwapped = true;
                    }
                }
                
                if (!strikeAlreadySwapped) {
                    swapStrikeOnOverComplete();
                } else {
                    // Strike already swapped, but still need new bowler
                    promptNewBowler();
                }
                
                // Update scorecard after handling over completion
                updateScorecard();
            }
        } else {
            // Handle non-success response
            const errorMsg = response.error || response.message || 'Failed to save event';
            console.error('Event save failed:', response);
            
            // Extract readable error message
            let readableError = 'Failed to save event';
            if (typeof errorMsg === 'string') {
                readableError = errorMsg;
            } else if (typeof errorMsg === 'object' && errorMsg.message) {
                readableError = errorMsg.message;
            } else if (response.message) {
                readableError = response.message;
            }
            
            if (typeof toast !== 'undefined') {
                toast.error('Error saving event: ' + readableError);
            }
            // Re-queue events on error so user can retry
            eventQueue = [...events, ...eventQueue];
        }
    } catch (error) {
        console.error('Failed to send events:', error);
        console.error('Error details:', {
            message: error.message,
            code: error.code,
            data: error.data,
            stack: error.stack
        });
        
        // Extract readable error message
        let errorMessage = 'Network error. Please check your connection and try again.';
        if (error.message && error.message !== '[object Object]') {
            errorMessage = error.message;
        } else if (error.data) {
            if (typeof error.data === 'string') {
                errorMessage = error.data;
            } else if (typeof error.data === 'object' && error.data !== null) {
                if (error.data.message) {
                    errorMessage = error.data.message;
                } else if (error.data.error) {
                    errorMessage = typeof error.data.error === 'string' ? error.data.error : 'Server error';
                } else {
                    errorMessage = 'Server error occurred. Please try again.';
                }
            }
        }
        
        // Only show alert if it's not a 409 conflict (those are handled separately)
        if (error.code !== 409) {
            if (typeof toast !== 'undefined') {
                toast.error('Error sending events: ' + errorMessage);
            }
            // Re-queue events on error (but not for 409 conflicts)
            eventQueue = [...events, ...eventQueue];
        }
    }
}

/**
 * Handle conflict (stale client) - sync with server
 */
async function handleConflict(conflictData) {
    console.log('Conflict detected - raw data:', conflictData);
    console.log('Conflict data type:', typeof conflictData);
    
    // PHP jsonError sends: {error: {message: '...', client_base_seq: X, server_seq: Y, missing_events: [...]}}
    // So conflictData should be an object like: {message, client_base_seq, server_seq, missing_events}
    let serverSeq = null;
    let missingEvents = null;
    
    // Handle the data structure
    if (Array.isArray(conflictData)) {
        // If it's an array, look for an object inside with server_seq
        console.log('Conflict data is an array, length:', conflictData.length);
        for (let item of conflictData) {
            if (typeof item === 'object' && item !== null && item.server_seq !== undefined) {
                serverSeq = item.server_seq;
                missingEvents = item.missing_events;
                console.log('Found server_seq in array item:', serverSeq);
                break;
            }
        }
    } else if (typeof conflictData === 'object' && conflictData !== null) {
        // Direct object format: {message, client_base_seq, server_seq, missing_events}
        serverSeq = conflictData.server_seq;
        missingEvents = conflictData.missing_events;
        
        // Log for debugging
        console.log('Extracted server_seq:', serverSeq, 'from conflict data object');
        console.log('Conflict data keys:', Object.keys(conflictData));
    } else {
        console.warn('Unexpected conflict data type:', typeof conflictData);
        console.warn('Conflict data value:', conflictData);
    }
    
    // Update lastServerSeq from server
    if (serverSeq !== undefined && serverSeq !== null && !isNaN(parseInt(serverSeq))) {
        lastServerSeq = parseInt(serverSeq);
        console.log('Updated lastServerSeq to:', lastServerSeq, 'from conflict data');
        
        // If there are missing events, note them
        if (missingEvents && Array.isArray(missingEvents)) {
            console.log('Missing events on server:', missingEvents.length);
        }
        
        // Reload events from server to get current state
        // For innings 2, we need to preserve or recalculate the innings2_start_seq
        try {
            // Store current innings before reload (to check if it's innings 2)
            const wasInnings2 = currentInnings === 2;
            const existingInnings2Seq = wasInnings2 ? sessionStorage.getItem(`innings2_start_seq_${matchId}`) : null;
            
            // Reload events
            await loadEvents();
            
            // If we were in innings 2 and had a stored seq, but loadEvents cleared it,
            // we need to restore it OR recalculate it
            if (wasInnings2 && existingInnings2Seq && currentMatchData && currentMatchData.current_innings === 2) {
                // Restore the stored seq if it was cleared
                const currentSeq = sessionStorage.getItem(`innings2_start_seq_${matchId}`);
                if (!currentSeq && existingInnings2Seq) {
                    console.log('Restoring innings2_start_seq after conflict sync:', existingInnings2Seq);
                    sessionStorage.setItem(`innings2_start_seq_${matchId}`, existingInnings2Seq);
                    // Reload events again with the restored seq
                    await loadEvents();
                }
            }
            
            updateScorecard();
            updateCompleteInningsButton();
            if (typeof toast !== 'undefined') {
                toast.success('Data synchronized. The page has been updated with the latest events from the server.', 3000);
            }
            return; // Successfully handled
        } catch (err) {
            console.error('Error reloading events after conflict:', err);
            if (typeof toast !== 'undefined') {
                toast.warning('Data was synchronized but there was an error refreshing the display. Please refresh the page.', 4000);
            }
            location.reload();
            return;
        }
    } else {
        // If we can't sync, reload the page
        console.error('Could not determine server_seq from conflict data');
        console.error('Conflict data:', conflictData);
        console.error('Server seq value:', serverSeq);
        if (typeof toast !== 'undefined') {
            toast.warning('Your data is out of sync. Please refresh the page to get the latest state.', 4000);
        }
        location.reload();
    }
}

/**
 * Load commentary feed
 */
async function loadCommentary() {
    if (!matchId || matchId === 0) {
        console.error('Match ID is not defined');
        return;
    }
    
    try {
        const response = await api.getMatchEvents(matchId);
        if (response.success && response.data) {
            displayCommentary(response.data);
        }
    } catch (error) {
        console.error('Failed to load commentary:', error);
    }
}

/**
 * Get player name from appearance_id
 */
function getPlayerName(appearanceId, isBowler = false) {
    if (!appearanceId) return 'Unknown';
    
    // Try to get from dropdowns
    const selectId = isBowler ? 'bowler-select' : 'striker-select';
    const select = document.getElementById(selectId) || document.getElementById('non-striker-select');
    
    if (select) {
        for (let option of select.options) {
            if (option.value == appearanceId) {
                return option.dataset.playerName || option.textContent.trim();
            }
        }
    }
    
    // Fallback to current players
    if (!isBowler && currentStriker && currentStriker.appearance_id == appearanceId) {
        return currentStriker.player_name;
    }
    if (!isBowler && currentNonStriker && currentNonStriker.appearance_id == appearanceId) {
        return currentNonStriker.player_name;
    }
    if (isBowler && currentBowler && currentBowler.appearance_id == appearanceId) {
        return currentBowler.player_name;
    }
    
    return 'Unknown';
}

/**
 * Display commentary in cricket-style format
 */
function displayCommentary(events) {
    const feed = document.getElementById('commentary-feed');
    if (!feed) return;
    
    if (events.length === 0) {
        feed.innerHTML = '<div class="loading">No commentary yet</div>';
        return;
    }
    
    // Build player lookup map from select dropdowns
    const playerMap = {};
    ['striker-select', 'non-striker-select', 'bowler-select'].forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            for (let option of select.options) {
                if (option.value && option.dataset.playerName) {
                    playerMap[option.value] = option.dataset.playerName;
                }
            }
        }
    });
    
    // Track over/ball as we process events from start
    let currentOverNum = 0;
    let currentBallNum = 0;
    let lastBatsmanAppearanceId = null;
    let lastBowlerAppearanceId = null;
    
    // Calculate over/ball for each event and enrich with player info
    const eventsWithOver = events.map((event) => {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        const isLegalBall = payload.event_type !== 'extra' || 
                          (payload.extra_type !== 'wide' && payload.extra_type !== 'noball');
        
        let overDisplay = currentOverNum;
        let ballDisplay = currentBallNum + 1;
        
        if (isLegalBall) {
            currentBallNum++;
            if (currentBallNum > 6) {
                currentBallNum = 1;
                currentOverNum++;
            }
            ballDisplay = currentBallNum;
            overDisplay = currentOverNum;
        }
        
        // Get player names
        const batsmanId = event.appearance_id || payload.appearance_id;
        const bowlerId = payload.bowler_appearance_id;
        const batsmanName = batsmanId ? (playerMap[batsmanId] || getPlayerName(batsmanId, false)) : 'Unknown';
        const bowlerName = bowlerId ? (playerMap[bowlerId] || getPlayerName(bowlerId, true)) : 'Unknown';
        
        // Track last batsman and bowler
        if (batsmanId) lastBatsmanAppearanceId = batsmanId;
        if (bowlerId) lastBowlerAppearanceId = bowlerId;
        
        return { event, payload, isLegalBall, overDisplay, ballDisplay, batsmanName, bowlerName };
    });
    
    // Track new batsmen - check after wicket events
    const newBatsmanNotes = {};
    for (let i = 0; i < eventsWithOver.length; i++) {
        const { event, payload } = eventsWithOver[i];
        if (payload.event_type === 'wicket' && payload.new_batsman_appearance_id) {
            const newBatsmanId = payload.new_batsman_appearance_id;
            const newBatsmanName = playerMap[newBatsmanId] || getPlayerName(newBatsmanId, false);
            // Find next legal ball event for this batsman
            for (let j = i + 1; j < eventsWithOver.length; j++) {
                const nextEvent = eventsWithOver[j];
                if (nextEvent.isLegalBall && nextEvent.event.appearance_id == newBatsmanId) {
                    newBatsmanNotes[j] = `<div class="commentary-note" style="margin: 8px 0; padding: 8px; background: #f0f0f0; border-left: 3px solid #4CAF50; border-radius: 4px; font-style: italic;">
                        ${newBatsmanName}, comes to the crease
                    </div>`;
                    break;
                }
            }
        }
    }
    
    // Reverse for display (newest first)
    let itemIndex = 0;
    feed.innerHTML = eventsWithOver.reverse().map(({ event, payload, isLegalBall, overDisplay, ballDisplay, batsmanName, bowlerName }, idx) => {
        const ballLabel = isLegalBall ? `${overDisplay}.${ballDisplay}` : '';
        
        // Get event details
        let eventType = '';
        let eventValue = '';
        let visualCircle = '';
        let eventDescription = '';
        let dismissalDetails = '';
        
        // Check if new batsman note should be shown (original index before reverse)
        const originalIdx = eventsWithOver.length - 1 - idx;
        const newBatsmanNote = newBatsmanNotes[originalIdx] || '';
        
        // Format event
        if (payload.event_type === 'runs') {
            const runs = payload.runs || 0;
            eventValue = runs === 0 ? 'no run' : runs === 4 ? 'FOUR' : runs === 6 ? 'SIX' : `${runs} run${runs !== 1 ? 's' : ''}`;
            eventType = runs === 0 ? 'no run' : runs === 4 ? 'FOUR' : runs === 6 ? 'SIX' : `${runs} run${runs !== 1 ? 's' : ''}`;
            
            // Visual indicator
            if (runs === 6) {
                visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #9c27b0; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">6</span>';
            } else if (runs === 4) {
                visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #2196F3; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">4</span>';
            }
            
            eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>${eventType.toUpperCase()}</strong>`;
        } else if (payload.event_type === 'wicket') {
            const wicketType = payload.wicket_type || 'Wicket';
            eventType = `out ${wicketType}!!`;
            visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #f44336; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">W</span>';
            
            eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>out ${wicketType}!!</strong>`;
            
            // Get dismissal details if available
            const outBatsmanId = payload.out_batsman_appearance_id || event.appearance_id;
            if (outBatsmanId) {
                const outBatsmanName = playerMap[outBatsmanId] || getPlayerName(outBatsmanId, false);
                // Try to get stats for dismissed batsman
                const outStats = playerStats[outBatsmanId] || {};
                const runsScored = outStats.runs || 0;
                const ballsFaced = outStats.balls || 0;
                const fours = outStats.fours || 0;
                const sixes = outStats.sixes || 0;
                
                dismissalDetails = `<div class="commentary-dismissal" style="margin: 8px 0; padding: 8px; background: #ffebee; border-left: 3px solid #f44336; border-radius: 4px; font-size: 0.9em;">
                    ${outBatsmanName} ${wicketType} b ${bowlerName} ${runsScored}(${ballsFaced}) ${fours > 0 ? `[4s-${fours}]` : ''} ${sixes > 0 ? `[6s-${sixes}]` : ''}
                </div>`;
            }
            
            // Add "THAT'S OUT!!" note
            eventDescription += `<br><span style="font-weight: bold; color: #f44336;">THAT'S OUT!!</span> ${wicketType}!!`;
        } else if (payload.event_type === 'extra') {
            const extraType = payload.extra_type || 'extra';
            const runs = payload.runs || 0;
            eventType = extraType === 'wide' ? 'Wide' : extraType === 'noball' ? 'No Ball' : 'Extra';
            
            if (extraType === 'wide') {
                eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>Wide</strong>${runs > 1 ? `, ${runs} runs` : ''}`;
            } else if (extraType === 'noball') {
                eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>No Ball</strong>${runs > 1 ? `, ${runs} runs` : ''}`;
            }
        }
        
        // Use custom commentary if available, otherwise use generated
        if (event.commentary && event.commentary.trim()) {
            eventDescription = `${bowlerName} to ${batsmanName}, ${event.commentary}`;
        }
        
        // Build HTML
        const html = `
            ${newBatsmanNote}
            <div class="commentary-item" style="margin-bottom: 16px; padding: 12px; border-left: 3px solid #e0e0e0; border-radius: 4px; background: #fafafa;">
                <div class="commentary-header" style="display: flex; align-items: center; margin-bottom: 8px;">
                    ${visualCircle}
                    <strong style="font-size: 1.1em; color: #333; margin-right: 8px;">${ballLabel}</strong>
                    <span style="font-weight: bold; color: #1976d2;">${eventType.toUpperCase()}</span>
                </div>
                <div class="commentary-description" style="color: #555; line-height: 1.6; margin-bottom: 4px;">
                    ${eventDescription}
                </div>
                ${dismissalDetails}
            </div>
        `;
        
        previousEvent = { event, payload };
        return html;
    }).join('');
    
    // Scroll to top (newest at top)
    feed.scrollTop = 0;
}

/**
 * Undo last event
 */
async function undoLastEvent() {
    let shouldProceed = false;
    
    if (typeof toast !== 'undefined') {
        shouldProceed = await toast.confirm('Undo last event? This cannot be undone.', {
            title: 'Undo Event',
            confirmText: 'Yes, Undo',
            cancelText: 'Cancel'
        });
    } else {
        shouldProceed = confirm('Undo last event? This cannot be undone.');
    }
    
    if (!shouldProceed) return;
    
    // TODO: Implement undo functionality
    if (typeof toast !== 'undefined') {
        toast.info('Undo feature coming soon');
    }
}

/**
 * Show POTM selection modal
 * Returns true if POTM was selected, false if cancelled
 */
async function showPOTMSelectionModal() {
    // Get player stats for the match
    try {
        const response = await api.request(`/stats.php?match_id=${matchId}`, {
            method: 'GET'
        });
        
        if (!response.success || !response.data || !response.data.player_stats) {
            console.warn('Could not load player stats for POTM selection');
            return false;
        }
        
        const playerStats = response.data.player_stats;
        
        if (playerStats.length === 0) {
            if (typeof toast !== 'undefined') {
                toast.warning('No player stats available for POTM selection');
            }
            return false;
        }
        
        // Create modal HTML
        const modalHtml = `
            <div class="potm-modal-overlay" id="potm-modal-overlay">
                <div class="potm-modal-dialog">
                    <div class="potm-modal-header">
                        <h3>Select Player of the Match (POTM)</h3>
                        <button class="potm-modal-close" onclick="document.getElementById('potm-modal-overlay').remove()">×</button>
                    </div>
                    <div class="potm-modal-body">
                        <div class="potm-player-list">
                            ${playerStats.map(stat => `
                                <div class="potm-player-item" data-player-id="${stat.player_id}" data-appearance-id="${stat.appearance_id}">
                                    <div class="potm-player-info">
                                        <strong>${stat.player_name}</strong>
                                        <span class="potm-team-name">${stat.team_name}</span>
                                    </div>
                                    <div class="potm-player-stats">
                                        <span>${stat.runs || 0} runs</span>
                                        ${stat.wickets > 0 ? `<span>${stat.wickets} wkts</span>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="potm-modal-footer">
                        <button class="potm-btn-cancel" onclick="document.getElementById('potm-modal-overlay').remove()">Skip</button>
                    </div>
                </div>
            </div>
        `;
        
        // Append modal to body
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = modalHtml;
        const modal = tempDiv.firstElementChild;
        document.body.appendChild(modal);
        
        // Add styles if not already added
        if (!document.getElementById('potm-modal-styles')) {
            const style = document.createElement('style');
            style.id = 'potm-modal-styles';
            style.textContent = `
                .potm-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    backdrop-filter: blur(4px);
                    z-index: 10002;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .potm-modal-dialog {
                    background: white;
                    border-radius: 16px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 600px;
                    width: 100%;
                    max-height: 90vh;
                    overflow-y: auto;
                }
                .potm-modal-header {
                    padding: 20px 24px;
                    border-bottom: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .potm-modal-header h3 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: 600;
                    color: #111827;
                }
                .potm-modal-close {
                    background: none;
                    border: none;
                    font-size: 28px;
                    color: #6b7280;
                    cursor: pointer;
                    padding: 0;
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                }
                .potm-modal-close:hover {
                    background: #f3f4f6;
                }
                .potm-modal-body {
                    padding: 24px;
                    max-height: 60vh;
                    overflow-y: auto;
                }
                .potm-player-list {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                .potm-player-item {
                    padding: 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .potm-player-item:hover {
                    border-color: #3b82f6;
                    background: #eff6ff;
                }
                .potm-player-item.selected {
                    border-color: #3b82f6;
                    background: #dbeafe;
                }
                .potm-player-info {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }
                .potm-player-info strong {
                    font-size: 16px;
                    color: #111827;
                }
                .potm-team-name {
                    font-size: 13px;
                    color: #6b7280;
                }
                .potm-player-stats {
                    display: flex;
                    gap: 12px;
                    font-size: 14px;
                    color: #4b5563;
                    font-weight: 500;
                }
                .potm-modal-footer {
                    padding: 16px 24px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                }
                .potm-btn-cancel {
                    padding: 10px 24px;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    background: white;
                    color: #6b7280;
                    cursor: pointer;
                    font-size: 15px;
                    font-weight: 500;
                }
                .potm-btn-cancel:hover {
                    background: #f9fafb;
                }
                .potm-btn-confirm {
                    padding: 10px 24px;
                    border: none;
                    border-radius: 8px;
                    background: #3b82f6;
                    color: white;
                    cursor: pointer;
                    font-size: 15px;
                    font-weight: 500;
                }
                .potm-btn-confirm:hover {
                    background: #2563eb;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Show modal
        setTimeout(() => modal.classList.add('show'), 10);
        
        // Return promise that resolves when POTM is selected or cancelled
        return new Promise((resolve) => {
            let selectedPlayerId = null;
            let selectedAppearanceId = null;
            
            // Handle player selection
            modal.querySelectorAll('.potm-player-item').forEach(item => {
                item.addEventListener('click', function() {
                    // Remove previous selection
                    modal.querySelectorAll('.potm-player-item').forEach(i => i.classList.remove('selected'));
                    // Add selection
                    this.classList.add('selected');
                    selectedPlayerId = parseInt(this.dataset.playerId);
                    selectedAppearanceId = parseInt(this.dataset.appearanceId);
                    
                    // Add confirm button if not already added
                    if (!modal.querySelector('.potm-btn-confirm')) {
                        const cancelBtn = modal.querySelector('.potm-btn-cancel');
                        const confirmBtn = document.createElement('button');
                        confirmBtn.className = 'potm-btn-confirm';
                        confirmBtn.textContent = 'Confirm POTM';
                        confirmBtn.onclick = async () => {
                            // Save POTM selection
                            try {
                                const potmResponse = await api.request('/admin.php/potm-select', {
                                    method: 'POST',
                                    body: JSON.stringify({
                                        match_id: matchId,
                                        player_id: selectedPlayerId,
                                        reason: 'Selected during match completion'
                                    })
                                });
                                
                                if (potmResponse.success) {
                                    if (typeof toast !== 'undefined') {
                                        toast.success('POTM selected successfully!');
                                    }
                                }
                            } catch (error) {
                                console.error('Failed to save POTM:', error);
                                if (typeof toast !== 'undefined') {
                                    toast.error('Failed to save POTM selection');
                                }
                            }
                            
                            modal.remove();
                            resolve(true);
                        };
                        cancelBtn.parentElement.insertBefore(confirmBtn, cancelBtn);
                    }
                });
            });
            
            // Handle cancel/close
            const cancelBtn = modal.querySelector('.potm-btn-cancel');
            const closeBtn = modal.querySelector('.potm-modal-close');
            
            const cleanup = () => {
                modal.remove();
                resolve(false);
            };
            
            cancelBtn.addEventListener('click', cleanup);
            closeBtn.addEventListener('click', cleanup);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                }
            });
        });
    } catch (error) {
        console.error('Failed to show POTM selection modal:', error);
        return false;
    }
}

/**
 * Utility functions
 */
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function getClientId() {
    let clientId = localStorage.getItem('client_id');
    if (!clientId) {
        clientId = generateUUID();
        localStorage.setItem('client_id', clientId);
    }
    return clientId;
}


/**
 * Critical Updates for Scoring System
 * - Toast notifications integration
 * - Bowler rotation tracking (no consecutive overs)
 * - Wicket dropdown filtering (only remaining players)
 * - Dismissed batsmen tracking
 */

// Track dismissed batsmen from events
function updateDismissedBatsmen() {
    dismissedBatsmen.clear();
    
    // Go through events to find dismissed batsmen
    for (const event of matchEvents) {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json;
        
        if (payload && payload.event_type === 'wicket') {
            // The batsman who got out
            if (payload.out_batsman_appearance_id) {
                dismissedBatsmen.add(payload.out_batsman_appearance_id);
            } else if (event.appearance_id) {
                // Fallback: if wicket event has appearance_id, it might be the new batsman
                // We need to find who got out - it's the previous striker
                // This is handled in wicket submission logic
            }
        }
    }
}

// Track last bowler after over completes
function trackLastBowler() {
    if (currentBowler) {
        lastBowler = {
            appearance_id: currentBowler.appearance_id,
            player_id: currentBowler.player_id,
            player_name: currentBowler.player_name
        };
    } else {
        // Fallback: Try to get from dropdown if currentBowler is null but we have a selection
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect && bowlerSelect.value) {
            const option = bowlerSelect.options[bowlerSelect.selectedIndex];
            if (option.value) {
                lastBowler = {
                    appearance_id: parseInt(option.value),
                    player_id: parseInt(option.dataset.playerId || 0),
                    player_name: option.dataset.playerName || option.textContent.trim()
                };
            }
        }
    }
}

// Filter bowler dropdown to exclude last bowler
function filterBowlerDropdown() {
    const bowlerSelect = document.getElementById('bowler-select');
    if (!bowlerSelect || !lastBowler) {
        return; // No filtering needed if no last bowler
    }
    
    // Disable the option for last bowler (can't bowl consecutive overs)
    for (let option of bowlerSelect.options) {
        if (option.value && parseInt(option.value) === lastBowler.appearance_id) {
            option.disabled = true;
            option.textContent = option.textContent + ' (just bowled)';
        } else {
            option.disabled = false;
            // Remove "(just bowled)" if it exists
            if (option.textContent.includes(' (just bowled)')) {
                option.textContent = option.textContent.replace(' (just bowled)', '');
            }
        }
    }
}

// Filter wicket dropdown to show only remaining players who haven't batted
function populateWicketDropdown() {
    const newBatsmanSelect = document.getElementById('new-batsman-select');
    if (!newBatsmanSelect) {
        return;
    }
    
    // Clear existing options (except default)
    while (newBatsmanSelect.options.length > 1) {
        newBatsmanSelect.remove(1);
    }
    
    // Update dismissed batsmen list
    updateDismissedBatsmen();
    
    const strikerSelect = document.getElementById('striker-select');
    if (!strikerSelect) {
        return;
    }
    
    // Get current striker and non-striker IDs
    const currentStrikerId = currentStriker?.appearance_id || null;
    const currentNonStrikerId = currentNonStriker?.appearance_id || null;
    
    // Populate dropdown with only remaining players
    for (let option of strikerSelect.options) {
        if (!option.value) continue; // Skip default option
        
        const appearanceId = parseInt(option.value);
        
        // Skip if:
        // 1. Currently striker or non-striker
        // 2. Already dismissed
        if (appearanceId === currentStrikerId || 
            appearanceId === currentNonStrikerId || 
            dismissedBatsmen.has(appearanceId)) {
            continue;
        }
        
        // Add to new batsman dropdown
        const newOption = option.cloneNode(true);
        newBatsmanSelect.appendChild(newOption);
    }
    
    // If no players available, show message
    if (newBatsmanSelect.options.length === 1) {
        const noOption = document.createElement('option');
        noOption.value = '';
        noOption.textContent = 'No remaining players available';
        noOption.disabled = true;
        newBatsmanSelect.appendChild(noOption);
    }
}



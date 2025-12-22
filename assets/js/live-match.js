/**
 * Live Match View JavaScript
 * Auto-refresh functionality for live match updates
 */

// Get match ID from URL
const urlParams = new URLSearchParams(window.location.search);
const matchId = urlParams.get('id');

if (!matchId) {
    console.error('Match ID not found in URL');
}

// Auto-refresh interval (every 10 seconds for live matches)
let refreshInterval = null;
const REFRESH_INTERVAL = 10000; // 10 seconds

/**
 * Check if match is live and start auto-refresh
 */
function checkMatchState() {
    if (!matchId) return;
    
    fetch(`/cricapp/api/v1/matches.php/${matchId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch match data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                const matchState = data.data.state;
                
                // Only auto-refresh if match is live
                if (matchState === 'live') {
                    if (!refreshInterval) {
                        startAutoRefresh();
                    }
                } else {
                    stopAutoRefresh();
                }
            }
        })
        .catch(error => {
            console.error('Failed to check match state:', error);
            // If API call fails, assume match might be live and try to refresh
            // This is a fallback in case the API endpoint structure is different
        });
}

/**
 * Start auto-refresh
 */
function startAutoRefresh() {
    console.log('Starting auto-refresh for live match');
    
    refreshInterval = setInterval(() => {
        refreshMatchData();
    }, REFRESH_INTERVAL);
    
    // Also refresh immediately
    refreshMatchData();
}

/**
 * Stop auto-refresh
 */
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
        console.log('Stopped auto-refresh');
    }
}

/**
 * Load and display commentary
 */
async function loadCommentary() {
    if (!matchId) return;
    
    try {
        const response = await fetch(`/cricapp/api/v1/events.php?match_id=${matchId}&limit=100`);
        if (!response.ok) {
            throw new Error('Failed to fetch commentary');
        }
        
        const data = await response.json();
        if (data.success && data.data && Array.isArray(data.data)) {
            displayCommentary(data.data);
        }
    } catch (error) {
        console.error('Failed to load commentary:', error);
    }
}

/**
 * Display commentary in cricket-style format (simplified version)
 */
function displayCommentary(events) {
    const feed = document.getElementById('commentary-feed');
    if (!feed) return;
    
    if (events.length === 0) {
        feed.innerHTML = '<div class="loading">No commentary yet</div>';
        return;
    }
    
    // Get player map from events
    const playerMap = {};
    events.forEach(event => {
        if (event.appearance_id && event.player_name) {
            playerMap[event.appearance_id] = event.player_name;
        }
    });
    
    // Track over/ball as we process events from start
    let currentOverNum = 0;
    let currentBallNum = 0;
    
    // Process events (oldest first to calculate over/ball correctly)
    const eventsWithOver = events.map(event => {
        const payload = typeof event.payload_json === 'string' 
            ? JSON.parse(event.payload_json) 
            : event.payload_json || {};
        
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
        const batsmanName = (batsmanId && playerMap[batsmanId]) || event.player_name || 'Unknown';
        const bowlerName = (bowlerId && playerMap[bowlerId]) || 'Unknown';
        
        return { event, payload, isLegalBall, overDisplay, ballDisplay, batsmanName, bowlerName };
    });
    
    // Reverse for display (newest first)
    feed.innerHTML = eventsWithOver.reverse().map(({ event, payload, isLegalBall, overDisplay, ballDisplay, batsmanName, bowlerName }) => {
        const ballLabel = isLegalBall ? `${overDisplay}.${ballDisplay}` : '';
        
        // Get event details
        let eventType = '';
        let eventDescription = '';
        let visualCircle = '';
        let dismissalDetails = '';
        
        if (payload.event_type === 'runs') {
            const runs = payload.runs || 0;
            eventType = runs === 0 ? 'no run' : runs === 4 ? 'FOUR' : runs === 6 ? 'SIX' : `${runs} run${runs !== 1 ? 's' : ''}`;
            
            if (runs === 6) {
                visualCircle = '<span class="commentary-circle" style="background: #9c27b0;">6</span>';
            } else if (runs === 4) {
                visualCircle = '<span class="commentary-circle" style="background: #2196F3;">4</span>';
            }
            
            eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>${eventType.toUpperCase()}</strong>`;
        } else if (payload.event_type === 'wicket') {
            const wicketType = payload.wicket_type || 'Wicket';
            eventType = `OUT ${wicketType.toUpperCase()}!!`;
            visualCircle = '<span class="commentary-circle" style="background: #f44336;">W</span>';
            
            eventDescription = event.commentary || `${bowlerName} to ${batsmanName}, <strong>out ${wicketType}!!</strong>`;
            eventDescription += `<br><span style="font-weight: bold; color: #f44336;">THAT'S OUT!!</span> ${wicketType.toUpperCase()}!!`;
            
            // Get dismissed batsman details
            const outBatsmanId = payload.out_batsman_appearance_id || event.appearance_id;
            if (outBatsmanId && playerMap[outBatsmanId]) {
                const outBatsmanName = playerMap[outBatsmanId];
                dismissalDetails = `<div class="commentary-dismissal">${outBatsmanName} ${wicketType.toUpperCase()} b ${bowlerName}</div>`;
            }
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
        
        // Use custom commentary if available
        if (event.commentary && event.commentary.trim()) {
            eventDescription = `${bowlerName} to ${batsmanName}, ${event.commentary}`;
        }
        
        // Build HTML
        return `
            <div class="commentary-item">
                <div class="commentary-header">
                    ${visualCircle}
                    <strong style="font-size: 1.1em; color: #333; margin-right: 8px;">${ballLabel}</strong>
                    <span style="font-weight: bold; color: #1976d2;">${eventType.toUpperCase()}</span>
                </div>
                <div class="commentary-description">
                    ${eventDescription}
                </div>
                ${dismissalDetails}
            </div>
        `;
    }).join('');
    
    // Scroll to top (newest at top)
    feed.scrollTop = 0;
}

/**
 * Refresh match data
 */
function refreshMatchData() {
    if (!matchId) return;
    
    // Check if page is visible (don't refresh if tab is hidden)
    if (document.hidden) {
        return;
    }
    
    // Reload the page to get latest data
    // In a production app, you'd use AJAX to update specific sections
    // For simplicity, we'll reload the page
    window.location.reload();
}

/**
 * Initialize on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    if (!matchId) return;
    
    // Load commentary immediately
    loadCommentary();
    
    // Refresh commentary every 10 seconds
    setInterval(() => {
        if (!document.hidden) {
            loadCommentary();
        }
    }, 10000);
    
    // Check match state and start/stop refresh accordingly
    checkMatchState();
    
    // Check match state periodically (every 30 seconds)
    setInterval(checkMatchState, 30000);
    
    // Stop refresh when page is hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // Don't stop, but won't refresh when hidden
        } else {
            // Resume refresh when visible
            checkMatchState();
            loadCommentary();
        }
    });
});

/**
 * Cleanup on page unload
 */
window.addEventListener('beforeunload', () => {
    stopAutoRefresh();
});

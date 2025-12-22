/**
 * Public Portal JavaScript
 * Handles live updates for public portal
 */

// Ensure api is available
if (typeof api === 'undefined') {
    console.error('API client not loaded. Please ensure api.js is loaded before public.js');
}

// Auto-refresh live matches every 15 seconds
if (window.location.pathname.includes('index.php') || window.location.pathname === '/cricapp/public/') {
    setInterval(() => {
        refreshLiveMatches();
    }, 15000);
}

async function refreshLiveMatches() {
    if (typeof api === 'undefined') {
        console.error('API client not available');
        return;
    }
    
    try {
        const response = await api.getMatches({ state: 'live' });
        if (response.success && response.data) {
            updateLiveMatchesDisplay(response.data);
        }
    } catch (error) {
        console.error('Failed to refresh live matches:', error);
    }
}

function updateLiveMatchesDisplay(matches) {
    const container = document.getElementById('live-matches');
    if (!container) return;

    if (matches.length === 0) {
        container.innerHTML = '<div class="no-matches">No live matches at the moment</div>';
        return;
    }

    // Update match cards or reload page for simplicity
    location.reload();
}



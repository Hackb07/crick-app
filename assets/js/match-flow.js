/**
 * Match Flow JavaScript
 * 
 * Tab navigation and UI interactions for match flow interface.
 * Service Worker removed - admin pages need real-time data
 */

/**
 * Show a specific tab and hide others
 * 
 * @param {string} tabName Tab name (e.g., 'assign-players', 'record-toss')
 * @param {HTMLElement|null} tabButton Tab button element that was clicked
 */
function showTab(tabName, tabButton) {
    // Don't switch if button is disabled
    if (tabButton && tabButton.disabled) {
        console.log('Tab is disabled:', tabName);
        return;
    }

    // Hide all tab contents
    document.querySelectorAll('.flow-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.flow-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab content
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
        // Scroll to top of tab content for better UX
        selectedTab.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        console.error('Tab not found:', tabName);
    }

    // Add active class to clicked tab
    if (tabButton) {
        tabButton.classList.add('active');
    }
}

// Update player card styling on checkbox change
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const card = this.closest('.player-card');
            if (card) {
                if (this.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
    });
});




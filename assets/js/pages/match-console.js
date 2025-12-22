/**
 * Match Console Sripts
 * Extracted from console.php for separation of concerns.
 * 
 * Compliance: @quality:standards, @arch:separation
 */

// ===== Offline Detection & PWA Support =====
window.addEventListener('online', () => {
    showToast('Connection restored', 'success');
});

window.addEventListener('offline', () => {
    showToast('You are offline. Changes will sync when online.', 'warning');
});

// Check initial connection status
if (!navigator.onLine) {
    showToast('You are currently offline', 'warning');
}

// ===== Toast Notification System =====
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${getToastIcon(type)}</span>
        <span class="toast-message">${message}</span>
    `;

    // Add toast styles if not already present
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast {
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: white;
                padding: 12px 20px;
                border-radius: 12px;
                box-shadow: var(--shadow-lg);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 9999;
                animation: slideInRight 0.3s ease-out;
                max-width: 400px;
                border-left: 4px solid;
            }
            .toast-success { border-left-color: var(--success); }
            .toast-error { border-left-color: var(--error); }
            .toast-warning { border-left-color: var(--warning); }
            .toast-info { border-left-color: var(--primary); }
            .toast-icon { font-size: 1.2rem; }
            .toast-message { flex: 1; font-size: 0.9rem; color: var(--text-main); }
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);

    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function getToastIcon(type) {
    const icons = {
        success: '✅',
        error: '⚠️',
        warning: '⚡',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}

// ===== Form Submission with Loading States =====
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function (e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            // Re-enable if form validation fails
            setTimeout(() => {
                if (!this.checkValidity()) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            }, 100);
        }
    });
});

// ===== Progress Tracking & Step Management =====
const stepOrder = ['basics', 'squads', 'toss', 'start'];
let completedSteps = new Set();

function markStepCompleted(step) {
    completedSteps.add(step);
    const tabBtn = document.querySelector(`[data-step="${step}"]`);
    if (tabBtn) {
        tabBtn.classList.add('completed');
    }
}

function updateProgressBar() {
    const progress = (completedSteps.size / stepOrder.length) * 100;
    const progressBar = document.getElementById('progress-bar');
    if (progressBar) {
        progressBar.style.width = progress + '%';
    }
}

function getNextStep(currentStep) {
    const currentIndex = stepOrder.indexOf(currentStep);
    return currentIndex < stepOrder.length - 1 ? stepOrder[currentIndex + 1] : null;
}

// ===== Navigation & Tab Management =====
function toggleSidebar() {
    document.querySelector('.app-shell').classList.toggle('sidebar-open');
}

function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    document.getElementById('tab-' + tabName).classList.add('active');

    // Find and activate the correct tab button
    const targetTab = document.querySelector(`[data-step="${tabName}"]`);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Scroll to top smoothly
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Announce tab change for screen readers
    announceToScreenReader(`Switched to ${tabName} tab`);
}

// Auto-advance after successful form submission
function handleFormSuccess(currentStep) {
    // Mark current step as completed
    markStepCompleted(currentStep);
    updateProgressBar();

    // Get next step
    const nextStep = getNextStep(currentStep);

    if (nextStep) {
        // Show success toast
        showToast(`${capitalize(currentStep)} saved successfully!`, 'success');

        // Auto-advance after short delay
        setTimeout(() => {
            switchTab(nextStep);
            showToast(`Continue with ${capitalize(nextStep)}`, 'info');
        }, 800);
    } else {
        showToast('All steps completed!', 'success');
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ===== Squad Team Switching =====
function showSquadTeam(teamId) {
    // Hide all team containers
    document.querySelectorAll('.squad-team-container').forEach(el => {
        el.classList.remove('active');
    });

    // Show selected team
    const targetTeam = document.getElementById(`squad-${teamId}`);
    if (targetTeam) {
        targetTeam.classList.add('active');
    }

    // Update sub-tab active state
    document.querySelectorAll('.squad-sub-tab').forEach(el => {
        el.classList.remove('active');
    });

    const targetTab = document.getElementById(`squad-tab-${teamId}`);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Announce for screen readers
    announceToScreenReader(`Switched to ${teamId === 'team1' ? 'Team 1' : 'Team 2'} squad`);
}

// ===== Enhanced Player Row Toggle =====
function togglePlayerRow(checkbox) {
    const playerRow = checkbox.closest('.player-row-enhanced');

    if (checkbox.checked) {
        playerRow.classList.add('selected');
        playerRow.setAttribute('aria-selected', 'true');

        // CSS handles visibility of actions (.player-actions opacity/pointer-events)
    } else {
        playerRow.classList.remove('selected');
        playerRow.setAttribute('aria-selected', 'false');

        // Uncheck Guest/Captain inputs when player is deselected
        const actions = playerRow.querySelector('.player-actions');
        if (actions) {
            actions.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
        }
    }
}

// ===== Player Search & Filter =====
function filterPlayers(input, listId) {
    const term = input.value.toLowerCase();
    const list = document.getElementById(listId);
    const items = list.getElementsByClassName('player-row-enhanced');
    let visibleCount = 0;

    for (let item of items) {
        const name = item.querySelector('.player-name').innerText.toLowerCase();
        const isVisible = name.includes(term);
        // Use empty string to revert to CSS-defined display (flex), otherwise 'none' to hide
        item.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    }

    // Announce results for screen readers (debounced ideally, but valid here)
    // announceToScreenReader(`${visibleCount} players found`);
}

// ===== Player Selection (Legacy/Fallback) =====
function toggleRow(checkbox) {
    const row = checkbox.closest('.player-row');
    if (checkbox.checked) {
        row.classList.add('selected');
        row.setAttribute('aria-selected', 'true');
    } else {
        row.classList.remove('selected');
        row.setAttribute('aria-selected', 'false');
    }
}

// ===== Selection Cards (Toss) =====
function updateCards(radio) {
    const name = radio.name;
    const cards = document.querySelectorAll(`input[name="${name}"]`);
    cards.forEach(input => {
        const card = input.closest('.selection-card');
        if (input.checked) {
            card.classList.add('active');
            card.setAttribute('aria-selected', 'true');
        } else {
            card.classList.remove('active');
            card.setAttribute('aria-selected', 'false');
        }
    });
}

// ===== Accessibility: Screen Reader Announcements =====
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.className = 'sr-only';
    announcement.textContent = message;

    // Add sr-only class if not exists
    if (!document.querySelector('.sr-only-styles')) {
        const style = document.createElement('style');
        style.className = 'sr-only-styles';
        style.textContent = `
            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border-width: 0;
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(announcement);
    setTimeout(() => announcement.remove(), 1000);
}

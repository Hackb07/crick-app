/**
* Score Page - Modal Management
* Handles all modal dialogs for wickets, extras, new batsmen, etc.
*/

/**
 * Open player selection dropdown
 * @param {string} playerType - 'striker', 'non-striker', or 'bowler'
 */
function openPlayerSelect(playerType) {
    const modal = document.getElementById('player-modal');
    const title = document.getElementById('modal-title');
    const strikerList = document.getElementById('striker-list');
    const nonStrikerList = document.getElementById('non-striker-list');
    const bowlerList = document.getElementById('bowler-list');

    if (!modal || !title) return;

    // Hide all lists
    if (strikerList) strikerList.style.display = 'none';
    if (nonStrikerList) nonStrikerList.style.display = 'none';
    if (bowlerList) bowlerList.style.display = 'none';

    // Show appropriate list and set title
    if (playerType === 'striker') {
        title.textContent = 'Select Striker';
        if (strikerList) strikerList.style.display = 'block';
    } else if (playerType === 'non-striker') {
        title.textContent = 'Select Non-Striker';
        if (nonStrikerList) nonStrikerList.style.display = 'block';
    } else if (playerType === 'bowler') {
        title.textContent = 'Select Bowler';
        if (bowlerList) bowlerList.style.display = 'block';
    }

    // Show modal
    modal.classList.remove('hidden'); // Ensure hidden class is removed if used
    modal.style.display = 'flex'; // Force display flex
    modal.removeAttribute('hidden'); // Remove hidden attribute
}

/**
 * Close player selection modal
 */
function closeModal(event) {
    if (event && event.target.id !== 'player-modal' && !event.target.classList.contains('btn-close')) return;
    const modal = document.getElementById('player-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
}

/**
 * Show extra runs modal for wide/no-ball
 * @param {string} extraType - 'wide' or 'no-ball'
 */
function showExtraRunsModal(extraType) {
    const modal = document.getElementById('extra-runs-modal');
    const modalTitle = document.getElementById('extra-runs-modal-title');
    const extraTypeInput = document.getElementById('extra-runs-type');

    if (modalTitle) modalTitle.textContent = extraType === 'wide' ? 'Wide - Additional Runs?' : 'No Ball - Additional Runs?';
    if (extraTypeInput) extraTypeInput.value = extraType;

    const amountInput = document.getElementById('extra-runs-amount');
    if (amountInput) amountInput.value = '1';

    if (modal) {
        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }
}

/**
 * Close extra runs modal
 */
function closeExtraRunsModal() {
    const modal = document.getElementById('extra-runs-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
}

/**
 * Record extra with runs from modal
 */
/**
 * Record extra with runs from modal
 * @param {number} runsArg - Amount of runs (optional)
 */
function recordExtraWithRuns(runsArg) {
    const extraType = document.getElementById('extra-runs-type').value;
    const runsInput = document.getElementById('extra-runs-amount');

    // Use argument if provided, otherwise check input, default to 1
    const runs = runsArg || (runsInput ? parseInt(runsInput.value) : 1) || 1;

    closeExtraRunsModal();

    recordBall({
        type: 'extra',
        extra_type: extraType,
        runs: runs
    });
}

/**
 * Show extras modal (for ... button)
 * Placeholder for additional extras options
 */
function showExtrasModal() {
    // For now, just show a simple alert
    // Can be expanded to show a modal with more extra types
    alert('Additional extras options:\n- Use WD for wide\n- Use NB for no ball\n- Use BYE for bye\n- Use LB for leg bye');
}

/**
 * Show wicket modal (confirmWicket is called by button)
 */
function confirmWicket() {
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        alert('Innings is complete! Please start 2nd innings.');
        return;
    }

    if (!currentStrikerId || !currentBowlerId) {
        alert('Please select striker and bowler');
        return;
    }
    const modal = document.getElementById('wicket-modal');
    if (modal) modal.classList.add('active');
}

/**
 * Close wicket modal
 */
function closeWicketModal(event) {
    if (event && event.target.id !== 'wicket-modal') return;
    const modal = document.getElementById('wicket-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
}

/**
 * Show wicket modal
 */
function showWicketModal() {
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        alert('Innings is complete! Please start 2nd innings.');
        return;
    }

    if (!currentStrikerId || !currentBowlerId) {
        alert('Please select striker and bowler');
        return;
    }

    const modal = document.getElementById('wicket-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }
}

/**
 * Record wicket type
 * @param {string} dismissalType - Type of dismissal
 */
function recordWicket(dismissalType) {
    closeWicketModal();

    // For now, simple wicket recording (can be enhanced later with fielder selection)
    pendingWicketData = {
        type: 'wicket',
        dismissal_type: dismissalType
    };

    showNewBatsmanModal();
}

/**
 * Record wicket type
 * @param {string} dismissalType - Type of dismissal
 */
function recordWicketType(dismissalType) {
    closeWicketModal();

    // Dismissals that require fielder selection
    if (dismissalType === 'caught' || dismissalType === 'stumped') {
        document.getElementById('pending-dismissal-type').value = dismissalType;
        showFielderModal();
        return;
    }

    // Direct dismissals (bowled, lbw, hit_wicket)
    pendingWicketData = {
        type: 'wicket',
        dismissal_type: dismissalType
    };

    showNewBatsmanModal();
}

/**
 * Show fielder selection modal
 */
function showFielderModal() {
    const modal = document.getElementById('fielder-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }
}

/**
 * Close fielder modal
 */
function closeFielderModal() {
    const modal = document.getElementById('fielder-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
}

/**
 * Confirm fielder selection
 */
function confirmFielder() {
    const fielderId = document.getElementById('fielder-select').value;
    if (!fielderId) {
        alert('Please select a fielder');
        return;
    }

    const dismissalType = document.getElementById('pending-dismissal-type').value;
    closeFielderModal();

    pendingWicketData = {
        type: 'wicket',
        dismissal_type: dismissalType,
        fielder_id: parseInt(fielderId)
    };

    showNewBatsmanModal();
}

/**
 * Show run out selection modal
 */
function showRunOutSelection() {
    closeWicketModal();
    const modal = document.getElementById('run-out-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }
}

/**
 * Close run out modal
 */
function closeRunOutModal() {
    const modal = document.getElementById('run-out-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
    showWicketModal(); // Go back
}

/**
 * Record run out
 * @param {string} who - 'striker' or 'non_striker'
 */
function recordRunOut(who) {
    closeRunOutModal();

    pendingWicketData = {
        type: 'wicket',
        dismissal_type: 'run out',
        _runOutPlayer: who
    };

    showNewBatsmanModal();
}

/**
 * Show new batsman selection modal
 */
function showNewBatsmanModal() {
    updateNewBatsmanDropdown();

    const dropdown = document.getElementById('new-batsman-select');
    const availableCount = dropdown.options.length - 1;

    if (availableCount === 0) {
        alert('No more batsmen available. Last batsman continues alone.');
        const wicketData = pendingWicketData;
        pendingWicketData = null;

        if (wicketData) {
            recordBall(wicketData);
        }
        return;
    }

    const modal = document.getElementById('new-batsman-modal');
    if (modal) {
        modal.style.display = 'flex';
        modal.removeAttribute('hidden');
    }

    const select = document.getElementById('new-batsman-select');
    if (select) select.value = '';
}

/**
 * Close new batsman modal
 */
function closeNewBatsmanModal() {
    const modal = document.getElementById('new-batsman-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('hidden', '');
    }
    pendingWicketData = null;
}

/**
 * Confirm new batsman selection
 */
function confirmNewBatsman() {
    const newBatsmanId = document.getElementById('new-batsman-select').value;
    if (!newBatsmanId) {
        alert('Please select a new batsman');
        return;
    }

    const wicketData = pendingWicketData;
    closeNewBatsmanModal();

    if (wicketData) {
        wicketData.new_batsman_id = parseInt(newBatsmanId);
        wicketData._newBatsmanId = parseInt(newBatsmanId);
        recordBall(wicketData);
    } else {
        alert('Error: Wicket data not found. Please try again.');
    }
}

/**
 * Update new batsman dropdown with available players
 */
function updateNewBatsmanDropdown() {
    const dropdown = document.getElementById('new-batsman-select');
    if (!dropdown) return;

    const strikerSelect = document.getElementById('striker');
    if (!strikerSelect) return;

    const currentStrikerId = parseInt(strikerSelect.value) || null;
    const nonStrikerSelect = document.getElementById('non-striker');
    const currentNonStrikerId = parseInt(nonStrikerSelect?.value) || null;

    // Get dismissed players from event history
    const dismissedPlayerIds = new Set();
    eventHistory.forEach(event => {
        if (event.type === 'wicket') {
            dismissedPlayerIds.add(event.striker_id);
        }
    });

    // Get all available options from striker dropdown
    const allPlayers = Array.from(strikerSelect.options).map(opt => ({
        value: opt.value,
        text: opt.text,
        playerId: parseInt(opt.value)
    })).filter(opt => opt.value);

    // Clear dropdown
    dropdown.innerHTML = '<option value="">Select...</option>';

    // Add available players
    allPlayers.forEach(player => {
        if (player.playerId !== currentStrikerId &&
            player.playerId !== currentNonStrikerId &&
            !dismissedPlayerIds.has(player.playerId)) {
            const option = document.createElement('option');
            option.value = player.value;
            option.textContent = player.text;
            dropdown.appendChild(option);
        }
    });
}

/**
 * Show retired hurt modal
 */
function showRetiredHurtModal() {
    if (isInningsComplete(currentWickets, currentOvers, currentBalls, maxOvers)) {
        alert('Innings is complete! Please start 2nd innings.');
        return;
    }

    if (!currentStrikerId) {
        alert('Please select striker');
        return;
    }
    document.getElementById('retired-hurt-modal').classList.add('active');
    document.getElementById('retired-hurt-replacement').value = '';
}

/**
 * Close retired hurt modal
 */
function closeRetiredHurtModal() {
    document.getElementById('retired-hurt-modal').classList.remove('active');
}

/**
 * Confirm retired hurt
 */
function confirmRetiredHurt() {
    const replacementId = document.getElementById('retired-hurt-replacement').value;
    if (!replacementId) {
        alert('Please select replacement batsman');
        return;
    }

    closeRetiredHurtModal();

    recordBall({
        type: 'retired_hurt',
        retired_player_id: currentStrikerId,
        replacement_player_id: parseInt(replacementId)
    });

    // Update striker to replacement
    const strikerSelect = document.getElementById('striker');
    strikerSelect.value = replacementId;
    updateStriker();
}

/**
 * Show over completion notification
 */
function showOverNotification() {
    const notification = document.getElementById('over-notification');
    const text = document.getElementById('over-notification-text');

    const overRuns = currentOverRuns;
    text.textContent = `Over ${currentOvers} Complete! ${overRuns} runs`;
    notification.classList.add('show');

    // Reset current over tracking
    currentOverRuns = 0;
    currentOverExtras = 0;
    currentOverLegalBalls = 0;

    // Clear bowler selection
    const bowlerSelect = document.getElementById('bowler');
    if (bowlerSelect) {
        bowlerSelect.value = '';
        updateBowler();
    }

    // Filter out previous bowler
    filterBowlerDropdown(lastOverBowlerId);

    setTimeout(() => {
        notification.classList.remove('show');

        setTimeout(() => {
            if (bowlerSelect) {
                bowlerSelect.focus();
            }
        }, 500);
    }, 5000);
}

/**
 * Show notification message
 * @param {string} title - Notification title
 * @param {string} message - Notification message
 * @param {string} type - Notification type ('success', 'info', 'warning', 'error')
 */
function showNotification(title, message, type = 'info') {
    let notification = document.getElementById('completion-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'completion-notification';
        notification.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            min-width: 300px;
            max-width: 500px;
            display: none;
        `;
        document.body.appendChild(notification);
    }

    let bgColor = '#17a2b8';
    if (type === 'success') bgColor = '#28a745';
    else if (type === 'warning') bgColor = '#ffc107';
    else if (type === 'error') bgColor = '#dc3545';

    notification.style.background = bgColor;
    notification.style.color = 'white';
    notification.innerHTML = `
        <div style="font-size: 2rem; margin-bottom: 12px;">${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</div>
        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 12px;">${title}</div>
        <div style="font-size: 1rem; white-space: pre-line; opacity: 0.95;">${message}</div>
    `;
    notification.style.display = 'block';

    setTimeout(() => {
        notification.style.display = 'none';
    }, 5000);
}

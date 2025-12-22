/**
 * Admin Bottom Bar Component
 * Floating action bar with primary CTA and quick actions
 */

class AdminBottomBar {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;
        
        this.actionQueue = [];
        this.undoTimeout = null;
        this.init();
    }
    
    init() {
        // Primary CTA handler
        const primaryCTA = this.container.querySelector('.btn-primary-cta');
        if (primaryCTA) {
            primaryCTA.addEventListener('click', (e) => this.handlePrimaryCTA(e));
        }
        
        // Action buttons
        this.container.querySelectorAll('.btn-action').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleAction(e));
        });
        
        // Quick Tools handler
        const quickTools = this.container.querySelector('.quick-tools');
        if (quickTools) {
            quickTools.addEventListener('click', (e) => this.handleQuickTools(e));
        }
    }
    
    handlePrimaryCTA(e) {
        // Haptic feedback
        if ('vibrate' in navigator) {
            navigator.vibrate(20);
        }
        
        // Get current match state and determine action
        const matchState = this.getMatchState();
        
        if (matchState === 'live') {
            // Quick Score action
            window.location.href = this.getQuickScoreUrl();
        } else {
            // Start Match action
            this.triggerStartMatch();
        }
    }
    
    handleAction(e) {
        const action = e.currentTarget.getAttribute('data-action');
        
        if ('vibrate' in navigator) {
            navigator.vibrate(10);
        }
        
        switch(action) {
            case 'pause':
                this.triggerPause();
                break;
            case 'end':
                this.triggerEnd();
                break;
            case 'settings':
                this.openSettings();
                break;
            case 'logs':
                this.openLogs();
                break;
        }
    }
    
    handleQuickTools(e) {
        // Open action sheet for quick tools
        this.showActionSheet([
            { label: 'Import Players', action: () => this.importPlayers() },
            { label: 'Export CSV', action: () => this.exportCSV() },
            { label: 'Bulk Actions', action: () => this.bulkActions() }
        ]);
    }
    
    getMatchState() {
        // Check for match state indicator in DOM
        const stateIndicator = document.querySelector('[data-match-state]');
        return stateIndicator ? stateIndicator.getAttribute('data-match-state') : 'scheduled';
    }
    
    getQuickScoreUrl() {
        const matchId = this.getMatchId();
        return `/cricapp/admin/matches/score.php?id=${matchId}`;
    }
    
    getMatchId() {
        const matchIdElement = document.querySelector('[data-match-id]');
        return matchIdElement ? matchIdElement.getAttribute('data-match-id') : null;
    }
    
    triggerStartMatch() {
        const matchId = this.getMatchId();
        if (!matchId) {
            this.showToast('No match selected', 'error');
            return;
        }
        
        // Optimistic update
        this.updateUIState('live');
        this.addToActionQueue('start_match', { matchId });
        
        // Show toast with undo
        this.showToastWithUndo('Match started', () => {
            this.undoLastAction();
        });
        
        // Make API call
        fetch(`/cricapp/api/v1/matches.php/${matchId}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                throw new Error('Failed to start match');
            }
            return response.json();
        }).catch(error => {
            // Revert optimistic update
            this.revertLastAction();
            this.showToast('Failed to start match', 'error');
        });
    }
    
    triggerPause() {
        this.addToActionQueue('pause', {});
        this.showToastWithUndo('Match paused', () => {
            this.undoLastAction();
        });
    }
    
    async triggerEnd() {
        let shouldProceed = false;
        if (typeof toast !== 'undefined') {
            shouldProceed = await toast.confirm('Are you sure you want to end this match?', {
                title: 'End Match',
                confirmText: 'Yes, End Match',
                cancelText: 'Cancel'
            });
        } else {
            shouldProceed = confirm('Are you sure you want to end this match?');
        }
        
        if (!shouldProceed) {
            return;
        }
        
        const matchId = this.getMatchId();
        this.addToActionQueue('end_match', { matchId });
        this.showToastWithUndo('Match ended', () => {
            this.undoLastAction();
        });
    }
    
    addToActionQueue(action, data) {
        this.actionQueue.push({
            action,
            data,
            timestamp: Date.now()
        });
        
        // Auto-clear old actions (older than 7 seconds)
        this.actionQueue = this.actionQueue.filter(
            item => Date.now() - item.timestamp < 7000
        );
    }
    
    undoLastAction() {
        if (this.actionQueue.length === 0) return;
        
        const lastAction = this.actionQueue.pop();
        // Revert based on action type
        this.revertAction(lastAction);
        this.showToast('Action undone', 'success');
    }
    
    revertAction(action) {
        switch(action.action) {
            case 'start_match':
                this.updateUIState('scheduled');
                break;
            case 'pause':
                this.updateUIState('live');
                break;
            case 'end_match':
                this.updateUIState('live');
                break;
        }
    }
    
    updateUIState(state) {
        const stateIndicator = document.querySelector('[data-match-state]');
        if (stateIndicator) {
            stateIndicator.setAttribute('data-match-state', state);
        }
        
        // Update primary CTA text/icon
        const primaryCTA = this.container.querySelector('.btn-primary-cta');
        if (primaryCTA) {
            if (state === 'live') {
                primaryCTA.innerHTML = '🏏';
                primaryCTA.setAttribute('title', 'Quick Score');
            } else {
                primaryCTA.innerHTML = '▶️';
                primaryCTA.setAttribute('title', 'Start Match');
            }
        }
    }
    
    showToastWithUndo(message, undoCallback) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <span class="toast-message">${message}</span>
            <button class="toast-action" onclick="window.adminBottomBar.undoLastAction()">Undo</button>
        `;
        
        const container = document.querySelector('.toast-container') || this.createToastContainer();
        container.appendChild(toast);
        
        // Store undo callback
        window.adminBottomBar = this;
        
        // Auto-dismiss after 7 seconds
        setTimeout(() => {
            toast.remove();
        }, 7000);
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span class="toast-message">${message}</span>`;
        
        const container = document.querySelector('.toast-container') || this.createToastContainer();
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
    showActionSheet(actions) {
        // Create action sheet modal
        const sheet = document.createElement('div');
        sheet.className = 'action-sheet';
        sheet.innerHTML = `
            <div class="action-sheet-backdrop"></div>
            <div class="action-sheet-content">
                ${actions.map((action, index) => `
                    <button class="action-sheet-item" onclick="window.actionSheetActions[${index}]()">
                        ${action.label}
                    </button>
                `).join('')}
                <button class="action-sheet-item action-sheet-cancel" onclick="this.closest('.action-sheet').remove()">
                    Cancel
                </button>
            </div>
        `;
        
        // Store actions globally for onclick handlers
        window.actionSheetActions = actions.map(a => a.action);
        
        document.body.appendChild(sheet);
        
        // Close on backdrop click
        sheet.querySelector('.action-sheet-backdrop').addEventListener('click', () => {
            sheet.remove();
        });
    }
    
    importPlayers() {
        // Open import modal
        window.location.href = '/cricapp/admin/players/?import=true';
    }
    
    exportCSV() {
        // Trigger CSV export
        window.location.href = '/cricapp/admin/export.php?format=csv';
    }
    
    bulkActions() {
        // Open bulk actions modal
        this.showToast('Bulk actions coming soon', 'info');
    }
    
    openSettings() {
        window.location.href = '/cricapp/admin/settings/';
    }
    
    openLogs() {
        window.location.href = '/cricapp/admin/settings/audit-log.php';
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('admin-bottom-bar')) {
            window.adminBottomBarInstance = new AdminBottomBar('admin-bottom-bar');
        }
    });
} else {
    if (document.getElementById('admin-bottom-bar')) {
        window.adminBottomBarInstance = new AdminBottomBar('admin-bottom-bar');
    }
}


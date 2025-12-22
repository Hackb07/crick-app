/**
 * Multi-Select Player Addition System
 * Allows selecting multiple players at once and adding them to teams
 */

class PlayerMultiSelect {
    constructor(matchId, teamId, teamName) {
        this.matchId = matchId;
        this.teamId = teamId;
        this.teamName = teamName;
        this.selectedPlayers = new Set();
        this.allPlayers = [];
        this.addedPlayers = new Set(); // Players already in this team
        this.init();
    }

    async init() {
        await this.loadPlayers();
        this.render();
    }

    async loadPlayers() {
        try {
            // Get all players from API
            const response = await api.getPlayers();

            if (response && response.success && response.data) {
                this.allPlayers = response.data;
            } else {
                // Fallback: try to get from page data
                const playerSelect = document.querySelector(`[data-team-id="${this.teamId}"] select[name="player_id"]`);
                if (playerSelect) {
                    this.allPlayers = Array.from(playerSelect.options)
                        .filter(opt => opt.value)
                        .map(opt => ({
                            player_id: parseInt(opt.value),
                            name: opt.textContent.trim()
                        }));
                }
            }

            // Get already added players
            await this.loadAddedPlayers();
        } catch (error) {
            console.error('Failed to load players:', error);
            if (typeof toast !== 'undefined') {
                toast.error('Failed to load players. Please refresh the page.');
            }
        }
    }

    async loadAddedPlayers() {
        try {
            const response = await api.request(`/matches.php/${this.matchId}/players`, {
                method: 'GET'
            });

            if (response && response.success && response.data) {
                const teamPlayers = response.data.teams?.find(t => t.team_id === this.teamId);
                if (teamPlayers) {
                    teamPlayers.players.forEach(p => {
                        this.addedPlayers.add(p.player_id);
                    });
                }
            }
        } catch (error) {
            console.error('Failed to load added players:', error);
        }
    }

    render() {
        const container = document.getElementById(`team${this.teamId}-multiselect`);
        if (!container) return;

        // Filter out already added players
        const availablePlayers = this.allPlayers.filter(p => !this.addedPlayers.has(p.player_id));

        container.innerHTML = `
            <div class="multiselect-container">
                <div class="multiselect-header">
                    <h5>Add Players to ${this.teamName}</h5>
                    <button class="btn btn-sm btn-secondary" onclick="window.playerMultiSelect_${this.teamId}.close()">×</button>
                </div>
                
                <div class="multiselect-search">
                    <input type="text" id="search-${this.teamId}" placeholder="Search players..." 
                           class="form-control" oninput="window.playerMultiSelect_${this.teamId}.filter(this.value)">
                </div>
                
                <div class="multiselect-list" id="player-list-${this.teamId}">
                    ${availablePlayers.map(p => `
                        <label class="multiselect-item">
                            <input type="checkbox" value="${p.player_id}" 
                                   onchange="window.playerMultiSelect_${this.teamId}.togglePlayer(${p.player_id})">
                            <span>${this.escapeHtml(p.name)}</span>
                        </label>
                    `).join('')}
                </div>
                
                <div class="multiselect-footer">
                    <div class="selected-count" id="selected-count-${this.teamId}">
                        <span id="count-${this.teamId}">0</span> selected
                    </div>
                    <div class="multiselect-actions">
                        <button class="btn btn-secondary" onclick="window.playerMultiSelect_${this.teamId}.close()">Cancel</button>
                        <button class="btn btn-primary" onclick="window.playerMultiSelect_${this.teamId}.addSelected()" 
                                id="add-btn-${this.teamId}" disabled>
                            Add Selected (<span id="btn-count-${this.teamId}">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    togglePlayer(playerId) {
        if (this.selectedPlayers.has(playerId)) {
            this.selectedPlayers.delete(playerId);
        } else {
            this.selectedPlayers.add(playerId);
        }
        this.updateUI();
    }

    updateUI() {
        const count = this.selectedPlayers.size;
        const countEl = document.getElementById(`count-${this.teamId}`);
        const btnCountEl = document.getElementById(`btn-count-${this.teamId}`);
        const addBtn = document.getElementById(`add-btn-${this.teamId}`);

        if (countEl) countEl.textContent = count;
        if (btnCountEl) btnCountEl.textContent = count;
        if (addBtn) {
            addBtn.disabled = count === 0;
            addBtn.classList.toggle('disabled', count === 0);
        }
    }

    filter(searchTerm) {
        const listEl = document.getElementById(`player-list-${this.teamId}`);
        if (!listEl) return;

        const term = searchTerm.toLowerCase();
        const items = listEl.querySelectorAll('.multiselect-item');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term) ? 'flex' : 'none';
        });
    }

    async addSelected() {
        if (this.selectedPlayers.size === 0) {
            toast.warning('Please select at least one player');
            return;
        }

        const playerIds = Array.from(this.selectedPlayers);
        
        // Show loading state
        const addBtn = document.getElementById(`add-btn-${this.teamId}`);
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.textContent = 'Adding...';
        }

        try {
            // Add players one by one (or in bulk if API supports it)
            let successCount = 0;
            let failCount = 0;

            for (const playerId of playerIds) {
                try {
                    const response = await api.request(`/matches.php/${this.matchId}/players`, {
                        method: 'POST',
                        body: JSON.stringify({
                            player_id: playerId,
                            team_id: this.teamId
                        })
                    });

                    if (response && response.success) {
                        successCount++;
                        this.addedPlayers.add(playerId);
                        this.selectedPlayers.delete(playerId);
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    console.error(`Failed to add player ${playerId}:`, error);
                    failCount++;
                }
            }

            if (successCount > 0) {
                toast.success(`Successfully added ${successCount} player(s) to ${this.teamName}`);
                // Reload page to show updated player list
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }

            if (failCount > 0) {
                toast.error(`Failed to add ${failCount} player(s). They may already be in the team.`);
            }

        } catch (error) {
            console.error('Failed to add players:', error);
            toast.error('Failed to add players. Please try again.');
        } finally {
            if (addBtn) {
                addBtn.disabled = false;
                addBtn.innerHTML = 'Add Selected (<span id="btn-count-' + this.teamId + '">0</span>)';
            }
        }
    }

    show() {
        const container = document.getElementById(`team${this.teamId}-multiselect`);
        if (container) {
            container.style.display = 'block';
            // Focus search input
            const searchInput = document.getElementById(`search-${this.teamId}`);
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        }
    }

    close() {
        const container = document.getElementById(`team${this.teamId}-multiselect`);
        if (container) {
            container.style.display = 'none';
            this.selectedPlayers.clear();
            this.updateUI();
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}


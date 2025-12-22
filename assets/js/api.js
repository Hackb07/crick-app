/**
 * API Client for Cricket Scoring App
 */

// Use dynamic API base URL from config if available, otherwise default to relative path
const API_BASE_URL = (window.APP_CONFIG && window.APP_CONFIG.apiBase) 
    ? window.APP_CONFIG.apiBase 
    : '/api/v1';

class ApiClient {
    constructor() {
        this.token = localStorage.getItem('auth_token');
    }

    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    }

    getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        return headers;
    }

    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const config = {
            ...options,
            headers: {
                ...this.getHeaders(),
                ...(options.headers || {})
            }
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                if (response.status === 401) {
                    // Token expired or invalid, redirect to login
                    this.setToken(null);
                    localStorage.removeItem('auth_token');
                    if (window.location.pathname.includes('/admin')) {
                        // Store redirect URL to return after login
                        const redirectUrl = window.location.pathname + window.location.search;
                        window.location.href = `/cricapp/admin/login.php?expired=1&redirect=${encodeURIComponent(redirectUrl)}`;
                    } else {
                        window.location.href = '/cricapp/admin/login.php?expired=1';
                    }
                    // Throw error to stop execution
                    throw new Error('Authentication required');
                }
                
                // For 409 conflicts, preserve the full error object
                if (response.status === 409) {
                    // PHP jsonError sends: {error: {message, client_base_seq, server_seq, missing_events}}
                    // data.error will be an object with the conflict details
                    const conflictInfo = data.error || data;
                    const errorMessage = typeof conflictInfo === 'object' && conflictInfo.message 
                        ? conflictInfo.message 
                        : 'Conflict: Client is stale';
                    
                    const error = new Error(errorMessage);
                    error.code = 409;
                    // Store the conflict details in error.data for handleConflict to use
                    error.data = conflictInfo;
                    error.originalResponse = data; // Also keep full response
                    throw error;
                }
                
                // For other errors, extract error message properly
                let errorMessage = 'Request failed';
                if (data.error) {
                    if (typeof data.error === 'string') {
                        errorMessage = data.error;
                    } else if (typeof data.error === 'object' && data.error.message) {
                        errorMessage = data.error.message;
                    } else if (data.message) {
                        errorMessage = data.message;
                    } else {
                        errorMessage = JSON.stringify(data.error);
                    }
                } else if (data.message) {
                    errorMessage = data.message;
                }
                
                const error = new Error(errorMessage);
                error.code = response.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Auth methods
    async login(username, password) {
        const data = await this.request('/auth.php', {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
        
        if (data.data && data.data.token) {
            this.setToken(data.data.token);
        }
        
        return data;
    }

    // Match methods
    async getMatches(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/matches.php?${params}`);
    }

    async getMatch(matchId) {
        return this.request(`/matches.php/${matchId}`);
    }

    async createMatch(matchData) {
        return this.request('/matches.php', {
            method: 'POST',
            body: JSON.stringify(matchData)
        });
    }

    // Event methods
    async getMatchEvents(matchId, limit = 100, offset = 0) {
        return this.request(`/events.php?match_id=${matchId}&limit=${limit}&offset=${offset}`);
    }

    async getSyncStatus(matchId) {
        return this.request(`/events.php/sync-status?match_id=${matchId}`);
    }

    // Player methods
    async getPlayers(search = '') {
        const params = search ? `?search=${encodeURIComponent(search)}` : '';
        return this.request(`/players.php${params}`);
    }

    async getPlayer(playerId) {
        return this.request(`/players.php/${playerId}`);
    }

    async getPlayerStats(playerId, seriesId = null) {
        const params = seriesId ? `?series_id=${seriesId}` : '';
        return this.request(`/players.php/${playerId}/stats${params}`);
    }

    // Stats methods
    async getMatchSummary(matchId) {
        return this.request(`/stats.php?match_id=${matchId}`);
    }

    async getLeaderboard() {
        return this.request('/stats.php/leaderboard');
    }

    async getSeriesLeaderboard(seriesId) {
        return this.request(`/stats.php/${seriesId}/leaderboard`);
    }
}

// Global API client instance
const api = new ApiClient();



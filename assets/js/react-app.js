/**
 * React 18 Application Setup
 * Shared hosting compatible - uses CDN (no build process)
 */

// React API Client compatible with React
const ReactApiClient = {
    baseUrl: '/cricapp/api/v1',
    token: localStorage.getItem('auth_token'),
    
    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    },
    
    getHeaders() {
        const headers = { 'Content-Type': 'application/json' };
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        return headers;
    },
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            ...options,
            headers: { ...this.getHeaders(), ...(options.headers || {}) }
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    this.setToken(null);
                    window.location.href = '/cricapp/admin/login.php?expired=1';
                    throw new Error('Authentication required');
                }
                const errorMessage = data.error?.message || data.error || data.message || 'Request failed';
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
    },
    
    // Match methods
    async getMatches(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/matches.php?${params}`);
    },
    
    async getMatch(matchId) {
        return this.request(`/matches.php/${matchId}`);
    },
    
    async getMatchEvents(matchId, limit = 100) {
        return this.request(`/events.php?match_id=${matchId}&limit=${limit}`);
    },
    
    async getLeaderboard() {
        return this.request('/stats.php/leaderboard');
    },
    
    async getPlayers(search = '') {
        const params = search ? `?search=${encodeURIComponent(search)}` : '';
        return this.request(`/players.php${params}`);
    }
};

// React Utilities
const ReactUtils = {
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    },
    
    formatDateTime(date) {
        return new Date(date).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }
};

// Global React App Utilities
window.ReactApp = {
    React,
    ReactDOM,
    apiClient: ReactApiClient,
    utils: ReactUtils
};


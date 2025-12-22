/**
 * Vue.js 3 Application Setup
 * Shared hosting compatible - uses CDN
 */

// Wait for Vue to be loaded
if (typeof Vue === 'undefined') {
    console.error('Vue.js is not loaded. Make sure Vue.js CDN script is loaded before this file.');
    // Create a stub to prevent errors
    window.Vue = { createApp: function() { return { mount: function() {} }; } };
}

// Vue 3 App Configuration (only if Vue is available)
let createApp = null;
if (typeof Vue !== 'undefined' && Vue.createApp) {
    createApp = Vue.createApp;
}

// API Client compatible with Vue
const apiClient = {
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

// Shared Vue Components
const VueComponents = {
    // Loading Spinner
    'loading-spinner': {
        template: `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p v-if="message">{{ message }}</p>
            </div>
        `,
        props: ['message']
    },
    
    // Match Card
    'match-card': {
        template: `
            <div :class="['match-card-modern', { 'live': match.state === 'live' }]">
                <div class="match-card-header">
                    <span :class="['status-badge', match.state]">{{ badgeText }}</span>
                    <span class="series-name">{{ match.series_name || 'Match' }}</span>
                </div>
                <div class="match-teams">
                    <div class="team">{{ match.team1_name }}</div>
                    <div class="vs">vs</div>
                    <div class="team">{{ match.team2_name }}</div>
                </div>
                <div class="match-info">
                    <div class="match-date" v-if="match.match_date">
                        {{ formatDate(match.match_date) }}
                    </div>
                    <a :href="link" class="btn-modern btn-primary">{{ buttonText }}</a>
                </div>
            </div>
        `,
        props: ['match'],
        computed: {
            badgeText() {
                const states = { 'live': 'LIVE', 'completed': 'Completed', 'scheduled': 'Scheduled', 'draft': 'Draft' };
                return states[this.match.state] || this.match.state;
            },
            link() {
                const basePath = (window.APP_CONFIG && window.APP_CONFIG.publicBase) 
                    ? window.APP_CONFIG.publicBase 
                    : '/public';
                if (this.match.state === 'live') {
                    return `${basePath}/live-match.php?id=${this.match.match_id}`;
                }
                return `${basePath}/match-view.php?id=${this.match.match_id}`;
            },
            buttonText() {
                return this.match.state === 'live' ? 'View Live' : 'View Details';
            }
        },
        methods: {
            formatDate(date) {
                return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }
        }
    },
    
    // Error Message
    'error-message': {
        template: `
            <div class="error-message-modern">
                <div class="error-icon">⚠️</div>
                <div class="error-text">{{ message }}</div>
            </div>
        `,
        props: ['message']
    },
    
    // Empty State
    'empty-state': {
        template: `
            <div class="empty-state-modern">
                <div class="empty-icon">{{ icon || '📭' }}</div>
                <div class="empty-title">{{ title }}</div>
                <div class="empty-message" v-if="message">{{ message }}</div>
            </div>
        `,
        props: ['icon', 'title', 'message']
    }
};

// Global Vue Utilities - Only set if Vue is available
if (typeof Vue !== 'undefined' && createApp) {
    window.VueApp = {
        createApp,
        apiClient,
        VueComponents
    };
} else {
    // Create a stub to prevent errors
    window.VueApp = {
        createApp: null,
        apiClient: apiClient,
        VueComponents: {}
    };
    console.warn('Vue.js not fully loaded. Some features may not work.');
}


/**
 * App Configuration - Auto-generated from PHP config
 * This file is generated dynamically by PHP pages
 * DO NOT EDIT MANUALLY - Values are set by PHP config
 */

// Base path for the application (auto-detected)
window.APP_CONFIG = window.APP_CONFIG || {
    basePath: '',
    apiBase: '',
    assetBase: '',
    adminBase: '',
    publicBase: ''
};

// Helper functions for URL generation
window.AppUrl = {
    base: function(path) {
        path = path ? '/' + path.replace(/^\//, '') : '';
        return window.APP_CONFIG.basePath + path;
    },
    api: function(endpoint) {
        endpoint = endpoint ? '/' + endpoint.replace(/^\//, '') : '';
        return window.APP_CONFIG.apiBase + endpoint;
    },
    asset: function(path) {
        path = path ? '/' + path.replace(/^\//, '') : '';
        return window.APP_CONFIG.assetBase + path;
    },
    admin: function(path) {
        path = path ? '/' + path.replace(/^\//, '') : '';
        return window.APP_CONFIG.adminBase + path;
    },
    public: function(path) {
        path = path ? '/' + path.replace(/^\//, '') : '';
        return window.APP_CONFIG.publicBase + path;
    }
};

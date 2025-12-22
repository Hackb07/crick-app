/**
 * Service Worker Registration Script
 * Include this in all public pages for PWA support
 */

(function() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            // Get base path from current location
            // If we're at /cricapp/public/index.php, base path is /cricapp
            const currentPath = window.location.pathname;
            let basePath = '';
            
            // Extract base path (everything before /public or /admin)
            const publicMatch = currentPath.match(/^(.+?)\/public\//);
            const adminMatch = currentPath.match(/^(.+?)\/admin\//);
            
            if (publicMatch) {
                basePath = publicMatch[1];
            } else if (adminMatch) {
                basePath = adminMatch[1];
            } else {
                // If no subdirectory, check if we're in root
                const pathParts = currentPath.split('/').filter(p => p);
                if (pathParts.length > 1) {
                    // We're in a subdirectory
                    basePath = '/' + pathParts[0];
                }
            }
            
            // Service worker path
            const swPath = basePath ? basePath + '/sw.js' : '/sw.js';
            
            navigator.serviceWorker.register(swPath)
                .then(function(reg) {
                    console.log('Service Worker registered successfully:', reg.scope);
                })
                .catch(function(err) {
                    console.error('Service Worker registration failed:', err);
                    // Log the attempted path for debugging
                    console.error('Attempted to register:', swPath);
                    console.error('Current location:', window.location.href);
                });
        });
    }
})();


/**
 * Service Worker for CricApp PWA
 * Provides offline functionality and caching
 */

const CACHE_NAME = 'cricapp-v1';
const RUNTIME_CACHE = 'cricapp-runtime-v1';

// Get base path from service worker scope
// Service worker scope is the directory it's in
const BASE_PATH = self.location.pathname.replace('/sw.js', '') || '';

// Helper function to create paths with base path
function getPath(path) {
  // If path starts with /, it's absolute - prepend base path
  if (path.startsWith('/')) {
    return BASE_PATH + path;
  }
  // Otherwise, it's relative
  return BASE_PATH + '/' + path;
}

// Assets to cache on install
// NOTE: Admin pages are excluded - they need real-time data
const STATIC_ASSETS = [
  getPath('/'),
  getPath('/index.php'),
  // Admin pages removed - they should NOT be cached
  // getPath('/admin/login.php'),
  // getPath('/admin/scorer-login.php'),
  getPath('/assets/css/main.css'),
  getPath('/assets/css/premium-design.css'),
  getPath('/assets/css/pwa-mobile.css'),
  // Admin CSS can still be cached as it's static
  getPath('/assets/css/admin.css'),
  getPath('/assets/images/icon-192x192.png'),
  getPath('/assets/images/icon-512x512.png'),
  getPath('/manifest.json')
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        // Use addAll with error handling - cache each asset individually
        // This prevents one failed asset from breaking the entire cache
        return Promise.allSettled(
          STATIC_ASSETS.map(url => {
            return fetch(url)
              .then(response => {
                // Only cache successful responses
                if (response.ok) {
                  return cache.put(url, response);
                } else {
                  console.warn('[SW] Failed to cache:', url, response.status);
                  return Promise.resolve(); // Don't fail on individual errors
                }
              })
              .catch(err => {
                console.warn('[SW] Error caching:', url, err);
                return Promise.resolve(); // Don't fail on individual errors
              });
          })
        );
      })
      .then(() => {
        console.log('[SW] Cache installation complete');
        return self.skipWaiting();
      })
      .catch(err => {
        console.error('[SW] Cache installation error:', err);
        // Still skip waiting even if cache fails
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
          })
          .map((cacheName) => {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
      .then(() => self.clients.claim())
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip API requests (they should always be fresh)
  if (event.request.url.includes('/api/')) {
    return;
  }

  // Skip admin pages (they need real-time data, not cached)
  if (event.request.url.includes('/admin/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((cachedResponse) => {
        // Return cached version if available
        if (cachedResponse) {
          return cachedResponse;
        }

        // Otherwise fetch from network
        return fetch(event.request)
          .then((response) => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }

            // Clone the response for caching
            const responseToCache = response.clone();

            // Cache dynamic content in runtime cache
            caches.open(RUNTIME_CACHE)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });

            return response;
          })
          .catch(() => {
            // If network fails and it's a navigation request, return offline page
            if (event.request.mode === 'navigate') {
              return caches.match(getPath('/index.php')) ||
                caches.match(getPath('/admin/login.php'));
            }
          });
      })
  );
});

// Background sync for offline actions (if needed in future)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-scores') {
    event.waitUntil(syncScores());
  }
});

async function syncScores() {
  // Implement score synchronization when back online
  console.log('[SW] Syncing scores...');
}


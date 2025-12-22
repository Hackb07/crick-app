# PWA (Progressive Web App) Standards

## 1. App-Like Feel
- **App Shell Model**: Separate the core UI (shell) from the dynamic content. Load shell instantly.
- **Stand-alone**: Use `display: standalone` in `manifest.json` to hide browser UI.
- **Splash Screen**: Define `background_color` and `theme_color` in manifest for seamless startup.

## 2. Offline Capabilities
- **Service Workers**: Cache critical assets (CSS, JS, key images) during install.
- **Network Strategies**:
  - *Stale-while-revalidate* for frequently changing content.
  - *Cache-first* for static assets.
  - *Network-first* for API calls.
- **Offline UI**: Show a custom "You are offline" message or functional reduced mode, never the browser's dinosaur.

## 3. Installability
- **Manifest Requirements**: Name, short_name, icons (192x192, 512x512), start_url, display mode.
- **Install Prompt**: Intercept `beforeinstallprompt` event to show a custom "Install App" button within the UI logic, rather than relying on the browser's default banner.

## 4. Performance
- **Lighthouse**: Target 90+ score in PWA category.
- **Lazy Loading**: Lazy load routes and heavy components to keep proper First Input Delay (FID).

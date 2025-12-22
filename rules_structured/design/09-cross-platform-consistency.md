# Cross-Platform Consistency

## 1. Platform-Specific Considerations

### Desktop (Windows, macOS, Linux)
- **Keyboard Shortcuts**: Support common shortcuts (Ctrl/Cmd+S, Ctrl/Cmd+Z).
- **Context Menus**: Right-click menus for power users.
- **Hover States**: Rich hover interactions are expected.
- **Multi-Window**: Consider multi-monitor setups.

### Mobile (iOS, Android)
- **Touch Gestures**: Swipe, pinch, long-press where appropriate.
- **Native Patterns**: Follow platform conventions (iOS: bottom tabs, Android: FAB).
- **Safe Areas**: Respect notches and system UI (use `env(safe-area-inset-*)`).
- **Haptic Feedback**: Use vibration API for tactile feedback on actions.

### Tablet (iPad, Android Tablets)
- **Hybrid Approach**: Combine desktop density with touch interactions.
- **Landscape Optimization**: Design for both orientations.
- **Split View**: Consider iPad split-screen multitasking.

## 2. Browser Compatibility

### Testing Matrix
- **Chrome/Edge** (Chromium): Latest 2 versions
- **Firefox**: Latest 2 versions
- **Safari**: Latest 2 versions (iOS + macOS)
- **Samsung Internet**: Latest version (if targeting Android)

### Fallbacks
- **Feature Detection**: Use `@supports` for CSS, feature detection for JS.
- **Polyfills**: Load only when needed (use `nomodule` for legacy browsers).
- **Graceful Degradation**: Core functionality must work without JS/CSS.

## 3. Performance Across Devices

### Low-End Devices
- **Bundle Size**: Keep initial JS < 200KB (gzipped).
- **Image Optimization**: Serve appropriate sizes via `srcset`.
- **Code Splitting**: Load features on-demand.
- **Throttling Testing**: Test on 3G/4G networks, not just WiFi.

### High-End Devices
- **Enhanced Experiences**: Progressive enhancement for capable devices.
- **High DPI**: Serve 2x/3x images for retina displays.
- **Advanced Features**: Use WebGL, WebAssembly where beneficial.

## 4. Input Method Diversity
- **Touch**: Primary for mobile/tablet.
- **Mouse**: Primary for desktop.
- **Keyboard**: Must be fully functional everywhere.
- **Stylus**: Consider for drawing/annotation features.
- **Voice**: Ensure form fields work with voice input.

## 5. Orientation & Viewport
- **Portrait & Landscape**: Both must be usable.
- **Viewport Meta**: `<meta name="viewport" content="width=device-width, initial-scale=1">`
- **Orientation Lock**: Only for specific use cases (games, video).
- **Fold Devices**: Consider foldable phones (Samsung Fold, Surface Duo).

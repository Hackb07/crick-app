# Responsive & Adaptive Design

## 1. Breakpoint Strategy
- **Mobile First**: Default styles are for mobile. Use `min-width` media queries for larger screens.
  - `sm`: 640px (Large Phones)
  - `md`: 768px (Tablets / IPads)
  - `lg`: 1024px (Laptops / Landscape Tablets)
  - `xl`: 1280px (Desktops)
  - `2xl`: 1536px (Large Screens)

## 2. Touch vs. Click
- **Touch Targets**: All interactive elements on mobile MUST be at least 44x44px.
- **Hover**: Do NOT rely on hover for essential information on touch devices. Use `media (hover: hover)` to apply hover effects only on supported devices.

## 3. Layout Patterns
- **Grid vs Flex**: Use CSS Grid for 2-dimensional page layouts. Use Flexbox for 1-dimensional components (navbars, lists).
- **Fluid Typography**: Use `clamp()` or `calc()` for font sizes that scale smoothly between viewports.

## 4. Cross-Browser & Device
- **Testing**: Verify on Chrome, Firefox, Safari (iOS & macOS), and Edge.
- **Reset/Normalize**: Use a modern CSS reset to ensure consistent rendering.
- **Vendor Prefixes**: Use Autoprefixer in build step; do not manually write `-webkit-` unless specific legacy support is needed.

# Animation & Motion Design

## 1. Purpose-Driven Animation
- **Feedback**: Confirm user actions (button press, form submission).
- **Guidance**: Direct attention (highlight new content, show relationships).
- **Continuity**: Maintain context during transitions (page changes, modal opens).
- **Delight**: Subtle personality (hover effects, micro-interactions).

## 2. Timing & Easing
- **Duration Guidelines**:
  - Micro-interactions: 100-200ms
  - UI transitions: 200-400ms
  - Page transitions: 400-600ms
  - Never exceed 1000ms for UI animations
- **Easing Functions**:
  - `ease-out`: Elements entering the screen
  - `ease-in`: Elements leaving the screen
  - `ease-in-out`: Elements moving within the screen
  - Avoid linear easing except for continuous animations

## 3. Performance
- **GPU Acceleration**: Animate `transform` and `opacity` only when possible.
- **Avoid**: Animating `width`, `height`, `top`, `left` (causes layout thrashing).
- **will-change**: Use sparingly and remove after animation completes.
- **Reduced Motion**: Respect `prefers-reduced-motion` media query.

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

## 4. Animation Patterns
- **Fade**: Opacity transitions for content appearing/disappearing.
- **Slide**: Transform translateX/Y for drawers, modals.
- **Scale**: Transform scale for emphasis, zoom effects.
- **Stagger**: Delay animations in lists for sequential reveal.

## 5. Loading States
- **Skeleton Screens**: Preferred over spinners for content loading.
- **Progress Indicators**: Show determinate progress when possible.
- **Optimistic UI**: Update UI immediately, rollback on error.

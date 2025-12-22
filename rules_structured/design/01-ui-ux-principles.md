# UI/UX Principles (Design System Core)

## 1. Visual Hierarchy
- **Typography Scale**: Use a strict scale (e.g., Major Third) to differentiate headers and body text.
- **Contrast**: High contrast for important actions (Primary Buttons), lower contrast for secondary.
- **Whitespace**: Use whitespace to group related elements ("Proximity") and separate distinct ones.

## 2. Consistency & Design System
- **Colors**: Define semantic colors (`primary`, `success`, `danger`, `surface`, `text-primary`). Avoid hardcoded hex values in components.
- **Spacing**: Use a 4px or 8px grid system (`0.5rem`, `1rem`, `1.5rem`).
- **Components**: Reuse atoms (buttons, inputs) to build molecules (forms, cards).

## 3. Accessibility (Universal Access)
- **Contrast Ratio**: minimum 4.5:1 for normal text (WCAG AA).
- **Focus States**: Never remove `outline` on focus without replacing it with a custom style.
- **Semantics**: Use correct HTML5 tags (`<button>`, `<nav>`, `<main>`) for screen readers.

## 4. Feedback & Interaction
- **Micro-interactions**: Hover effects, active states, and focus rings are mandatory for interactive elements.
- **Loading States**: Use skeletons instead of spinners for layout stability during data fetches.
- **Error Handling**: Inline validation with clear error messages near the input.

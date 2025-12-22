# Design to Code Workflow

## 1. Component Driven Development (CDD)
- **Atomic Design**: Structure code to match design atoms -> molecules -> organisms.
- **Single Source of Truth**: The code is the final truth, but it must mirror the Figma/Design file structure.
- **Naming Parity**: If a component is named `PrimaryButton` in Figma, it must be `PrimaryButton.tsx` (or `.js`) in code.

## 2. Visual Development (Builder.io Philosophy)
- **Visual Editing**: Where possible, expose visual controls (CMS or props) for non-technical editors.
- **Declarative UI**: Focus on *what* the UI looks like in a given state, rather than *how* to change it (React/Vue/Modern Framework mindset).
- **Code Generation**: Use tools to generate boilerplate from design tokens when possible.

## 3. Handoff Protocol
- **Specs**: Designs must include spacing, typography, colors, and *interactive states* (hover, focus, disabled).
- **Assets**: Export SVGs for icons; use modern formats like WebP for images.
- **Responsive Logic**: Annotate how specific layouts behave on mobile vs desktop.

## 4. Rapid Prototyping
- **Iterative Approach**: Build "ugly" functional versions first to test flow, then style.
- **Mock Data**: Use realistic mock data early to test layout limits (long names, empty states).

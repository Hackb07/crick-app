# Component Library Standards

## 1. Component Architecture
- **Composition over Configuration**: Build complex components from simple ones.
- **Props API**: Design intuitive, predictable prop interfaces.
  - Use semantic names (`variant="primary"` not `type="1"`)
  - Provide sensible defaults
  - Support both controlled and uncontrolled modes where applicable
- **Slots/Children**: Allow flexible content injection via slots or children props.

## 2. Component Variants
- **Size System**: `xs`, `sm`, `md`, `lg`, `xl` for consistent scaling.
- **Visual Variants**: `primary`, `secondary`, `outline`, `ghost`, `danger`.
- **State Variants**: `default`, `hover`, `active`, `focus`, `disabled`, `loading`.

## 3. Documentation Requirements
Each component MUST have:
- **Usage Examples**: Basic, advanced, and edge cases.
- **Props Table**: Name, type, default, description.
- **Accessibility Notes**: ARIA attributes, keyboard navigation.
- **Do's and Don'ts**: Visual examples of correct/incorrect usage.

## 4. Storybook/Component Explorer
- **Isolated Development**: Build components in isolation before integration.
- **Interactive Controls**: Expose all props for live testing.
- **Visual Regression**: Screenshot testing to catch unintended changes.

## 5. Theming Support
- **CSS Variables**: Use custom properties for theme values.
- **Dark Mode**: All components MUST support dark mode via theme context.
- **Theme Tokens**: Never hardcode colors; always reference design tokens.

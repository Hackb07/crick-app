# Design Tokens System

## 1. Token Categories

### Color Tokens
```css
/* Semantic Colors */
--color-primary: hsl(220, 90%, 56%);
--color-secondary: hsl(280, 70%, 60%);
--color-success: hsl(142, 71%, 45%);
--color-warning: hsl(38, 92%, 50%);
--color-danger: hsl(0, 84%, 60%);

/* Neutral Colors */
--color-text-primary: hsl(0, 0%, 10%);
--color-text-secondary: hsl(0, 0%, 40%);
--color-background: hsl(0, 0%, 100%);
--color-surface: hsl(0, 0%, 98%);
--color-border: hsl(0, 0%, 88%);
```

### Spacing Tokens
```css
/* 4px base scale */
--spacing-xs: 0.25rem;   /* 4px */
--spacing-sm: 0.5rem;    /* 8px */
--spacing-md: 1rem;      /* 16px */
--spacing-lg: 1.5rem;    /* 24px */
--spacing-xl: 2rem;      /* 32px */
--spacing-2xl: 3rem;     /* 48px */
--spacing-3xl: 4rem;     /* 64px */
```

### Typography Tokens
```css
/* Font Families */
--font-sans: 'Inter', system-ui, sans-serif;
--font-mono: 'Fira Code', monospace;

/* Font Sizes (Type Scale) */
--text-xs: 0.75rem;      /* 12px */
--text-sm: 0.875rem;     /* 14px */
--text-base: 1rem;       /* 16px */
--text-lg: 1.125rem;     /* 18px */
--text-xl: 1.25rem;      /* 20px */
--text-2xl: 1.5rem;      /* 24px */
--text-3xl: 1.875rem;    /* 30px */
--text-4xl: 2.25rem;     /* 36px */

/* Font Weights */
--font-normal: 400;
--font-medium: 500;
--font-semibold: 600;
--font-bold: 700;

/* Line Heights */
--leading-tight: 1.25;
--leading-normal: 1.5;
--leading-relaxed: 1.75;
```

### Shadow Tokens
```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
```

### Border Radius Tokens
```css
--radius-sm: 0.25rem;    /* 4px */
--radius-md: 0.5rem;     /* 8px */
--radius-lg: 0.75rem;    /* 12px */
--radius-xl: 1rem;       /* 16px */
--radius-full: 9999px;   /* Pill shape */
```

## 2. Token Organization

### File Structure
```
tokens/
├── colors.json
├── spacing.json
├── typography.json
├── shadows.json
├── borders.json
└── index.js (exports all)
```

### Platform Outputs
- **CSS**: Custom properties (variables)
- **SCSS**: Sass variables
- **JS/TS**: Exported constants
- **iOS**: Swift constants
- **Android**: XML resources

## 3. Naming Convention
- **Format**: `--{category}-{property}-{variant}-{state}`
- **Examples**:
  - `--color-button-primary-hover`
  - `--spacing-card-padding`
  - `--text-heading-large`

## 4. Dark Mode Tokens
```css
[data-theme="dark"] {
  --color-text-primary: hsl(0, 0%, 95%);
  --color-text-secondary: hsl(0, 0%, 70%);
  --color-background: hsl(0, 0%, 10%);
  --color-surface: hsl(0, 0%, 14%);
  --color-border: hsl(0, 0%, 25%);
}
```

## 5. Token Management Tools
- **Style Dictionary**: Transform tokens to multiple platforms
- **Figma Tokens**: Sync design tokens with Figma
- **Version Control**: Track token changes in git
- **Documentation**: Auto-generate token documentation

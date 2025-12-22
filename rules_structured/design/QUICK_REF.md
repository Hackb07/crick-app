# Design Rules - Quick Reference Card

**One-page cheat sheet for UI/UX development**

---

## 🎨 Core Principles

### Visual Hierarchy
- ✅ Use typography scale (1.2x - 1.5x ratio)
- ✅ High contrast for CTAs, low for secondary
- ✅ Whitespace groups related elements

### Consistency
- ✅ Define semantic colors (primary, success, danger)
- ✅ Use 4px/8px spacing grid
- ✅ Reuse components (atoms → molecules → organisms)

### Accessibility
- ✅ 4.5:1 contrast minimum (WCAG AA)
- ✅ Never remove focus outlines
- ✅ Use semantic HTML (`<button>`, `<nav>`, `<main>`)

---

## 📱 Responsive Breakpoints

```css
/* Mobile First */
sm:  640px   /* Large Phones */
md:  768px   /* Tablets */
lg:  1024px  /* Laptops */
xl:  1280px  /* Desktops */
2xl: 1536px  /* Large Screens */
```

**Touch Targets**: Minimum 44x44px on mobile

---

## 🚀 PWA Checklist

- [ ] `manifest.json` with icons (192x192, 512x512)
- [ ] Service worker with caching strategy
- [ ] `display: standalone` in manifest
- [ ] Offline fallback page
- [ ] Lighthouse PWA score 90+

---

## 🎭 Animation Guidelines

### Timing
- Micro-interactions: **100-200ms**
- UI transitions: **200-400ms**
- Page transitions: **400-600ms**
- Never exceed: **1000ms**

### Easing
- Entering: `ease-out`
- Leaving: `ease-in`
- Moving: `ease-in-out`

### Performance
- ✅ Animate `transform` and `opacity` only
- ❌ Avoid `width`, `height`, `top`, `left`
- ✅ Respect `prefers-reduced-motion`

---

## 📝 Form Best Practices

### Structure
- ✅ Single column layout
- ✅ Top-aligned labels
- ✅ Inline validation (on blur)
- ✅ Error messages below field

### Mobile
- ✅ Correct input types (`email`, `tel`, `number`)
- ✅ `font-size: 16px` minimum (prevents zoom)
- ✅ Support autofill with `autocomplete`

### Accessibility
- ✅ Every input has `<label for="...">`
- ✅ Errors linked with `aria-describedby`
- ✅ Focus moves to first error on submit

---

## 🎨 Design Tokens Template

```css
/* Colors */
--color-primary: hsl(220, 90%, 56%);
--color-text-primary: hsl(0, 0%, 10%);
--color-background: hsl(0, 0%, 100%);

/* Spacing (4px scale) */
--spacing-sm: 0.5rem;   /* 8px */
--spacing-md: 1rem;     /* 16px */
--spacing-lg: 1.5rem;   /* 24px */

/* Typography */
--font-sans: 'Inter', system-ui, sans-serif;
--text-base: 1rem;      /* 16px */
--text-lg: 1.25rem;     /* 20px */
--font-medium: 500;

/* Shadows */
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

/* Border Radius */
--radius-md: 0.5rem;    /* 8px */
```

---

## 🧩 Component Variants

### Size System
`xs` | `sm` | `md` | `lg` | `xl`

### Visual Variants
`primary` | `secondary` | `outline` | `ghost` | `danger`

### State Variants
`default` | `hover` | `active` | `focus` | `disabled` | `loading`

---

## 🌐 Cross-Browser Testing

### Minimum Support
- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions (iOS + macOS)
- Samsung Internet: Latest version

### Fallbacks
- Use `@supports` for CSS features
- Feature detection for JavaScript
- Core functionality works without JS

---

## 📄 Landing Page Structure (AIDA)

1. **Attention** (Hero)
   - Strong headline + subheadline
   - Primary CTA
   - High-quality visual

2. **Interest** (Features)
   - Benefits over features
   - Visual demonstrations

3. **Desire** (Social Proof)
   - Testimonials
   - Case studies
   - Trust badges

4. **Action** (Closing)
   - Final strong CTA
   - Minimal friction

---

## ⚡ Performance Targets

### Core Web Vitals
- **LCP** (Largest Contentful Paint): < 2.5s
- **FID** (First Input Delay): < 100ms
- **CLS** (Cumulative Layout Shift): < 0.1

### Bundle Size
- Initial JS: < 200KB (gzipped)
- Images: Use WebP, responsive `srcset`
- Critical CSS: Inline above-the-fold

---

## 🎯 Quick Decision Tree

### "Should I build a component library?"
- **Yes** if: Medium+ project, reusable components
- **No** if: One-off landing page, prototype

### "Do I need a PWA?"
- **Yes** if: Offline functionality, app-like experience
- **No** if: Simple marketing site, SEO-focused

### "When to use animations?"
- **Always**: Feedback, loading states
- **Sometimes**: Delight, branding
- **Never**: If it slows interaction

### "Mobile-first or desktop-first?"
- **Mobile-first**: 95% of projects
- **Desktop-first**: Complex data dashboards only

---

## 🔧 Essential Tools

- **Design**: Figma, Sketch
- **Prototyping**: Framer, ProtoPie
- **Components**: Storybook
- **Testing**: Lighthouse, axe DevTools
- **Visual Dev**: Builder.io

---

## 📋 Pre-Launch Checklist

### Design
- [ ] Design tokens implemented
- [ ] Dark mode supported
- [ ] Responsive on all breakpoints
- [ ] Consistent component variants

### Accessibility
- [ ] WCAG AA compliance (4.5:1 contrast)
- [ ] Keyboard navigation works
- [ ] Screen reader tested
- [ ] Focus states visible

### Performance
- [ ] Lighthouse score 90+
- [ ] Images optimized (WebP, srcset)
- [ ] Bundle size < 200KB
- [ ] Core Web Vitals pass

### Cross-Platform
- [ ] Tested on Chrome, Firefox, Safari
- [ ] Mobile tested (iOS + Android)
- [ ] Tablet layout works
- [ ] Touch targets 44x44px minimum

---

## 🚨 Common Mistakes to Avoid

❌ Hardcoding colors instead of using tokens  
❌ Removing focus outlines without replacement  
❌ Animating layout properties (width, height)  
❌ Placeholder text as labels  
❌ Desktop-first responsive design  
❌ Ignoring `prefers-reduced-motion`  
❌ Touch targets smaller than 44x44px  
❌ Not testing on real devices  

---

## 📞 Quick Reference Shorthands

```bash
@design:principles   # UI/UX Core
@design:responsive   # Mobile/Desktop
@design:pwa          # PWA Rules
@design:workflow     # Design Handoff
@design:landing      # Landing Pages
@design:components   # Component Library
@design:animation    # Motion Design
@design:forms        # Form UX
@design:platform     # Cross-Device
@design:tokens       # Design Tokens
```

---

**Print this page and keep it at your desk!**

**Version**: 1.0.0 | **Updated**: 2025-12-05

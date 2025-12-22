# Design Glossary - Terms & Definitions

**Quick reference for UI/UX terminology used in design rules**

---

## 🎯 Project Types

### Prototype
**Definition**: An interactive mockup or proof-of-concept that simulates the final product's look and behavior without full backend functionality.

**Characteristics**:
- ✅ Interactive UI elements (clickable buttons, navigable screens)
- ✅ Visual design matches final product
- ✅ Demonstrates user flows and interactions
- ❌ No real data processing
- ❌ No backend integration (uses mock/dummy data)
- ❌ Not production-ready

**Use Cases**:
- User testing before development
- Stakeholder presentations
- Design validation
- Developer handoff specifications

**Tools**: Figma, Framer, ProtoPie, Adobe XD, InVision

**Example**: A clickable mobile app design showing all screens and transitions, but using fake data and no actual API calls.

---

### Web Application
**Definition**: A fully functional, interactive application accessed through a web browser with complete backend integration.

**Characteristics**:
- ✅ Full CRUD operations (Create, Read, Update, Delete)
- ✅ Real database integration
- ✅ User authentication and authorization
- ✅ Business logic implementation
- ✅ Production-ready code

**Types**:
- **SPA (Single Page Application)**: React, Vue, Angular apps
- **MPA (Multi-Page Application)**: Traditional server-rendered apps
- **Dashboard/Admin Panel**: Data management interfaces
- **SaaS Applications**: Cloud-based software services

**Example**: Gmail, Trello, Notion, your CricApp admin panel

---

### Progressive Web App (PWA)
**Definition**: A web application that uses modern web capabilities to deliver an app-like experience, including offline functionality and installability.

**Characteristics**:
- ✅ Works offline (service workers)
- ✅ Installable on home screen
- ✅ App-like navigation (no browser UI)
- ✅ Push notifications
- ✅ Fast and responsive
- ✅ Secure (HTTPS required)

**Requirements**:
- `manifest.json` file
- Service worker for caching
- HTTPS hosting
- Responsive design

**Example**: Twitter Lite, Starbucks PWA, Pinterest PWA

---

### Landing Page
**Definition**: A standalone web page designed for a specific marketing campaign or conversion goal.

**Characteristics**:
- ✅ Single focused objective (sign-up, purchase, download)
- ✅ Minimal navigation (reduces distractions)
- ✅ Strong call-to-action (CTA)
- ✅ Optimized for conversion
- ✅ SEO optimized

**Structure**: AIDA (Attention, Interest, Desire, Action)

**Example**: Product launch pages, event registration pages, lead capture pages

---

## 🎨 Design Concepts

### Design System
**Definition**: A collection of reusable components, patterns, and guidelines that ensure consistency across products.

**Components**:
- Design tokens (colors, spacing, typography)
- UI components (buttons, inputs, cards)
- Patterns (navigation, forms, modals)
- Documentation and usage guidelines

**Example**: Material Design (Google), Fluent Design (Microsoft), Carbon Design (IBM)

---

### Design Tokens
**Definition**: Named entities that store visual design attributes (colors, spacing, typography) as variables for reuse.

**Example**:
```css
--color-primary: #3B82F6;
--spacing-md: 16px;
--font-heading: 'Inter', sans-serif;
```

**Benefit**: Change one token, update entire design system

---

### Atomic Design
**Definition**: Methodology for creating design systems with five distinct levels:

1. **Atoms**: Basic building blocks (button, input, label)
2. **Molecules**: Simple component groups (search form = input + button)
3. **Organisms**: Complex components (header = logo + nav + search)
4. **Templates**: Page-level layouts
5. **Pages**: Specific instances with real content

---

### Responsive Design
**Definition**: Design approach where layouts adapt to different screen sizes and devices.

**Approach**: Mobile-first (design for smallest screen, then scale up)

**Techniques**:
- Fluid grids (percentages, not fixed pixels)
- Flexible images (`max-width: 100%`)
- Media queries (breakpoints)

---

### Adaptive Design
**Definition**: Design approach where specific layouts are created for specific device categories.

**Difference from Responsive**: 
- **Responsive**: One fluid layout that adapts
- **Adaptive**: Multiple fixed layouts for different breakpoints

---

## 🛠️ Development Terms

### Component Library
**Definition**: A collection of reusable, pre-built UI components that can be imported and used across projects.

**Examples**: 
- React: Material-UI, Chakra UI, Ant Design
- Vue: Vuetify, Element UI
- Framework-agnostic: Web Components

---

### Storybook
**Definition**: Tool for developing and testing UI components in isolation.

**Features**:
- Interactive component playground
- Documentation generation
- Visual regression testing
- Accessibility testing

---

### Design to Code
**Definition**: The process of converting design files (Figma, Sketch) into production-ready code.

**Workflow**:
1. Designer creates mockups in Figma
2. Developer receives design specs (spacing, colors, fonts)
3. Developer builds components matching design
4. Designer reviews implementation
5. Iterate until pixel-perfect

---

### Visual Development
**Definition**: Building applications using visual editors instead of writing code manually.

**Tools**: Builder.io, Webflow, Framer

**Philosophy**: "What you see is what you get" (WYSIWYG)

---

## 📱 Platform Terms

### Cross-Browser
**Definition**: Ensuring consistent functionality and appearance across different web browsers.

**Browsers to Test**: Chrome, Firefox, Safari, Edge

---

### Cross-Platform
**Definition**: Ensuring consistent experience across different devices and operating systems.

**Platforms**: Desktop (Windows, macOS, Linux), Mobile (iOS, Android), Tablet

---

### Mobile-First
**Definition**: Design and development approach starting with mobile layout, then progressively enhancing for larger screens.

**Why**: Majority of web traffic is mobile, forces focus on essential content

---

## ♿ Accessibility Terms

### WCAG (Web Content Accessibility Guidelines)
**Definition**: International standards for making web content accessible to people with disabilities.

**Levels**:
- **A**: Minimum accessibility
- **AA**: Recommended standard (most common target)
- **AAA**: Highest level of accessibility

---

### Contrast Ratio
**Definition**: Difference in luminance between text and background.

**Requirements**:
- Normal text: 4.5:1 minimum (WCAG AA)
- Large text: 3:1 minimum (WCAG AA)

**Tool**: Use browser DevTools or online contrast checkers

---

### Screen Reader
**Definition**: Assistive technology that reads web content aloud for visually impaired users.

**Examples**: NVDA (Windows), JAWS (Windows), VoiceOver (macOS/iOS)

**Best Practice**: Use semantic HTML and ARIA attributes

---

## 🎭 Animation Terms

### Micro-interaction
**Definition**: Small, subtle animations that provide feedback for user actions.

**Examples**:
- Button hover effect
- Form field focus highlight
- Loading spinner
- Success checkmark animation

**Duration**: 100-200ms

---

### Easing Function
**Definition**: Mathematical function that controls animation acceleration/deceleration.

**Types**:
- `linear`: Constant speed (rarely used)
- `ease-in`: Slow start, fast end
- `ease-out`: Fast start, slow end
- `ease-in-out`: Slow start and end

---

### GPU Acceleration
**Definition**: Using the graphics processor instead of CPU for smoother animations.

**How**: Animate `transform` and `opacity` properties only

---

## 📊 Performance Terms

### Core Web Vitals
**Definition**: Google's metrics for measuring user experience quality.

**Metrics**:
1. **LCP (Largest Contentful Paint)**: Loading performance (< 2.5s)
2. **FID (First Input Delay)**: Interactivity (< 100ms)
3. **CLS (Cumulative Layout Shift)**: Visual stability (< 0.1)

---

### Lighthouse
**Definition**: Automated tool for auditing web page quality.

**Categories**: Performance, Accessibility, Best Practices, SEO, PWA

**Target**: 90+ score in all categories

---

### Code Splitting
**Definition**: Breaking JavaScript bundle into smaller chunks loaded on-demand.

**Benefit**: Faster initial page load

---

### Lazy Loading
**Definition**: Deferring loading of non-critical resources until they're needed.

**Use Cases**: Images below the fold, route-based code splitting

---

## 🎨 UI Patterns

### Hero Section
**Definition**: Large, prominent section at the top of a landing page.

**Contents**: Headline, subheadline, primary CTA, hero image/video

---

### CTA (Call to Action)
**Definition**: Button or link prompting user to take specific action.

**Examples**: "Sign Up", "Get Started", "Download Now", "Learn More"

---

### Modal / Dialog
**Definition**: Overlay window that appears on top of main content.

**Use Cases**: Forms, confirmations, alerts, image galleries

**Best Practice**: Trap focus, allow ESC to close, click outside to dismiss

---

### Skeleton Screen
**Definition**: Placeholder UI shown while content is loading.

**Benefit**: Better perceived performance than spinners

**Example**: Gray boxes mimicking layout of upcoming content

---

## 📝 Form Terms

### Inline Validation
**Definition**: Real-time validation that shows errors as user fills out form.

**Timing**: Validate on blur (when user leaves field)

---

### Autocomplete
**Definition**: Browser feature that fills in form fields with previously entered data.

**Implementation**: Use `autocomplete` attribute (`name`, `email`, `tel`, etc.)

---

### Progressive Disclosure
**Definition**: Showing advanced options only when needed to reduce cognitive load.

**Example**: "Advanced Settings" accordion that expands on click

---

## 🔧 Tools & Technologies

### Figma
**Definition**: Cloud-based design tool for creating UI/UX designs and prototypes.

**Features**: Real-time collaboration, component libraries, prototyping, developer handoff

---

### Service Worker
**Definition**: JavaScript file that runs in background, enabling offline functionality and push notifications.

**Use Cases**: Caching assets, offline pages, background sync

---

### Manifest.json
**Definition**: JSON file providing metadata about a web application for PWA installation.

**Contents**: App name, icons, theme colors, display mode, start URL

---

This glossary will be updated as new terms are introduced in the design rules.

**Version**: 1.0.0  
**Last Updated**: 2025-12-05

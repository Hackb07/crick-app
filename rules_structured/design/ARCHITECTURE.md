# Design Rules - Visual Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                     DESIGN RULES SYSTEM v1.0                        │
│                    (Priority: P1 - Always Apply)                    │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
            ┌───────▼────────┐              ┌──────▼──────┐
            │  FOUNDATION    │              │  USE CASES  │
            └───────┬────────┘              └──────┬──────┘
                    │                               │
        ┌───────────┼───────────┐          ┌───────┼───────┬───────┐
        │           │           │          │       │       │       │
   ┌────▼────┐ ┌───▼────┐ ┌───▼────┐ ┌───▼───┐ ┌─▼──┐ ┌─▼──┐ ┌──▼──┐
   │ Tokens  │ │ Princ. │ │ Comp.  │ │ Web   │ │PWA │ │Pro-│ │Land-│
   │ System  │ │ (Core) │ │ Library│ │ Apps  │ │    │ │type│ │ ing │
   └────┬────┘ └───┬────┘ └───┬────┘ └───┬───┘ └─┬──┘ └─┬──┘ └──┬──┘
        │          │          │          │       │      │       │
        └──────────┴──────────┴──────────┴───────┴──────┴───────┘
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
            ┌───────▼────────┐              ┌──────▼──────┐
            │  IMPLEMENTATION │              │  PLATFORMS  │
            └───────┬────────┘              └──────┬──────┘
                    │                               │
        ┌───────────┼───────────┐          ┌───────┼───────┬───────┐
        │           │           │          │       │       │       │
   ┌────▼────┐ ┌───▼────┐ ┌───▼────┐ ┌───▼───┐ ┌─▼──┐ ┌─▼──┐ ┌──▼──┐
   │Respons. │ │ Anim.  │ │ Forms  │ │Desktop│ │Mob.│ │Tab.│ │Brow-│
   │ Design  │ │ Motion │ │ UX     │ │       │ │    │ │    │ │sers │
   └─────────┘ └────────┘ └────────┘ └───────┘ └────┘ └────┘ └─────┘
```

---

## 📊 Rule Dependencies & Flow

### Level 1: Foundation (Start Here)
```
┌──────────────────────────────────────────────────────────┐
│ 1. Design Tokens System (10-design-tokens-system.md)    │
│    └─> Define: Colors, Spacing, Typography, Shadows     │
│                                                          │
│ 2. UI/UX Principles (01-ui-ux-principles.md)            │
│    └─> Learn: Hierarchy, Consistency, Accessibility     │
│                                                          │
│ 3. Component Library Standards (06-component-library)    │
│    └─> Build: Reusable components with variants         │
└──────────────────────────────────────────────────────────┘
```

### Level 2: Platform Strategy (Choose Your Path)
```
┌─────────────────────┐  ┌─────────────────────┐  ┌──────────────────┐
│ Responsive Design   │  │ PWA Standards       │  │ Landing Page Opt │
│ (02-responsive...)  │  │ (03-pwa-standards)  │  │ (05-landing...)  │
│                     │  │                     │  │                  │
│ For: Web Apps       │  │ For: Offline Apps   │  │ For: Marketing   │
│ Devices: All        │  │ Devices: Mobile+    │  │ Devices: All     │
└─────────────────────┘  └─────────────────────┘  └──────────────────┘
```

### Level 3: Implementation Details (Apply as Needed)
```
┌──────────────────────┐  ┌──────────────────────┐  ┌─────────────────┐
│ Animation & Motion   │  │ Form Design Patterns │  │ Cross-Platform  │
│ (07-animation...)    │  │ (08-form-design...)  │  │ (09-cross...)   │
│                      │  │                      │  │                 │
│ When: Interactive UI │  │ When: Data Input     │  │ When: Multi-OS  │
└──────────────────────┘  └──────────────────────┘  └─────────────────┘
```

### Level 4: Workflow Integration
```
┌──────────────────────────────────────────────────────────┐
│ Design to Code Workflow (04-design-to-code-workflow.md) │
│    └─> Handoff: Figma → Code → Review → Deploy          │
└──────────────────────────────────────────────────────────┘
```

---

## 🔄 Rule Interaction Matrix

| Rule | Depends On | Enhances | Used By |
|------|------------|----------|---------|
| **Tokens** | None | All rules | All rules |
| **Principles** | Tokens | Components, Forms | All rules |
| **Responsive** | Principles | PWA, Landing | Web Apps, PWA |
| **PWA** | Responsive, Tokens | Web Apps | Mobile Apps |
| **Workflow** | Principles, Components | All rules | Teams |
| **Landing** | Responsive, Forms | Web Apps | Marketing |
| **Components** | Tokens, Principles | All rules | All projects |
| **Animation** | Principles | Components, Forms | Interactive UIs |
| **Forms** | Principles, Responsive | Landing, Web Apps | Data Input |
| **Cross-Platform** | Responsive | All rules | Multi-device |

---

## 🎯 Decision Tree: Which Rules to Apply?

```
START: New Project
    │
    ├─> Is it a web application?
    │   ├─> YES: Apply @design:principles + @design:responsive + @design:components
    │   │         + @design:forms + @design:animation
    │   │
    │   └─> Does it need offline functionality?
    │       ├─> YES: Add @design:pwa
    │       └─> NO: Continue
    │
    ├─> Is it a landing page?
    │   └─> YES: Apply @design:landing + @design:responsive + @design:forms
    │             + @design:animation (minimal)
    │
    ├─> Is it a prototype?
    │   └─> YES: Apply @design:principles + @design:workflow + @design:components
    │             Focus on visual accuracy, skip performance optimization
    │
    └─> Is it a component library?
        └─> YES: Apply @design:tokens + @design:components + @design:animation
                  + @design:cross-platform

ALWAYS APPLY:
    ✅ @design:tokens (Design system foundation)
    ✅ @design:principles (Core UI/UX)
    ✅ Accessibility guidelines (WCAG AA)
```

---

## 📱 Platform-Specific Rule Combinations

### Desktop Web Application
```
Required:
├─ @design:principles (Core UX)
├─ @design:tokens (Design system)
├─ @design:components (UI library)
├─ @design:responsive (lg, xl, 2xl breakpoints)
└─ @design:forms (Data input)

Optional:
├─ @design:animation (Enhanced interactions)
└─ @design:cross-platform (Multi-OS support)
```

### Mobile PWA
```
Required:
├─ @design:principles (Core UX)
├─ @design:tokens (Design system)
├─ @design:pwa (Offline, installability)
├─ @design:responsive (sm, md breakpoints)
├─ @design:components (Touch-optimized)
└─ @design:cross-platform (iOS + Android)

Optional:
├─ @design:animation (Micro-interactions)
└─ @design:forms (If data input needed)
```

### Marketing Landing Page
```
Required:
├─ @design:landing (AIDA structure)
├─ @design:responsive (All breakpoints)
├─ @design:forms (Lead capture)
└─ @design:animation (Engagement)

Optional:
├─ @design:components (If reusable elements)
└─ @design:tokens (If part of larger system)
```

### Design Prototype
```
Required:
├─ @design:principles (Visual consistency)
├─ @design:workflow (Handoff specs)
└─ @design:components (Reusable elements)

Optional:
├─ @design:responsive (Show breakpoint variations)
├─ @design:animation (Demonstrate interactions)
└─ @design:tokens (If building design system)
```

---

## 🔗 Integration with Other Rule Categories

```
┌─────────────────────────────────────────────────────────────┐
│                    RULES ECOSYSTEM                          │
└─────────────────────────────────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
   ┌────▼────┐         ┌────▼────┐        ┌────▼────┐
   │ @core   │         │ @design │        │ @arch   │
   │         │◄────────┤         ├───────►│         │
   └────┬────┘         └────┬────┘        └────┬────┘
        │                   │                   │
        │              ┌────▼────┐              │
        │              │ @quality│              │
        └─────────────►│         │◄─────────────┘
                       └────┬────┘
                            │
                       ┌────▼────┐
                       │ @test   │
                       │         │
                       └─────────┘

Connections:
├─ @design + @core     = Clean component code
├─ @design + @arch     = Component architecture
├─ @design + @quality  = CSS naming conventions
├─ @design + @test     = Visual regression testing
└─ @design + @ops      = Performance monitoring
```

---

## 📚 Learning Path

### Beginner (Week 1)
```
Day 1-2: Read GLOSSARY.md (Understand terminology)
Day 3-4: Study @design:principles (Core concepts)
Day 5-6: Review @design:tokens (Design system basics)
Day 7:   Practice with QUICK_REF.md
```

### Intermediate (Week 2-3)
```
Week 2: 
├─ @design:responsive (Mobile-first development)
├─ @design:components (Build component library)
└─ @design:forms (User input patterns)

Week 3:
├─ @design:animation (Micro-interactions)
├─ @design:cross-platform (Multi-device testing)
└─ Apply to real project
```

### Advanced (Week 4+)
```
Week 4:
├─ @design:pwa (Progressive enhancement)
├─ @design:landing (Conversion optimization)
└─ @design:workflow (Team collaboration)

Ongoing:
├─ Build complete design system
├─ Contribute to component library
└─ Mentor team members
```

---

## 🎓 Certification Checklist

### Design Rules Mastery
- [ ] Read all 10 core rule files
- [ ] Understand all terms in GLOSSARY.md
- [ ] Can explain design tokens system
- [ ] Built at least one component library
- [ ] Implemented responsive design (mobile-first)
- [ ] Created accessible forms (WCAG AA)
- [ ] Optimized animations for performance
- [ ] Tested on multiple browsers/devices
- [ ] Achieved Lighthouse score 90+
- [ ] Completed a full design-to-code workflow

---

**This visual architecture helps you understand how all design rules connect and when to apply each one.**

**Version**: 1.0.0  
**Last Updated**: 2025-12-05

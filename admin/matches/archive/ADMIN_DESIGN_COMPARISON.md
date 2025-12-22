# Design Comparison: Admin Dashboard vs Match Console

**Reference**: `admin/index.php` (The Admin Standard)
**Target**: `admin/matches/console.php` (The Match Console)

## 🚨 The Core Conflict: Identity Crisis

The `admin/index.php` establishes a clear **Admin Brand Identity** that is distinct from the Public site.

| Feature | Admin Dashboard (Standard) | Match Console (Current State) | Status |
| :--- | :--- | :--- | :--- |
| **Primary Theme** | 🔵 **Bright Blue** (`#2563eb`) | 🟢 **Cricbuzz Green** (`#009270`) | ❌ **Brand Clash** |
| **Font Stack** | System Fonts (Native Feel) | `Inter` (Custom Web Font) | ⚠️ **Inconsistent** |
| **Sidebar** | Dark Gradient (`#1f2937`→`#111827`) | Dark Gradient (Shared) | ✅ **Good** |
| **Header** | White + Sticky | White + Sticky | ✅ **Good** |
| **Layout** | **Fluid** (Max 1200px on Desktop) | **Constrained** (Max 800px) | ⚠️ **Density Mismatch** |

## 🔍 Why `admin/index.php` feels "Right" for Admin

1.  **Context Switching**: The **Blue Theme** indicates "I am in Management Mode". The **Green Theme** indicates "I am in Viewing Mode". Making the Console green blurs this line, confusing the user about their role.
2.  **Native Performance**: Using system fonts makes the admin panel feel snappier and utilitarian, fitting for a dashboard.
3.  **Density**: The Admin Dashboard spreads out on desktop (`max-width: 1200px`), maximizing screen real estate for data. The Console is artificially narrow (`max-width: 800px`).

## 🛠️ The Correction Plan

To make `console.php` feel like a native part of the `admin/` directory:

1.  **Restore Admin Blue**: Change Primary Color from Green (`#009270`) back to Blue (`#2563eb`) to match `admin-pwa.css`.
2.  **Expand Layout**: Increase `max-width` to `1200px` on desktop to match the Dashboard's grid.
3.  **Semantics**: Use "Success Green" for positive states (like "Saved"), but keep the primary interactions (Buttons, Tabs) in "Admin Blue".

## 📋 Action Items for `match-console.css`

- [ ] Change `--primary` to `#2563eb` (Admin Blue).
- [ ] Change `--bg-dark-gradient` to match Sidebar (if used).
- [ ] Remove `@import` for Inter font to use System Stack (optional, but recommended for perfect match).
- [ ] Update Grid Layout to use full width on desktop.

# Data Visualization

**Category**: Design
**Priority**: P2
**Shorthand**: `@design:dataviz`

---

## 🎯 Purpose

Ensure charts, graphs, and data presentations are accessible, accurate, and easy to understand.

---

## 📋 Rules

### Rule 1: Use the Right Chart Type

- **Comparison**: Bar chart (categorical), Line chart (time-series).
- **Composition**: Pie/Donut (only for < 5 slices), Stacked Bar.
- **Distribution**: Histogram, Scatter plot.
- **Bad** ❌: Using a Pie chart for 15 categories.

### Rule 2: Color and Accessibility

**Bad** ❌:
- Relying on color alone (Red vs Green) for meaning.
- Low contrast grid lines.

**Good** ✅:
- **Patterns**: Use textures or patterns + color.
- **Labels**: Direct labels on lines/bars instead of legends where possible.
- **Contrast**: High contrast against background.
- **Palette**: Use distinct categorical colors (not shades of blue for different categories).

### Rule 3: Responsiveness

**Bad** ❌:
- Chart shrinks and overlapping labels become unreadable.
- Fixed width canvas causing horizontal scroll.

**Good** ✅:
- **Simplify**: Hide non-essential labels on small screens.
- **Reflow**: Switch from horizontal bar to vertical list on mobile.
- **Tooltips**: Essential for touch interaction on small points.

### Rule 4: Zero Baselines

- **Bar Charts**: MUST start at 0. Truncating y-axis exaggerates differences (misleading).
- **Line Charts**: Can start non-zero if clearly labeled, to show trends.

---

## ✅ Dataviz Checklist

- [ ] Is the chart type appropriate for the data?
- [ ] Are axes clearly labeled?
- [ ] Is it readable in grayscale?
- [ ] Do tooltips provide precise values?
- [ ] Does it scale to mobile?

---

**Status**: ✅ Active
**Version**: 1.0.0
**Date**: 2025-12-08

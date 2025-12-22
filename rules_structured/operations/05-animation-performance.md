# Animation Performance

**Category**: Operations
**Priority**: P2
**Shorthand**: `@ops:animation`

---

## 🎯 Purpose

Ensure smooth 60 FPS animations and prevent performance issues.

---

## 📋 Rules

### Rule 1: Use requestAnimationFrame for Animations

**Bad** ❌:
```javascript
setInterval(() => {
    update();
    draw();
}, 16);  // Trying to hit 60 FPS
```

**Good** ✅:
```javascript
function gameLoop() {
    update();
    draw();
    requestAnimationFrame(gameLoop);
}
requestAnimationFrame(gameLoop);
```

**Why**:
- Syncs with browser refresh rate (60 FPS)
- Pauses when tab inactive (saves battery)
- Better performance
- No drift over time

---

### Rule 2: Throttle Expensive Operations

**Bad** ❌:
```javascript
window.addEventListener('scroll', () => {
    expensiveCalculation();  // Called 100+ times/sec!
});
```

**Good** ✅:
```javascript
let ticking = false;
window.addEventListener('scroll', () => {
    if (!ticking) {
        requestAnimationFrame(() => {
            expensiveCalculation();
            ticking = false;
        });
        ticking = true;
    }
});
```

---

### Rule 3: Batch DOM Updates

**Bad** ❌:
```javascript
for (let i = 0; i < 100; i++) {
    element.style.top = i + 'px';  // 100 reflows!
}
```

**Good** ✅:
```javascript
element.style.transform = `translateY(100px)`;  // 1 reflow
// Or use CSS classes
element.classList.add('moved');
```

---

### Rule 4: Use CSS for Animations When Possible

**Bad** ❌:
```javascript
// JavaScript animation
function animate() {
    element.style.left = position + 'px';
    position++;
    requestAnimationFrame(animate);
}
```

**Good** ✅:
```css
/* CSS animation (GPU accelerated) */
.animated {
    animation: slide 1s ease-in-out;
}

@keyframes slide {
    from { transform: translateX(0); }
    to { transform: translateX(100px); }
}
```

---

### Rule 5: Measure Performance

**Always measure**:
```javascript
const start = performance.now();
expensiveOperation();
const end = performance.now();
console.log(`Took ${end - start}ms`);

// Target: < 16ms per frame (60 FPS)
```

---

## 📊 Performance Targets

| Metric | Target | Critical |
|--------|--------|----------|
| Frame time | < 16ms | < 33ms |
| FPS | 60 | > 30 |
| Animation start | < 100ms | < 200ms |
| Jank (dropped frames) | 0% | < 5% |

---

## ✅ Checklist

- [ ] Use requestAnimationFrame for game loops
- [ ] Throttle scroll/resize handlers
- [ ] Batch DOM updates
- [ ] Use CSS animations when possible
- [ ] Measure and optimize

---

## 🔧 Automation

**Checked by**: `check-performance.js`
**Detects**: setInterval in animation code

---

**Status**: ✅ Active
**Version**: 1.0.0
**Date**: 2025-12-08

# Algorithm Complexity Guidelines

**Category**: Code Quality
**Priority**: P2
**Shorthand**: `@quality:algorithms`

---

## 🎯 Purpose

Ensure efficient algorithms to prevent performance degradation as data grows.

---

## 📋 Rules

### Rule 1: Avoid O(n²) When O(n) Possible

**Bad** ❌:
```javascript
// O(n²) - nested loops
for (let i = 0; i < items.length; i++) {
    for (let j = 0; j < items.length; j++) {
        if (items[i] === items[j]) { ... }
    }
}
```

**Good** ✅:
```javascript
// O(n) - use Set
const seen = new Set();
for (const item of items) {
    if (seen.has(item)) { ... }
    seen.add(item);
}
```

---

### Rule 2: Use Appropriate Data Structures

**Bad** ❌:
```javascript
// O(n) lookup in array
const found = array.find(item => item.id === targetId);
```

**Good** ✅:
```javascript
// O(1) lookup in Map
const map = new Map(array.map(item => [item.id, item]));
const found = map.get(targetId);
```

---

### Rule 3: Avoid Retry Loops

**Bad** ❌:
```javascript
// Retry loop - can be slow
let position;
do {
    position = getRandomPosition();
} while (isOccupied(position));
```

**Good** ✅:
```javascript
// Track available positions
const available = allPositions.filter(p => !isOccupied(p));
const position = available[Math.floor(Math.random() * available.length)];
```

---

### Rule 4: Cache Expensive Computations

**Bad** ❌:
```javascript
// Recalculate every time
function render() {
    const data = expensiveCalculation();  // Called 60 times/sec!
    draw(data);
}
```

**Good** ✅:
```javascript
// Cache and invalidate
let cachedData = null;
function updateData() {
    cachedData = expensiveCalculation();
}
function render() {
    draw(cachedData);  // Use cached value
}
```

---

### Rule 5: Pre-allocate When Possible

**Bad** ❌:
```javascript
// Create new array every frame
function update() {
    const particles = [];
    for (let i = 0; i < 100; i++) {
        particles.push(new Particle());
    }
}
```

**Good** ✅:
```javascript
// Reuse array (object pooling)
const particlePool = new Array(100).fill(null).map(() => new Particle());
function update() {
    particlePool.forEach(p => p.update());
}
```

---

## 📊 Complexity Reference

| Notation | Name | Example |
|----------|------|---------|
| O(1) | Constant | Map.get(), array[i] |
| O(log n) | Logarithmic | Binary search |
| O(n) | Linear | array.find(), for loop |
| O(n log n) | Linearithmic | array.sort() |
| O(n²) | Quadratic | Nested loops |
| O(2ⁿ) | Exponential | Recursive fibonacci |

**Target**: Keep most operations O(n) or better

---

## ✅ Checklist

- [ ] No nested loops over same data
- [ ] Use Map/Set for lookups
- [ ] Cache expensive calculations
- [ ] Pre-allocate when size known
- [ ] Avoid retry loops with large datasets

---

## 🔧 Automation

**Checked by**: `check-code-quality.js` (partial)
**Manual review**: For complex algorithms

---

**Status**: ✅ Active
**Version**: 1.0.0
**Date**: 2025-12-08

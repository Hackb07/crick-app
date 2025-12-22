# Score Page Fixes (Part 2)

**Date**: 2025-12-05 03:05 IST  
**Issue**: Console errors (404 API calls, SyntaxError, TypeError)  
**Status**: ✅ **FIXED**

---

## 🐛 Problems

1.  **API 404 & SyntaxError**: `matchesApiUrl` and `eventsApiUrl` were undefined in `score.php`, causing API calls to fail and return 404 HTML pages (leading to "Unexpected token <").
2.  **TypeError**: `document.getElementById('undo-btn')` was returning null because the UNDO button lacked an ID.

---

## ✅ Solutions

### **1. Initialized API Variables**

Added the following JavaScript variables to the inline script in `score.php`:

```javascript
// API Configuration
const matchesApiUrl = '<?= apiV1Url('matches.php') ?>';
const eventsApiUrl = '<?= apiV1Url('events.php') ?>';
const eventsApiEndpoint = '<?= apiV1Url('events.php') ?>';
```

This ensures all API calls point to the correct endpoints (`api/v1/matches.php` and `api/v1/events.php`).

### **2. Added ID to Undo Button**

Updated the UNDO button in `score.php` to include the required ID:

```html
<button class="c-btn btn-undo" id="undo-btn" onclick="undoLastBall()">UNDO</button>
```

This resolves the `TypeError: Cannot set properties of null` when enabling/disabling the button.

---

## 🧪 Verification

1.  **API Calls**: Should now succeed (200 OK) and return JSON.
2.  **Undo Button**: Should be correctly enabled/disabled without console errors.
3.  **Console**: Should be clean of these specific errors.

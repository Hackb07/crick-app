# Vue.js Pages Debugging Guide

## Quick Diagnostic Steps

### 1. Check Browser Console
Open browser console (F12) and look for errors:
- ❌ **Red errors** = Critical issues
- ⚠️ **Yellow warnings** = Non-critical
- ✅ **No errors** = Everything OK

### 2. Check Network Tab
In browser DevTools Network tab, verify:
- ✅ `vue.global.js` loads (status 200)
- ✅ `vue-app.js` loads (status 200)
- ✅ `vue-modern.css` loads (status 200)

### 3. Common Issues & Fixes

#### Issue: "Vue is not defined"
**Symptoms**: Console error, page shows error message
**Fix**: 
- Check internet connection (CDN requires internet)
- Verify CDN URL: `https://unpkg.com/vue@3/dist/vue.global.js`
- Try loading page again

#### Issue: "VueApp is not defined"
**Symptoms**: Console error when accessing VueApp.apiClient
**Fix**:
- Check that `vue-app.js` loaded successfully
- Verify script load order: Vue.js → vue-app.js → page script
- Refresh page

#### Issue: "Cannot read property 'length' of undefined"
**Symptoms**: Console error, sections don't display
**Fix**: Already fixed! Arrays are now initialized with `Array.isArray()` checks

#### Issue: Blank page / No content
**Symptoms**: Page loads but shows nothing
**Possible Causes**:
- Vue.js didn't mount properly
- Check if `#app` div exists
- Check if Vue app initialized (console should show Vue app)

### 4. Manual Test Script

Add this to browser console to test:

```javascript
// Test Vue.js
console.log('Vue.js:', typeof Vue !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');

// Test VueApp
console.log('VueApp:', typeof VueApp !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');

// Test API Client
if (typeof VueApp !== 'undefined' && VueApp.apiClient) {
    console.log('API Client:', '✅ Available');
} else {
    console.log('API Client:', '❌ Not available');
}

// Test Vue App Mount
const appDiv = document.getElementById('app');
if (appDiv && appDiv.__vue_app__) {
    console.log('Vue App:', '✅ Mounted');
} else {
    console.log('Vue App:', '❌ Not mounted');
}
```

### 5. Pages to Test

1. **Public Home**: `http://localhost/cricapp/public/`
2. **Admin Dashboard**: `http://localhost/cricapp/admin/`
3. **Live Match**: `http://localhost/cricapp/public/live-match.php?id=1`

### 6. Expected Behavior

#### Public Home (`public/index-vue.php`)
- ✅ Shows header with "🏏 Cricket Scoring"
- ✅ Shows "Live Matches" section
- ✅ Shows "Recent Matches" section
- ✅ Shows "Scheduled Matches" section
- ✅ Shows bottom navigation (mobile)

#### Admin Dashboard (`admin/index-vue.php`)
- ✅ Shows "Admin Dashboard" header
- ✅ Shows stats cards (Live Matches, Recent Matches, Quick Actions)
- ✅ Shows Recent Matches table
- ✅ Shows bottom navigation (mobile)

#### Live Match (`public/live-match-vue.php`)
- ✅ Shows match header with teams
- ✅ Shows scorecard (if available)
- ✅ Shows commentary feed
- ✅ Auto-refreshes if match is live

### 7. Error Recovery

If page shows error message:
1. **Check Console**: Read the exact error message
2. **Check Network**: Verify all scripts loaded
3. **Check Internet**: Vue.js CDN requires internet
4. **Try Refresh**: Hard refresh (Ctrl+F5)

### 8. Fallback Options

If Vue.js pages don't work:
- Old pages still available as fallback
- Access directly: `/cricapp/public/index.php` (original version)
- Or fix Vue.js issues and try again

## Status

**All Known Issues Fixed** ✅

The pages should now work correctly. If errors persist:
1. Share the exact console error message
2. Check network tab for failed loads
3. Verify internet connection


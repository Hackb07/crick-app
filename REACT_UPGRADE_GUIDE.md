# React Frontend Setup Guide

## Overview

This project now supports **React 18** alongside Vue.js, making it compatible with shared hosting using CDN (no build process required).

## Architecture

### Shared Hosting Compatibility

- ✅ **React 18 via CDN** - No build process needed
- ✅ **Babel Standalone** - JSX transformation in browser
- ✅ **Pure PHP Backend** - API endpoints remain PHP
- ✅ **No Node.js Required** - Everything works with standard LAMP stack

### File Structure

```
cricapp/
├── assets/
│   ├── js/
│   │   └── react-app.js       # React setup & API client
│   └── css/
│       └── react-modern.css    # React-specific styles
├── public/
│   └── index-react.php        # React home page
├── admin/
│   └── index-react.php        # React admin dashboard
└── REACT_UPGRADE_GUIDE.md     # This guide
```

## React Pages

### Public Portal

1. **Home Page** (`public/index-react.php`)
   - Live matches section
   - Recent matches grid
   - Scheduled matches
   - Real-time updates for live matches

### Admin Panel

1. **Dashboard** (`admin/index-react.php`)
   - Stats cards
   - Recent matches table
   - Quick actions
   - Auto-refresh functionality

## Features

### Modern Design System

- ✅ **Design Tokens** - CSS variables for colors, spacing
- ✅ **Responsive Grid** - Mobile-first design
- ✅ **Modern Cards** - Elevated cards with shadows
- ✅ **Smooth Animations** - Transitions and interactions
- ✅ **Loading States** - Spinners and loaders
- ✅ **Error Handling** - User-friendly error messages

### React Components

**Available Components:**

1. `MatchCard` - Match card component
2. `LoadingSpinner` - Loading state component
3. `EmptyState` - Empty state component
4. `StatCard` - Statistics card component

### API Integration

The React app uses `ReactApp.apiClient` for all API calls:

```javascript
// Get matches
const data = await ReactApp.apiClient.getMatches({ state: 'live' });

// Get match details
const match = await ReactApp.apiClient.getMatch(matchId);

// Get events
const events = await ReactApp.apiClient.getMatchEvents(matchId);
```

## Usage

### Accessing React Pages

**Public Portal:**
- Home: `/cricapp/public/index-react.php`

**Admin Panel:**
- Dashboard: `/cricapp/admin/index-react.php`

### React vs Vue.js

You now have **both options**:

1. **Vue.js Pages** (existing):
   - `/cricapp/public/index-vue.php`
   - `/cricapp/public/live-match-vue.php`
   - `/cricapp/admin/index-vue.php`

2. **React Pages** (new):
   - `/cricapp/public/index-react.php`
   - `/cricapp/admin/index-react.php`

Choose the framework that best fits your needs!

## React Setup

### CDN Scripts

The React pages use these CDN links:

```html
<!-- React 18 -->
<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>

<!-- Babel Standalone for JSX -->
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
```

### JSX Syntax

All React components use JSX syntax with `type="text/babel"`:

```javascript
<script type="text/babel">
    const App = () => {
        return <div>Hello React!</div>;
    };
</script>
```

## Creating New React Pages

1. **Create PHP file:**
```php
<?php
require_once __DIR__ . '/../includes/bootstrap.php';
// ... load data ...
?>
```

2. **Add React template:**
```html
<div id="root"></div>

<script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<script src="/cricapp/assets/js/react-app.js"></script>

<script type="text/babel">
    const { useState, useEffect } = React;
    
    const App = () => {
        const [data, setData] = useState(<?= json_encode($phpData) ?>);
        
        return <div>Your React App</div>;
    };
    
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
</script>
```

## React Hooks

### useState

```javascript
const [matches, setMatches] = useState([]);
```

### useEffect

```javascript
useEffect(() => {
    // Fetch data
    fetchData();
    
    // Cleanup
    return () => {
        // Cleanup code
    };
}, [dependencies]);
```

### useCallback

```javascript
const refreshData = useCallback(async () => {
    // API call
}, [dependencies]);
```

## Browser Support

- ✅ Chrome/Edge (latest 2 versions)
- ✅ Firefox (latest 2 versions)
- ✅ Safari (latest 2 versions)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

### Optimizations

- ✅ **CDN Loading** - React loaded from unpkg CDN
- ✅ **Production Build** - Using production React build
- ✅ **Babel Standalone** - JSX transformation in browser
- ✅ **Lazy Loading** - Data loaded on-demand
- ✅ **Debouncing** - Input handlers debounced

### Shared Hosting Benefits

- No build process required
- No npm/node dependencies
- Works on any shared hosting
- Fast initial load
- Easy deployment

## React vs Vue.js Comparison

### React Advantages

- ✅ More popular, larger ecosystem
- ✅ Better TypeScript support
- ✅ More third-party libraries
- ✅ Industry standard

### Vue.js Advantages

- ✅ Easier learning curve
- ✅ Smaller bundle size
- ✅ Better documentation
- ✅ More opinionated (easier for beginners)

### Recommendation

- **Use React** if you prefer JSX and want more control
- **Use Vue.js** if you prefer templates and want simplicity
- **Use Both** - Choose per page based on requirements

## Troubleshooting

### React Not Loading

- Check CDN connection: `https://unpkg.com/react@18/umd/react.production.min.js`
- Verify internet connection
- Check browser console for errors

### JSX Not Working

- Ensure Babel Standalone is loaded
- Verify script type is `text/babel`
- Check browser console for transformation errors

### API Calls Failing

- Check API endpoint URLs
- Verify authentication token
- Check network tab in browser dev tools

## Next Steps

1. **Test React Pages**: Visit the new React pages
2. **Create More Components**: Build reusable React components
3. **Add More Pages**: Migrate more pages to React
4. **Enhance Features**: Add more React-specific features

## Support

For issues or questions:
- Check browser console for errors
- Verify API endpoints are working
- Review React documentation: https://react.dev


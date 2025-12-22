// Quick fix for offline queue issue
// Add this to browser console or run on page load

// Clear the offline queue
localStorage.removeItem('score_offline_queue');

// Reload page
location.reload();

console.log('✅ Offline queue cleared and page reloaded');

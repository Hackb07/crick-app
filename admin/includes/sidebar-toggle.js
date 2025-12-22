/**
 * Standard Sidebar Toggle Script
 * Include this in all admin pages for consistent mobile sidebar behavior
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const wrapper = document.querySelector('.admin-wrapper');
    if (sidebar && wrapper) {
        sidebar.classList.toggle('open');
        wrapper.classList.toggle('sidebar-open');
    }
}

// Close sidebar when clicking overlay on mobile
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const wrapper = document.querySelector('.admin-wrapper');
    
    document.addEventListener('click', function(event) {
        // Optimization: Check condition first before querying DOM or doing heavy work
        if (window.innerWidth > 768) return;
        
        if (!sidebar || !sidebar.classList.contains('open')) return;
        
        // Only query toggle button when necessary (in case it's dynamic)
        const toggle = document.querySelector('.mobile-sidebar-toggle');
        
        if (!sidebar.contains(event.target) && (!toggle || !toggle.contains(event.target))) {
            sidebar.classList.remove('open');
            if (wrapper) {
                wrapper.classList.remove('sidebar-open');
            }
        }
    });
});


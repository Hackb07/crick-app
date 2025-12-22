/**
 * Mobile App Navigation JavaScript
 * Handles sidebar menu, bottom navigation, and app interactions
 */

(function() {
    'use strict';

    // Initialize app navigation
    function initMobileApp() {
        initSidebar();
        initBottomNav();
        setActiveMenuItem();
        preventBodyScroll();
    }

    // Initialize sidebar/drawer menu
    function initSidebar() {
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('app-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebarClose = document.querySelector('.sidebar-close');

        if (!menuToggle || !sidebar || !sidebarOverlay) return;

        // Open sidebar
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            openSidebar();
        });

        // Close sidebar
        if (sidebarClose) {
            sidebarClose.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebar();
            });
        }

        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });

        // Close sidebar on menu link click (mobile only)
        if (window.innerWidth <= 767) {
            const menuLinks = sidebar.querySelectorAll('.sidebar-menu-link');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeSidebar, 300);
                });
            });
        }

        // Close sidebar on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 767 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                closeSidebar();
            }
        });
    }

    function openSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Initialize bottom navigation
    function initBottomNav() {
        const bottomNavItems = document.querySelectorAll('.app-bottom-nav-item');
        
        bottomNavItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Remove active class from all items
                bottomNavItems.forEach(nav => nav.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
            });

            // Add touch feedback
            item.addEventListener('touchstart', function() {
                this.style.opacity = '0.7';
            });

            item.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.opacity = '';
                }, 150);
            });
        });
    }

    // Set active menu item based on current page
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.sidebar-menu-link, .app-bottom-nav-item');
        
        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace(/\/$/, ''))) {
                link.classList.add('active');
                
                // Update bottom nav icon/label if it exists
                if (link.closest('.app-bottom-nav-item')) {
                    link.closest('.app-bottom-nav-item').classList.add('active');
                }
            }
        });
    }

    // Prevent body scroll when sidebar is open (mobile)
    function preventBodyScroll() {
        let isSidebarOpen = false;
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'app-sidebar') {
                    const isOpen = mutation.target.classList.contains('open');
                    if (isOpen !== isSidebarOpen) {
                        isSidebarOpen = isOpen;
                        if (isSidebarOpen && window.innerWidth <= 767) {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = '';
                        }
                    }
                }
            });
        });

        const sidebar = document.getElementById('app-sidebar');
        if (sidebar) {
            observer.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class']
            });
        }
    }

    // Handle window resize
    function handleResize() {
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        // On desktop, ensure sidebar is visible
        if (window.innerWidth >= 768) {
            if (sidebar) sidebar.classList.add('open');
        } else {
            // On mobile, close sidebar if open
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileApp);
    } else {
        initMobileApp();
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 250);
    });

    // Export functions for external use
    window.mobileApp = {
        openSidebar: openSidebar,
        closeSidebar: closeSidebar
    };
})();


/**
 * Bottom Navigation Component
 * Public 5-tab navigation with active state management
 */

class BottomNav {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;
        
        this.init();
    }
    
    init() {
        // Set active tab based on current URL
        this.setActiveTab();
        
        // Add click handlers for haptic feedback (if available)
        this.container.querySelectorAll('.bottom-nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                this.handleNavClick(e);
            });
        });
    }
    
    setActiveTab() {
        const currentPath = window.location.pathname;
        const navItems = this.container.querySelectorAll('.bottom-nav-item');
        
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    handleNavClick(e) {
        // Haptic feedback (if available on mobile)
        if ('vibrate' in navigator) {
            navigator.vibrate(10);
        }
        
        // Visual feedback
        const item = e.currentTarget;
        item.classList.add('active');
        
        // Remove active from siblings
        Array.from(item.parentElement.children).forEach(sibling => {
            if (sibling !== item) {
                sibling.classList.remove('active');
            }
        });
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('bottom-nav')) {
            new BottomNav('bottom-nav');
        }
    });
} else {
    if (document.getElementById('bottom-nav')) {
        new BottomNav('bottom-nav');
    }
}


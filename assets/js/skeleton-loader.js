/**
 * Skeleton Loader Component
 * Shows skeleton loaders for async content (>200ms latency)
 */

class SkeletonLoader {
    static show(targetElement, type = 'card') {
        const skeleton = document.createElement('div');
        skeleton.className = `skeleton skeleton-${type}`;
        skeleton.setAttribute('data-skeleton', 'true');
        
        if (type === 'card') {
            skeleton.className = 'skeleton skeleton-card';
        } else if (type === 'list') {
            skeleton.innerHTML = `
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text" style="width: 80%"></div>
            `;
        } else if (type === 'table') {
            skeleton.innerHTML = `
                <div class="skeleton skeleton-text" style="height: 2rem; margin-bottom: 1rem;"></div>
                ${Array(5).fill(0).map(() => `
                    <div class="skeleton skeleton-text" style="margin-bottom: 0.5rem;"></div>
                `).join('')}
            `;
        }
        
        targetElement.appendChild(skeleton);
        return skeleton;
    }
    
    static hide(targetElement) {
        const skeleton = targetElement.querySelector('[data-skeleton="true"]');
        if (skeleton) {
            skeleton.remove();
        }
    }
    
    static async loadWithSkeleton(targetElement, loadFn, type = 'card') {
        const skeleton = SkeletonLoader.show(targetElement, type);
        
        try {
            const result = await loadFn();
            SkeletonLoader.hide(targetElement);
            return result;
        } catch (error) {
            SkeletonLoader.hide(targetElement);
            throw error;
        }
    }
}

// Utility function for fetch with automatic skeleton
async function fetchWithSkeleton(targetElement, url, options = {}, type = 'card') {
    return SkeletonLoader.loadWithSkeleton(
        targetElement,
        async () => {
            const response = await fetch(url, options);
            if (!response.ok) throw new Error('Fetch failed');
            return response.json();
        },
        type
    );
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.SkeletonLoader = SkeletonLoader;
    window.fetchWithSkeleton = fetchWithSkeleton;
}


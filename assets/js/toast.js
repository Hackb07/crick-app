/**
 * Modern Toast Notification System
 * Replaces alert(), confirm(), prompt() with beautiful in-app notifications
 */

class Toast {
    constructor() {
        this.toastContainer = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
            this.toastContainer = container;
        } else {
            this.toastContainer = document.getElementById('toast-container');
        }
    }

    /**
     * Show success toast
     */
    success(message, duration = 3000) {
        return this.show(message, 'success', duration);
    }

    /**
     * Show error toast
     */
    error(message, duration = 4000) {
        return this.show(message, 'error', duration);
    }

    /**
     * Show info toast
     */
    info(message, duration = 3000) {
        return this.show(message, 'info', duration);
    }

    /**
     * Show warning toast
     */
    warning(message, duration = 3500) {
        return this.show(message, 'warning', duration);
    }

    /**
     * Show toast with custom type
     */
    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = this.getIcon(type);
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-message">${this.escapeHtml(message)}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        this.toastContainer.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    }

    /**
     * Show confirm dialog (returns Promise)
     */
    confirm(message, options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Confirm',
                confirmText = 'Yes',
                cancelText = 'No',
                confirmColor = '#dc2626',
                cancelColor = '#6b7280'
            } = options;

            const overlay = document.createElement('div');
            overlay.className = 'toast-confirm-overlay';
            
            const dialog = document.createElement('div');
            dialog.className = 'toast-confirm-dialog';
            
            dialog.innerHTML = `
                <div class="toast-confirm-header">
                    <h3>${this.escapeHtml(title)}</h3>
                </div>
                <div class="toast-confirm-body">
                    <p>${this.escapeHtml(message)}</p>
                </div>
                <div class="toast-confirm-actions">
                    <button class="toast-confirm-btn toast-confirm-cancel" style="background: ${cancelColor}">${this.escapeHtml(cancelText)}</button>
                    <button class="toast-confirm-btn toast-confirm-ok" style="background: ${confirmColor}">${this.escapeHtml(confirmText)}</button>
                </div>
            `;

            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            // Animate in
            setTimeout(() => overlay.classList.add('show'), 10);

            const cleanup = () => {
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
            };

            dialog.querySelector('.toast-confirm-cancel').addEventListener('click', () => {
                cleanup();
                resolve(false);
            });

            dialog.querySelector('.toast-confirm-ok').addEventListener('click', () => {
                cleanup();
                resolve(true);
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    cleanup();
                    resolve(false);
                }
            });
        });
    }

    /**
     * Show prompt dialog (returns Promise)
     */
    prompt(message, defaultValue = '', options = {}) {
        return new Promise((resolve) => {
            const {
                title = 'Enter Value',
                placeholder = '',
                type = 'text',
                okText = 'OK',
                cancelText = 'Cancel',
                okColor = '#059669',
                cancelColor = '#6b7280'
            } = options;

            const overlay = document.createElement('div');
            overlay.className = 'toast-confirm-overlay';
            
            const dialog = document.createElement('div');
            dialog.className = 'toast-confirm-dialog';
            
            dialog.innerHTML = `
                <div class="toast-confirm-header">
                    <h3>${this.escapeHtml(title)}</h3>
                </div>
                <div class="toast-confirm-body">
                    <p style="margin-bottom: 1rem;">${this.escapeHtml(message)}</p>
                    <input type="${type}" id="toast-prompt-input" class="toast-prompt-input" value="${this.escapeHtml(defaultValue)}" placeholder="${this.escapeHtml(placeholder)}" autofocus>
                </div>
                <div class="toast-confirm-actions">
                    <button class="toast-confirm-btn toast-confirm-cancel" style="background: ${cancelColor}">${this.escapeHtml(cancelText)}</button>
                    <button class="toast-confirm-btn toast-confirm-ok" style="background: ${okColor}">${this.escapeHtml(okText)}</button>
                </div>
            `;

            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            const input = dialog.querySelector('#toast-prompt-input');
            
            // Animate in
            setTimeout(() => {
                overlay.classList.add('show');
                input.focus();
                input.select();
            }, 10);

            const cleanup = () => {
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
            };

            const handleOk = () => {
                cleanup();
                resolve(input.value);
            };

            const handleCancel = () => {
                cleanup();
                resolve(null);
            };

            dialog.querySelector('.toast-confirm-ok').addEventListener('click', handleOk);
            dialog.querySelector('.toast-confirm-cancel').addEventListener('click', handleCancel);
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    handleOk();
                } else if (e.key === 'Escape') {
                    handleCancel();
                }
            });

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    handleCancel();
                }
            });
        });
    }

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global toast instance
const toast = new Toast();




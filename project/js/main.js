/**
 * GreenGuard — Core Frontend JavaScript
 * Handles navigation, interactive animations, and UI notifications
 */

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initCounters();
});

/**
 * Mobile Navigation Toggle
 */
function initNavigation() {
    const toggleBtn = document.querySelector('.mobile-toggle');
    const navContainer = document.querySelector('.nav-container');

    if (toggleBtn && navContainer) {
        toggleBtn.addEventListener('click', () => {
            navContainer.classList.toggle('active');
            const isExpanded = navContainer.classList.contains('active');
            toggleBtn.setAttribute('aria-expanded', isExpanded);
            toggleBtn.innerHTML = isExpanded ? '✕' : '☰';
        });

        // Close on link click
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navContainer.classList.remove('active');
                toggleBtn.innerHTML = '☰';
            });
        });
    }
}

/**
 * Smooth Number Counter Animation
 */
function initCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target') || counter.innerText.replace(/\D/g, ''), 10);
        if (isNaN(target)) return;

        let count = 0;
        const speed = target > 50 ? 25 : 60;
        const increment = Math.max(1, Math.floor(target / 30));

        const updateCount = () => {
            count += increment;
            if (count < target) {
                counter.innerText = count;
                setTimeout(updateCount, speed);
            } else {
                counter.innerText = target + (counter.getAttribute('data-suffix') || '');
            }
        };

        // If in viewport or directly run
        updateCount();
    });
}

/**
 * Toast Notification Utility
 * @param {string} message 
 * @param {string} type 'success' | 'error' | 'info'
 * @param {number} duration milliseconds
 */
function showToast(message, type = 'info', duration = 4000) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: '✅',
        error: '⚠️',
        info: 'ℹ️'
    };

    toast.innerHTML = `
        <span>${icons[type] || 'ℹ️'}</span>
        <span style="font-size: 0.9rem; font-weight: 500;">${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Helper to escape HTML and prevent XSS
 */
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

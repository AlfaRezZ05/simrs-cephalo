/**
 * Component Kit JavaScript
 * Handles interactive components: modals, dropdowns, sidebar toggle, mobile navbar.
 */

function toggleDropdown(menuId) {
    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');

    closeAllDropdowns(menuId);

    if (isHidden) {
        menu.classList.remove('hidden');
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0)';
        });
    } else {
        closeDropdown(menuId);
    }
}

function closeDropdown(menuId) {
    const menu = document.getElementById(menuId);
    if (!menu) return;

    menu.style.opacity = '0';
    menu.style.transform = 'translateY(-4px)';
    setTimeout(() => menu.classList.add('hidden'), 150);
}

function closeAllDropdowns(exceptId) {
    document.querySelectorAll('[id$="-menu"], [id$="Menu"]').forEach(el => {
        if (el.id !== exceptId && !el.classList.contains('hidden')) {
            closeDropdown(el.id);
        }
    });
}

document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('[id$="-menu"], [id$="Menu"]');
    dropdowns.forEach(menu => {
        const parent = menu.closest('.relative, [id$="Dropdown"]');
        if (parent && !parent.contains(e.target)) {
            closeDropdown(menu.id);
        }
    });
});

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const panel = document.getElementById(modalId + '-panel');
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => {
        if (panel) {
            panel.style.opacity = '1';
            panel.style.transform = 'scale(1)';
        }
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const panel = document.getElementById(modalId + '-panel');
    if (!modal) return;

    if (panel) {
        panel.style.opacity = '0';
        panel.style.transform = 'scale(0.95)';
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.fixed.inset-0.z-\\[100\\]:not(.hidden)').forEach(modal => {
            closeModal(modal.id);
        });
    }
});

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const iconOpen = document.getElementById('menuIconOpen');
    const iconClose = document.getElementById('menuIconClose');

    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    menu.classList.toggle('hidden');

    if (iconOpen && iconClose) {
        iconOpen.classList.toggle('hidden', isHidden);
        iconClose.classList.toggle('hidden', !isHidden);
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    const isVisible = !sidebar.classList.contains('-translate-x-full');

    if (isVisible) {
        sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
    }
}

function copyToClipboard(text, feedbackEl) {
    navigator.clipboard.writeText(text).then(() => {
        if (feedbackEl) {
            const original = feedbackEl.textContent;
            feedbackEl.textContent = 'Copied!';
            setTimeout(() => { feedbackEl.textContent = original; }, 2000);
        }
    });
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

/**
 * Sneat Theme Main JavaScript for EcoTrack Admin
 */

(function () {
    'use strict';

    // Menu Toggle
    const menuToggle = document.querySelectorAll('.layout-menu-toggle');
    const layoutMenu = document.querySelector('.layout-menu');
    const layoutOverlay = document.querySelector('.layout-overlay');

    menuToggle.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (layoutMenu) {
                layoutMenu.classList.toggle('active');
            }
            if (layoutOverlay) {
                layoutOverlay.classList.toggle('active');
            }
        });
    });

    // Close menu on overlay click
    if (layoutOverlay) {
        layoutOverlay.addEventListener('click', function () {
            if (layoutMenu) {
                layoutMenu.classList.remove('active');
            }
            layoutOverlay.classList.remove('active');
        });
    }

    // Navbar scroll effect
    const navbar = document.querySelector('.layout-navbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 10) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0 && typeof bootstrap !== 'undefined') {
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0 && typeof bootstrap !== 'undefined') {
        popoverTriggerList.forEach(function (popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
    }

    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn, [data-confirm]');
    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const message = btn.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.click();
            }
        }, 5000);
    });

    // Form validation styles
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Responsive table wrapper
    const tables = document.querySelectorAll('table:not(.table-responsive table)');
    tables.forEach(function (table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('table-responsive');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // Active menu item highlight based on current URL
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.split('/').pop())) {
            const menuItem = link.closest('.menu-item');
            if (menuItem) {
                menuItem.classList.add('active');
            }
        }
    });

})();

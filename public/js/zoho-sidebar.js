/**
 * JAPS POS — Zoho-style sidebar collapse (mini + flyout)
 */
(function (window, document) {
    'use strict';

    var STORAGE_KEY = 'upos_sidebar_collapse';

    function getSidebar() {
        return document.querySelector('aside.side-bar');
    }

    function isCollapsed() {
        var sidebar = getSidebar();
        if (sidebar) {
            return sidebar.classList.contains('zoho-sidebar-collapsed');
        }

        return localStorage.getItem(STORAGE_KEY) === 'true';
    }

    function isMobileView() {
        return window.innerWidth < 1024;
    }

    function openMobileSidebar() {
        var sidebar = getSidebar();
        var overlay = document.querySelector('.overlay');

        if (sidebar) {
            sidebar.classList.add('small-view-side-active');
            sidebar.style.display = 'flex';
        }

        if (overlay) {
            overlay.style.display = 'block';
        }
    }

    function closeMobileSidebar() {
        var sidebar = getSidebar();
        var overlay = document.querySelector('.overlay');

        if (sidebar) {
            sidebar.classList.remove('small-view-side-active');
            sidebar.style.removeProperty('display');
        }

        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    function isMobileSidebarOpen() {
        var sidebar = getSidebar();
        return !!(sidebar && sidebar.classList.contains('small-view-side-active'));
    }

    function applySidebarCollapsed(collapsed) {
        var sidebar = getSidebar();
        var body = document.body;

        if (sidebar) {
            sidebar.classList.toggle('zoho-sidebar-collapsed', collapsed);
            sidebar.style.removeProperty('display');
        }

        if (body) {
            body.classList.toggle('sidebar-collapse', collapsed);
            body.classList.toggle('zoho-sidebar-collapsed', collapsed);
        }

        localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');

        document.dispatchEvent(
            new CustomEvent('zoho-sidebar-toggled', { detail: { collapsed: collapsed } })
        );
    }

    function toggleSidebarCollapsed() {
        if (isMobileView()) {
            if (isMobileSidebarOpen()) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
            return;
        }

        applySidebarCollapsed(!isCollapsed());
    }

    function bindCollapseButtons() {
        document.addEventListener(
            'click',
            function (event) {
                var button = event.target.closest('.side-bar-collapse');
                if (!button) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                toggleSidebarCollapsed();
            },
            true
        );
    }

    function initSidebarCollapse() {
        applySidebarCollapsed(localStorage.getItem(STORAGE_KEY) === 'true');
        bindCollapseButtons();
    }

    window.ZohoSidebar = {
        isCollapsed: isCollapsed,
        setCollapsed: applySidebarCollapsed,
        toggle: toggleSidebarCollapsed,
        closeMobileSidebar: closeMobileSidebar,
    };

    window.addEventListener('resize', function () {
        if (!isMobileView()) {
            closeMobileSidebar();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarCollapse);
    } else {
        initSidebarCollapse();
    }
})(window, document);

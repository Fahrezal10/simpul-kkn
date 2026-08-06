/* SIMPUL-KKN — skrip antarmuka */

(function () {
    'use strict';

    var body = document.body;
    var sidebar = document.getElementById('appSidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarClose = document.getElementById('sidebarClose');
    var sidebarBackdrop = document.getElementById('sidebarBackdrop');
    var sidebarCollapse = document.getElementById('sidebarCollapse');

    /* --- Mobile: off-canvas --- */
    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar);

    /* --- Desktop: collapse 64px icon-only (§10.5/§10.8), state dipersist --- */
    function setCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapsed', collapsed);
        try { localStorage.setItem('simpul.sidebarCollapsed', collapsed ? '1' : '0'); } catch (e) { /* private mode */ }
    }

    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function () {
            setCollapsed(!body.classList.contains('sidebar-collapsed'));
        });
    }

    // Pulihkan preferensi collapse saat load (desktop saja).
    if (window.matchMedia('(min-width: 992px)').matches) {
        var saved = null;
        try { saved = localStorage.getItem('simpul.sidebarCollapsed'); } catch (e) { /* ignore */ }
        if (saved === '1') setCollapsed(true);
    }

    /* --- Tooltip Bootstrap (mis. tombol notifikasi) --- */
    if (window.bootstrap && document.querySelectorAll('[data-bs-toggle="tooltip"]').length) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
})();

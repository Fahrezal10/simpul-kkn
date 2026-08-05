/* SIMPUL-KKN — skrip antarmuka */

(function () {
    'use strict';

    var sidebar = document.getElementById('appSidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarClose = document.getElementById('sidebarClose');
    var sidebarBackdrop = document.getElementById('sidebarBackdrop');

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

    // Aktifkan tooltip Bootstrap (mis. tombol notifikasi).
    if (window.bootstrap && document.querySelectorAll('[data-bs-toggle="tooltip"]').length) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
})();

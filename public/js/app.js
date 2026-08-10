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

    /* ======================================================================
       Dropdown data dengan pencarian (searchable select) via Tom Select.
       Dipakai untuk <select> bertanda data-searchable — daftar opsi panjang
       (kecamatan, DPL, filter) sehingga perlu kotak cari, bukan scroll manual.

       API global:
         window.SimpulSelect.init()               — inisialisasi semua select[data-searchable]
         window.SimpulSelect.reinit(scopeEl)      — inisialisasi ulang dalam scope
                                                    (elemen dinamis / filter yang
                                                    opsi-nya di-populate via AJAX)
         window.SimpulSelect.setValue(el, v)      — set nilai + refresh UI

       Bila Tom Select gagal dimuat (CDN blokir/offline) → fallback ke <select>
       native tanpa mengubah perilaku form.
       ====================================================================== */
    window.SimpulSelect = (function () {
        function tsAvailable() {
            return typeof window.TomSelect === 'function';
        }

        // Opsi default yang disesuaikan dengan tema SIMPUL-KKN.
        function baseOptions() {
            return {
                placeholder: 'Cari…',
                allowEmptyOption: true,
                create: false,
                maxOptions: 50,
                // Tampilkan opsi apa adanya; baris placeholder (value='')
                // dibiarkan sebagai item biasa agar bisa di-reset.
                render: {
                    option: function (data, escape) {
                        return '<div class="option">' + escape(data.text) + '</div>';
                    },
                    item: function (data, escape) {
                        return '<div class="item">' + escape(data.text) + '</div>';
                    }
                },
                dropdownDirection: 'auto'
            };
        }

        function makeSelect(el, opts) {
            if (!tsAvailable()) return null;
            try {
                var ts = new window.TomSelect(el, opts || baseOptions());
                // Warisi state error validasi ke wrapper Tom Select (Bootstrap).
                if (el.classList.contains('is-invalid') && ts.wrapper) {
                    ts.wrapper.classList.add('is-invalid');
                }
                return ts;
            } catch (e) {
                return null;
            }
        }

        function initWithin(scope) {
            if (!scope) return;
            var nodes = scope.querySelectorAll ? scope.querySelectorAll('select[data-searchable]') : [];
            nodes.forEach(function (el) {
                if (el.dataset.simpulTs === '1') return; // sudah di-upgrade
                // Select yang opsi-nya masih placeholder saja (filter AJAX belum
                // selesai di-populate) → tunda sampai reinit() dipanggil setelah
                // opsi terisi. Hindari instance Tom Select ganda.
                if (el.dataset.simpulLazy === '1') return;
                if (el.options.length <= 1) {
                    el.dataset.simpulLazy = '1'; // tunggu populate
                    return;
                }
                var ts = makeSelect(el);
                if (ts) el.dataset.simpulTs = '1';
            });
        }

        return {
            init: function () { initWithin(document); },
            reinit: function (scopeEl) {
                var scope = scopeEl || document;
                // Buka kembali select yang sempat di-skip karena opsi masih
                // placeholder (kini sudah di-populate) sebelum upgrade ulang.
                if (scope.querySelectorAll) {
                    scope.querySelectorAll('select[data-searchable]').forEach(function (el) {
                        if (el.dataset.simpulTs === '1') return;
                        delete el.dataset.simpulLazy;
                    });
                }
                initWithin(scope);
            },
            isApplied: function () { return tsAvailable(); },
            // Set nilai pada select yang sudah di-upgrade Tom Select.
            setValue: function (el, v) {
                if (!el) return;
                var val = v == null || v === '' ? '' : String(v);
                if (el.dataset.simpulTs === '1' && el.tomselect) {
                    el.tomselect.setValue(val);
                } else {
                    el.value = val;
                }
            }
        };
    })();

    // Inisialisasi dropdown searchable saat dokumen siap (setelah script layout).
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { window.SimpulSelect.init(); });
    } else {
        window.SimpulSelect.init();
    }

    /* --- SYS-01: Klik notifikasi pada dropdown popup ---
       Tandai dibaca via AJAX, perbarui badge, lalu navigasi ke halaman sumber. */
    document.addEventListener('click', function (e) {
        var item = e.target.closest('.notification-list-item[data-notif-id]');
        if (!item) return;

        e.preventDefault();
        var id = item.getAttribute('data-notif-id');
        var url = item.getAttribute('href');
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) return;

        fetch('/notifications/ajax/' + id + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        }).then(function (res) { return res.json(); }).then(function (data) {
            // Update badge bila masih tersisa belum dibaca.
            var badge = document.getElementById('notifCount');
            if (badge) {
                if (data.unreadCount > 0) {
                    badge.textContent = data.unreadCount;
                } else {
                    badge.remove();
                }
            }
            window.location.href = url;
        }).catch(function () {
            // Fallback: tetap navigasi bila AJAX gagal.
            window.location.href = url;
        });
    });
})();

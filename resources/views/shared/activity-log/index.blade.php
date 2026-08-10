@extends('layouts.app')

@section('title', 'Aktivitas Sistem')

@section('content')
    <x-page-header title="Aktivitas Sistem"
                   subtitle="Jejak aksi pengguna — tambah/ubah/hapus data, matching, verifikasi, persetujuan."
                   icon="bi-clock-history" />

    {{-- Filter & pencarian --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="position-relative" style="max-width: 280px; width: 100%;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="searchLog" class="form-control form-control-sm ps-5"
                       placeholder="Cari aksi / deskripsi / pengguna...">
            </div>
            <select id="filterAksi" class="form-select form-select-sm w-auto">
                <option value="">Semua Aksi</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnReset">Reset</button>
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelLog">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-arrow-repeat me-1"></i> Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-card>

    <div id="pagination" class="d-flex justify-content-end mt-3"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';
            var $tbody = null;
            var $info = $('#infoTotal');
            var $search = $('#searchLog');
            var $aksi = $('#filterAksi');
            var url = @json(route('activity-log.data'));
            var searchTimer = null;

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' aksi');
                var html = '<nav><ul class="pagination pagination-sm">';
                html += '<li class="page-item ' + (page.current_page <= 1 ? 'disabled' : '') + '">'
                    + '<a class="page-link" href="#" data-page="' + (page.current_page - 1) + '">&laquo;</a></li>';
                for (var i = 1; i <= page.last_page; i++) {
                    html += '<li class="page-item ' + (i === page.current_page ? 'active' : '') + '">'
                        + '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                }
                html += '<li class="page-item ' + (page.current_page >= page.last_page ? 'disabled' : '') + '">'
                    + '<a class="page-link" href="#" data-page="' + (page.current_page + 1) + '">&raquo;</a></li>';
                html += '</ul></nav>';
                el.innerHTML = html;
            }

            function load(page) {
                if (!$tbody) $tbody = document.querySelector('#tabelLog tbody');
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, {
                    page: page || 1,
                    search: $search.val(),
                    aksi: $aksi.val()
                })
                .done(function (res) {
                    // Isi dropdown aksi sekali (dari respons pertama).
                    if ($aksi.find('option').length === 1) {
                        var opts = '<option value="">Semua Aksi</option>';
                        (res.filters.aksi || []).forEach(function (a) {
                            opts += '<option value="' + a + '">' + a + '</option>';
                        });
                        $aksi.html(opts);
                    }
                    if (!res.data.length) {
                        $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-clock-history"></i><h6 class="mt-3">Belum ada aktivitas</h6><p>Jejak aksi akan tampil setelah pengguna melakukan tindakan pada sistem.</p></div></td></tr>';
                        $info.text('Total 0 aksi');
                        renderPagination(null);
                        return;
                    }
                    var html = '';
                    res.data.forEach(function (r, i) {
                        var no = res.from + i;
                        html += '<tr>'
                            + '<td>' + no + '</td>'
                            + '<td class="text-nowrap small text-muted">' + r.waktu + '</td>'
                            + '<td class="fw-semibold">' + r.user + '</td>'
                            + '<td class="small text-muted">' + r.role + '</td>'
                            + '<td>' + r.aksi + '</td>'
                            + '<td class="text-muted small">' + r.deskripsi + '</td>'
                            + '<td class="small text-muted">' + r.ip + '</td>'
                            + '</tr>';
                    });
                    $tbody.innerHTML = html;
                    renderPagination(res);
                })
                .fail(function () {
                    $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
                    renderPagination(null);
                });
            }

            $aksi.on('change', function () { load(1); });
            $search.on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { load(1); }, 350);
            });
            $('#btnReset').on('click', function () {
                $search.val(''); $aksi.val(''); load(1);
            });
            document.getElementById('pagination').addEventListener('click', function (e) {
                var link = e.target.closest('a[data-page]');
                if (!link) return;
                e.preventDefault();
                load(parseInt(link.getAttribute('data-page'), 10) || 1);
            });

            load(1);
        })();
    </script>
@endpush
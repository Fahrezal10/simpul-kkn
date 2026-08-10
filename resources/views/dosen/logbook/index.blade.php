@extends('layouts.app')

@section('title', 'Approval Logbook')

@section('content')
    <x-page-header title="Approval Logbook"
                   subtitle="Tinjau dan setujui logbook harian mahasiswa bimbingan Anda."
                   icon="bi-clipboard-check" />

    {{-- Pencarian --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" id="searchLogbook" class="form-control form-control-sm" style="max-width: 280px;"
                   placeholder="Cari nama mahasiswa...">
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelLogbook">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kelompok</th>
                        <th>Mahasiswa</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Foto</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
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
            var $search = $('#searchLogbook');
            var url = @json(route('dosen.logbook.data'));
            var searchTimer = null;

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' logbook');
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
                if (!$tbody) $tbody = document.querySelector('#tabelLogbook tbody');
                $tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, search: $search.val() })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="bi bi-clipboard-check"></i><h6 class="mt-3">Tidak ada logbook menunggu approval</h6><p>Semua logbook bimbingan Anda sudah diproses.</p></div></td></tr>';
                            $info.text('Total 0 logbook');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r) {
                            html += '<tr>'
                                + '<td class="fw-semibold">' + r.tanggal + '</td>'
                                + '<td>' + r.kelompok + '</td>'
                                + '<td>' + r.mahasiswa + '</td>'
                                + '<td>' + r.deskripsi + '</td>'
                                + '<td class="text-center">' + r.foto + '</td>'
                                + '<td class="text-end">' + r.aksi + '</td>'
                                + '</tr>';
                        });
                        $tbody.innerHTML = html;
                        renderPagination(res);
                    })
                    .fail(function () {
                        $tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
                        renderPagination(null);
                    });
            }

            $search.on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { load(1); }, 350);
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

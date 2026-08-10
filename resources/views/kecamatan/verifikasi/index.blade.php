@extends('layouts.app')

@section('title', 'Verifikasi Kesiapan Desa')

@section('content')
    <x-page-header title="Verifikasi Kesiapan Desa"
                   subtitle="Verifikasi kesiapan desa kandidat KKN di wilayah kecamatan Anda sebelum pelaksanaan disetujui."
                   icon="bi-clipboard-check" />

    {{-- Pencarian --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" id="searchVerifikasi" class="form-control form-control-sm" style="max-width: 280px;"
                   placeholder="Cari kode kelompok / tema / desa...">
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelVerifikasi">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Kelompok</th>
                        <th>PT</th>
                        <th>Tema</th>
                        <th>Desa Terpilih</th>
                        <th class="text-center">Skor</th>
                        <th class="text-end">Aksi</th>
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
            var $search = $('#searchVerifikasi');
            var url = @json(route('kecamatan.verifikasi.data'));
            var searchTimer = null;

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' kelompok');
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
                if (!$tbody) $tbody = document.querySelector('#tabelVerifikasi tbody');
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, search: $search.val() })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-clipboard-check"></i><h6 class="mt-3">Tidak ada kelompok untuk diverifikasi</h6><p>Belum ada desa di kecamatan ini yang menunggu verifikasi kesiapan.</p></div></td></tr>';
                            $info.text('Total 0 kelompok');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r, i) {
                            var no = res.from + i;
                            html += '<tr>'
                                + '<td>' + no + '</td>'
                                + '<td>' + r.kode + '</td>'
                                + '<td>' + r.pt + '</td>'
                                + '<td>' + r.tema + '</td>'
                                + '<td>' + r.desa + '</td>'
                                + '<td class="text-center"><span class="badge text-bg-info">' + r.skor + '</span></td>'
                                + '<td class="text-end">' + r.aksi + '</td>'
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

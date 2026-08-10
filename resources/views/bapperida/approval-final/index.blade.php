@extends('layouts.app')

@section('title', 'Persetujuan Akhir KKN')

@section('content')
    <x-page-header title="Persetujuan Akhir Pelaksanaan KKN"
                   subtitle="Review hasil verifikasi kecamatan lalu setujui pelaksanaan — atau tolak dan minta desa alternatif."
                   icon="bi-check2-circle" />

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelApproval">
                <thead>
                    <tr>
                        <th>Kode Kelompok</th>
                        <th>PT</th>
                        <th>Tema</th>
                        <th>Desa Terpilih</th>
                        <th>Hasil Verifikasi</th>
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
            var url = @json(route('bapperida.approval-final.data'));

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
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
                if (!$tbody) $tbody = document.querySelector('#tabelApproval tbody');
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1 })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-check2-circle"></i><h6 class="mt-3">Tidak ada kelompok menunggu persetujuan</h6><p>Semua kelompok yang diverifikasi kecamatan belum ada, atau sudah diproses.</p></div></td></tr>';
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r) {
                            html += '<tr>'
                                + '<td>' + r.kode + '</td>'
                                + '<td>' + r.pt + '</td>'
                                + '<td>' + r.tema + '</td>'
                                + '<td>' + r.desa + '</td>'
                                + '<td>' + r.verif + '</td>'
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

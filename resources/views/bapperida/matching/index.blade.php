@extends('layouts.app')

@section('title', 'Matching KKN')

@section('content')
    <x-page-header title="Matching Engine"
                   subtitle="Jalankan rekomendasi desa untuk setiap kelompok KKN dari permohonan terverifikasi."
                   icon="bi-magic" />

    {{-- Filter status --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <select id="filterStatus" class="form-select form-select-sm w-auto"
                    data-searchable data-placeholder="Cari status…">
                <option value="">Semua Status</option>
                <option value="menunggu_matching">Menunggu Matching</option>
                <option value="menunggu_verifikasi_kecamatan">Menunggu Verifikasi Kecamatan</option>
                <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                <option value="aktif">Aktif</option>
            </select>
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelMatching">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>PT</th>
                        <th>DPL</th>
                        <th>Tema</th>
                        <th class="text-center">Mhs</th>
                        <th class="text-center">Hasil</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
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
            var url = @json(route('bapperida.matching.data'));

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
                if (!$tbody) $tbody = document.querySelector('#tabelMatching tbody');
                var status = $('#filterStatus').val();
                $tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, status: status })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><i class="bi bi-magic"></i><h6 class="mt-3">Belum ada kelompok untuk di-match</h6><p>Pastikan ada permohonan yang sudah terverifikasi.</p></div></td></tr>';
                            $info.text('Total 0 kelompok');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r, i) {
                            var no = res.from + i;
                            html += '<tr>'
                                + '<td class="fw-semibold">' + no + '. ' + r.kode + '</td>'
                                + '<td>' + r.pt + '</td>'
                                + '<td>' + r.dpl + '</td>'
                                + '<td>' + r.tema + '</td>'
                                + '<td class="text-center"><span class="badge text-bg-secondary">' + r.mahasiswa + '</span></td>'
                                + '<td class="text-center">' + r.hasil + '</td>'
                                + '<td>' + r.status + '</td>'
                                + '<td class="text-end">' + r.aksi + '</td>'
                                + '</tr>';
                        });
                        $tbody.innerHTML = html;
                        renderPagination(res);
                    })
                    .fail(function () {
                        $tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
                        renderPagination(null);
                    });
            }

            $('#filterStatus').on('change', function () { load(1); });
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
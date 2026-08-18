@extends('layouts.app')

@section('title', 'Verifikasi Permohonan')

@section('content')
    <x-page-header title="Verifikasi Permohonan KKN"
                   subtitle="Periksa kelengkapan permohonan masuk dari seluruh perguruan tinggi."
                   icon="bi-clipboard-check" />

    {{-- Filter status --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <select id="filterStatus" class="form-select form-select-sm w-auto"
                    data-searchable data-placeholder="Cari status…">
                <option value="">Semua Status</option>
                <option value="diajukan">Diajukan</option>
                <option value="terverifikasi">Terverifikasi</option>
                <option value="ditolak">Ditolak</option>
            </select>
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelPermohonan">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Perguruan Tinggi</th>
                        <th>Periode</th>
                        <th>Tanggal</th>
                        <th>Kelompok</th>
                        <th>Status</th>
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
            var url = @json(route('bapperida.permohonan.data'));

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' permohonan');
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
                if (!$tbody) $tbody = document.querySelector('#tabelPermohonan tbody');
                var status = $('#filterStatus').val();
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, status: status })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><h6 class="mt-3">Belum ada permohonan</h6><p>Tidak ada data untuk ditampilkan.</p></div></td></tr>';
                            $info.text('Total 0 permohonan');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r, i) {
                            var no = res.from + i;
                            html += '<tr>'
                                + '<td>' + no + '</td>'
                                + '<td class="fw-semibold">' + r.pt + '</td>'
                                + '<td>' + r.periode + '</td>'
                                + '<td>' + r.tanggal + '</td>'
                                + '<td>' + r.kelompok + '</td>'
                                + '<td>' + r.status + '</td>'
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
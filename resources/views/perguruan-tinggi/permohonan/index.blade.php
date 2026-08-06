@extends('layouts.app')

@section('title', 'Permohonan KKN')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <x-page-header title="Permohonan KKN"
                           subtitle="Pantau status pengajuan permohonan Anda secara real-time."
                           icon="bi-journal-text" />
        </div>
        <a href="{{ route('perguruan-tinggi.permohonan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Ajukan Permohonan Baru
        </a>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelPermohonan">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Periode</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Kelompok</th>
                        <th>Mahasiswa</th>
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

    {{-- Pagination Bootstrap di-render via JS --}}
    <div id="pagination" class="d-flex justify-content-end mt-3"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';
            var $tbody = null;
            var url = @json(route('perguruan-tinggi.permohonan.data'));

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
                if (!$tbody) $tbody = document.querySelector('#tabelPermohonan tbody');
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1 })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><h6 class="mt-3">Belum ada permohonan</h6><p>Coba klik tombol Ajukan Permohonan Baru.</p></div></td></tr>';
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r, i) {
                            var no = res.from + i;
                            html += '<tr>'
                                + '<td>' + no + '</td>'
                                + '<td class="fw-semibold">' + r.periode + '</td>'
                                + '<td>' + r.tanggal + '</td>'
                                + '<td><span class="badge text-bg-secondary">' + r.kelompok + '</span></td>'
                                + '<td><span class="badge text-bg-secondary">' + r.mahasiswa + '</span></td>'
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

            // Delegasi klik pagination
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
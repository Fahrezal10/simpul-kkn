@extends('layouts.app')

@section('title', 'Master Data Desa')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-1">
        <x-page-header title="Master Data Desa"
                       subtitle="Kelola data desa se-Kabupaten Indramayu — profil, potensi, permasalahan, kebutuhan."
                       icon="bi-geo-alt" />
        <a href="{{ route('bapperida.desa.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Desa
        </a>
    </div>

    {{-- Filter & pencarian --}}
    <div class="mb-3">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" id="searchDesa" class="form-control form-control-sm" style="max-width: 260px;"
                   placeholder="Cari nama desa / kode / kecamatan...">
            <select id="filterKecamatan" class="form-select form-select-sm w-auto">
                <option value="">Semua Kecamatan</option>
            </select>
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelDesa">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Wilayah</th>
                        <th>Desa</th>
                        <th class="text-center">Penduduk</th>
                        <th class="text-center">Luas</th>
                        <th>Data Profil</th>
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
            var $search = $('#searchDesa');
            var $kec = $('#filterKecamatan');
            var url = @json(route('bapperida.desa.data'));
            var searchTimer = null;

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' desa');
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
                if (!$tbody) $tbody = document.querySelector('#tabelDesa tbody');
                $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, {
                    page: page || 1,
                    search: $search.val(),
                    kecamatan_id: $kec.val()
                })
                .done(function (res) {
                    // Isi dropdown kecamatan sekali (dari respons pertama).
                    if ($kec.find('option').length === 1) {
                        var opts = '<option value="">Semua Kecamatan</option>';
                        (res.filters.kecamatan || []).forEach(function (k) {
                            opts += '<option value="' + k.id + '">' + k.nama_kecamatan + '</option>';
                        });
                        $kec.html(opts);
                    }
                    if (!res.data.length) {
                        $tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-geo-alt"></i><h6 class="mt-3">Tidak ada data desa</h6><p>Coba ubah kata kunci pencarian atau tambah desa baru.</p></div></td></tr>';
                        $info.text('Total 0 desa');
                        renderPagination(null);
                        return;
                    }
                    var html = '';
                    res.data.forEach(function (r, i) {
                        var no = res.from + i;
                        html += '<tr>'
                            + '<td>' + no + '</td>'
                            + '<td class="text-muted small">' + r.kode_wilayah + '</td>'
                            + '<td class="fw-semibold">' + r.nama_desa + '</td>'
                            + '<td class="text-center">' + r.penduduk + '</td>'
                            + '<td class="text-center">' + r.luas + '</td>'
                            + '<td>' + r.data + '</td>'
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

            $kec.on('change', function () { load(1); });
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

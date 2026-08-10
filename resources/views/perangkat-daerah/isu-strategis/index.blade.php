@extends('layouts.app')

@section('title', 'Isu Strategis')

@section('content')
    <x-page-header title="Input Isu Strategis"
                   subtitle="Isu strategis pembangunan sesuai bidang tugas OPD — menjadi parameter Prioritas Daerah pada Matching System."
                   icon="bi-bullseye" />

    {{-- Form tambah isu --}}
    <div class="row mb-3">
        <div class="col-lg-9 col-xl-7">
            <x-card title="Tambah Isu Strategis">
                <form method="POST" action="{{ route('perangkat-daerah.isu-strategis.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="kategori_isu">Kategori Isu <span class="text-danger">*</span></label>
                        <input type="text" name="kategori_isu" id="kategori_isu"
                               class="form-control @error('kategori_isu') is-invalid @enderror"
                               value="{{ old('kategori_isu') }}" required maxlength="100"
                               placeholder="cth: stunting, UMKM, lingkungan">
                        @error('kategori_isu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="wilayah_terdampak">Wilayah Terdampak</label>
                        <input type="text" name="wilayah_terdampak" id="wilayah_terdampak"
                               class="form-control @error('wilayah_terdampak') is-invalid @enderror"
                               value="{{ old('wilayah_terdampak') }}" maxlength="255"
                               placeholder="cth: Haurgeulis, Gabuswetan">
                        @error('wilayah_terdampak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" id="deskripsi" rows="3"
                                  class="form-control @error('deskripsi') is-invalid @enderror"
                                  required maxlength="2000">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="rekomendasi_tema">Rekomendasi Tema KKN</label>
                        <input type="text" name="rekomendasi_tema" id="rekomendasi_tema"
                               class="form-control @error('rekomendasi_tema') is-invalid @enderror"
                               value="{{ old('rekomendasi_tema') }}" maxlength="255"
                               placeholder="Opsional — tema KKN yang disarankan terkait isu ini">
                        @error('rekomendasi_tema')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Simpan Isu</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Daftar isu --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Daftar Isu — {{ $opd->nama_opd }}</h6>
        <div class="d-flex gap-2 align-items-center">
            <input type="text" id="searchIsu" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Cari isu...">
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelIsu">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Wilayah Terdampak</th>
                        <th>Rekomendasi Tema</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
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
            var $search = $('#searchIsu');
            var url = @json(route('perangkat-daerah.isu-strategis.data'));
            var searchTimer = null;

            function renderPagination(page) {
                var el = document.getElementById('pagination');
                if (!page) { el.innerHTML = ''; return; }
                $info.text('Total ' + page.total + ' isu');
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
                if (!$tbody) $tbody = document.querySelector('#tabelIsu tbody');
                $tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, search: $search.val() })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="bi bi-bullseye"></i><h6 class="mt-3">Belum ada isu strategis</h6><p>Tambahkan isu melalui form di atas.</p></div></td></tr>';
                            $info.text('Total 0 isu');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r) {
                            html += '<tr>'
                                + '<td>' + r.kategori + '</td>'
                                + '<td>' + r.deskripsi + '</td>'
                                + '<td>' + r.wilayah + '</td>'
                                + '<td>' + r.rekomendasi + '</td>'
                                + '<td class="text-end">' + r.aksi + '</td>'
                                + '</tr>';
                        });
                        $tbody.innerHTML = html;
                        renderPagination(res);
                    })
                    .fail(function () {
                        $tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
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

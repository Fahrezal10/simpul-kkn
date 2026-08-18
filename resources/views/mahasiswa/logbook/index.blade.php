@extends('layouts.app')

@section('title', 'Logbook Harian')

@section('content')
    <x-page-header title="Logbook Harian"
                   subtitle="Catat kegiatan harian KKN Anda. Satu logbook per tanggal."
                   icon="bi-journal-text" />

    {{-- Form tambah logbook --}}
    <div class="row mb-3">
        <div class="col-lg-8 col-xl-7">
            <x-card title="Tambah Logbook Hari Ini">
                <form method="POST" action="{{ route('mahasiswa.logbook.store') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="tanggal">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}">
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="foto">Foto Dokumentasi</label>
                        <input type="file" name="foto" id="foto" accept="image/*"
                               class="form-control @error('foto') is-invalid @enderror">
                        <div class="form-text">JPG/PNG maks 2MB.</div>
                        @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="deskripsi_kegiatan">Deskripsi Kegiatan <span class="text-danger">*</span></label>
                        <textarea name="deskripsi_kegiatan" id="deskripsi_kegiatan" rows="4"
                                  class="form-control @error('deskripsi_kegiatan') is-invalid @enderror"
                                  required maxlength="2000" placeholder="Apa yang Anda kerjakan hari ini?">{{ old('deskripsi_kegiatan') }}</textarea>
                        @error('deskripsi_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Logbook</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Daftar logbook --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Riwayat Logbook</h6>
        <div class="d-flex gap-2 align-items-center">
            <select id="filterStatus" class="form-select form-select-sm w-auto"
                    data-searchable data-placeholder="Cari status…">
                <option value="">Semua Status</option>
                <option value="menunggu">Menunggu</option>
                <option value="disetujui">Disetujui</option>
                <option value="revisi">Perlu Revisi</option>
            </select>
            <span class="text-muted small" id="infoTotal"></span>
        </div>
    </div>

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelLogbook">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Foto</th>
                        <th>Status</th>
                        <th>Catatan DPL</th>
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
            var url = @json(route('mahasiswa.logbook.data'));

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
                $tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                $.getJSON(url, { page: page || 1, status: $('#filterStatus').val() })
                    .done(function (res) {
                        if (!res.data.length) {
                            $tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="bi bi-journal-text"></i><h6 class="mt-3">Belum ada logbook</h6><p>Catat kegiatan harian Anda melalui form di atas.</p></div></td></tr>';
                            $info.text('Total 0 logbook');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        res.data.forEach(function (r) {
                            html += '<tr>'
                                + '<td class="fw-semibold">' + r.tanggal + '</td>'
                                + '<td>' + r.deskripsi + '</td>'
                                + '<td class="text-center">' + r.foto + '</td>'
                                + '<td>' + r.status + '</td>'
                                + '<td class="text-muted small">' + r.catatan + '</td>'
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

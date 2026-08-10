@php
    // Halaman indeks master data: tampil dua mode.
    // - Tanpa $jenis  → grid pemilih jenis master data.
    // - Dengan $jenis → tabel CRUD untuk jenis tersebut.
@endphp

@extends('layouts.app')

@section('title', $jenis ? 'Master Data ' . $jenisDef['label'] : 'Master Data')

@section('content')
    @if ($jenis === null)
        {{-- ===== Mode pemilih jenis ===== --}}
        <x-page-header title="Master Data"
                       subtitle="Kelola data referensi sistem yang dipakai permohonan, matching, dan verifikasi."
                       icon="bi-database-gear" />

        <div class="row g-3">
            @foreach ($semuaJenis as $def)
                <div class="col-sm-6 col-lg-4">
                    <a href="{{ route('master-data.list', $def['slug']) }}" class="text-decoration-none">
                        <x-card>
                            <div class="d-flex align-items-center gap-3">
                                <span class="master-tile-icon"><i class="bi {{ $def['icon'] }}"></i></span>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $def['label'] }}</div>
                                    <small class="text-muted">{{ $def['subtitle'] }}</small>
                                </div>
                            </div>
                        </x-card>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        {{-- ===== Mode CRUD per jenis ===== --}}
        @php
            $cols = collect($jenisDef['columns']);
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('master-data.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Semua Jenis
                </a>
                <x-page-header :title="'Master Data ' . $jenisDef['label']"
                               :subtitle="$jenisDef['subtitle']"
                               :icon="$jenisDef['icon']" />
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah {{ $jenisDef['label'] }}
            </button>
        </div>

        {{-- Pencarian --}}
        <div class="mb-3">
            <div class="d-flex gap-2 align-items-center">
                <div class="position-relative" style="max-width: 280px; width: 100%;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="searchMaster" class="form-control form-control-sm ps-5"
                           placeholder="Cari {{ $jenisDef['label'] }}...">
                </div>
                <span class="text-muted small" id="infoTotal"></span>
            </div>
        </div>

        <x-card :bodyClass="'p-0'">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelMaster">
                    <thead>
                        <tr>
                            <th>#</th>
                            @foreach ($cols as $col)
                                <th>{{ $col['label'] }}</th>
                            @endforeach
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="{{ $cols->count() + 2 }}" class="text-center text-muted py-4">
                                <i class="bi bi-arrow-repeat me-1"></i> Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-card>

        <div id="pagination" class="d-flex justify-content-end mt-3"></div>

        {{-- Modal form tambah/edit --}}
        <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form method="POST" id="formMaster" action="{{ route('master-data.store', $jenis) }}">
                        @csrf
                        <input type="hidden" name="_method" value="POST" id="formMethod">
                        <div class="modal-header">
                            <h5 class="modal-title" id="formModalTitle">Tambah {{ $jenisDef['label'] }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                @foreach ($cols as $col)
                                    @php
                                        $wrapClass = ($col['type'] ?? 'text') === 'textarea' ? 'col-12' : 'col-md-6';
                                    @endphp
                                    <div class="{{ $wrapClass }}">
                                        <label class="form-label" for="f_{{ $col['key'] }}">
                                            {{ $col['label'] }}
                                            @if (!empty($col['required']))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>

                                        @if (($col['type'] ?? 'text') === 'textarea')
                                            <textarea name="{{ $col['key'] }}" id="f_{{ $col['key'] }}" rows="3"
                                                      class="form-control"
                                                      {{ !empty($col['required']) ? 'required' : '' }}></textarea>
                                        @elseif (($col['type'] ?? 'text') === 'select')
                                            <select name="{{ $col['key'] }}" id="f_{{ $col['key'] }}"
                                                    class="form-select"
                                                    {{ !empty($col['required']) ? 'required' : '' }}>
                                                <option value="">— Pilih —</option>
                                                @foreach ($col['options'] ?? [] as $val => $optLabel)
                                                    <option value="{{ $val }}">{{ $optLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="{{ ($col['type'] ?? 'text') === 'number' ? 'number' : 'text' }}"
                                                   name="{{ $col['key'] }}" id="f_{{ $col['key'] }}"
                                                   class="form-control"
                                                   maxlength="{{ $col['max'] ?? '' }}"
                                                   {{ !empty($col['required']) ? 'required' : '' }}>
                                        @endif

                                        @if (!empty($col['hint']))
                                            <div class="form-text">{{ $col['hint'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi hapus --}}
        <div class="modal fade" id="hapusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center pt-4">
                        <div class="master-tile-icon mx-auto mb-3"><i class="bi bi-trash"></i></div>
                        <h5 class="mb-2">Hapus {{ $jenisDef['label'] }}?</h5>
                        <p class="text-muted mb-0" id="hapusNama"></p>
                        <p class="text-muted small mb-0 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <form method="POST" id="formHapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .master-tile-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            border-radius: 12px;
            font-size: 1.3rem;
            color: #0f766e;
            background: linear-gradient(135deg, rgba(13, 148, 136, .12), rgba(13, 148, 136, .04));
        }
    </style>
@endpush

@if ($jenis !== null)
    @push('scripts')
        <script>
            (function () {
                'use strict';
                var cols = @json($jenisDef['columns']);
                var urlData = @json(route('master-data.data', $jenis));
                var urlStore = @json(route('master-data.store', $jenis));
                var urlUpdate = @json(route('master-data.update', [$jenis, '__ID__']));
                var urlDestroy = @json(route('master-data.destroy', [$jenis, '__ID__']));

                // Data mentah per baris (untuk isi ulang form saat edit).
                var rowsMap = {};
                var $tbody = null;
                var $info = $('#infoTotal');
                var $search = $('#searchMaster');
                var searchTimer = null;

                function renderPagination(page) {
                    var el = document.getElementById('pagination');
                    if (!page) { el.innerHTML = ''; return; }
                    $info.text('Total ' + page.total + ' data');
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

                function esc(v) {
                    return $('<span>').text(v == null ? '' : String(v)).html();
                }

                function load(page) {
                    if (!$tbody) $tbody = document.querySelector('#tabelMaster tbody');
                    $tbody.innerHTML = '<tr><td colspan="' + (cols.length + 2) + '" class="text-center text-muted py-4">'
                        + '<i class="bi bi-arrow-repeat me-1"></i> Memuat data...</td></tr>';
                    $.getJSON(urlData, { page: page || 1, search: $search.val() })
                    .done(function (res) {
                        if (!res.data.length) {
                            rowsMap = {};
                            $tbody.innerHTML = '<tr><td colspan="' + (cols.length + 2) + '"><div class="empty-state">'
                                + '<i class="bi bi-database-gear"></i><h6 class="mt-3">Belum ada data</h6>'
                                + '<p>Klik "Tambah {{ $jenisDef['label'] }}" untuk menambahkan.</p></div></td></tr>';
                            $info.text('Total 0 data');
                            renderPagination(null);
                            return;
                        }
                        var html = '';
                        rowsMap = {};
                        res.data.forEach(function (r, i) {
                            rowsMap[r.id] = r;
                            var no = res.from + i;
                            html += '<tr>';
                            html += '<td>' + no + '</td>';
                            cols.forEach(function (col) {
                                var v = r[col.key] || '-';
                                html += '<td class="fw-semibold">' + esc(v) + '</td>';
                            });
                            html += '<td class="text-end text-nowrap">'
                                + '<a href="#" class="btn btn-sm btn-outline-primary btn-edit me-1" data-id="' + r.id + '" title="Edit">'
                                + '<i class="bi bi-pencil"></i></a>'
                                + '<a href="#" class="btn btn-sm btn-outline-danger btn-hapus" data-id="' + r.id + '" title="Hapus">'
                                + '<i class="bi bi-trash"></i></a>'
                                + '</td>';
                            html += '</tr>';
                        });
                        $tbody.innerHTML = html;
                        renderPagination(res);
                    })
                    .fail(function () {
                        $tbody.innerHTML = '<tr><td colspan="' + (cols.length + 2) + '" class="text-center text-danger py-4">'
                            + 'Gagal memuat data.</td></tr>';
                        renderPagination(null);
                    });
                }

                // ==== Modal tambah / edit ====
                var $form = $('#formMaster');
                var methodInput = document.getElementById('formMethod');
                var titleEl = document.getElementById('formModalTitle');
                var editing = false;

                // Saat modal dibuka tanpa konteks edit → siapkan mode "Tambah".
                $('#formModal').on('show.bs.modal', function () {
                    if (editing) return; // mode edit: field sudah diisi oleh handler edit
                    $form[0].reset();
                    methodInput.value = 'POST';
                    $form.attr('action', urlStore);
                    titleEl.textContent = 'Tambah {{ $jenisDef['label'] }}';
                    $('#f_' + cols[0].key).trigger('focus');
                });

                $(document).on('click', '.btn-edit', function (e) {
                    e.preventDefault();
                    var row = rowsMap[$(this).data('id')];
                    if (!row) return;

                    editing = true;
                    $form[0].reset();

                    cols.forEach(function (col) {
                        var $el = $('#f_' + col.key);
                        var val = row[col.key] != null ? String(row[col.key]) : '';
                        if ($el.is('select')) {
                            $el.val(val).trigger('change');
                        } else {
                            $el.val(val);
                        }
                    });

                    methodInput.value = 'PUT';
                    $form.attr('action', urlUpdate.replace('__ID__', row.id));
                    titleEl.textContent = 'Edit {{ $jenisDef['label'] }}';

                    $('#formModal').modal('show');
                    // Setelah tampil dan selesai, kembalikan edit ke mode tambah utk sesi berikutnya.
                    $('#formModal').one('shown.bs.modal', function () { editing = false; });
                });

                // ==== Hapus ====
                $(document).on('click', '.btn-hapus', function (e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var name = $(this).closest('tr').find('td').eq(1).text();
                    if (name === '-') name = 'Data ini';
                    $('#hapusNama').text('"' + name + '"');
                    $('#formHapus').attr('action', urlDestroy.replace('__ID__', id));
                    $('#hapusModal').modal('show');
                });

                // ==== Pagination & pencarian ====
                document.getElementById('pagination').addEventListener('click', function (e) {
                    var link = e.target.closest('a[data-page]');
                    if (!link) return;
                    e.preventDefault();
                    load(parseInt(link.getAttribute('data-page'), 10) || 1);
                });

                $search.on('input', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () { load(1); }, 350);
                });

                if (window.jQuery) {
                    $(function () { load(1); });
                } else {
                    load(1);
                }
            })();
        </script>
    @endpush
@endif
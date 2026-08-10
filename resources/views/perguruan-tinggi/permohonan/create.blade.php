@extends('layouts.app')

@section('title', 'Ajukan Permohonan KKN')

@section('content')
    <x-page-header title="Ajukan Permohonan KKN"
                   subtitle="Isi detail permohonan, tambahkan mahasiswa peserta, dan tautkan DPL."
                   icon="bi-journal-plus" />

    @if ($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('perguruan-tinggi.permohonan.store') }}" enctype="multipart/form-data" id="formPermohonan">
        @csrf

        {{-- Detail Permohonan --}}
        <x-card title="Detail Permohonan" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="periode" class="form-label">Periode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('periode') is-invalid @enderror"
                           id="periode" name="periode" value="{{ old('periode') }}"
                           placeholder="mis. Ganjil 2026/2027" required>
                    @error('periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                           id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required>
                    @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                           id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required>
                    @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="tema" class="form-label">Tema KKN <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('tema') is-invalid @enderror"
                           id="tema" name="tema" value="{{ old('tema') }}"
                           placeholder="mis. Digitalisasi Desa, Ketahanan Pangan" required>
                    @error('tema')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="bidang_keilmuan" class="form-label">Bidang Keilmuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('bidang_keilmuan') is-invalid @enderror"
                           id="bidang_keilmuan" name="bidang_keilmuan" value="{{ old('bidang_keilmuan') }}"
                           placeholder="mis. Teknologi Informasi, Pertanian, Kesehatan" required>
                    @error('bidang_keilmuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="file_surat_permohonan" class="form-label">Surat Permohonan (PDF) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('file_surat_permohonan') is-invalid @enderror"
                           id="file_surat_permohonan" name="file_surat_permohonan" accept=".pdf" required>
                    <div class="form-text">PDF, maksimal 5 MB.</div>
                    @error('file_surat_permohonan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="file_proposal" class="form-label">Proposal (PDF) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('file_proposal') is-invalid @enderror"
                           id="file_proposal" name="file_proposal" accept=".pdf" required>
                    <div class="form-text">PDF, maksimal 5 MB.</div>
                    @error('file_proposal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-card>

        {{-- Data Mahasiswa & DPL --}}
        <x-card title="Data Mahasiswa & DPL"
                subtitle="Tambahkan minimal 1 baris. Mahasiswa dikelompokkan otomatis per DPL.">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tabelMahasiswa">
                    <thead>
                        <tr>
                            <th style="width:110px">NIM <span class="text-danger">*</span></th>
                            <th style="min-width:160px">Nama Mahasiswa <span class="text-danger">*</span></th>
                            <th style="width:130px">Prodi</th>
                            <th style="width:140px">No. HP</th>
                            <th style="min-width:200px">DPL <span class="text-danger">*</span></th>
                            <th style="width:40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Baris awal (kosong) — diisi JS bila tidak ada old() --}}
                        @php
                            $oldMhs = old('mahasiswa', []);
                        @endphp
                        @if (count($oldMhs) > 0)
                            @foreach ($oldMhs as $idx => $m)
                                <tr>
                                    <td><input type="text" name="mahasiswa[{{ $idx }}][nim]" value="{{ $m['nim'] ?? '' }}" class="form-control form-control-sm" required></td>
                                    <td><input type="text" name="mahasiswa[{{ $idx }}][nama]" value="{{ $m['nama'] ?? '' }}" class="form-control form-control-sm" required></td>
                                    <td><input type="text" name="mahasiswa[{{ $idx }}][prodi]" value="{{ $m['prodi'] ?? '' }}" class="form-control form-control-sm"></td>
                                    <td><input type="text" name="mahasiswa[{{ $idx }}][no_hp]" value="{{ $m['no_hp'] ?? '' }}" class="form-control form-control-sm"></td>
                                    <td>
                                        <select name="mahasiswa[{{ $idx }}][dpl_id]" class="form-select form-select-sm dpl-select"
                                                data-searchable data-placeholder="Cari DPL…" required>
                                            <option value="">Pilih DPL</option>
                                            @foreach ($dosen as $d)
                                                <option value="{{ $d->id }}" @selected(($m['dpl_id'] ?? '') == $d->id)>{{ $d->nama }}</option>
                                            @endforeach
                                            <option value="-1" @selected(($m['dpl_id'] ?? '') == '-1')>+ DPL Baru</option>
                                        </select>
                                        <div class="dpl-baru-field mt-1 d-none">
                                            <input type="text" name="mahasiswa[{{ $idx }}][dpl_baru_nama]" class="form-control form-control-sm mt-1" placeholder="Nama DPL baru">
                                            <input type="text" name="mahasiswa[{{ $idx }}][dpl_baru_nip_niy]" class="form-control form-control-sm mt-1" placeholder="NIP/NIY">
                                            <input type="text" name="mahasiswa[{{ $idx }}][dpl_baru_no_hp]" class="form-control form-control-sm mt-1" placeholder="No. HP">
                                        </div>
                                    </td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td><input type="text" name="mahasiswa[0][nim]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="mahasiswa[0][nama]" class="form-control form-control-sm" required></td>
                                <td><input type="text" name="mahasiswa[0][prodi]" class="form-control form-control-sm"></td>
                                <td><input type="text" name="mahasiswa[0][no_hp]" class="form-control form-control-sm"></td>
                                <td>
                                    <select name="mahasiswa[0][dpl_id]" class="form-select form-select-sm dpl-select"
                                            data-searchable data-placeholder="Cari DPL…" required>
                                        <option value="">Pilih DPL</option>
                                        @foreach ($dosen as $d)
                                            <option value="{{ $d->id }}">{{ $d->nama }}</option>
                                        @endforeach
                                        <option value="-1">+ DPL Baru</option>
                                    </select>
                                    <div class="dpl-baru-field mt-1 d-none">
                                        <input type="text" name="mahasiswa[0][dpl_baru_nama]" class="form-control form-control-sm mt-1" placeholder="Nama DPL baru">
                                        <input type="text" name="mahasiswa[0][dpl_baru_nip_niy]" class="form-control form-control-sm mt-1" placeholder="NIP/NIY">
                                        <input type="text" name="mahasiswa[0][dpl_baru_no_hp]" class="form-control form-control-sm mt-1" placeholder="No. HP">
                                    </div>
                                </td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary" id="btnTambahBaris">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Baris Mahasiswa
                </button>
            </div>
        </x-card>

        <div class="d-flex justify-content-end mt-4 gap-2">
            <a href="{{ route('perguruan-tinggi.permohonan.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i> Ajukan Permohonan
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var tabel = document.getElementById('tabelMahasiswa');
            var dosenList = @json($dosen->map(fn ($d) => ['id' => $d->id, 'nama' => $d->nama]));

            // Escape teks user sebelum disisipkan ke innerHTML (cegah XSS).
            var escHtml = function (s) {
                return String(s ?? '').replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            };

            var selectTemplate = function (dosen) {
                var html = '<option value="">Pilih DPL</option>';
                dosen.forEach(function (d) {
                    html += '<option value="' + d.id + '">' + escHtml(d.nama) + '</option>';
                });
                html += '<option value="-1">+ DPL Baru</option>';
                return html;
            };

            function reindex() {
                var rows = tabel.querySelectorAll('tbody tr');
                rows.forEach(function (tr, i) {
                    tr.querySelectorAll('[name^="mahasiswa["]').forEach(function (input) {
                        var name = input.getAttribute('name');
                        name = name.replace(/^mahasiswa\[\d+\]/, 'mahasiswa[' + i + ']');
                        input.setAttribute('name', name);
                    });
                });
            }

            function addRow() {
                var tr = document.createElement('tr');
                var i = tabel.querySelectorAll('tbody tr').length;
                var dosenOptions = selectTemplate(dosenList);
                tr.innerHTML =
                    '<td><input type="text" name="mahasiswa[' + i + '][nim]" class="form-control form-control-sm" required></td>' +
                    '<td><input type="text" name="mahasiswa[' + i + '][nama]" class="form-control form-control-sm" required></td>' +
                    '<td><input type="text" name="mahasiswa[' + i + '][prodi]" class="form-control form-control-sm"></td>' +
                    '<td><input type="text" name="mahasiswa[' + i + '][no_hp]" class="form-control form-control-sm"></td>' +
                    '<td>' +
                        '<select name="mahasiswa[' + i + '][dpl_id]" class="form-select form-select-sm dpl-select" data-searchable data-placeholder="Cari DPL…" required>' + dosenOptions + '</select>' +
                        '<div class="dpl-baru-field mt-1 d-none">' +
                            '<input type="text" name="mahasiswa[' + i + '][dpl_baru_nama]" class="form-control form-control-sm mt-1" placeholder="Nama DPL baru">' +
                            '<input type="text" name="mahasiswa[' + i + '][dpl_baru_nip_niy]" class="form-control form-control-sm mt-1" placeholder="NIP/NIY">' +
                            '<input type="text" name="mahasiswa[' + i + '][dpl_baru_no_hp]" class="form-control form-control-sm mt-1" placeholder="No. HP">' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bi bi-trash"></i></button></td>';
                tabel.querySelector('tbody').appendChild(tr);
                // Baris baru: upgrade select DPL-nya ke Tom Select (searchable)
                // lalu ikat toggle "DPL Baru".
                var selectBaru = tr.querySelector('.dpl-select');
                if (selectBaru && window.SimpulSelect) {
                    window.SimpulSelect.reinit(tr);
                    wireDplBaruToggle(selectBaru);
                    toggleDplBaru(selectBaru);
                }
            }

            // Delegasi event: tambah/ubah/hapus baris.
            document.getElementById('btnTambahBaris').addEventListener('click', addRow);

            tabel.addEventListener('click', function (e) {
                if (e.target.closest('.btn-remove-row')) {
                    var rows = tabel.querySelectorAll('tbody tr');
                    if (rows.length <= 1) return; // minimal 1 baris
                    e.target.closest('tr').remove();
                    reindex();
                }
            });

            // Delegasi native change (fallback bila Tom Select tidak diterapkan
            // — mis. CDN gagal dimuat, select tetap native).
            tabel.addEventListener('change', function (e) {
                var select = e.target.closest('.dpl-select');
                if (!select) return;
                toggleDplBaru(select);
            });

            function toggleDplBaru(select) {
                var field = select.closest('td').querySelector('.dpl-baru-field');
                if (!field) return;
                var isBaru = parseInt(select.value, 10) < 0;
                field.classList.toggle('d-none', !isBaru);
            }

            // Tom Select pada select yang di-upgrade tidak selalu menyalakan
            // event 'change' native → ikat langsung ke instance agar toggle
            // "DPL Baru" tetap bekerja apa pun metodenya.
            function wireDplBaruToggle(select) {
                if (!select || !select.tomselect) return;
                select.tomselect.off('change');
                select.tomselect.on('change', function () { toggleDplBaru(select); });
            }

            function wireAllDpl() {
                tabel.querySelectorAll('.dpl-select').forEach(function (select) {
                    wireDplBaruToggle(select);
                    // Restore state "DPL Baru" bila select sudah punya nilai -1
                    // (saat reload karena error validasi).
                    toggleDplBaru(select);
                });
            }

            // Saat DOM siap, Tom Select sudah diterapkan oleh SimpulSelect.init().
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', wireAllDpl);
            } else {
                wireAllDpl();
            }
        })();
    </script>
@endpush
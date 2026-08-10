@extends('layouts.app')

@section('title', 'Laporan Akhir')

@section('content')
    <x-page-header title="Laporan Akhir"
                   subtitle="Unggah laporan akhir & luaran kegiatan KKN kelompok Anda."
                   icon="bi-file-earmark-richtext" />

    {{-- Form upload --}}
    <div class="row mb-3">
        <div class="col-lg-8 col-xl-7">
            <x-card title="Unggah Laporan & Luaran">
                <form method="POST" action="{{ route('mahasiswa.laporan-akhir.store') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="file_laporan">Laporan Akhir (PDF/DOC) <span class="text-danger">*</span></label>
                        <input type="file" name="file_laporan" id="file_laporan" accept=".pdf,.doc,.docx"
                               class="form-control @error('file_laporan') is-invalid @enderror" required>
                        <div class="form-text">PDF/DOC/DOCX maks 5MB.</div>
                        @error('file_laporan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="file_luaran">Luaran Kegiatan</label>
                        <input type="file" name="file_luaran" id="file_luaran" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                               class="form-control @error('file_luaran') is-invalid @enderror">
                        <div class="form-text">Opsional — dokumen/gambar/arsip luaran, maks 10MB.</div>
                        @error('file_luaran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload me-1"></i> Upload</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Daftar laporan --}}
    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelLaporan">
                <thead>
                    <tr>
                        <th>File Laporan</th>
                        <th>Luaran</th>
                        <th>Diunggah</th>
                        <th>Status</th>
                        <th>Catatan Verifikasi</th>
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
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';
            var url = @json(route('mahasiswa.laporan-akhir.data'));

            $.getJSON(url, function (res) {
                var $tbody = document.querySelector('#tabelLaporan tbody');
                if (!res.data.length) {
                    $tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="bi bi-file-earmark-richtext"></i><h6 class="mt-3">Belum ada laporan</h6><p>Unggah laporan akhir melalui form di atas.</p></div></td></tr>';
                    return;
                }
                var html = '';
                res.data.forEach(function (r) {
                    html += '<tr>'
                        + '<td>' + r.file_laporan + '</td>'
                        + '<td>' + r.file_luaran + '</td>'
                        + '<td>' + r.uploaded_at + '</td>'
                        + '<td>' + r.status + '</td>'
                        + '<td class="text-muted small">' + r.catatan + '</td>'
                        + '</tr>';
                });
                $tbody.innerHTML = html;
            });
        })();
    </script>
@endpush

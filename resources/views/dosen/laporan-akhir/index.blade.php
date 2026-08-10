@extends('layouts.app')

@section('title', 'Verifikasi Laporan Akhir')

@section('content')
    <x-page-header title="Verifikasi Laporan Akhir"
                   subtitle="Tinjau laporan akhir kelompok bimbingan Anda."
                   icon="bi-file-earmark-check" />

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelLaporan">
                <thead>
                    <tr>
                        <th>Kelompok</th>
                        <th>Diunggah</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
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
            var url = @json(route('dosen.laporan-akhir.data'));

            $.getJSON(url, function (res) {
                var $tbody = document.querySelector('#tabelLaporan tbody');
                if (!res.data.length) {
                    $tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="bi bi-file-earmark-check"></i><h6 class="mt-3">Belum ada laporan dari kelompok bimbingan</h6><p>Laporan yang di-upload mahasiswa akan tampil di sini.</p></div></td></tr>';
                    return;
                }
                var html = '';
                res.data.forEach(function (r) {
                    html += '<tr>'
                        + '<td>' + r.kelompok + '</td>'
                        + '<td>' + r.uploaded + '</td>'
                        + '<td>' + r.status + '</td>'
                        + '<td class="text-end">' + r.aksi + '</td>'
                        + '</tr>';
                });
                $tbody.innerHTML = html;
            });
        })();
    </script>
@endpush

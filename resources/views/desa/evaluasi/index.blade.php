@extends('layouts.app')

@section('title', 'Evaluasi Kelompok')

@section('content')
    <x-page-header title="Evaluasi Kelompok Mahasiswa"
                   subtitle="Beri penilaian terhadap kelompok KKN yang bertugas di desa Anda."
                   icon="bi-star" />

    <x-card :bodyClass="'p-0'">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelEvaluasi">
                <thead>
                    <tr>
                        <th>Kelompok</th>
                        <th>PT</th>
                        <th>DPL</th>
                        <th>Status</th>
                        <th class="text-center">Evaluasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
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
            var url = @json(route('desa.evaluasi.data'));

            $.getJSON(url, function (res) {
                var $tbody = document.querySelector('#tabelEvaluasi tbody');
                if (!res.data.length) {
                    $tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="bi bi-star"></i><h6 class="mt-3">Belum ada kelompok untuk dievaluasi</h6><p>Kelompok yang sedang/penah bertugas di desa Anda akan tampil di sini.</p></div></td></tr>';
                    return;
                }
                var html = '';
                res.data.forEach(function (r) {
                    html += '<tr>'
                        + '<td>' + r.kode + '</td>'
                        + '<td>' + r.pt + '</td>'
                        + '<td>' + r.dpl + '</td>'
                        + '<td>' + r.status + '</td>'
                        + '<td class="text-center">' + r.evaluasi + '</td>'
                        + '<td class="text-end">' + r.aksi + '</td>'
                        + '</tr>';
                });
                $tbody.innerHTML = html;
            });
        })();
    </script>
@endpush

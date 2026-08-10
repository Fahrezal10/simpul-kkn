@extends('layouts.app')

@section('title', 'Penutupan Periode KKN')

@section('content')
    <x-page-header title="Penutupan Periode KKN"
                   subtitle="Akhiri seluruh pelaksanaan KKN yang masih berjalan. Kelompok aktif akan berubah status menjadi Selesai."
                   icon="bi-flag-fill" />

    @if ($kelompokAktif->isEmpty())
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="bi bi-info-circle"></i>
            <div>Tidak ada kelompok berstatus <strong>Aktif</strong> saat ini.</div>
        </div>
    @else
        <x-card :bodyClass="'p-0'">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <div>
                    <strong class="fs-5">{{ $kelompokAktif->count() }}</strong>
                    <span class="text-muted"> kelompok aktif akan diselesaikan</span>
                </div>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#tutupModal">
                    <i class="bi bi-flag-fill me-1"></i> Tutup Periode Saat Ini
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Kelompok</th>
                            <th>Perguruan Tinggi</th>
                            <th>Tema</th>
                            <th>Desa Lokasi</th>
                            <th class="text-center">Mahasiswa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelompokAktif as $k)
                            <tr>
                                <td><span class="badge badge-teal">{{ $k->kode_kelompok }}</span></td>
                                <td>{{ $k->permohonanKkn?->perguruanTinggi?->nama_pt ?? '-' }}</td>
                                <td class="text-muted small">{{ $k->tema }}</td>
                                <td class="text-muted small">{{ $k->desa?->nama_desa ?? '-' }}</td>
                                <td class="text-center">{{ $k->jumlah_mahasiswa }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    {{-- Modal konfirmasi --}}
    <div class="modal fade" id="tutupModal" tabindex="-1" aria-labelledby="tutupModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('bapperida.penutupan-periode.store') }}">
                    @csrf
                    <div class="modal-body text-center pt-4">
                        <div class="tutup-modal-icon mx-auto mb-3"><i class="bi bi-flag-fill"></i></div>
                        <h5 class="mb-2">Tutup Periode KKN?</h5>
                        <p class="text-muted mb-0">
                            <strong>{{ $kelompokAktif->count() }}</strong> kelompok aktif akan diubah
                            menjadi status <strong>Selesai</strong>.
                        </p>
                        <p class="text-muted small mt-2 mb-0">
                            Mahasiswa tidak lagi dapat mengisi logbook setelah penutupan.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-flag-fill me-1"></i> Ya, Tutup Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .tutup-modal-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 14px;
            font-size: 1.6rem;
            color: #dc3545;
            background: rgba(220, 53, 69, .1);
        }
    </style>
@endpush
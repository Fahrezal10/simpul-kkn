@extends('layouts.app')

@section('title', 'Review Persetujuan — '.$kelompok->kode_kelompok)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bapperida.approval-final.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Review Persetujuan — '.$kelompok->kode_kelompok"
                :subtitle="'Tema: '.$kelompok->tema.' · DPL: '.($kelompok->dosen->nama ?? '-')"
                :icon="'bi-check2-circle'" />
        </div>
        <x-status-badge :status="$kelompok->status" />
    </div>

    {{-- Info kelompok & desa --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-card title="Info Kelompok" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Perguruan Tinggi</dt>
                    <dd class="col-7">{{ $kelompok->permohonanKkn->perguruanTinggi->nama_pt ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Periode</dt>
                    <dd class="col-7">{{ $kelompok->permohonanKkn->periode ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Jumlah Mahasiswa</dt>
                    <dd class="col-7">{{ $kelompok->jumlah_mahasiswa }}</dd>
                    <dt class="col-5 text-muted">Bidang Keilmuan</dt>
                    <dd class="col-7">{{ $kelompok->bidang_keilmuan }}</dd>
                </dl>
            </x-card>
        </div>
        <div class="col-md-6">
            <x-card title="Lokasi Desa Terpilih" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Desa</dt>
                    <dd class="col-7 fw-semibold">{{ $kelompok->desa->nama_desa ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Kecamatan</dt>
                    <dd class="col-7">{{ $kelompok->desa->kecamatan->nama_kecamatan ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Skor Total</dt>
                    <dd class="col-7">
                        @if ($dipilih = $kelompok->riwayatMatching->firstWhere('status', 'dipilih'))
                            <span class="badge text-bg-info">{{ number_format($dipilih->skor_total, 0) }}</span>
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Hasil verifikasi kecamatan --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <x-card title="Hasil Verifikasi Kecamatan">
                @php $verif = $kelompok->verifikasiKecamatan->last(); @endphp
                @if ($verif)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <x-status-badge :status="$verif->status" />
                        <span class="small text-muted">
                            Diverifikasi oleh {{ $verif->verifier->nama ?? '-' }}
                            — {{ $verif->verified_at?->format('d M Y H:i') }}
                        </span>
                    </div>
                    <p class="mb-0 text-muted small">{{ $verif->catatan ?: 'Tidak ada catatan verifikasi.' }}</p>
                @else
                    <p class="text-muted mb-0">Belum ada hasil verifikasi kecamatan.</p>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Aksi approve/tolak --}}
    @if ($kelompok->status === 'menunggu_persetujuan')
        <div class="row">
            <div class="col-lg-8">
                <x-card title="Keputusan Akhir">
                    <p class="text-muted small mb-3">
                        Menyetujui membuat kelompok <strong>{{ $kelompok->kode_kelompok }}</strong> berstatus
                        <span class="badge badge-teal">Aktif</span> — mahasiswa dapat mulai mengisi logbook.
                        Menolak mengembalikan ke tahap matching untuk pilih desa alternatif.
                    </p>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('bapperida.approval-final.approve', $kelompok) }}"
                              onsubmit="return confirm('Setujui pelaksanaan KKN kelompok {{ addslashes($kelompok->kode_kelompok) }}?');">
                            @csrf
                            <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Setujui & Aktifkan</button>
                        </form>
                        <form method="POST" action="{{ route('bapperida.approval-final.tolak', $kelompok) }}"
                              onsubmit="return confirm('Tolak lokasi ini? Kelompok kembali ke tahap matching.');">
                            @csrf
                            <button class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Tolak Lokasi</button>
                        </form>
                    </div>
                </x-card>
            </div>
        </div>
    @endif
@endsection

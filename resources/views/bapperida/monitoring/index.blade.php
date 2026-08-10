@extends('layouts.app')

@section('title', 'Dashboard Monitoring')

@section('content')
    <x-page-header title="Dashboard Monitoring & Evaluasi"
                   subtitle="Ringkasan pelaksanaan KKN se-Kabupaten Indramayu."
                   icon="bi-graph-up" />

    {{-- Stat card utama --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-building"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['pt'] }}</h5><p class="text-muted small mb-0">PT</p></div>
                </div>
            </x-card>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-amber-subtle text-amber"><i class="bi bi-clipboard-check"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['permohonan'] }}</h5><p class="text-muted small mb-0">Permohonan</p></div>
                </div>
            </x-card>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-people"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['mahasiswa'] }}</h5><p class="text-muted small mb-0">Mahasiswa</p></div>
                </div>
            </x-card>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-people-fill"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['kelompok_aktif'] }}</h5><p class="text-muted small mb-0">Kelompok Aktif</p></div>
                </div>
            </x-card>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-geo-alt"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['desa_aktif'] }}</h5><p class="text-muted small mb-0">Desa Aktif</p></div>
                </div>
            </x-card>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <x-card>
                <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                    <div><h5 class="mb-0 fs-5">{{ $stats['desa'] }}</h5><p class="text-muted small mb-0">Total Desa</p></div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Kelompok per status --}}
        <div class="col-lg-6">
            <x-card title="Distribusi Kelompok per Status">
                <div class="d-flex flex-wrap gap-3">
                    <div class="stat-pill"><span class="badge badge-amber">Menunggu Matching</span><strong class="fs-5">{{ $stats['kelompok']['menunggu_matching'] }}</strong></div>
                    <div class="stat-pill"><span class="badge badge-amber">Verifikasi Kecamatan</span><strong class="fs-5">{{ $stats['kelompok']['menunggu_verifikasi_kecamatan'] }}</strong></div>
                    <div class="stat-pill"><span class="badge badge-amber">Menunggu Persetujuan</span><strong class="fs-5">{{ $stats['kelompok']['menunggu_persetujuan'] }}</strong></div>
                    <div class="stat-pill"><span class="badge badge-teal">Aktif</span><strong class="fs-5">{{ $stats['kelompok']['aktif'] }}</strong></div>
                    <div class="stat-pill"><span class="text-muted">Total</span><strong class="fs-5">{{ $stats['kelompok']['total'] }}</strong></div>
                </div>
            </x-card>
        </div>

        {{-- Kelompok aktif per PT --}}
        <div class="col-lg-6">
            <x-card title="Kelompok Aktif per Perguruan Tinggi">
                @if ($stats['kelompok_per_pt']->isEmpty())
                    <div class="empty-state py-3"><i class="bi bi-building"></i><h6 class="mt-2">Belum ada kelompok aktif</h6></div>
                @else
                    @foreach ($stats['kelompok_per_pt'] as $pt => $jumlah)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                            <span>{{ $pt }}</span>
                            <span class="badge text-bg-info">{{ $jumlah }}</span>
                        </div>
                    @endforeach
                @endif
            </x-card>
        </div>
    </div>

    <div class="row g-3">
        {{-- Evaluasi desa --}}
        <div class="col-lg-6">
            <x-card title="Evaluasi Desa (skala 1–5)">
                @if ($stats['evaluasi_desa']['jumlah'] === 0)
                    <div class="empty-state py-3"><i class="bi bi-star"></i><h6 class="mt-2">Belum ada evaluasi desa</h6></div>
                @else
                    <div class="row text-center g-3">
                        <div class="col-4"><h3 class="mb-0 text-primary">{{ $stats['evaluasi_desa']['kualitas'] }}</h3><small class="text-muted">Kualitas Program</small></div>
                        <div class="col-4"><h3 class="mb-0 text-teal">{{ $stats['evaluasi_desa']['manfaat'] }}</h3><small class="text-muted">Manfaat</small></div>
                        <div class="col-4"><h3 class="mb-0 text-amber">{{ $stats['evaluasi_desa']['kedisiplinan'] }}</h3><small class="text-muted">Kedisiplinan</small></div>
                    </div>
                    <p class="text-muted small mt-3 mb-0">{{ $stats['evaluasi_desa']['jumlah'] }} kelompok telah dievaluasi desa.</p>
                @endif
            </x-card>
        </div>

        {{-- Evaluasi DPL --}}
        <div class="col-lg-6">
            <x-card title="Evaluasi DPL (skala 0–100)">
                @if ($stats['evaluasi_dpl']['jumlah'] === 0)
                    <div class="empty-state py-3"><i class="bi bi-star"></i><h6 class="mt-2">Belum ada evaluasi DPL</h6></div>
                @else
                    <div class="text-center py-2">
                        <h3 class="mb-0 text-primary">{{ $stats['evaluasi_dpl']['rata_rata'] }}</h3>
                        <small class="text-muted">Rata-rata nilai akhir kelompok</small>
                    </div>
                    <p class="text-muted small mt-2 mb-0">{{ $stats['evaluasi_dpl']['jumlah'] }} kelompok telah dievaluasi DPL.</p>
                @endif
            </x-card>
        </div>
    </div>
@endsection

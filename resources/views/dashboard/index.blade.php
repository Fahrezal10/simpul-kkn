@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard {{ $roleLabel ?? '' }}"
                   subtitle="Selamat datang kembali, {{ $user->nama ?? '' }} — {{ $stats['label'] ?? '' }}" />

    {{-- Stat card ringkas --}}
    <div class="row g-3 mb-3">
        {{-- Stat: PT --}}
        @if (array_key_exists('pt', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-building"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['pt'] ?? 0 }}</h5>
                            <p class="text-muted small mb-0">Perguruan Tinggi</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Permohonan --}}
        @if (array_key_exists('permohonan', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-amber-subtle text-amber"><i class="bi bi-clipboard-check"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['permohonan'] }}</h5>
                            <p class="text-muted small mb-0">Permohonan</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Desa --}}
        @if (array_key_exists('desa', $stats) && $stats['desa'] !== null)
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['desa'] }}</h5>
                            <p class="text-muted small mb-0">Desa</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Mahasiswa --}}
        @if (array_key_exists('mahasiswa', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-people"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['mahasiswa'] }}</h5>
                            <p class="text-muted small mb-0">Mahasiswa</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Isu strategis (OPD) --}}
        @if (array_key_exists('isu', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-bullseye"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['isu'] }}</h5>
                            <p class="text-muted small mb-0">Isu Strategis</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Potensi (desa) --}}
        @if (array_key_exists('potensi', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-lightning"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['potensi'] }}</h5>
                            <p class="text-muted small mb-0">Potensi</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif

        {{-- Stat: Kebutuhan (desa) --}}
        @if (array_key_exists('kebutuhan', $stats))
            <div class="col-6 col-md-4 col-lg-3">
                <x-card>
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-amber-subtle text-amber"><i class="bi bi-clipboard-plus"></i></div>
                        <div>
                            <h5 class="mb-0 fs-5">{{ $stats['kebutuhan'] }}</h5>
                            <p class="text-muted small mb-0">Kebutuhan</p>
                        </div>
                    </div>
                </x-card>
            </div>
        @endif
    </div>

    {{-- Kelompok per status --}}
    @if (isset($stats['kelompok']))
        <div class="row g-3">
            <div class="col-12">
                <x-card title="Kelompok KKN per Status">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="stat-pill">
                            <span class="badge badge-amber">Menunggu Matching</span>
                            <strong class="fs-5">{{ $stats['kelompok']['menunggu_matching'] }}</strong>
                        </div>
                        <div class="stat-pill">
                            <span class="badge badge-amber">Verifikasi Kecamatan</span>
                            <strong class="fs-5">{{ $stats['kelompok']['menunggu_verifikasi_kecamatan'] }}</strong>
                        </div>
                        <div class="stat-pill">
                            <span class="badge badge-amber">Menunggu Persetujuan</span>
                            <strong class="fs-5">{{ $stats['kelompok']['menunggu_persetujuan'] }}</strong>
                        </div>
                        <div class="stat-pill">
                            <span class="badge badge-teal">Aktif</span>
                            <strong class="fs-5">{{ $stats['kelompok']['aktif'] }}</strong>
                        </div>
                        <div class="stat-pill">
                            <span class="text-muted">Total</span>
                            <strong class="fs-5">{{ $stats['kelompok']['total'] }}</strong>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    @endif

    {{-- Info placeholder untuk role yang belum punya modul penuh --}}
    @if (empty($stats['kelompok']) && ! array_key_exists('isu', $stats))
        <div class="row g-3 mt-1">
            <div class="col-12">
                <x-empty-state icon="bi-speedometer2"
                               title="Modul Anda sedang disiapkan"
                               message="Dashboard akan menampilkan statistik relevan untuk role Anda setelah modul terkait aktif." />
            </div>
        </div>
    @endif
@endsection

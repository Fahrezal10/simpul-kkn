@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard {{ $roleLabel ?? '' }}" subtitle="Selamat datang kembali, {{ $user->nama ?? '' }}" />

    {{-- §10.8: grid card ringkas di atas --}}
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-3">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                    <div>
                        <h5 class="mb-0 fs-5">Modul Aktif</h5>
                        <p class="text-muted small mb-0">Persiapan</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-map"></i></div>
                    <div>
                        <h5 class="mb-0 fs-5">Wilayah</h5>
                        <p class="text-muted small mb-0">31 kecamatan</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-amber-subtle text-amber"><i class="bi bi-diagram-3"></i></div>
                    <div>
                        <h5 class="mb-0 fs-5">Struktur DB</h5>
                        <p class="text-muted small mb-0">23 tabel</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-person-badge"></i></div>
                    <div>
                        <h5 class="mb-0 fs-5">Role Anda</h5>
                        <p class="text-muted small mb-0">{{ $roleLabel ?? '-' }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- §10.8: tabel/grafik detail di bawah --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <x-data-table
                title="Modul yang akan dikembangkan"
                :columns="['modul', 'status', 'fase']"
                :rows="[
                    ['modul' => 'Perguruan Tinggi', 'status' => 'menunggu', 'fase' => 'Fase 1'],
                    ['modul' => 'Bapperida',        'status' => 'menunggu', 'fase' => 'Fase 1'],
                    ['modul' => 'Kecamatan & Desa', 'status' => 'menunggu', 'fase' => 'Fase 2'],
                    ['modul' => 'Mahasiswa & DPL',  'status' => 'menunggu', 'fase' => 'Fase 3'],
                ]"
                :cell="fn ($key, $row) => $key === 'status'
                    ? view('components.status-badge', ['status' => $row['status']])->render()
                    : e($row[$key] ?? '')"
            />
        </div>
    </div>
@endsection

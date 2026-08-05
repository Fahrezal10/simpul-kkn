@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard {{ $roleLabel ?? '' }}" subtitle="Selamat datang kembali, {{ $user->nama ?? '' }}" />

    <div class="row g-4">
        <div class="col-12 col-md-6 col-xxl-4">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                    <div>
                        <h5 class="mb-0">Modul {{ $roleLabel ?? '' }}</h5>
                        <p class="text-muted small mb-0">Fitur modul akan tersedia pada fase pengembangan berikutnya.</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-teal-subtle text-teal"><i class="bi bi-map"></i></div>
                    <div>
                        <h5 class="mb-0">Struktur Data Siap</h5>
                        <p class="text-muted small mb-0">Basis data SIMPUL-KKN telah dimigrasikan lengkap sesuai ERD.</p>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="col-12 col-md-6 col-xxl-4">
            <x-card>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-amber-subtle text-amber"><i class="bi bi-megaphone"></i></div>
                    <div>
                        <h5 class="mb-0">Tahap Persiapan</h5>
                        <p class="text-muted small mb-0">Autentikasi, role, dan layout utama telah aktif.</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

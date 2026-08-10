@extends('layouts.app')

@section('title', 'Detail Desa — '.$desa->nama_desa)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bapperida.desa.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Detail Desa — '.$desa->nama_desa"
                :subtitle="$desa->kecamatan->nama_kecamatan ?? 'Kecamatan tidak diketahui'"
                :icon="'bi-geo-alt'" />
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bapperida.desa.edit', $desa) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('bapperida.desa.destroy', $desa) }}"
                  onsubmit="return confirm('Hapus desa {{ addslashes($desa->nama_desa) }}? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    {{-- Ringkasan profil --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <x-card title="Identitas Wilayah" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Kode Wilayah</dt>
                    <dd class="col-7">{{ $desa->kode_wilayah }}</dd>
                    <dt class="col-5 text-muted">Kecamatan</dt>
                    <dd class="col-7">{{ $desa->kecamatan->nama_kecamatan ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Penduduk</dt>
                    <dd class="col-7">{{ $desa->jumlah_penduduk ? number_format($desa->jumlah_penduduk, 0, ',', '.') : '-' }}</dd>
                    <dt class="col-5 text-muted">Luas Wilayah</dt>
                    <dd class="col-7">{{ $desa->luas_wilayah !== null ? $desa->luas_wilayah.' km²' : '-' }}</dd>
                    <dt class="col-5 text-muted">Koordinat</dt>
                    <dd class="col-7">{{ $desa->latitude !== null ? number_format($desa->latitude, 4).', '.number_format($desa->longitude, 4) : '-' }}</dd>
                </dl>
            </x-card>
        </div>
        <div class="col-md-8">
            <x-card title="Profil Umum" :bodyClass="'py-3'">
                <p class="mb-0 text-muted small">{{ $desa->profil_umum ?: 'Belum ada profil umum.' }}</p>
            </x-card>
        </div>
    </div>

    {{-- Potensi / Permasalahan / Kebutuhan --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <x-card :title="'Potensi ('.$desa->potensi->count().')'" :bodyClass="'p-0'">
                @forelse ($desa->potensi as $p)
                    <div class="border-bottom p-3">
                        <strong class="small">{{ $p->kategori }}</strong>
                        <div class="small text-muted">{{ $p->deskripsi }}</div>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-lightning"></i><h6 class="mt-2">Belum ada potensi</h6></div>
                @endforelse
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card :title="'Permasalahan ('.$desa->permasalahan->count().')'" :bodyClass="'p-0'">
                @forelse ($desa->permasalahan as $p)
                    <div class="border-bottom p-3">
                        <strong class="small">{{ $p->kategori }}</strong>
                        <div class="small text-muted">{{ $p->deskripsi }}</div>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-exclamation-triangle"></i><h6 class="mt-2">Belum ada permasalahan</h6></div>
                @endforelse
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card :title="'Kebutuhan ('.$desa->kebutuhan->count().')'" :bodyClass="'p-0'">
                @forelse ($desa->kebutuhan as $p)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <strong class="small">{{ $p->kategori }}</strong>
                            @php
                                $label = ucfirst($p->prioritas);
                                $badge = ['tinggi' => 'badge-danger', 'sedang' => 'badge-amber', 'rendah' => 'badge-secondary'][$p->prioritas] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ $label }}</span>
                        </div>
                        <div class="small text-muted">{{ $p->deskripsi }}</div>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-clipboard-plus"></i><h6 class="mt-2">Belum ada kebutuhan</h6></div>
                @endforelse
            </x-card>
        </div>
    </div>
@endsection

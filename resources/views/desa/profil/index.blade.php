@extends('layouts.app')

@section('title', 'Profil Desa')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <x-page-header title="Profil Desa"
                       subtitle="Kelola data profil desa Anda — menjadi parameter Kebutuhan Desa pada Matching System."
                       icon="bi-house-gear" />
        <a href="{{ route('desa.profil.edit') }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Profil
        </a>
    </div>

    {{-- Identitas --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <x-card title="Identitas Wilayah" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Desa</dt>
                    <dd class="col-7 fw-semibold">{{ $desa->nama_desa }}</dd>
                    <dt class="col-5 text-muted">Kecamatan</dt>
                    <dd class="col-7">{{ $desa->kecamatan->nama_kecamatan ?? '-' }}</dd>
                    <dt class="col-5 text-muted">Kode Wilayah</dt>
                    <dd class="col-7">{{ $desa->kode_wilayah }}</dd>
                    <dt class="col-5 text-muted">Penduduk</dt>
                    <dd class="col-7">{{ $desa->jumlah_penduduk ? number_format($desa->jumlah_penduduk, 0, ',', '.') : '-' }}</dd>
                    <dt class="col-5 text-muted">Luas</dt>
                    <dd class="col-7">{{ $desa->luas_wilayah !== null ? $desa->luas_wilayah.' km²' : '-' }}</dd>
                </dl>
            </x-card>
        </div>
        <div class="col-md-8">
            <x-card title="Profil Umum" :bodyClass="'py-3'">
                <p class="mb-0 text-muted">{{ $desa->profil_umum ?: 'Belum ada profil umum.' }}</p>
            </x-card>
        </div>
    </div>

    {{-- Potensi / Permasalahan / Kebutuhan --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <x-card :title="'Potensi ('.$desa->potensi->count().')'" :bodyClass="'p-0'">
                <div class="p-3 border-bottom">
                    <form method="POST" action="{{ route('desa.profil.potensi.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Kategori potensi" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <textarea name="deskripsi" class="form-control form-control-sm" rows="2" placeholder="Deskripsi potensi" required maxlength="2000"></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
                        </div>
                    </form>
                </div>
                @forelse ($desa->potensi as $p)
                    <div class="border-bottom p-3 d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <strong class="small">{{ $p->kategori }}</strong>
                            <div class="small text-muted">{{ $p->deskripsi }}</div>
                        </div>
                        <form method="POST" action="{{ route('desa.profil.potensi.destroy', $p) }}"
                              onsubmit="return confirm('Hapus potensi ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-lightning"></i><h6 class="mt-2">Belum ada potensi</h6></div>
                @endforelse
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card :title="'Permasalahan ('.$desa->permasalahan->count().')'" :bodyClass="'p-0'">
                <div class="p-3 border-bottom">
                    <form method="POST" action="{{ route('desa.profil.permasalahan.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Kategori permasalahan" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <textarea name="deskripsi" class="form-control form-control-sm" rows="2" placeholder="Deskripsi permasalahan" required maxlength="2000"></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
                        </div>
                    </form>
                </div>
                @forelse ($desa->permasalahan as $p)
                    <div class="border-bottom p-3 d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <strong class="small">{{ $p->kategori }}</strong>
                            <div class="small text-muted">{{ $p->deskripsi }}</div>
                        </div>
                        <form method="POST" action="{{ route('desa.profil.permasalahan.destroy', $p) }}"
                              onsubmit="return confirm('Hapus permasalahan ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-exclamation-triangle"></i><h6 class="mt-2">Belum ada permasalahan</h6></div>
                @endforelse
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card :title="'Kebutuhan ('.$desa->kebutuhan->count().')'" :bodyClass="'p-0'">
                <div class="p-3 border-bottom">
                    <form method="POST" action="{{ route('desa.profil.kebutuhan.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Kategori kebutuhan" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <textarea name="deskripsi" class="form-control form-control-sm" rows="2" placeholder="Deskripsi kebutuhan" required maxlength="2000"></textarea>
                        </div>
                        <div class="col-12">
                            <select name="prioritas" class="form-select form-select-sm">
                                <option value="tinggi">Tinggi</option>
                                <option value="sedang" selected>Sedang</option>
                                <option value="rendah">Rendah</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
                        </div>
                    </form>
                </div>
                @forelse ($desa->kebutuhan as $p)
                    <div class="border-bottom p-3 d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <strong class="small">{{ $p->kategori }}</strong>
                                @php
                                    $badge = ['tinggi' => 'badge-danger', 'sedang' => 'badge-amber', 'rendah' => 'badge-secondary'][$p->prioritas] ?? 'badge-secondary';
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst($p->prioritas) }}</span>
                            </div>
                            <div class="small text-muted">{{ $p->deskripsi }}</div>
                        </div>
                        <form method="POST" action="{{ route('desa.profil.kebutuhan.destroy', $p) }}"
                              onsubmit="return confirm('Hapus kebutuhan ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <div class="empty-state py-4"><i class="bi bi-clipboard-plus"></i><h6 class="mt-2">Belum ada kebutuhan</h6></div>
                @endforelse
            </x-card>
        </div>
    </div>
@endsection

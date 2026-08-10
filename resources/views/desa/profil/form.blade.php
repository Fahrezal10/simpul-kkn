@extends('layouts.app')

@section('title', 'Edit Profil Desa')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('desa.profil.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <x-page-header title="Edit Profil Desa" subtitle="{{ $desa->nama_desa }}" icon="bi-house-gear" />
    </div>

    <div class="row justify-content-start">
        <div class="col-lg-9 col-xl-8">
            <x-card title="Data Umum Desa">
                <form method="POST" action="{{ route('desa.profil.update') }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label">Nama Desa</label>
                        <input type="text" class="form-control" value="{{ $desa->nama_desa }}" disabled>
                        <div class="form-text">Nama desa & kecamatan dikelola Bapperida.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" value="{{ $desa->kecamatan->nama_kecamatan ?? '-' }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="jumlah_penduduk">Jumlah Penduduk</label>
                        <input type="number" name="jumlah_penduduk" id="jumlah_penduduk" min="0"
                               class="form-control @error('jumlah_penduduk') is-invalid @enderror"
                               value="{{ old('jumlah_penduduk', $desa->jumlah_penduduk) }}">
                        @error('jumlah_penduduk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="luas_wilayah">Luas Wilayah (km²)</label>
                        <input type="number" step="0.01" min="0" name="luas_wilayah" id="luas_wilayah"
                               class="form-control @error('luas_wilayah') is-invalid @enderror"
                               value="{{ old('luas_wilayah', $desa->luas_wilayah) }}">
                        @error('luas_wilayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="profil_umum">Profil Umum</label>
                        <textarea name="profil_umum" id="profil_umum" rows="5"
                                  class="form-control @error('profil_umum') is-invalid @enderror"
                                  placeholder="Kondisi geografis, sosial-ekonomi, infrastruktur, dll.">{{ old('profil_umum', $desa->profil_umum) }}</textarea>
                        @error('profil_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="{{ route('desa.profil.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', $desa->exists ? 'Edit Desa — '.$desa->nama_desa : 'Tambah Desa')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ $desa->exists ? route('bapperida.desa.show', $desa) : route('bapperida.desa.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <x-page-header
            :title="$desa->exists ? 'Edit Desa — '.$desa->nama_desa : 'Tambah Desa'"
            :subtitle="'Data master wilayah untuk matching & penempatan KKN.'"
            :icon="'bi-geo-alt'" />
    </div>

    <div class="row justify-content-start">
        <div class="col-lg-9 col-xl-8">
            <x-card :title="'Data Umum Desa'">
                <form method="POST"
                      action="{{ $desa->exists ? route('bapperida.desa.update', $desa) : route('bapperida.desa.store') }}"
                      class="row g-3">
                    @csrf
                    @if ($desa->exists)
                        @method('PUT')
                    @endif

                    <div class="col-md-6">
                        <label class="form-label" for="nama_desa">Nama Desa <span class="text-danger">*</span></label>
                        <input type="text" name="nama_desa" id="nama_desa"
                               class="form-control @error('nama_desa') is-invalid @enderror"
                               value="{{ old('nama_desa', $desa->nama_desa) }}" required maxlength="100">
                        @error('nama_desa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="kode_wilayah">Kode Wilayah <span class="text-danger">*</span></label>
                        <input type="text" name="kode_wilayah" id="kode_wilayah"
                               class="form-control @error('kode_wilayah') is-invalid @enderror"
                               value="{{ old('kode_wilayah', $desa->kode_wilayah) }}" required maxlength="20"
                               placeholder="cth: 3201010010">
                        @error('kode_wilayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="kecamatan_id">Kecamatan <span class="text-danger">*</span></label>
                        <select name="kecamatan_id" id="kecamatan_id"
                                class="form-select @error('kecamatan_id') is-invalid @enderror" required
                                data-searchable data-placeholder="Cari kecamatan…">
                            <option value="">— Pilih Kecamatan —</option>
                            @foreach ($kecamatans as $kec)
                                <option value="{{ $kec->id }}"
                                    {{ old('kecamatan_id', $desa->kecamatan_id) == $kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama_kecamatan }}
                                </option>
                            @endforeach
                        </select>
                        @error('kecamatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="jumlah_penduduk">Jumlah Penduduk</label>
                        <input type="number" name="jumlah_penduduk" id="jumlah_penduduk" min="0"
                               class="form-control @error('jumlah_penduduk') is-invalid @enderror"
                               value="{{ old('jumlah_penduduk', $desa->jumlah_penduduk) }}">
                        @error('jumlah_penduduk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="luas_wilayah">Luas Wilayah (km²)</label>
                        <input type="number" step="0.01" min="0" name="luas_wilayah" id="luas_wilayah"
                               class="form-control @error('luas_wilayah') is-invalid @enderror"
                               value="{{ old('luas_wilayah', $desa->luas_wilayah) }}">
                        @error('luas_wilayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="latitude">Latitude</label>
                        <input type="number" step="any" name="latitude" id="latitude"
                               class="form-control @error('latitude') is-invalid @enderror"
                               value="{{ old('latitude', $desa->latitude) }}" placeholder="cth: -6.3278">
                        @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="longitude">Longitude</label>
                        <input type="number" step="any" name="longitude" id="longitude"
                               class="form-control @error('longitude') is-invalid @enderror"
                               value="{{ old('longitude', $desa->longitude) }}" placeholder="cth: 108.3201">
                        @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="profil_umum">Profil Umum</label>
                        <textarea name="profil_umum" id="profil_umum" rows="4"
                                  class="form-control @error('profil_umum') is-invalid @enderror"
                                  placeholder="Deskripsi singkat kondisi geografis, sosial-ekonomi desa, dll.">{{ old('profil_umum', $desa->profil_umum) }}</textarea>
                        @error('profil_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="{{ $desa->exists ? route('bapperida.desa.show', $desa) : route('bapperida.desa.index') }}"
                           class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ $desa->exists ? 'Simpan Perubahan' : 'Tambah Desa' }}
                        </button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

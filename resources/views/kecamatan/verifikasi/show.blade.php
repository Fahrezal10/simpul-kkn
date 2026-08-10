@extends('layouts.app')

@section('title', 'Verifikasi Kelompok — '.$kelompok->kode_kelompok)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('kecamatan.verifikasi.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Verifikasi Kelompok — '.$kelompok->kode_kelompok"
                :subtitle="'Tema: '.$kelompok->tema.' · DPL: '.($kelompok->dosen->nama ?? '-')"
                :icon="'bi-clipboard-check'" />
        </div>
        <x-status-badge :status="$kelompok->status" />
    </div>

    {{-- Info kelompok & desa terpilih --}}
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
            <x-card title="Desa Terpilih (Hasil Matching)" :bodyClass="'py-3'">
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
                    <dt class="col-5 text-muted">Profil Desa</dt>
                    <dd class="col-7">{{ $kelompok->desa->profil_umum ? \Illuminate\Support\Str::limit($kelompok->desa->profil_umum, 90) : '-' }}</dd>
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Form verifikasi --}}
    <div class="row">
        <div class="col-lg-8">
            <x-card title="Verifikasi Kesiapan Desa">
                @if ($kelompok->status === 'menunggu_verifikasi_kecamatan')
                    <p class="text-muted small mb-3">
                        Pastikan desa <strong>{{ $kelompok->desa->nama_desa ?? '-' }}</strong> benar-benar siap menerima KKN
                        (koordinasi dengan aparat desa bila perlu) sebelum memberi status.
                    </p>
                    <form method="POST" action="{{ route('kecamatan.verifikasi.store', $kelompok) }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Keputusan Verifikasi <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statSiap" value="siap" required>
                                    <label class="form-check-label" for="statSiap">
                                        <span class="badge badge-teal">Siap</span> — desa siap menerima KKN
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statTidak" value="tidak_siap">
                                    <label class="form-check-label" for="statTidak">
                                        <span class="badge badge-danger">Tidak Siap</span> — desa belum siap, Bapperida pilih alternatif
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="catatan">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3"
                                      class="form-control @error('catatan') is-invalid @enderror"
                                      maxlength="1000" placeholder="Catatan verifikasi (kondisi desa, kesiapan fasilitas, dll.)">{{ old('catatan') }}</textarea>
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Verifikasi</button>
                        </div>
                    </form>
                @else
                    @php $verif = $kelompok->verifikasiKecamatan->last(); @endphp
                    <div class="alert alert-{{ $kelompok->status === 'menunggu_persetujuan' ? 'success' : 'info' }} mb-0">
                        <strong>Kelompok sudah diverifikasi.</strong> Status saat ini:
                        <x-status-badge :status="$kelompok->status" />
                        @if ($verif)
                            <div class="mt-2 small text-muted">
                                Hasil: <x-status-badge :status="$verif->status" />
                                @if ($verif->catatan)<br>Catatan: {{ $verif->catatan }}@endif
                                <br>Diverifikasi: {{ $verif->verifier->nama ?? '-' }} — {{ $verif->verified_at?->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection

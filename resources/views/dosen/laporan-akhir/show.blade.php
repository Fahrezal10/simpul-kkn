@extends('layouts.app')

@section('title', 'Review Laporan Akhir')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dosen.laporan-akhir.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Review Laporan — '.$laporan->kelompokKkn->kode_kelompok"
                :subtitle="'Diunggah '.($laporan->uploaded_at?->format('d M Y H:i')).' oleh '.($laporan->uploader->nama ?? '-')"
                :icon="'bi-file-earmark-check'" />
        </div>
        <x-status-badge :status="$laporan->status" />
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-card title="Dokumen Laporan" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-4 text-muted">Kelompok</dt>
                    <dd class="col-8">{{ $laporan->kelompokKkn->kode_kelompok }}</dd>
                    <dt class="col-4 text-muted">Laporan</dt>
                    <dd class="col-8"><a href="{{ asset('storage/'.$laporan->file_laporan) }}" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Unduh Laporan</a></dd>
                    @if ($laporan->file_luaran)
                        <dt class="col-4 text-muted">Luaran</dt>
                        <dd class="col-8"><a href="{{ asset('storage/'.$laporan->file_luaran) }}" target="_blank"><i class="bi bi-paperclip me-1"></i>Unduh Luaran</a></dd>
                    @endif
                    @if ($laporan->catatan_verifikasi)
                        <dt class="col-4 text-muted">Catatan</dt>
                        <dd class="col-8 text-danger">{{ $laporan->catatan_verifikasi }}</dd>
                    @endif
                </dl>
            </x-card>
        </div>
        <div class="col-lg-5">
            <x-card title="Verifikasi">
                @if ($laporan->status === 'menunggu')
                    <div class="d-flex flex-column gap-3">
                        <form method="POST" action="{{ route('dosen.laporan-akhir.approve', $laporan) }}"
                              onsubmit="return confirm('Setujui laporan ini?');">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('dosen.laporan-akhir.revisi', $laporan) }}" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label" for="catatan_verifikasi">Catatan Revisi</label>
                                <textarea name="catatan_verifikasi" id="catatan_verifikasi" rows="3"
                                          class="form-control @error('catatan_verifikasi') is-invalid @enderror"
                                          required maxlength="1000" placeholder="Alasan / yang perlu diperbaiki">{{ old('catatan_verifikasi') }}</textarea>
                                @error('catatan_verifikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Minta Revisi</button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="text-muted mb-0">
                        Laporan berstatus <x-status-badge :status="$laporan->status" />.
                        @if ($laporan->verifier) Verifikasi oleh {{ $laporan->verifier->nama }} ({{ $laporan->verified_at?->format('d M Y H:i') }}).@endif
                    </p>
                @endif
            </x-card>
        </div>
    </div>
@endsection

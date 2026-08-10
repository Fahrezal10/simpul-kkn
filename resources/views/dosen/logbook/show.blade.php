@extends('layouts.app')

@section('title', 'Review Logbook')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dosen.logbook.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Review Logbook — '.$logbook->mahasiswa->nama"
                :subtitle="$logbook->kelompokKkn->kode_kelompok.' · '.$logbook->tanggal->format('d M Y')"
                :icon="'bi-journal-text'" />
        </div>
        <x-status-badge :status="$logbook->status" />
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-card title="Detail Kegiatan" :bodyClass="'py-3'">
                <dl class="row mb-0 small">
                    <dt class="col-4 text-muted">Tanggal</dt>
                    <dd class="col-8">{{ $logbook->tanggal->format('d M Y') }}</dd>
                    <dt class="col-4 text-muted">Mahasiswa</dt>
                    <dd class="col-8">{{ $logbook->mahasiswa->nama }} ({{ $logbook->mahasiswa->nim }})</dd>
                    <dt class="col-4 text-muted">Kelompok</dt>
                    <dd class="col-8">{{ $logbook->kelompokKkn->kode_kelompok }}</dd>
                    <dt class="col-4 text-muted">Deskripsi</dt>
                    <dd class="col-8">{{ $logbook->deskripsi_kegiatan }}</dd>
                    @if ($logbook->foto)
                        <dt class="col-4 text-muted">Foto</dt>
                        <dd class="col-8"><a href="{{ route('file.download', ['jenis' => 'logbook', 'path' => $logbook->foto]) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-image me-1"></i>Lihat Foto</a></dd>
                    @endif
                    @if ($logbook->catatan_dpl)
                        <dt class="col-4 text-muted">Catatan DPL</dt>
                        <dd class="col-8 text-danger">{{ $logbook->catatan_dpl }}</dd>
                    @endif
                </dl>
            </x-card>
        </div>
        <div class="col-lg-5">
            <x-card title="Keputusan">
                @if ($logbook->status === 'menunggu')
                    <div class="d-flex flex-column gap-3">
                        <form method="POST" action="{{ route('dosen.logbook.approve', $logbook) }}"
                              onsubmit="return confirm('Setujui logbook ini?');">
                            @csrf
                            <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('dosen.logbook.revisi', $logbook) }}" class="row g-2">
                            @csrf
                            <div class="col-12">
                                <label class="form-label" for="catatan_dpl">Catatan Revisi</label>
                                <textarea name="catatan_dpl" id="catatan_dpl" rows="3"
                                          class="form-control @error('catatan_dpl') is-invalid @enderror"
                                          required maxlength="1000" placeholder="Alasan penolakan / yang perlu diperbaiki">{{ old('catatan_dpl') }}</textarea>
                                @error('catatan_dpl')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Minta Revisi</button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="text-muted mb-0">Logbook ini sudah berstatus <x-status-badge :status="$logbook->status" />.</p>
                @endif
            </x-card>
        </div>
    </div>
@endsection

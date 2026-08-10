@extends('layouts.app')

@section('title', 'Evaluasi — '.$kelompok->kode_kelompok)

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('dosen.evaluasi.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <x-page-header
            :title="'Evaluasi Kelompok — '.$kelompok->kode_kelompok"
            :subtitle="'PT: '.($kelompok->permohonanKkn->perguruanTinggi->nama_pt ?? '-').' · Tema: '.$kelompok->tema"
            :icon="'bi-star'" />
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-card title="Penilaian DPL (0–100)">
                @if ($evaluasi)
                    <div class="alert alert-success">
                        <strong>Nilai:</strong> {{ $evaluasi->nilai }}
                        @if ($evaluasi->catatan)<div class="small mt-1">{{ $evaluasi->catatan }}</div>@endif
                        <div class="mt-2"><a href="#" onclick="event.preventDefault(); document.getElementById('formEval').style.display='block';" class="small">Ubah penilaian</a></div>
                    </div>
                @endif

                <form method="POST" action="{{ route('dosen.evaluasi.store', $kelompok) }}"
                      id="formEval" class="row g-3 {{ $evaluasi ? 'd-none' : '' }}">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label" for="nilai">Nilai Akhir (0–100)</label>
                        <input type="number" name="nilai" id="nilai" min="0" max="100"
                               class="form-control" value="{{ $evaluasi?->nilai }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="catatan">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control" maxlength="1000"
                                  placeholder="Catatan evaluasi kinerja kelompok">{{ $evaluasi?->catatan }}</textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Evaluasi</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

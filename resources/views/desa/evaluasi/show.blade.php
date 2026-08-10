@extends('layouts.app')

@section('title', 'Evaluasi — '.$kelompok->kode_kelompok)

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('desa.evaluasi.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <x-page-header
            :title="'Evaluasi Kelompok — '.$kelompok->kode_kelompok"
            :subtitle="'PT: '.($kelompok->permohonanKkn->perguruanTinggi->nama_pt ?? '-').' · DPL: '.($kelompok->dosen->nama ?? '-')"
            :icon="'bi-star'" />
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-card title="Penilaian Desa (skala 1–5)">
                @if ($evaluasi)
                    <div class="alert alert-success">
                        <strong>Sudah dievaluasi.</strong> Rata-rata:
                        {{ round(($evaluasi->skor_kualitas_program + $evaluasi->skor_manfaat + $evaluasi->skor_kedisiplinan) / 3, 1) }}
                        @if ($evaluasi->catatan)<div class="small mt-1">{{ $evaluasi->catatan }}</div>@endif
                        <div class="mt-2"><a href="#" onclick="event.preventDefault(); document.getElementById('formEval').style.display='block';" class="small">Ubah penilaian</a></div>
                    </div>
                @endif

                <form method="POST" action="{{ route('desa.evaluasi.store', $kelompok) }}"
                      id="formEval" class="row g-3 {{ $evaluasi ? 'd-none' : '' }}">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">Kualitas Program</label>
                        <select name="skor_kualitas_program" class="form-select" required>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $evaluasi && $evaluasi->skor_kualitas_program == $i ? 'selected' : '' }}>{{ $i }} {{ $i === 1 ? '(Buruk)' : ($i === 5 ? '(Sangat Baik)' : '') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Manfaat Bagi Desa</label>
                        <select name="skor_manfaat" class="form-select" required>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $evaluasi && $evaluasi->skor_manfaat == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kedisiplinan</label>
                        <select name="skor_kedisiplinan" class="form-select" required>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $evaluasi && $evaluasi->skor_kedisiplinan == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="catatan">Catatan</label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control" maxlength="1000"
                                  placeholder="Kesan/saran untuk kelompok">{{ $evaluasi?->catatan }}</textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan Evaluasi</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection

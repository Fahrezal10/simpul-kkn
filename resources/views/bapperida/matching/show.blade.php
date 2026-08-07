@extends('layouts.app')

@section('title', 'Ranking Matching — '.$kelompok->kode_kelompok)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <a href="{{ route('bapperida.matching.index') }}" class="btn btn-sm btn-light mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <x-page-header
                :title="'Ranking Matching — '.$kelompok->kode_kelompok"
                :subtitle="'Tema: '.$kelompok->tema.' · DPL: '.($kelompok->dosen->nama ?? '-').' · '.($kelompok->permohonanKkn->perguruanTinggi->nama_pt ?? '-')"
                :icon="'bi-list-ol'" />
        </div>
        <div class="d-flex align-items-center gap-2">
            <x-status-badge :status="$kelompok->status" />
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Info skor & aksi --}}
    <x-card :title="'Skor Matching'" :bodyClass="'py-3'">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($kelompok->status === 'menunggu_matching')
                <form method="POST" action="{{ route('bapperida.matching.run', $kelompok) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-primary"><i class="bi bi-magic me-1"></i> Jalankan Matching</button>
                </form>
                <span class="text-muted small">Belum ada hasil — klik tombol di samping untuk menilai seluruh desa.</span>
            @elseif ($kelompok->riwayatMatching->isNotEmpty())
                <span class="text-muted small">
                    <strong>{{ $kelompok->riwayatMatching->count() }}</strong> desa dinilai.
                    @if ($dipilih = $kelompok->riwayatMatching->firstWhere('status', 'dipilih'))
                        <x-status-badge :status="'dipilih'" /> Lokasi terpilih: <strong>{{ $dipilih->desa->nama_desa }}</strong>
                    @endif
                </span>
            @else
                <span class="text-muted small">Belum ada hasil.</span>
            @endif
        </div>
    </x-card>

    {{-- Tabel ranking --}}
    <x-card :bodyClass="'p-0'">
        <div class="p-3 border-bottom">
            <h6 class="mb-1">Rekomendasi desa (diurutkan skor total terbesar)</h6>
            <small class="text-muted">Berat: Tema 30% · Bidang 25% · Prioritas 25% · Kebutuhan 20%. Urutan disusun oleh sistem; Bapperida dapat memilih lokasi final (override).</small>
        </div>

        @if ($kelompok->riwayatMatching->isEmpty())
            <div class="empty-state p-5">
                <i class="bi bi-inbox"></i>
                <h6 class="mt-3">Belum ada data ranking</h6>
                <p>Klik "Jalankan Matching" untuk menghitung rekomendasi desa.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Desa</th>
                            <th class="text-center">Tema (30%)</th>
                            <th class="text-center">Bidang (25%)</th>
                            <th class="text-center">Prioritas (25%)</th>
                            <th class="text-center">Kebutuhan (20%)</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Flag</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelompok->riwayatMatching as $i => $r)
                            <tr @if($r->status === 'dipilih') class="table-success" @endif>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $r->desa->nama_desa }}</strong>
                                    <div class="small text-muted">{{ $r->desa->kecamatan->nama_kecamatan ?? '-' }}</div>
                                </td>
                                <td class="text-center">{{ number_format($r->skor_tema, 0) }}</td>
                                <td class="text-center">{{ number_format($r->skor_bidang, 0) }}</td>
                                <td class="text-center">{{ number_format($r->skor_prioritas, 0) }}</td>
                                <td class="text-center">{{ number_format($r->skor_kebutuhan, 0) }}</td>
                                <td class="text-center fw-bold">{{ number_format($r->skor_total, 0) }}</td>
                                <td class="text-center">
                                    @if($r->flag_tumpang_tindih)
                                        <span class="badge text-bg-warning" title="Tema serupa sudah diterapkan di desa ini dari kelompok lain">Tumpang tindih</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($r->status === 'dipilih')
                                        <form method="POST" action="{{ route('bapperida.matching.batal-pilih', $kelompok) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i> Batal pilih</button>
                                        </form>
                                    @elseif ($kelompok->status === 'menunggu_verifikasi_kecamatan' || $kelompok->status === 'menunggu_persetujuan')
                                        <form method="POST" action="{{ route('bapperida.matching.override', $kelompok) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="desa_id" value="{{ $r->desa_id }}">
                                            <button class="btn btn-sm btn-primary"><i class="bi bi-check2-circle me-1"></i> Pilih</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
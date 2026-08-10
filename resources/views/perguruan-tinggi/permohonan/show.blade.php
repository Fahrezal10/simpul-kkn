@extends('layouts.app')

@section('title', 'Detail Permohonan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <x-page-header title="Detail Permohonan" subtitle="Status terkini dan data peserta permohonan KKN." icon="bi-journal-text" />
        <a href="{{ route('perguruan-tinggi.permohonan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <x-card title="Info Permohonan">
                <table class="table table-sm table-borderless detail-table mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted">Periode</th>
                            <td class="fw-semibold">{{ $permohonan->periode }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tanggal Pelaksanaan</th>
                            <td>{{ $permohonan->tanggal_mulai?->format('d M Y') }} – {{ $permohonan->tanggal_selesai?->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td><x-status-badge :status="$permohonan->status" /></td>
                        </tr>
                        @if ($permohonan->catatan_verifikasi)
                            <tr>
                                <th class="text-muted">Catatan Verifikasi</th>
                                <td class="text-danger">{{ $permohonan->catatan_verifikasi }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="text-muted">Surat Permohonan</th>
                            <td>
                                @if ($permohonan->file_surat_permohonan)
                                    <a href="{{ route('file.download', ['jenis' => 'permohonan', 'path' => $permohonan->file_surat_permohonan]) }}" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Proposal</th>
                            <td>
                                @if ($permohonan->file_proposal)
                                    <a href="{{ route('file.download', ['jenis' => 'permohonan', 'path' => $permohonan->file_proposal]) }}" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat</a>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </x-card>
        </div>

        <div class="col-lg-7">
            <x-card title="Kelompok & Peserta">
                @forelse ($permohonan->kelompokKkn as $kelompok)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-1"></i>{{ $kelompok->kode_kelompok }}
                                <span class="badge text-bg-secondary">{{ $kelompok->jumlah_mahasiswa }} mahasiswa</span>
                            </h6>
                            <small class="text-muted">DPL: {{ $kelompok->dosen->nama ?? '-' }}</small>
                        </div>
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Prodi</th>
                                    <th>No. HP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelompok->mahasiswa as $mhs)
                                    <tr>
                                        <td>{{ $mhs->nim }}</td>
                                        <td>{{ $mhs->nama }}</td>
                                        <td>{{ $mhs->prodi ?? '-' }}</td>
                                        <td>{{ $mhs->no_hp ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted">Belum ada mahasiswa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @empty
                    <x-empty-state title="Belum ada kelompok" />
                @endforelse
            </x-card>
        </div>
    </div>
@endsection
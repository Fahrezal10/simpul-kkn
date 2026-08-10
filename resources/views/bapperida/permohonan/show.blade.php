@extends('layouts.app')

@section('title', 'Review Permohonan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <x-page-header title="Review Permohonan"
                       subtitle="Periksa kelengkapan dokumen dan data sebelum verifikasi."
                       icon="bi-clipboard-check" />
        <a href="{{ route('bapperida.permohonan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <x-card title="Info Permohonan">
                <table class="table table-sm table-borderless detail-table mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted">Perguruan Tinggi</th>
                            <td class="fw-semibold">{{ $permohonan->perguruanTinggi->nama_pt ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Periode</th>
                            <td>{{ $permohonan->periode }}</td>
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
                                    <a href="{{ route('file.download', ['jenis' => 'permohonan', 'path' => $permohonan->file_surat_permohonan]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Surat
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Proposal</th>
                            <td>
                                @if ($permohonan->file_proposal)
                                    <a href="{{ route('file.download', ['jenis' => 'permohonan', 'path' => $permohonan->file_proposal]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Proposal
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kelompok->mahasiswa as $mhs)
                                    <tr>
                                        <td>{{ $mhs->nim }}</td>
                                        <td>{{ $mhs->nama }}</td>
                                        <td>{{ $mhs->prodi ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">Belum ada mahasiswa.</td></tr>
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

    {{-- Aksi verifikasi --}}
    @if ($permohonan->status === 'diajukan')
        <div class="row g-3 mt-1">
            <div class="col-lg-5">
                <x-card title="Keputusan Verifikasi">
                    <form method="POST" action="{{ route('bapperida.permohonan.verify', $permohonan) }}" class="mb-3">
                        @csrf
                        <p class="text-muted small mb-2">Lengkapi jika data dan dokumen sudah sesuai.</p>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Verifikasi Permohonan
                        </button>
                    </form>
                    <hr>
                    <form method="POST" action="{{ route('bapperida.permohonan.reject', $permohonan) }}">
                        @csrf
                        <label for="catatan_verifikasi" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_verifikasi" id="catatan_verifikasi" rows="2"
                                  class="form-control mb-2 @error('catatan_verifikasi') is-invalid @enderror"
                                  placeholder="Catatan perbaikan untuk PT"></textarea>
                        @error('catatan_verifikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i> Tolak Permohonan
                        </button>
                    </form>
                </x-card>
            </div>
        </div>
    @endif
@endsection